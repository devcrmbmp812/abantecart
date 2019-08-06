<style>
.ultraimage {
  margin-right: 4px;
  float: left;
  margin-bottom: 2px;

}

<?php if ($this->config->get('ultra_search_textcut')) {
    ?>
a.search_result {
  overflow: hidden;
  text-overflow: ellipsis;
  -o-text-overflow: ellipsis;
  white-space: nowrap;
}
<?php
} ?>
.newsearchchosen:before {
    content: "\f002";
    font-family: FontAwesome;
    position:absolute;
    right: 10px;
    top: 10px;
}

<?php if (!$this->config->get('ultra_search_colour_ignore')) {
        ?>
.chosen-single.chosen-default {
background-color: #<?php echo $this->config->get('ultra_search_colour_1'); ?>;
}
#global_search_chosen .chosen-single span {
color: #<?php echo $this->config->get('ultra_search_colour_2'); ?>;
}

.chosen-container .chosen-results li.group-result {
background-color: #<?php echo $this->config->get('ultra_search_colour_3'); ?>;
}
.chosen-container .chosen-results li.group-result {
color: #<?php echo $this->config->get('ultra_search_colour_8'); ?>;
}



.chosen-results, .chosen-container-single .chosen-drop {
background-color: #<?php echo $this->config->get('ultra_search_colour_4'); ?>;
}

#global_search_chosen .active-result.group-option > a {
color: #<?php echo $this->config->get('ultra_search_colour_5'); ?>;
}

#global_search_chosen .active-result.highlighted {
background-color: #<?php echo $this->config->get('ultra_search_colour_6'); ?>;
}

#global_search_chosen .highlighted a {
color: #<?php echo $this->config->get('ultra_search_colour_7'); ?> !important;
}
/*background-image:url(resources/'.$prlx.');*/
<?php
    } ?>
</style>
<script type="text/javascript">
console.log('<?php echo $search_auto; ?>');
function myclickFunction() {
        <?php if ($this->config->get('ultra_search_enter')) {
        ?>
		var searchkey = document.querySelector('#enterkey').value;
		window.location = "index.php?rt=product/search&keyword="+searchkey+"&limit=20&sort=p.price-DESC";
        <?php
    } ?>
}



$(document).ready(function () {
  /*enter hint*/
  /*$("#enterkey").keypress(function (e) {
      console.log('keypress');
      if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
          //$('button[type=submit] .default').click();
          console.log('false');
          return false;

      } else {
          console.log('true');
          return true;
      }
  });*/
  var searchSectionIcon = function(section) {
  	switch(section) {
  		case 'product_categories':
  			return '<i class="fa fa-tags fa-fw"></i> ';
  			break;
  		case 'products':
  			return '<i class="fa fa-tag fa-fw"></i> ';
  			break;
  		case 'reviews':
  			return '<i class="fa fa-comment fa-fw"></i> ';
  			break;
  		case 'manufacturers':
  			return '<i class="fa fa-bookmark fa-fw"></i> ';
  			break;
  		case 'pages':
  			return '<i class="fa fa-clipboard fa-fw"></i> ';
  			break;
  		default:
  			return '<i class="fa fa-info-circle fa-fw"></i> ';
  			break;
  	}
  };


//place chosen
if($("#filter_category_id_block" + name).length == 0) {
  //it doesn't exist
  var replace_id = '<?php echo $ultra_search_search_id; ?>';
} else {
  var replace_id = 'filter_category_id_block';
}
$( "#" + replace_id ).before("<select id=\"global_search\" name=\"search\" data-placeholder=\"<?php echo $search_everywhere; ?>\" class=\"chosen-select form-control aselect\" style=\"display:none;\"><option></option></select>");


//hide default search input
/*if (/Android/i.test(window.navigator.userAgent)) {
	if (/Mobile/i.test(window.navigator.userAgent)) {
		//return false;
	}
} else {*/
var searchw = $( "#" + replace_id ).width();
console.log(searchw);
var chooswidth = 260;
if (searchw > 160) { var chooswidth = searchw;}
$( "#" + replace_id ).hide();
//}


//global search section
            $("#global_search").chosen({'width': chooswidth + 'px', 'white-space': 'nowrap',
            no_results_text: "<?php echo $ultra_search_text_oops; ?>",
						placeholder_text_single: "<?php echo $ultra_search_text_search; ?>",
						search_contains: true,
						enable_split_word_search: true,
						search_contains: true,
                        //width: '',
						//max_shown_results
          });


		     var new_w = <?php echo $ultra_search_new_window; ?>;
			//alert(new_w);
            $("#global_search").ajaxChosen({
                type: 'GET',
                url: '<?php echo $search_auto; ?>',
                dataType: 'json',
                jsonTermKey: "term",
                keepTypingMsg: "<?php echo $text_continue_typing; ?>",
                lookingForMsg: "<?php echo $text_looking_for; ?>"
            }, function (data) {
                if (data.response.length < 1) {
                    $("#searchform").chosen({no_results_text: "<?php echo $text_no_results; ?>"});
                    return '';
                }
                //build result array
                var dataobj = new Object;
                $.each(data.response, function (i, row) {
                    if (!dataobj[row.category]) {
                        dataobj[row.category] = new Object;
                        dataobj[row.category].name = row.category_name;
                        dataobj[row.category].icon = row.category_icon;
                        dataobj[row.category].items = [];
                    }

										if (new_w == 1) {
											var onclick = 'onClick="window.open(\'' + row.page + '\');"';
										} else {
											var onclick = 'onClick="window.location.replace(\'' + row.page + '\');"';
										}

                    if(typeof row.text !== 'undefined' || variable !== null){
                    // variable is undefined or null
                    if(typeof row.image !== 'undefined'){
                    var html = '<a ' + onclick + ' class="search_result" title="' + row.text + '"><img class="ultraimage" src="' + row.image + '" height="40" width="40">' + row.title + '</a>';
                    } else {
                    var html = '<a ' + onclick + ' class="search_result" title="' + row.text + '">' + row.title + '</a>';
                    }
                    }

                    dataobj[row.category].items.push({value: row.order_id, text: html});
                });
                var results = [];
                var search_action = '<?php echo $search_action ?>&search=' + $('#global_search_chosen input').val();
                //console.log(search_action);
                var onclick = 'onClick="window.open(\'' + search_action + '\');"';
                results.push({
                    value: 0,
                    text: '<div class="text-center"><a ' + onclick + ' class="btn btn-deafult"><?php echo $search_everywhere; ?></a></div>'
                });
                $.each(dataobj, function (category, datacat) {
                    var url = search_action + '#' + category;
                    var onclick = 'onClick="window.open(\'' + url + '\');"';
                    var header = '<span class="h5">' + searchSectionIcon(category) + datacat.name + '</span>';
                    //show more result only if there are more records
                    if (datacat.items.length == 3) {
                        //header += '<span class="pull-right"><a class="more-in-category" ' + onclick + '><?php echo $ultra_search_text_matches;?></a></span>';
                        //console.log(onclick);
                    }
                    results.push({
                        group: true,
                        text: header,
                        items: datacat.items
                    });
                });
                //unbind chosen click events
                $('#global_search_chosen .chosen-results').unbind();

                return results;
            });


});
</script>
