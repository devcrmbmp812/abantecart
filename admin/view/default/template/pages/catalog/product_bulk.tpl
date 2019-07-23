<div id="content" class="panel panel-default">
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<div class="tsv-search-box">
		    <div class="tsv-search-input">
		      <form method="post" action="<?php echo $action;?>">
		        <input type="text" class="csv-file-input" name="tsv_file" placeholder="Enter Tsv file" 
		        	value="https://docs.google.com/spreadsheets/d/e/2PACX-1vRmykdQpTo49WvEPqkjIWARg0UgxIOpwaTMtIq7ETVrNwHMhviJMPkBTiB81I1lzMxGV8bZmXwRH8S3/pub?gid=302416844&single=true&output=tsv">
		        <input type="submit" name="tsv_submit" class="preview" value="Preview">
		      </form>
		    </div>  	
		</div>
		<?php
		if (sizeof($sheet) > 0) {
		?>
			<form name="frmUser" method="post" action="<?php echo $update;?>">
				<input type="hidden" name="tsv_file" value="<?php echo $tsv_file; ?>">
				<table border="1" cellpadding="10" cellspacing="0" width="100%" class="tblListForm"> 
					<tr class="listheader" align="center" style="background-color:#00A2C6; color: white; font-weight: 700;">
					  <td>Update</td>
					  <td>Model</td>
					  <td>Old Price</td>
					  <td>New Price</td>
					</tr>
				<?php
				foreach ($sheet as $value) {
					$name = $value['name'];
					$id = $value['id'];
					$oldprice = $value['oldprice'];
					$price = $value['price'];
				?>
					<tr align="center">
					  <td><input type="checkbox" class="chk_boxes1"	name="users[<?php echo $id; ?>]" value="<?php echo $id; ?>" ></td>
					  <td><input name="name[<?php echo $id; ?>]" type="text" id="name" value="<?php echo $name; ?>" readonly></td>
					  <td><input name="oldprice[<?php echo $id; ?>]" type="text" id="oldprice" value="<?php echo $oldprice; ?>" readonly></td>
					  <td><input name="price[<?php echo $id; ?>]" type="text" id="price" value="<?php echo $price; ?>" ></td>
					</tr>
				<?php
				}
				?>
				</table>
			  <input type="hidden" name="action" value="update">
			  <input type="submit" class="update" name="action" value="Update">
			</form>
		<?php
		}
		?>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('.chk_boxes1').attr('checked',true);			    
	});
</script>