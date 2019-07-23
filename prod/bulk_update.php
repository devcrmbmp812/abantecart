<?php
ini_set('memory_limit','32M');
ini_set('display_errors', '1');
error_reporting(E_ALL);

?>

<!DOCTYPE html>
<html>
<head>
  <script language="javascript" src="users.js" type="text/javascript"></script>
  <style type="text/css">
  	.csv-file-input{
	  width: 40%;
	  padding: 10px 15px;
	  margin-bottom: 10px;
	  box-sizing: border-box;
	}
	.preview{
	  background-color: #00A2C6;
	  border: none;
	  color: white;
	  padding: 10px 30px;
	  text-align: center;
	  text-decoration: none;
	  font-size: 16px;
	  cursor: pointer;
	}
	.preview:hover{
  	  opacity: 0.8;
	}

	.update{
	  background-color: #00A2C6;
	  border: none;
	  color: white;
	  padding: 10px 30px;
	  text-align: center;
	  text-decoration: none;
	  font-size: 16px;
	  cursor: pointer;
	  width: 750px;
	  margin: 10px 0px;
	}
	.update:hover{
  	  opacity: 0.9;
	}
	input:disabled, textarea:disabled{
		color: black;
	}

  </style>
</head>
<body>
  <div class="tsv-search-box">
    <div class="tsv-search-input">
      <form method="post">
        <input type="text" class="csv-file-input" name="tsv_file" placeholder="Enter Tsv file" value="https://docs.google.com/spreadsheets/d/e/2PACX-1vRmykdQpTo49WvEPqkjIWARg0UgxIOpwaTMtIq7ETVrNwHMhviJMPkBTiB81I1lzMxGV8bZmXwRH8S3/pub?gid=302416844&single=true&output=tsv">
        <input type="submit" name="tsv_submit" class="preview" value="Preview">
      </form>
    </div>  	
  </div>
</body>
</html>
<?php
	define('MIN_PHP_VERSION', '5.3.0');
	if (version_compare(phpversion(), MIN_PHP_VERSION, '<') == true){
		die(MIN_PHP_VERSION . '+ Required for AbanteCart to work properly! Please contact your system administrator or host service provider.');
	}
	
	// ob_start();
	
	// Load Configuration
	// Real path (operating system web root) to the directory where abantecart is installed
	$root_path = dirname(dirname(__FILE__));
	
	// Windows IIS Compatibility  
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		define('IS_WINDOWS', true);
		$root_path = str_replace('\\', '/', $root_path);
	}
	
	
	define('DIR_ROOT', $root_path);
	define('DIR_CORE', DIR_ROOT . '/core/');
	
	require_once(DIR_ROOT . '/system/config.php');
	
	ini_set('memory_limit','32M');
	
	//set server name for correct email sending
	if (defined('SERVER_NAME') && SERVER_NAME != ''){
		putenv("SERVER_NAME=" . SERVER_NAME);
	}
	
	// New Installation
	if (!defined('DB_DATABASE')){
		header('Location: install/index.php');
		exit;
	}
	$stream = '';
	if (isset($_POST['tsv_submit'])) {
		if($_POST['tsv_submit'] == 'Preview'){
			$stream = $_POST['tsv_file'];			
		}

	}
	// sign of admin side for controllers run from dispatcher
	$_GET['s'] = ADMIN_PATH;
	// Load all initial set up
	require_once(DIR_ROOT . '/core/init.php');
		
	// not needed anymore
	unset($_GET['s']);
	
	    
		
			
		
		$conn = new mysqli(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
		if($conn->connect_error)
		{
			echo "connect db fail";
			exit;
		}

		if(isset($_POST["users"]) && !empty($_POST["users"])) {
			$id = $_POST['users'];
			$updated_price = $_POST['price'];	
			$results = array_intersect_key($id,$updated_price);
			  foreach ($results as $result) {
				$sql = "update abc_product_discounts set price = '".$_POST['price'][$result]."' where product_id = '".$_POST['users'][$result]."'";
				$res = mysqli_query($conn,$sql);
				if (!$res) {
					echo "Error: " . $sql . "<br>" . mysqli_error($conn);
				}
			  }
	    }
		
		try
		{

		if(($fileTsv = fopen($stream, 'r')))
				{

			?> 
			<form name="frmUser" method="post" action="">
			  <table border="1" cellpadding="10" cellspacing="0" width="750" class="tblListForm"> 
				<tr class="listheader" align="center" style="background-color:#00A2C6; color: white; font-weight: 700;">
				  <td>Update</td>
				  <td>Model</td>
				  <td>Old Price</td>
				  <td>New Price</td>
				</tr>
					<input type="hidden" name="tsv_file" value="<?php echo $_POST['tsv_file']; ?>">
			<?php
			$data = fgets($fileTsv);
			for ($i=0; $i <100 ; $i++) {
				if(!feof($fileTsv))
				{
					// if($i == 0) { continue; }
					$data = fgets($fileTsv);
				    $pieces= explode("\t",$data);
				    $name = $pieces[0];
				    $id = $pieces[2];
				    $oldprice = $pieces[3];
				    $price = $pieces[7];
				?>
					<tr align="center">
					  <td><input type="checkbox" class="chk_boxes1"	name="users[<?php echo $id; ?>]" value="<?php echo $id; ?>" ></td>
					  <td><input name="name[<?php echo $id; ?>]" type="text" id="name" value="<?php echo $name; ?>" readonly></td>
					  <td><input name="oldprice[<?php echo $id; ?>]" type="text" id="oldprice" value="<?php echo $oldprice; ?>" readonly></td>
					  <td><input name="price[<?php echo $id; ?>]" type="text" id="price" value="<?php echo $price; ?>" readonly></td>
					</tr>
				<?php	 
				}else{
					break;
				}
		    }
			?>
			</table>
			<input type="hidden" name="action" value="update">
				<!-- <input type="button" class="update" name="update" value="Update" onClick="setUpdateAction();" /> -->
				<input type="submit" class="update" name="action" value="Update">
			</form>
			<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
			<script>
			$(document).ready(function() {
				$('.chk_boxes1').attr('checked',true);			    
			});
			</script>
			<?php
			
			// throw new Exception(sprintf('%s: Cannot open file',$stream));
		}else{
			echo 'Please enter a tsv file <br>';
		}

		}
		catch (Exception $e)
		{
				echo $e;
		}
		finally
		{
			fclose($stream);
			mysqli_close($conn);
		}




?>