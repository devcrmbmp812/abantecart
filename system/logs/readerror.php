<?php
$error=file_get_contents('error.txt');
print_r(str_replace("\n",'<br>',$error));
?>