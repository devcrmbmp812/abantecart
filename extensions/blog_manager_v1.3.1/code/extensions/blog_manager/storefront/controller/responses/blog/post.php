<?php 
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' )) {
	header ( 'Location: static_pages/' );
}
class ControllerResponsesBlogPost extends AController {
	public $error = array();
	public $post = array();
	
	public function main() {
		
		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);
			
		$this->loadModel('blog/blog');
		$this->loadLanguage('blog/blog');
		
		$blog_category_id = $this->request->get['blog_category_id'];
		$blog_entry_id = $this->request->get['blog_entry_id'];
	
		$sanitize = $this->model_blog_blog->getblog_config('sanitize');
		$whitelist_tags = $this->model_blog_blog->getblog_config('whitelist_tags');
		
		$filter_bad_words = $this->model_blog_blog->getblog_config('filter_bad_words');
		$bad_words = $this->model_blog_blog->getblog_config('bad_words');
		
		$restrict = $this->model_blog_blog->getblog_config('restrict');
		if($restrict) {
			if(!$this->session->data['blog_user_name'] || !$this->customer->isLogged()) {
				$error = $this->language->get('error_not_logged_in');
			}
		}
		
		if ($whitelist_tags) {
			$html_tags = $this->prepareTags($whitelist_tags);
		}
		
		foreach($this->request->post as $key => $value) {
			$this->post[$key] = $value;	
		}
			
