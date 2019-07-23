<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript">
  (function($){
    $(document).ready(function(){
	  var keyword = '';
	  $.ajax({
	    url: "index.php?rt=a/fetch/fetch_top_result/searchKeywords&api_key=<?php echo $this->config->get('config_storefront_api_key'); ?>",
	    success: function(data) {
	      keyword = JSON.parse(data);
		  $( "#search_form" ).find("input[type=text]").autocomplete({
		    source: function (request, response) {
		    	var searchedKeyword = request.term.toLowerCase();
		    	var responseKeyword = [];
		    	for(var i in keyword){
		    		var se = new RegExp("(\\b"+searchedKeyword+"\\S+\\b)", "ig");
		    		var nse = new RegExp("(\\b"+searchedKeyword+" "+"\\S+\\b)", "ig");
					if(keyword[i].toLowerCase().indexOf(searchedKeyword) > -1 && (keyword[i].match(se) != null || keyword[i].match(nse) != null)){
		    			responseKeyword.push(keyword[i]);
		    		}
		    	}
		    	if(responseKeyword.length == 0){
		    		$.ajax({
					    url: "index.php?rt=a/fetch/fetch_top_result/searchDatabase&api_key=<?php echo $this->config->get('config_storefront_api_key'); ?>&term="+searchedKeyword,
					    success: function(data) {
					    	var result = JSON.parse(data);
					    	console.log("From Database");
					    	response(result);
					    }
					});
		    	}else{
		    		console.log("From Google");
			    	response(responseKeyword.slice(0, 10));
			    }
		    },
		    select: function(event, ui) {
		      $( "#search_form" ).find("input[type=text]").val(ui.item.value);
    	      $("#search_form").submit(); 
	        }
		  });
		}
	  });
        $(".search-bar").removeClass('open');
	    $( "#search_form" ).find("input[type=text]").change(function(){
	    	var searchedLogKeyword = $(this).val();
	    	$.ajax({
			    url: "index.php?rt=a/fetch/fetch_top_result/logKeywords&api_key=<?php echo $this->config->get('config_storefront_api_key'); ?>&term="+searchedLogKeyword,
			    success: function(data) {
			    	console.log(data);
			    }
			});			   
		});
	});
  }(jQuery));  
</script>
<style type="text/css">
  #search-category{ display: none !important; }
</style>