
<form id="blog_search_form" class="form-search top-search">
    <div class="btn-group search-bar">
    	<input type="text"
			   id="blog_keyword"
			   name="blog_keyword"
			   autocomplete="off"
    		   class="pull-left form-control"
			   placeholder="<?php echo $text_blog_search; ?>"
			   value="" />
    	 <div id="blog_search_button" class="button-in-search" title="<?php echo $button_go; ?>"><i class="fa fa-search"></i></div>
    </div>
</form>

<script type="text/javascript"><!--

$(document).ready(function() {

	//submit search
	$('#blog_search_form').submit(function() {
		return blog_search_submit();
	});
	$('#blog_search_button').on('click', function() {
		return blog_search_submit();
	});
});	

function blog_search_submit () {

    var url = '<?php echo $search_url; ?>';

	var blog_keyword = $('#blog_keyword').val();
	if (blog_keyword) {
	    url = url + '&keyword=' + encodeURIComponent(blog_keyword);
	}
	location = url;

	return false;
}
//--></script>