		if($this->post['username']) {
			if ($sanitize) {
				$this->post['username'] = html_entity_decode($this->post['username']);
				$this->cleanElements($this->post['username']);
				$this->post['username'] = strip_tags($this->post['username']);
				$this->post['username'] = $this->cleanUsername($this->post['username']);
				$this->post['username'] = filter_var(trim($this->post['username']), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
				if(!$this->post['username']) {
					$error = $this->language->get('error_post_data');
				}
			}
		}
		
		if($this->post['email']) {
			if ($sanitize) {
				$this->post['email'] = html_entity_decode($this->post['email']);
				$this->cleanElements($this->post['email']);
				$this->post['email'] = strip_tags($this->post['email']);
				$this->post['email'] = filter_var(trim($this->post['email']), FILTER_SANITIZE_EMAIL);
			}
			if(!filter_var(trim($this->post['email']), FILTER_VALIDATE_EMAIL)) {		
				$error = $this->language->get('error_post_data');
			}
		}
		if($this->post['site_url']) {
			if ($sanitize) {
				$this->post['site_url'] = html_entity_decode($this->post['site_url']);
				$this->post['site_url'] = $this->cleanElements($this->post['site_url']);
				$this->post['site_url'] = strip_tags($this->post['site_url']);
				$this->post['site_url'] = filter_var(trim($this->post['site_url']), FILTER_SANITIZE_URL);
			}
			if(!filter_var(trim($this->post['site_url']), FILTER_VALIDATE_URL)) {		
				$error = $this->language->get('error_post_data');
			}
		}

		if($this->post['comment_detail']) {
			if ($sanitize) {
				$this->post['comment_detail'] = html_entity_decode($this->post['comment_detail']);
				$this->cleanElements($this->post['comment_detail']);
				$this->post['comment_detail'] = preg_replace('/\n(\s*\n)+/', '</p><p>', $this->post['comment_detail']);
				$this->post['comment_detail'] = strip_tags($this->post['comment_detail'], $html_tags);
				$this->post['comment_detail'] = htmlspecialchars(trim($this->post['comment_detail']), ENT_COMPAT, 'UTF-8');
				if($filter_bad_words && !empty($bad_words)) {
					$this->post['comment_detail'] = $this->filterComment($this->post['comment_detail']);	
				}
				if(!$this->post['comment_detail']) {
					$error = $this->language->get('error_post_data');
				}
			}
		}

		if (!$error) {
      		//post to db
			$result = $this->model_blog_blog->postComment($this->post);
			$this->post['blog_comment_id'] = $result['blog_comment_id'];
			$this->post['primary_comment_id'] = $result['primary_comment_id']; 
			if ($result['approved'] == 1) {
				$this->session->data['success'] = $this->language->get('success_approved');	
			}else{
				$this->session->data['success'] = $this->language->get('success_not_approved');
			}
			
			$email_to_be_approved = $this->model_blog_blog->getblog_config('email_to_be_approved');
			$email_all = $this->model_blog_blog->getblog_config('email_all');
            $blog_access = $this->model_blog_blog->getblog_config('blog_access');
			$entry_title = $this->model_blog_blog->getEntryTitle($blog_entry_id);
			$notification_all = $this->model_blog_blog->getblog_config('notification_all');
			$notification_reply = $this->model_blog_blog->getblog_config('notification_on_reply');
			$blog_title = $this->model_blog_blog->getblog_config('title');
			$owner_email = $this->model_blog_blog->getblog_config('owner_email');
			
			if ($email_all == 1 ) {
				
				$subject = sprintf($this->language->get('text_mail_subject'), $blog_title);
				
				if($this->post['parent_id'] != 0) {
					$parent_author = $this->model_blog_blog->getParentAuthor($this->post['parent_id']);
					$message = sprintf($this->language->get('text_new_reply'), $author , $entry_title) . "\n\n";	
				}else{
					$message = sprintf($this->language->get('text_new_comment'), $entry_title) . "\n\n";
				}
				
				$message .= $this->language->get('text_comment').': '.$this->post['comment_detail'] . "\n\n";
				
				$message .= $this->language->get('text_approval_status').' ' . ($result['approved'] ? $this->language->get('text_approved') : $this->language->get('text_not_approved')). "\n\n";
				$message .= $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' .$blog_entry_id).'#comment-'.$result['blog_comment_id']. "\n\n";
				
				$mail = new AMail( $this->config );
				$mail->setTo($owner_email);
				$mail->setFrom($this->config->get('store_main_email'));
				$mail->setSender($blog_title);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
				
			}
				
			if($email_to_be_approved == 1 && $result['approved'] == 0){
				
				$subject = sprintf($this->language->get('text_mail_approve_subject'), $blog_title);
				
				if($this->post['parent_id'] != 0) {
					$parent_author = $this->model_blog_blog->getParentAuthor($this->post['parent_id']);
					$message = sprintf($this->language->get('text_new_reply'), $this->post['username'] , $entry_title) . "\n\n";	
				}else{
					$message = sprintf($this->language->get('text_new_comment'), $entry_title) . "\n\n";
				}
				
				$message .= $this->language->get('text_comment').': '.$this->post['comment_detail'] . "\n\n";
				
				$message .= $this->language->get('text_approval_status').' ' . ($result['approved'] ? $this->language->get('text_approved') : $this->language->get('text_not_approved')). "\n\n";
				
				if($result['approved'] == 0) {
					//fix to make link to approve
					
					$message .= $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' .$blog_entry_id).'#comment-'.$result['blog_comment_id']. "\n\n";
				}
				
				$mail = new AMail( $this->config );
				$mail->setTo($owner_email);
				$mail->setFrom($this->config->get('store_main_email'));
				$mail->setSender($blog_title);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
			
			
			}
			
			if($notification_all && $result['approved']) { 
				$all_notices = $this->model_blog_blog->getAllNotifications($this->post['blog_entry_id']);
				if($all_notices) {
					foreach($all_notices as $notices) {
						$subject = sprintf($this->language->get('text_mail_subject'), $blog_title);
						$message = $notices['user_name'].',';
						$message .= "\n\n";
						$message .= sprintf($this->language->get('text_new_comment'), $entry_title) . "\n\n";
						$message .= $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' .$blog_entry_id).'#comment-'.$result['blog_comment_id']. "\n\n\n";
						if($blog_access == 'restrict') {
							$message .= $this->language->get('text_cancel_notification')."\n\n";
						}
						$message .= $blog_title."\n\n";
					
						$mail = new AMail( $this->config );
						$mail->setTo($notices['email']);
						$mail->setFrom($owner_email);
						$mail->setSender($blog_title);
						$mail->setSubject($subject);
						$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
						$mail->send();
						
					}
				}
			}
				
			if($notification_reply && $result['approved']) {
				$reply_notices = $this->model_blog_blog->getReplyNotifications($this->post['blog_entry_id'], $this->post['primary_comment_id']);
				if($reply_notices) {
					foreach($reply_notices as $notices) {
						$subject = sprintf($this->language->get('text_subject_new_reply'), $blog_title);
						$message = $notices['user_name'].',';
						$message .= "\n\n";
						$message .= sprintf($this->language->get('text_new_user_reply'), $entry_title) . "\n\n";
						$message .= $this->blog_html->getBLOGSEOURL('blog/entry','&blog_entry_id=' .$blog_entry_id).'#comment-'.$result['blog_comment_id']. "\n\n\n";
						if($blog_access == 'restrict') {
							$message .= $this->language->get('text_cancel_notification').'\n\n';
						}
						$message .= $blog_title."\n\n";
					
						$mail = new AMail( $this->config );
						$mail->setTo($notices['email']);
						$mail->setFrom($owner_email);
						$mail->setSender($blog_title);
						$mail->setSubject($subject);
						$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
						$mail->send();
						
					}
				}
			}
			
			if($this->post['notification_all'] || $this->post['notification_reply']) {
				$this->model_blog_blog->addNotification($this->post);
			}

			$this->redirect($this->blog_html->getBLOGSEOURL('blog/entry','&bcat=' . $blog_category_id . '&blog_entry_id=' .$blog_entry_id, '&encode'));
    	} else {
			$this->session->data['warning'] = $error;
			$this->redirect($this->blog_html->getBLOGSEOURL('blog/entry','&bcat=' . $blog_category_id . '&blog_entry_id=' .$blog_entry_id, '&encode'));
    	}
		
		//init controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
		
	}
	
