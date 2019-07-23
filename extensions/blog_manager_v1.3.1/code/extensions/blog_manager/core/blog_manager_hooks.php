<?php
/*------------------------------------------------------------------------------
  $Id$
  
  Blog Manager Extension for
  AbanteCart, Ideal OpenSource Ecommerce Solution
  
  Copyright © 2016 - 2017 Corner Stores Online
------------------------------------------------------------------------------*/
if (!defined('DIR_CORE')) {
	header('Location: static_pages/');
}

class ExtensionBlogManager extends Extension {
    
    public $errors = array();
    public $data = array();
    protected $registry;

    public function  __construct() {
        $this->registry = Registry::getInstance();
		$this->config = $this->registry->get('config');
		
		if(!defined('IS_ADMIN') || !IS_ADMIN) {
			$this->_getBlogURLs();
			require_once(DIR_EXT . "blog_manager/core/engine/blog_html.php");
			$this->registry->set('blog_html', new ABlogHtml($this->registry));
			
			
		}
	}

	public function __get($key) {
		return $this->registry->get($key);
	}
	
	public function __set($key, $value) {
        $this->registry->set($key, $value);
    }
	
	public function onControllerPagesDesignTemplate_InitData() {
		$that = $this->baseObject;
		if($this->baseObject_method == 'set_default')   {
			$cur_tmpl_id = $this->config->get('config_storefront_template');
		
			if (!$this->user->canModify('design/template')){
				$that->session->data['warning'] = $that->language->get('error_permission');
				redirect($that->html->getSecureURL('design/template'));
			}
		
			if ($that->request->get['tmpl_id']){
				$tmpl_id = $that->request->get['tmpl_id'];
				$layout = new ALayoutManager();
				
				$query = $this->db->query("SELECT layout_id, layout_name 
					FROM " . $this->db->table("layouts") . " WHERE template_id = '" . $cur_tmpl_id . "' AND layout_name LIKE '%blog%'");
				
				foreach($query->rows as $exist_layout) {

					$exists = $this->db->query("SELECT * FROM " . $this->db->table("layouts") . " 
							WHERE template_id = '" . $tmpl_id . "' AND layout_name = '" . $exist_layout['layout_name'] . "'");
							
					if(!$exists->row) {
						$this->db->query("INSERT INTO " . $this->db->table("layouts") . " (template_id,layout_name,layout_type,date_added,date_modified)
									VALUES ('" . $this->db->escape($tmpl_id) . "',
											'" . $this->db->escape($exist_layout['layout_name']) . "',
											'1',
											NOW(),
											NOW())");
											
						$layout_id = $this->db->getLastId();
			
						$query = $this->db->query("SELECT page_id FROM " . $this->db->table("pages_layouts") . " WHERE layout_id = '" . $exist_layout['layout_id'] . "'");
						$page_id = $query->row['page_id'];
						
						$this->db->query("INSERT INTO " . $this->db->table("pages_layouts") . " (layout_id,page_id)
									VALUES ('" . (int)$layout_id . "','" . ( int )$page_id . "')");
									
						$layout->cloneLayoutBlocks($exist_layout['layout_id'], $layout_id);
					}	
				}
			}
		}
	}
	
	private function _getBlogURLs() {
		 
		if ( !$this->_checkAdmin()) return;
		
		if($this->config->get('use_store_url') == 1) {
			$this->config->set('http_blog_server', $this->config->get('blog_url'));
			if($this->config->get('config_ssl')) {
				$this->config->set('https_blog_server', $this->config->get('blog_ssl_url'));
			}else{
				$this->config->set('https_blog_server', $this->config->get('http_blog_server'));
			}
		}else{
			$this->config->set('http_blog_server', $this->config->get('config_url'));
			if($this->config->get('blog_ssl')) {
				$this->config->set('https_blog_server', $this->config->get('config_ssl_url'));
			}else{
				$this->config->set('https_blog_server', $this->config->get('http_blog_server'));
			}
		}
	}

	private function _checkAdmin() {
 		if(!defined('IS_ADMIN') || !IS_ADMIN) {
			return true;
		}
	}
	public function onControllerCommonSeoUrl_InitData(){
		

		if (isset($this->baseObject->request->get['_route_'])) {
			$parts = explode('/', $this->baseObject->request->get['_route_']);
				
			foreach ($parts as $part) {
				if ($part == 'blog') {
					$this->request->get['blog'] = 'blog';
				}else{
					$sql = "SELECT *
							FROM " . $this->registry->get('db')->table("url_aliases") . "
							WHERE keyword = '" . $this->registry->get('db')->escape($part) . "'";
					$query = $this->registry->get('db')->query($sql);
	
					if ($query->num_rows) {
						$url = explode('=', $query->row['query']);
						if ($url[0] == 'blog_entry_id') {
						
							$this->request->get['blog_entry_id'] = $url[1];
						}
						
						if ($url[0] == 'blog_category_id') {
							if (!isset($this->request->get['bcat'])) {
								$this->request->get['bcat'] = $url[1];
							} else {
								$this->request->get['bcat'] .= '_' . $url[1];
							}
						}
						if ($url[0] == 'blog_author_id') {
						
							$this->request->get['blog_author_id'] = $url[1];
						}		
					}
				}
			}
			if (isset($this->request->get['blog'])) {
				$this->request->get['rt'] = 'pages/blog/blog';
			} elseif (isset($this->request->get['blog_entry_id'])) {
				$this->request->get['rt'] = 'pages/blog/entry';
			} elseif (isset($this->request->get['blog_author_id'])) {
				$this->request->get['rt'] = 'pages/blog/author';
			} elseif (isset($this->request->get['bcat'])) {
				$this->request->get['rt'] = 'pages/blog/category';
			} 
		}
		
		
	}
	
	public function onControllerPagesIndexHome_InitData() {
		
		if ( IS_ADMIN!==TRUE) return null;
	
		$that = $this->baseObject;
		$that->loadModel('design/blog_entry');
		$dashboard = $that->model_design_blog_entry->getblog_config('enable_dashboard');
		if(!isset($dashboard) || $dashboard == 0) { return false; }
		
		$this->document->addStyle(array(
			'href' => '/extensions/blog_manager/admin/view/default/stylesheet/blog_manager.css',
			'rel' => 'stylesheet'
		 ));
		
		$that->loadLanguage('blog_manager_blog_manager');
		$that->loadLanguage('blog_manager/blog_user');
		$that->loadLanguage('blog_manager/blog_author');
		$that->loadLanguage('blog_manager/blog_comment');
		
		$active = $that->model_design_blog_entry->getEntriesCount(1);
		$pending = $that->model_design_blog_entry->getEntriesCount(0);
		
		$that->loadModel('design/blog_comment');
		$approved_comments = $that->model_design_blog_comment->getTotalCommentsCount(1);
		$unapproved_comments = $that->model_design_blog_comment->getTotalCommentsCount(0);
		
		$blog_author_status = $that->config->get('blog_author_status');
		if(isset($blog_author_status) && $blog_author_status == '1') {
			$that->loadModel('blog_author/blog_entry');
			$submitted_articles = $that->model_blog_author_blog_entry->getSubmittedArticleCount();
		}

		$blog_access = $that->model_design_blog_entry->getblog_config('blog_access');
		if($blog_access == 'restrict') {
			$that->loadModel('design/blog_user');
			$total_user = $that->model_design_blog_user->getUserCount(1);
			$unapproved_user = $that->model_design_blog_user->getUserCount(0);
		}
		$html = '<div class="row">
					<div class="col-sm-5 col-lg-5">
						<div class="panel panel-default">
							<div class="panel-body">
								<h5 class="title"><i class="fa fa-plus-square fa-lg fa-fw"></i> Blog At A Glance</h5>
					
								
								<div class="table-responsive">
					<table class="table">
						<tr>
							<td width="80%">Active Article Count:</td>
							<td align="right">' . $active . '</td>
						</tr>
						<tr>
							<td width="80%">Pending Article Count:</td>
							<td align="right">' . $pending . '</td>
						</tr>
						<tr>
							<td width="80%">Comment Count:</td>
							<td align="right">' . $approved_comments . '</td>
						</tr>
						<tr>
							<td width="80%">Comments Waiting Approval:</td>
							<td align="right">' . $unapproved_comments . '</td>
						</tr>';
						
					if($blog_access == 'restrict') {
						$html .= '<tr>
							<td width="80%">Users Count:</td>
							<td align="right">' . $total_user . '</td>
						</tr>
						<tr>
							<td width="80%">Users Waiting Approval:</td>
							<td align="right">' . $unapproved_user . '</td>
						</tr>';
						
					}
					if(isset($blog_author_status) && $blog_author_status == '1') {
						$html .= '<tr>
							<td width="80%">Submitted Articles Count:</td>
							<td align="right">' . $submitted_articles . '</td>
						</tr>';	
					}
								
					$html .= '</table>
					</div>
					<h5 class="title"><i class="fa fa-plus-square fa-lg fa-fw"></i> Actions</h5>	
								
					<div class="col-sm-4 col-lg-4 blog_action"> <a title="'.$that->language->get('tab_settings').'" href="'.$that->html->getSecureURL('design/blog_manager').'" target="_blank">' . $that->language->get('tab_settings') .'</a></div>		
					<div class="col-sm-4 col-lg-4 blog_action"> <a title="'.$that->language->get('text_new_article').'" href="'.$that->html->getSecureURL('design/blog_entry/insert').'" target="_blank">' . $that->language->get('text_new_article') .'</a></div>			
					<div class="col-sm-4 col-lg-4 blog_action"> <a title="'.$that->language->get('blog_entry_name').'" href="'.$that->html->getSecureURL('design/blog_entry').'" target="_blank">' . $that->language->get('blog_entry_name') .'</a></div>	
					<div class="col-sm-4 col-lg-4 blog_action"> <a title="'.$that->language->get('tab_comments').'" href="'.$that->html->getSecureURL('design/blog_comment').'" target="_blank">' . $that->language->get('tab_comments') .'</a></div>
					<div class="col-sm-4 col-lg-4blog_action"> <a title="'.$that->language->get('authors_name').'" href="'.$that->html->getSecureURL('design/blog_author').'" target="_blank">' . $that->language->get('authors_name') .'</a></div>';
					
					if($blog_access == 'restrict') {
						$that->loadLanguage('blog_manager/blog_user');
						$html .= '<div class="col-sm-3 col-lg-3 blog_action">	<a title="'.$that->language->get('users_name').'" href="'.$that->html->getSecureURL('design/blog_user').'" target="_blank">' . $that->language->get('users_name') .'</a></div>';
						
						
					}

		$html .= '</div></div></div>';
		$total_entries = $active;
		$dashboard_article_count = $that->model_design_blog_entry->getblog_config('dashboard_article_count');
		$latest_articles = $that->model_design_blog_entry->getLatestArticles(0, $dashboard_article_count);
		
		$article_next_start = $dashboard_article_count;
		
		$html .= '<div class="col-sm-7 col-lg-7">
						<div class="panel panel-default">
							<div class="panel-body">
								<h5 class="title"><i class="fa fa-plus-square fa-lg fa-fw"></i> ' . $this->language->get('text_latest_activity') . '</h5>';
		$html .= '<h5 class="title">' . $this->language->get('text_recent_articles');
				$html .= '<span class="next_prev pull-right">';
				$html .= '<span class="hide" id="article_prev_text"><a href="#" data-limit="' . $dashboard_article_count . '" data-start="0" id="article_less">' . $this->language->get('text_previous') . '</a></span>';
				$html .= '<span id="article_divider" class="hide"> | </span>';
				if($total_entries > $dashboard_article_count) {
					$html .= '<span id="article_next_text"><a href="#" data-limit="' . $dashboard_article_count . '" data-start="' . $article_next_start . '" id="article_more">' . $this->language->get('text_next') . '</a></span>';
				}
				$html .= '</span></h5>
				<div class="table-responsive">
					<table class="table" id="dashboard_article_list">';
						if($latest_articles) {
							foreach($latest_articles as $article) {
								$html .= '<tr><td><span>' . $article['date_modified'] . ' - <a href="'.$that->html->getSecureURL('design/blog_entry/update','&blog_entry_id='.$article['blog_entry_id']).'" target="_blank">' . $article['entry_title'] . '</a> </td>
									<td align="right"><a href="' . $this->html->getCatalogURL('blog/entry','&blog_entry_id='.$article['blog_entry_id']) . '" target="_blank">' . $this->language->get('text_view') . '</a></span></td></tr>';
					
							}
						}
		$html .= '</table></div>';
		
		$total_comments = $approved_comments + $unapproved_comments;
		$dashboard_comment_count = $that->model_design_blog_comment->getblog_config('dashboard_comment_count');
		$latest_comments = $that->model_design_blog_comment->getLatestComments(0, $dashboard_comment_count);
		
		$comment_next_start = $dashboard_comment_count;
		
		$html .= '<h5 class="title">' . $this->language->get('text_recent_comments');
				$html .= '<span class="next_prev pull-right">';
				$html .= '<span class="hide" id="comment_prev_text"><a href="#" data-limit="' . $dashboard_comment_count . '" data-start="0" id="comment_less">' . $this->language->get('text_previous') . '</a></span>';
				$html .= '<span id="comment_divider" class="hide"> | </span>';
				if($total_comments > $dashboard_comment_count) {
					$html .= '<span id="comment_next_text"><a href="#" data-limit="' . $dashboard_comment_count . '" data-start="' . $comment_next_start . '" id="comment_more">' . $this->language->get('text_next') . '</a></span>';
				}
				$html .= '</span></h5>
				<div class="table-responsive">
					<table class="table" id="dashboard_comment_list">';
						if($latest_comments) {
							foreach($latest_comments as $comment) {
								$html .= '<tr><td><span class="comment_list_item">' . $comment['date_modified'] . ' - ' . $this->language->get('text_from') . ' ' . $comment['username'] . ' ' . $this->language->get('text_on') . ' <a href="'.$that->html->getSecureURL('design/blog_entry/update','&blog_entry_id='.$comment['blog_entry_id']).'" target="_blank">' . $comment['entry_title'] . '</a>';
								if($comment['type'] == 'reply') {
									$html .= ' ' . $this->language->get('text_in_reply_to') . ' ' . $comment['parent_username'];
								}
								if(!$comment['approved']) {
									$html .= ' - <i class="fa fa-flag-o"></i> ' .  $this->language->get('text_pending');	
								}
								$html .= '</span>'; 
								
								$html .= '<p class="comment_list_item">' . $comment['comment'] . '</p>';
								$html .= '<div id="upc-' . $comment['blog_comment_id'] . '" class="comment_list_actions ' . ($comment['approved'] ? 'show' : 'hide') . '">';
									$html .= '<a href="#" data-comment_id="' . $comment['blog_comment_id'] . '" class="toggle_comment">' . $this->language->get('text_unapprove') . '</a>';
									$html .= '<a href="' . $this->html->getSecureURL('design/blog_comment/update','&blog_comment_id='.$comment['blog_comment_id']) . '" target="_blank">' . $this->language->get('text_edit') . '</a>';
									$html .= '<a href="' . $this->html->getSecureURL('design/blog_comment/reply','&blog_comment_id='.$comment['blog_comment_id']) . '" target="_blank">' . $this->language->get('text_entry_insert') . '</a>';
									$html .= '<a href="' . $this->html->getCatalogURL('blog/entry','&blog_entry_id='.$comment['blog_entry_id'].'#comment-'.$comment['blog_comment_id']) . '" target="_blank">' . $this->language->get('text_view') . '</a>';
								$html .= '</div>';
								$html .= '<div id="apc-' . $comment['blog_comment_id'] . '" class="comment_list_actions ' . ($comment['approved'] ? 'hide' : 'show') . '">';
									$html .= '<a href="#" data-comment_id="' . $comment['blog_comment_id'] . '" class="toggle_comment">' . $this->language->get('text_approve') . '</a>';
									$html .= '<a href="' . $this->html->getSecureURL('design/blog_comment/update','&blog_comment_id='.$comment['blog_comment_id']) . '" target="_blank">' . $this->language->get('text_edit') . '</a>';
								
								$html .= '</div>';
								$html .= '</div></td></tr>';
							}
						}
		$html .= '</table></div>';
								
		$html .= '</div></div></div></div>';	
		
		$html .= '<script type="text/javascript">
					$("document").ready(function () {	
						$("#article_more").on("click", function(event) {
							event.preventDefault();
							var start = $(this).attr("data-start");
							var limit = $(this).attr("data-limit");
							getArticles(start, limit, ' . $total_entries . ');
						});
						$("#article_less").on("click", function(event) {
							event.preventDefault();
							var start = $(this).attr("data-start");
							var limit = $(this).attr("data-limit");
							getArticles(start, limit, ' . $total_entries . ');
						});
						$("#comment_more").on("click", function(event) {
							event.preventDefault();
							var start = $(this).attr("data-start");
							var limit = $(this).attr("data-limit");
							getComments(start, limit, ' . $total_comments . ');
						});
						$("#comment_less").on("click", function(event) {
							event.preventDefault();
							var start = $(this).attr("data-start");
							var limit = $(this).attr("data-limit");
							getComments(start, limit, ' . $total_comments . ');
						});
						
					});
					$(document).on("click","a.toggle_comment", function(event) {
							event.preventDefault();
							var id = $(this).attr("data-comment_id");
							var url = "' . $this->html->getSecureURL('tool/content/toggleApproval') .'";
							$.ajax({
								url: url + "&id=" + id,
								cache: false,
								type: "GET",
								dataType: "html",
								success: function(data){
									if(data == "1") {
										$("#upc-" + id).removeClass("hide").addClass("show");
										$("#apc-" + id).removeClass("show").addClass("hide");
									}else{
										$("#upc-" + id).removeClass("show").addClass("hide");
										$("#apc-" + id).removeClass("hide").addClass("show");
									}
								},
								error: function(jqXHR, textStatus, errorThrown) {
									console.log(textStatus+" "+errorThrown);
								}
							});
						});	
					function getArticles(start, limit, total_entries) {
						var url = "' . $this->html->getSecureURL('tool/content/getArticles') .'";
						var edit_url = "' . $this->html->getSecureURL('design/blog_entry/update') . '";
						var view_url = "' . $this->html->getCatalogURL('blog/entry') . '";
						var text_view = "' . $this->language->get('text_view') . '";
						
						var next_start = Number(start) + Number(limit);
						var prev_start = Number(start) - Number(limit);
						var html = "";
						$.ajax({
							url: url + "&start=" + start + "&limit=" + limit,
							cache: false,
							type: "GET",
							dataType: "json",
							success: function(data){
								if(data) {
									$("#dashboard_article_list").html("");
									$.each(data, function(index, value) {
										html = html + "<tr><td><span>" +value.date_modified+ " - <a href=" +edit_url+ "&blog_entry_id=" +value.blog_entry_id+"\" target=\"_blank\">" +value.entry_title+ "</a> </td>";
										html = html + "<td align=\"right\"><a href=\"" +view_url+ "&blog_entry_id=" +value.blog_entry_id+ "\" target=\"_blank\">" +text_view+"</a></span></td></tr>";
									});
									$("#dashboard_article_list").html(html);
									if(start == 0) {
										$("#article_prev_text").addClass("hide");
										$("#article_divider").addClass("hide");
										$("#article_less").attr("data-start", "0");
									}else{
										$("#article_prev_text").removeClass("hide");
										$("#article_divider").removeClass("hide");
										$("#article_less").attr("data-start", prev_start);
									}
									if(next_start < total_entries) {
										$("#article_next_text").removeClass("hide");
										$("#article_more").attr("data-start", next_start);
									}else{
										$("#article_next_text").addClass("hide");
										$("#article_divider").addClass("hide");
									}
								}
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus+" "+errorThrown);
							}
						});
					}
					function getComments(start, limit, total_comments) {
						var url = "' . $this->html->getSecureURL('tool/content/getComments') .'";
						var edit_url = "' . $that->html->getSecureURL('design/blog_entry/update') . '";
						var edit_comment_url = "' . $this->html->getSecureURL('design/blog_comment/update') . '";
						var reply_url = "' . $this->html->getSecureURL('design/blog_comment/reply') . '";
						var view_url = "' . $this->html->getCatalogURL('blog/entry') . '";
						
						var text_from = "' .  $this->language->get('text_from') . '";
						var text_on = "' . $this->language->get('text_on') . '";
						var text_in_reply_to = "' . $this->language->get('text_in_reply_to') . '";
						var text_pending = "' . $this->language->get('text_pending') . '";
						var text_unapprove = "' . $this->language->get('text_unapprove') . '";
						var text_approve = "' . $this->language->get('text_approve') . '";
						var text_edit = "' . $this->language->get('text_edit') . '";
						var text_entry_insert = "' . $this->language->get('text_entry_insert') . '";
						var text_view = "' . $this->language->get('text_view') . '";
						
						var next_start = Number(start) + Number(limit);
						var prev_start = Number(start) - Number(limit);
						var html = "";
						$.ajax({
							url: url + "&start=" + start + "&limit=" + limit,
							cache: false,
							type: "GET",
							dataType: "json",
							success: function(data){
								if(data) {
									$("#dashboard_comment_list").html("");
									$.each(data, function(index, value) {
										html = html + "<tr><td><span class=\"comment_list_item\">" +value.date_modified+ " - " +text_from+ " " +value.username+ " " +text_on+ " <a href=\"" +edit_url+ "&blog_entry_id=" +value.blog_entry_id+"\" target=\"_blank\">" +value.entry_title+ "</a>";
										if(value.type == "reply") {
											html = html + text_in_reply_to+ " " +value.parent_username;
										}
										if(value.approved == "0") {
											html = html + " - <i class=\"fa fa-flag-o\"></i> " +text_pending;	
										}
										html = html + "</span>"; 
										html = html + "<p class=\"comment_list_item\">" +value.comment+ "</p>";
										if(value.approved == "1") { 
											var upc_display = "show";
											var apc_display = "hide"; 
										}else{ 
											var upc_display = "hide"; 
											var apc_display = "show";
										}
										html = html + "<div id=\"upc-" +value.blog_comment_id+ "\" class=\"comment_list_actions " +upc_display+ "\">"; 
											html = html + "<a href=\"#\" data-comment_id=\"" +value.blog_comment_id+ "\" class=\"toggle_comment\">" +text_unapprove+ "</a>";
											html = html + "<a href=\"" +edit_comment_url+ "&blog_comment_id=" +value.blog_comment_id+ "\" target=\"_blank\">" +text_edit+ "</a>";
											html = html + "<a href=\"" +reply_url+ "&blog_comment_id=" +value.blog_comment_id+ "\" target=\"_blank\">" +text_entry_insert+ "</a>";
											html = html + "<a href=\"" +view_url+ "&blog_entry_id=" +value.blog_entry_id+ "#comment-" +value.blog_comment_id+ "\" target=\"_blank\">" +text_view+ "</a>";
										html = html + "</div>";
										html = html + "<div id=\"apc-" +value.blog_comment_id+ "\" class=\"comment_list_actions " +apc_display+ "\">";
											html = html + "<a href=\"#\" data-comment_id=\"" +value.blog_comment_id+ "\" class=\"toggle_comment\">" +text_approve+ "</a>";
											html = html + "<a href=\"" +edit_comment_url+ "&blog_comment_id=" +value.blog_comment_id+ "\" target=\"_blank\">" +text_edit+ "</a>";
										html = html + "</div>";
										
										html = html + "</div></td></tr>";
									});
									$("#dashboard_comment_list").html(html);
									if(start == 0) {
										$("#comment_prev_text").addClass("hide");
										$("#comment_divider").addClass("hide");
										$("#comment_less").attr("data-start", "0");
									}else{
										$("#comment_prev_text").removeClass("hide");
										$("#comment_divider").removeClass("hide");
										$("#comment_less").attr("data-start", prev_start);
									}
									if(next_start < total_comments) {
										$("#comment_next_text").removeClass("hide");
										$("#comment_more").attr("data-start", next_start);
									}else{
										$("#comment_next_text").addClass("hide");
										$("#comment_divider").addClass("hide");
									}
								}
							},
							error: function(jqXHR, textStatus, errorThrown) {
								console.log(textStatus+" "+errorThrown);
							}
						});
					}
				
				</script>';
		
		$that->view->addHookVar('home_page_bottom',$html);
	}
	
	public function onControllerPagesAccountAccount_InitData() {
		
		$that = $this->baseObject;
		$that->loadModel('blog/blog');
		$access = $that->model_blog_blog->getblog_config('blog_access');
		$source = $that->model_blog_blog->getblog_config('login_data');
		
		if($access == 'restrict' && $source == 'customer') {
			$that->loadLanguage('blog/blog');
			$result = $that->model_blog_blog->getCustBlogUserData($this->customer->getId());
			$html = '<li>
					<a title="'.$that->language->get('text_blog_settings').'" data-toggle="tooltip" href="'.$that->html->getSecureURL('account/blog_settings').'" data-original-title="'.$that->language->get('text_blog_settings').'">
					<i class="fa fa-comments"></i></a>
					</li>';
			
			$that->view->addHookVar('account_newsletter_dash_icons',$html);
		
		}
	}
	
	public function onControllerPagesToolCache_UpdateData() {
		$that = $this->baseObject;
		$this->data['sections'] = $that->view->getData('sections');
		$that->loadLanguage('blog_manager/blog_manager');
		$this->data['sections'][] = array(
			'id' => 'blog',
			'text' => $this->language->get('text_blog'),
			'description' => $this->language->get('desc_blog'),
			'keywords' => 'blog' 
		);
		
		$that->view->assign('sections', $this->data['sections']);
		
		
	}
	
	public function onControllerPagesAccountCreate_UpdateData() {
		$that = $this->baseObject;
		if($this->baseObject_method == 'main')   {
			if ($this->request->is_POST()){
				$that->loadModel('blog/blog');
				$access = $that->model_blog_blog->getblog_config('blog_access');
				$source = $that->model_blog_blog->getblog_config('login_data');
				if($access == 'restrict' && $source == 'customer') {
					if($that->data['customer_id']) {
						$that->model_blog_blog->customerToBlogUser($that->data['customer_id']);
					}
				}
			}
		}
	}
	
	public function onControllerPagesAccountLogin_InitData() {
		$that = $this->baseObject;
		if($this->baseObject_method == 'main')   {
			if(has_value($that->request->get['ac']) ){
				$enc = new AEncryption($this->config->get('encryption_key'));	
				list($customer_id, $activation_code) = explode("::", $enc->decrypt($this->request->get['ac']));
				if($customer_id && $activation_code) {	
					$that->loadModel('account/customer');
					$customer_info = $that->model_account_customer->getCustomer((int)$customer_id);			
					if($customer_info) {
						//if activation code presents in data and matching
						if ($activation_code == $customer_info['data']['email_activation']){
							$that->loadModel('blog/blog');
							$that->model_blog_blog->activateBlogUser($customer_id);
						}
					}
				}
			}
		}
	}
	
	public function onControllerPagesAccountLogout_InitData() {
		
		unset($this->session->data['blog_user_logged']);
		unset($this->session->data['blog_user_id']);
		unset($this->session->data['blog_user_name']);
		unset($this->session->data['blog_role']);
		unset($this->session->data['blog_role_id']);
		unset($this->session->data['blog_first_name']);
		
	}

}


?>