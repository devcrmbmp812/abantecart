<!DOCTYPE html>
<html lang="en" dir="ltr" >
<head><meta charset="utf-8">
<title><?php echo $title; ?></title>

<link rel="stylesheet" type="text/css" href="admin/view/default/stylesheet/stylesheet.css" />
<link rel="stylesheet" type="text/css" href="/extensions/blog_manager/admin/view/default/stylesheet/blog_manager.css" />
</head>
<body>


<div class="modal-header">
	<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
	
</div>
<div class="tab-content">
	<div class="panel-body panel-body-nopadding">
    <h4 class="modal-title"><?php echo $title; ?></h4>
		<div class="blog_img" style="max-height: 700px; overflow-y: scroll;"><?php echo $content; ?></div>
	</div>
</div>

</body></html>