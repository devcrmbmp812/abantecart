<script>
window.addEventListener("load", dynWidth);
window.addEventListener("resize", dynWidth);
//~ dynWidth();
function dynWidth() {
	//~ chooswidth = window.innerWidth - 1010;
	chooswidth = window.innerWidth - 1060;
	if (window.innerWidth <= 1200) {chooswidth = window.innerWidth - 790;}
	if (window.innerWidth <= 1040) {chooswidth = window.innerWidth - 780;}
	if (window.innerWidth <= 1000) {chooswidth = window.innerWidth - 710;}
	if (window.innerWidth <= 940) {chooswidth = window.innerWidth - 740;}
	if (window.innerWidth <= 799 && window.innerWidth > 599) {chooswidth = 320;}
	document.getElementById('searchbox').setAttribute("style","width:" + chooswidth + "px");
}

</script>
<header>
<div class="headerstrip navbar navbar-inverse" role="navigation">
	<div class="container-fluid">
	  <div class="navbar-header header-logo">
	    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
	      <span class="sr-only"></span>
	      <span class="icon-bar"></span>
	      <span class="icon-bar"></span>
	      <span class="icon-bar"></span>
	    </button>
	    <?php if (is_file(DIR_RESOURCE . $logo)) { ?>
		<a class="logo" href="<?php echo $homepage; ?>">
			<img src="resources/<?php echo $logo; ?>" width="<?php echo $logo_width; ?>" height="<?php echo $logo_height; ?>" title="<?php echo $store; ?>" alt="<?php echo $store; ?>"/>
		</a>
		<?php } else if (!empty($logo)) { ?>
	    	<a class="logo" href="<?php echo $homepage; ?>"><?php echo $logo; ?></a>
	    <?php } ?>
	  </div>
	  <div class="navbar-collapse collapse">
	  	<div class="navbar-right headerstrip_blocks">
	  	    <div class="block_1"><?php echo ${$children_blocks[0]}; ?></div>
	  	    <div class="block_2" id="searchbox"><?php echo ${$children_blocks[1]}; ?></div>
	  	    <div class="block_3" width="10px">&nbsp;</div>
	  	    <div class="block_3"><?php echo ${$children_blocks[2]}; ?></div>
	  	    <div class="block_4"><?php echo ${$children_blocks[3]}; ?></div>
	  	</div>
	   </div><!--/.navbar-collapse -->
	</div>
</div>
<div class="container-fluid">
    <div class="col-md-12 headerdetails">
    	<!-- header blocks placeholder -->
    	<div class="block_5"><?php echo ${$children_blocks[4]}; ?></div>
    	<div class="block_6"><?php echo ${$children_blocks[5]}; ?></div>
    	<div class="block_7"><?php echo ${$children_blocks[6]}; ?></div>
    	<div class="block_8"><?php echo ${$children_blocks[7]}; ?></div>
    	<!-- header blocks placeholder (EOF) -->
    </div>
</div>
</header>