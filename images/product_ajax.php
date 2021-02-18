<?php 
include('import_product.php');
$product = new Product();
$product->ImportProduct($_GET['code'],$_GET['child_data']);
?>