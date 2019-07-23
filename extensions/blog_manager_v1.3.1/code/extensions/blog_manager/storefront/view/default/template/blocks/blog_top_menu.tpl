<section id="blog_top_menu"> 
 	
		<ul class="nav-pills blog_top_menu">
            <?php foreach ($btm as $menu) { ?>
                <?php if (!$menu['list']) {  ?>
                    <li><a href="<?php echo $menu['url']; ?>" <?php if($menu['id']) { echo 'id="' .$menu['id'] . '"'; } ?>><?php echo $menu['name']; ?></a></li>
                <?php }else{ ?>
                    <li class="dropdown"><a href="<?php echo $menu['url']; ?>" <?php if($menu['id']) { echo 'id="' .$menu['id'] . '"'; } ?>><?php echo $menu['name']; ?><?php if (!empty($menu['list'])) { ?><b class="caret"></b><?php } ?></a>
                        <div >
                            <ul class="dropdown">
                                <?php foreach($menu['list'] as $list) { ?>
                                    <?php if(!$list['children']) { ?>
                                        <li class="dropdown"><a href="<?php echo $list['url']; ?>">&nbsp;&nbsp;<?php echo $list['name']; ?></a></li>
                                        <?php if($menu['id'] == 'blog_login_top_link' && $list['name'] == $text_notifications) { ?>
                                            <?php echo $this->getHookVar('blog_author_top_menu_item'); ?>
                                        <?php } ?>
                                    <?php }else{ ?>
                                        <li class="dropdown"><a href="<?php echo $list['url']; ?>">&nbsp;&nbsp;<?php echo $list['name']; ?><?php if (!empty($list['children'])) { ?><b class="caret"></b><?php } ?></a>
                                            <ul class="dropdown-menu">
                                                <?php foreach($list['children'] as $child) { ?> 	
                                                    <li class="dropdown"><a href="<?php echo $child['url']; ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $child['name']; ?></a></li>
                                                <?php } ?>
                                            </ul>
                                        </li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        </div>
                    </li>
                <?php } ?>
            <?php } ?> 
        </ul>
 	
</section>

<script type="text/javascript"><!--

    $('document').ready(function () {
		
		$("#blog_top_menu a").on('click', function() {
			var el = $(this);
			if(el.attr('href').length==0 || el.attr('href')=='#'){ return false;}
		});
    // Blog Top Menu mobile
        $('<select id="blog-menu" class="form-control" />').appendTo("#blog_top_menu");
        
        // Populate dropdown with menu items
        $("#blog_top_menu a").each(function () {
            var el = $(this);

			if(el.attr("id") == 'blog_login_top_link') {
				 $("<option />", {
            		"value": el.attr("href"),
            		"text": el.text()
				}).prependTo("#blog_top_menu select");
			}else if (el.attr("id") == 'author_login_top_link') {
				$("<option />", {
            		"value": el.attr("href"),
            		"text": el.text()
				}).prependTo("#blog_top_menu select");
			}else{
				$("<option />", {
					"value": el.attr("href"),
					"text": el.text()
				}).appendTo("#blog_top_menu select");
			}
        });
		// Create default option
        $("<option />", {
            "selected": "selected",
            "value": "",
            "text": "Blog Menu"
        }).prependTo("#blog_top_menu select");
        // To make dropdown actually work
        // To make more unobtrusive: http://css-tricks.com/4064-unobtrusive-page-changer/
        $("#blog_top_menu select").change(function () {
            window.location = $(this).find("option:selected").val();
        });
    });
	
//--></script>