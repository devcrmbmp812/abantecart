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


class ABlogHtml extends AController {
	protected $registry;
	protected $args = array();
	private $blogOgTags = array();
	private $blogTwTags = array();
	
	/**
	 * @param Registry $registry
	 * @param array $args
	 */
	public function __construct($registry, $args = array()) {
		$this->registry = $registry;
		$this->config = $this->registry->get('config');
		
		if (isset($this->registry->get('request')->server['HTTPS'])
				&& (($this->registry->get('request')->server['HTTPS'] == 'on') || ($this->registry->get('request')->server['HTTPS'] == '1'))) {
			$this->server = $this->config->get('https_blog_server');
		} else {
			$this->server = defined($this->config->get('http_blog_server')) ? $this->config->get('http_blog_server') : 'http://' . REAL_HOST . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/' ;
		}
	}
	
	public function getBLOGHOME($encode = '') {
		if($this->config->get('use_store_url')) {  //value = 0 - custom domain
			$url = $this->url_encode($this->server, $encode);
		}else{
			$parts = explode('/', $this->server);
			if(count($parts) > 4) {
				$url = $this->url_encode($this->server.'index.php?rt=blog/blog', $encode);
			}else{
				$url = $this->url_encode($this->server .'blog', $encode);
			}
		}
		return $url;
	}
	
	public function getBLOGSEOURL($rt, $params = '', $encode = '') {
		//#PR Generate SEO URL based on standard URL
		$this->loadModel('tool/blog_seo_url');
		return $this->url_encode($this->model_tool_blog_seo_url->rewrite($this->getURL($rt, $params)), $encode);
	}

	
	public function getBlogArchiveURL($month, $year, $params = '', $encode = '') {
		$url = $this->server .'archive/' . $month . '/' . $year . $this->url_encode($params, $encode);
		//not sure if needed or not
		if (defined('IS_WINDOWS')) {
			$url = str_replace('\\', '/', $url);
		}

		return $url;
	}
	
	public function getBlogFeedURL($source = '') {

		if (!$source) {
			$url = $this->server . 'feed';
		}else{
			$url = $this->server . 'feed?source=' .$source;
		}
		return $url;
		
	}
	
	public function getImageURL() {
		$url = $this->server . 'image/blog';
		return $url;	
		
	}
	
	public function getURL($rt, $params = '', $encode = '') {

		if ($this->registry->get('config')->get('storefront_template_debug') && isset($this->registry->get('request')->get['tmpl_debug'])) {
			$params .= '&tmpl_debug=' . $this->registry->get('request')->get['tmpl_debug'];
		}
		// add session id for crossdomain transition in secure mode
		if($this->registry->get('config')->get('config_shared_session')	&& HTTPS===true){
			$params .= '&session_id='.session_id();
		}

		$url = $this->server . INDEX_FILE . $this->url_encode($this->buildURL($rt, $params), $encode);
		return $url;
	}
	
	public function getSecureURL($rt, $params = '', $encode = '') {
		// add session id for crossdomain transition in non-secure mode
		if($this->registry->get('config')->get('config_shared_session')	&& HTTPS!==true){
			$params .= '&session_id='.session_id();
		}

		$suburl = $this->buildURL($rt, $params);
		//#PR Add session
		if (isset($this->session->data['token']) && $this->session->data['token']) {
			$suburl .= '&token=' . $this->session->data['token'];
		}

		if ($this->registry->get('config')->get('storefront_template_debug') && isset($this->registry->get('request')->get['tmpl_debug'])) {
			$suburl .= '&tmpl_debug=' . $this->registry->get('request')->get['tmpl_debug'];
		}

		$url = $this->config->get('https_blog_server') . INDEX_FILE . $this->url_encode($suburl, $encode);
		return $url;
	}
	
	private function buildURL($rt, $params = '') {
		$suburl = '';
		//#PR Add admin path if we are in admin
		if (IS_ADMIN) {
			$suburl .= '&s=' . ADMIN_PATH;
		}
		//add template if present
		if (!empty($this->registry->get('request')->get['sf'])) {
			$suburl .= '&sf=' . $this->registry->get('request')->get['sf'];
		}

		$suburl = '?' . ($rt ? 'rt=' . $rt : '') . $params . $suburl;
		return $suburl;
	}
	
	public function url_encode($url, $encode = false) {
		if ($encode) {
			return str_replace('&', '&amp;', $url);
		} else {
			return $url;
		}
	}
	
	public function getBLOGCONSEOURL($rt, $params = '') {
		//#PR Generate SEO URL based on standard URL
		$this->loadModel('tool/blog_seo_url');
		return $this->model_tool_blog_seo_url->rewrite($this->getBLOGCONURL($rt, $params));
	}
	
	public function getBLOGCONURL($rt, $params = '') {

		if ($this->registry->get('config')->get('storefront_template_debug') && isset($this->registry->get('request')->get['tmpl_debug'])) {
			$params .= '&tmpl_debug=' . $this->registry->get('request')->get['tmpl_debug'];
		}

		$url = $this->server . INDEX_FILE . $this->url_encode($this->buildURL($rt, $params));
		return $url;
	}
	
	/**
	 * method add new og:property content
	 *
	 * @param array $ogtag_item
	 * @internal param array $item ("property"=>"","content"=>"")
	 * Examples: property => 'og:title', 'rel'  => 'page_title'
	 * @return null
	 */
	public function addBlogOgTag($ogtag_item = array()) {
		if ($ogtag_item["content"]) {
			$this->blogOgTags[ ] = $ogtag_item;
		}
	}

	public function getBlogOgTags() {
		return $this->blogOgTags;
	}

	public function resetBlogOgTags() {
		$this->blogOgTags = array();
	}

	/**
	 * method add new twitter:property content
	 *
	 * @param array $twtag_item
	 * @internal param array $item ("name"=>"","content"=>"")
	 * Examples: name => 'twitter:title', 'content'  => 'page_title'
	 * @return null
	 */
	public function addBlogTwTag($twtag_item = array()) {
		if ($twtag_item["content"]) {
			$this->blogTwTags[ ] = $twtag_item;
		}
	}

	public function getBlogTwTags() {
		return $this->blogTwTags;
	}

	public function resetBlogTwTags() {
		$this->blogTwTags = array();
	}
















}

?>