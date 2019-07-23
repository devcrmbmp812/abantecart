<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (! defined ( 'DIR_CORE' ) || !IS_ADMIN) {
	header ( 'Location: static_pages/' );
}
class ControllerResponsesListingGridBlogComment extends AController {

    public function main() {

	    //init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->loadLanguage('blog_manager/blog_comment');
		$this->loadModel('design/blog_comment');
		$this->loadModel('design/blog_manager');
        $this->loadModel('tool/image');
		
		

		//Prepare filter config
		$filter_params = array( 'blog_entry_id' );
		$grid_filter_params = array('name', 'bd.title', 'sort_order', 'status');
	    $filter_form = new AFilter(array( 'method' => 'get', 'filter_params' => $filter_params ));
		$filter_grid = new AFilter(array( 'method' => 'post', 'grid_filter_params' => $grid_filter_params ));
		
		$filter_data = array_merge($filter_form->getFilterData(), $filter_grid->getFilterData());
	    //Add custom params
	    $filter_data['parent_id'] = ( isset( $this->request->get['parent_id'] ) ? $this->request->get['parent_id'] : 0 );
	    $new_level = 0;
		//get all leave comments 
		$leafnodes = $this->model_design_blog_comment->getBlogLeafComments();
	    if ($this->request->post['nodeid'] ) {
	    	$sort = $filter_data['sort'];
	    	$order = $filter_data['order'];
	    	//reset filter to get only parent comment
	    	$filter_data = array();
	    	$filter_data['sort'] = $sort;
	    	$filter_data['order'] = $order;
	    	$filter_data['parent_id'] = (integer)$this->request->post['nodeid'];
			$new_level = (integer)$this->request->post["n_level"] + 1;
	    }
	    
	    $total = $this->model_design_blog_comment->getTotalBlogComments($filter_data);
	    $response = new stdClass();
		$response->page = $filter_grid->getParam('page');
		$response->total = $filter_grid->calcTotalPages( $total );
		$response->records = $total;
	    $response->userdata = new stdClass();
	    $results = $this->model_design_blog_comment->getBlogCommentsData($filter_data);

	    $i = 0;

	    foreach ($results as $result) {

            $response->rows[$i]['id'] = $result['blog_comment_id'];
			
			if($result['parent_id'] > 0) {
				$title_prefix = $this->language->get('text_reply') . '<br />';
			}else{
				$title_prefix = $this->language->get('text_comment') . '<br />';	
			}
			
			$details = '<span class="detail_list_head">'. $this->language->get('text_comment_by') . '</span>';
			$details .= '<br /><span class="detail_list_name">'. $result['username'] . '</span>';
			$details .= '<br /><span class="detail_list_email"><a href="mailto:' . $result['email'] . '">' . $this->language->get('text_email') . '</a></span>';
			if ($result['site_url']) {
				$details .= ' <span class="detail_list_url"><a href="' . $result['site_url'] . '" target="_blank">' . $this->language->get('text_website') . '</a></span>';
			}
			
			$comment = '<span class="comment_list_head">' . $this->language->get('text_comment_on') . '</span> <span class="comment_list_date">' . $result['date_added'] . '</span>';
			if ($result['date_added'] != $result['date_modified']) {
				$comment .= ' - <span class="comment_list_edited">' . $this->language->get('text_last_edited') . ' ' . $result['date_modified'] . '</span>';
			}
			$comment .= '<br /><span class="comment_list_comment">' . $result['comment'] . '</span>';
			
			$reply_approvals = $this->model_design_blog_comment->getCommentReplyTotals($result['blog_comment_id'], $result['primary_comment_id'], $result['blog_entry_id']);
			$approved = $reply_approvals['total_approved'] ? $reply_approvals['total_approved'] : 0;
			$un_approved = $reply_approvals['total_unapproved'] ? $reply_approvals['total_unapproved'] : 0;
			
			$replies = '<span class="approved_box">' . $approved . '</span>';

			if ($un_approved != 0) {
				$replies .= '  <span class="unapproved_box">' . $un_approved . '</span>'; 
			}
			
			if($result['approval_req'] == 1) {
				$approve = '1';
			}else{
				if($result['approved'] == 0) {
					$approve = '1';
				}else{
					$approve = '0';
				}
			}
			
			$response->rows[$i]['cell'] = array(
				$title_prefix . $result['entry_title'], 
				$details,
 				$comment,
				$replies,
				$this->html->buildCheckbox(array(
                    'name'  => 'status['.$result['blog_comment_id'].']',
                    'value' => $result['status'],
                    'style'  => 'btn_switch',
                )),
				$approve ? 
						$this->html->buildCheckbox(array(
							'name'  => 'approved['.$result['blog_comment_id'].']',
							'value' => $result['approved'],
							'style'  => 'btn_switch',
						)) : $this->language->get('text_automatic'),
                 'action',
                 $new_level,
                 ( $filter_data['parent_id'] ? $filter_data['parent_id'] : NULL ),
                 ( $result['blog_comment_id'] == $leafnodes[$result['blog_comment_id']] ? true : false ),
                 false              
			);
			$i++;
		}
		//update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);

		$this->load->library('json');
		$this->response->setOutput(AJson::encode($response));
	}