	private function filterComment($comment) {
		$bad_words = $this->model_blog_blog->getblog_config('bad_words');
		
		$bad_words = explode(',', $bad_words);
		
		foreach($bad_words as $word){
			$word = trim($word);
			if(empty($word)) { continue; }
			$word = preg_quote($word,'#');

			$pattern = "#$word#i";
			$comment = preg_replace($pattern, $this->getRandomChar(strlen($word)),$comment);
		}

		return($comment);
	}
	
	private function getRandomChar($num) {
		
		$replace_char = array('!',')','@','(','*','$','&','%','^');
		$result = '';
		for ($i=1; $i<=$num; $i++) {
			$result .= $replace_char[mt_rand(0, count($replace_char) - 1)];	
		}
		return $result;
	}
	static function cleanElements($html){
  
		 $search = array (
			   "'<script[^>]*?>.*?</script>'si",  //remove js
				"'<style[^>]*?>.*?</style>'si", //remove css 
				"'<head[^>]*?>.*?</head>'si", //remove head
			   "'<link[^>]*?>.*?</link>'si", //remove link
			   "'<body[^>]*?>.*?</body>'si", //remove body
			   "'<object[^>]*?>.*?</object>'si",
			   "'on[A-Za-z]*?\=\".*?\"/'", //removes onclick, onmouseover, etc.
			   "'<![\s\S]*?--[ \t\n\r]*>'"  // Strip multi-line comments
	 	);                  
  		return preg_replace ($search, '', $html);
 	}
	
 	static function cleanUsername($username){
		$username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
		$username = preg_replace( '/&.+?;/', '', $username ); // Kill entities
	
		return $username;
	}
	
	static function prepareTags($tags) {
		$tag_list = '';
		$tags = explode(', ', $tags);
		$count = count($tags);
		for($i=0; $i<=$count; $i++) {
			if($tags[$i]) {
				$tag_list .= '<'.$tags[$i].'>';	
			}
		}
		return $tag_list;
		
		
	}
}