	public function update() {

		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

		$this->loadModel('design/blog_manager');
	    $this->loadModel('design/blog_comment');
		$this->loadLanguage('blog_manager/blog_comment');
		if (!$this->user->canModify('listing_grid/blog_comment')) {
			        $error = new AError('');
			        return $error->toJSONResponse('NO_PERMISSIONS_402',
			                                      array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_comment'),
			                                             'reset_value' => true
			                                           ) );
		}

		switch ($this->request->post['oper']) {
			case 'del':
				$ids = explode(',', $this->request->post['id']);
				if ( !empty($ids) )
				foreach( $ids as $id ) {
					$this->model_design_blog_comment->deleteBlogComment($id);
				}
				break;
			case 'save':
				$allowedFields = array('approved', 'status');

				$ids = explode(',', $this->request->post['id']);
				if ( !empty($ids) ) {
					foreach( $ids as $id ) {
						foreach ( $allowedFields as $field ) {
							$this->model_design_blog_comment->editBlogComment($id, array($field => $this->request->post[$field][$id]) );
						}
					}
				}
				break;
			default:

		}

		//update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}

    /**
     * update only one field
     *
     * @return void
     */
    public function update_field() {

		//init controller data
        $this->extensions->hk_InitData($this,__FUNCTION__);

        $this->loadLanguage('design/blog_comment');
        if (!$this->user->canModify('listing_grid/blog_comment')) {
	        $error = new AError('');
	        return $error->toJSONResponse('NO_PERMISSIONS_402',
	                                      array( 'error_text' => sprintf($this->language->get('error_permission_modify'), 'listing_grid/blog_comment'),
	                                             'reset_value' => true
	                                           ) );
		}

        $this->loadModel('design/blog_comment');

	    if ( isset( $this->request->get['id'] ) ) {
		    //request sent from edit form. ID in url
		    foreach ($this->request->post as $field => $value ) {

				$err = $this->_validateField($field, $value);
				if (!empty($err)) {
					$error = new AError('');
					return $error->toJSONResponse('VALIDATION_ERROR_406', array( 'error_text' => $err ));
				}

				$response = $this->model_design_blog_comment->editBlogComment($this->request->get['id'], array($field => $value) );
				if(isset($response) && $response == "notify" && $field == "approved" && $value == '1') {
					$this->notifications($this->request->get['id']);	
				}
			}
		    return null;
	    }
	
	    //request sent from jGrid. ID is key of array
        foreach ($this->request->post as $field => $value ) {
            foreach ( $value as $k => $v ) {
				$response = $this->model_design_blog_comment->editBlogComment($k, array($field => $v) );
				if(isset($response) && $response == "notify" && $field == "approved" && $v == '1') {
					$this->notifications($k);	
				}
            }
        }

		//update controller data
        $this->extensions->hk_UpdateData($this,__FUNCTION__);
	}
	
	public function notifications($blog_comment_id) {
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		if(!$blog_comment_id){
			return false;
		}
		
		$this->loadLanguage('blog_manager/blog_comment');
		
		$blog_title = $this->model_design_blog_comment->getblog_config('title');
		$owner_email = $this->model_design_blog_comment->getblog_config('owner_email');

		$blog_comment_info = $this->model_design_blog_comment->getBlogComment($blog_comment_id);
		$entry_title = $this->model_design_blog_comment->getEntryTitle($blog_comment_info['blog_entry_id']);
		
		$all_notices = $this->model_design_blog_comment->getAllNotifications($blog_comment_info['blog_entry_id']);
		if(count($all_notices)>0) {
			foreach($all_notices as $notices) {
				$subject = sprintf($this->language->get('text_mail_subject'), $blog_title);
				$message = $notices['user_name'].',';
				$message .= "\n\n";
				$message .= sprintf($this->language->get('text_new_comment'), $entry_title) . "\n\n";
				$message .= $this->html->getCatalogURL('blog/entry','&blog_entry_id=' .$blog_comment_info['blog_entry_id']).'#comment-'.$blog_comment_id. "\n\n\n";
				$message .= $this->language->get('text_cancel_notification')."\n";
				$message .= $this->html->getCatalogURL('blog/xxxxx','&n=' . $notices['notification_id'])."\n\n";
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

		$reply_notices = $this->model_design_blog_comment->getReplyNotifications($blog_comment_info['blog_entry_id'], $blog_comment_info['primary_comment_id']);
		if(count($reply_notices)>0) {
			foreach($reply_notices as $notices) {
				$subject = sprintf($this->language->get('text_subject_new_reply'), $blog_title);
				$message = $notices['user_name'].',';
				$message .= "\n\n";
				$message .= sprintf($this->language->get('text_new_user_reply'), $entry_title) . "\n\n";
				$message .= $this->html->getCatalogURL('blog/entry','&blog_entry_id=' .$blog_comment_info['blog_entry_id']).'#comment-'.$blog_comment_id. "\n\n\n";
				$message .= $this->language->get('text_cancel_notification')."\n";
				$message .= $this->html->getCatalogURL('blog/xxxxx','&n=' . $notices['notification_id'])."\n\n";
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
		
		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);
		
		return true;
	}


	private function _validateField($field, $value) {

		$err = '';
		switch ($field) {
			case 'username' :
				if (mb_strlen($this->request->post['username']) < 2 || mb_strlen($this->request->post['username']) > 40) {
					$err =  $this->language->get('error_username');
				}
				break;
			case 'comment' :
				if (mb_strlen($this->request->post['comment']) < 5) {
					$err =  $this->language->get('error_comment');
				}
				break;
			case 'email' :
				if (mb_strlen($this->request->post['email']) > 96 || !preg_match(EMAIL_REGEX_PATTERN, $this->request->post['email'])) {
					$err =  $this->language->get('error_email');
				}
				break;
		}
		return $err;
	}


	public function blog_categories() {

		$output = array();
		//init controller data
		$this->extensions->hk_InitData($this, __FUNCTION__);
		$this->loadModel('design/blog_comment');
		if (isset($this->request->post['term'])) {
			$filter = array('limit' => 20,
							'language_id' => $this->language->getContentLanguageID(),
							'subsql_filter' => "bcd.name LIKE '%".$this->request->post['term']."%'
												OR bcd.description LIKE '%".$this->request->post['term']."%'
												OR bcd.meta_keywords LIKE '%".$this->request->post['term']."%'"
							);
			$results = $this->model_design_blog_comment->getBlogCategoriesData($filter);

			$resource = new AResource('image');
			foreach ($results as $item) {
				$thumbnail = $resource->getMainThumb('blog_categories',
												$item['blog_comment_id'],
												(int)$this->config->get('config_image_grid_width'),
												(int)$this->config->get('config_image_grid_height'),
												true);

				$output[ ] = array(
					'image' => $icon = $thumbnail['thumb_html'] ? $thumbnail['thumb_html'] : '<i class="fa fa-code fa-4x"></i>&nbsp;',
					'id' => $item['blog_comment_id'],
					'name' => $item['name'],
					'meta' => '',
					'sort_order' => (int)$item['sort_order'],
				);
			}
		}

		//update controller data
		$this->extensions->hk_UpdateData($this, __FUNCTION__);

		$this->load->library('json');
		$this->response->addJSONHeader();
		$this->response->setOutput(AJson::encode($output));
	}

}
