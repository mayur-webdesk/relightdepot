<?php
class Productmodel extends CI_Model{
	
	var $table_name	= "";
	function __construct()
	{
		$this->allmagento_products_sku_table 	= "allmagento_products_sku";
		$this->product_rel_child_table 			= "product_rel_child";
		$this->products_magenot_table 			= "products";
		$this->product_category_table	   		= "categories";
		$this->setting_table 		 	   		= "users";
		$this->mg_mconnectuploadfile_table 		= "mg_mconnectuploadfile";
		$this->productstock_table 				= "productstock";		
	}

	function getAllProducts()
	{
		$query = $this->db->query("select ProductID from newproducts where status = 'no'");
		return $query->result_array();
	}

	function CheckProducts($old_url)
	{
		$query = $this->db->query("select * from redirect where old_url = '".$old_url."'");
		return $query->row_array();
	}

	function updateUrlRecord($getUrls)
	{
		$query = $this->db->query("UPDATE `redirect_second` SET status = 'yes' where id = '".$getUrls['id']."'");
	}

	function getAccount($account)
	{
		$query = $this->db->query("select label from account where value = '".$account."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getCogs_account($cogs_account)
	{
		$query = $this->db->query("select label from cogs_account where value = '".$cogs_account."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getSize($size)
	{
		$query = $this->db->query("select label from size where value = '".$size."'");
		$data = $query->row_array();
		return $data['label'];
	}
	
	function getProduct_options($product_options)
	{
		$query = $this->db->query("select label from product_options where value = '".$product_options."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getWagon_size($wagon_size)
	{
		$query = $this->db->query("select label from wagon_size where value = '".$wagon_size."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getSpoke_size($spoke_size)
	{
		$query = $this->db->query("select label from spoke_size where value = '".$spoke_size."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getTire_width($tire_width)
	{
		$query = $this->db->query("select label from tire_width where value = '".$tire_width."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getFinish($finish)
	{
		$query = $this->db->query("select label from finish where value = '".$finish."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getSpecifymount($specify_mount)
	{
		$query = $this->db->query("select label from specify_mount where value = '".$specify_mount."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getStyle($lamp_style)
	{
		$query = $this->db->query("select label from lamp_style where value = '".$lamp_style."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getAxle_size($axle_size)
	{
		$query = $this->db->query("select label from axle_size where value = '".$axle_size."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getOptionValue($table,$value,$bcproductid)
	{
		$query = $this->db->query("select label from ".$table." where value = '".$value."'");
		$magento_v = $query->row_array();
		
		if(isset($magento_v) && !empty($magento_v))
		{
			$query = $this->db->query("select * from `option_values` where option_value = '".$magento_v['label']."' and product_id = '".$bcproductid."'");
			return $query->row_array();
		}
	}

	function getOptionValue1($table,$value,$bcproductid)
	{
		$query = $this->db->query("select label from ".$table." where value = '".$value."'");
		$magento_v = $query->row_array();

		if(isset($magento_v) && !empty($magento_v))
		{
			$query = $this->db->query("select * from `option_values` where option_value = '".$magento_v['label']."' and product_id = '".$bcproductid."'");
			return $query->row_array();
		}
	}

	function getOptionValue2($table,$value,$bcproductid)
	{
		$query = $this->db->query("select label from ".$table." where value = '".$value."'");
		$magento_v = $query->row_array();

		if(isset($magento_v) && !empty($magento_v))
		{
			$query = $this->db->query("select * from `option_values` where option_value = '".$magento_v['label']."' and product_id = '".$bcproductid."'");
			return $query->row_array();
		}
	}

	function updateSKUstatus($product_id)
	{
		$query = $this->db->query("UPDATE `products` SET sku_status = 'yes' where product_id = '".$product_id."'");
	}

	function UpdateSimpleProduct($simple_id,$Product_Id)
	{
		$query = $this->db->query("UPDATE `products` SET simple_product_map = '".$Product_Id."',status = 'yes' where product_id = '".$simple_id."'");
	}

	function updateTireProduct($product_id)
	{
		$query = $this->db->query("UPDATE `products` SET tire_price = 'yes' where product_id = '".$product_id."'");
	}

	function getCategory($category_id)
	{
		$query = $this->db->query("select bc_category_id from ".$this->product_category_table." where category_id = '".$category_id."'");
		$data = $query->row_array();
		if(isset($data) && !empty($data))
		{
			return $data['bc_category_id'];
		}else{
			return 0;
		}
	}

	function inserOptionValue($Option_value)
	{
		$this->db->insert("optionvalue",$Option_value);
	}

	function deleteProduct($product_id)
	{
		//$query = $this->db->query("DELETE FROM `products` WHERE `product_id` = '".$product_id."'");
		$query = $this->db->query("UPDATE `products` SET `delete` = 'yes' WHERE product_id = '".$product_id."'");
	}

	function updateImageStatus($product_id)
	{
		$query = $this->db->query("UPDATE `products` SET image_status = 'yes' WHERE product_id = '".$product_id."'");
	}
	
	function updateHrefStatus($product_id)
	{
		$query = $this->db->query("UPDATE `newproducts` SET status = 'yes' WHERE ProductID = '".$product_id."'");
	}

	function updateProductdata($product_id,$ProductStatus)
	{
		$query = $this->db->query("UPDATE `products` SET visibility = '".$ProductStatus."' WHERE product_id = '".$product_id."'");
	}
	
	function UpdateProductStatusp($bcproductid,$bc_product_url,$product_id)
	{
		$query = $this->db->query("UPDATE ".$this->products_magenot_table." SET bc_product_id = '".$bcproductid."', bc_product_url = '".$bc_product_url."' , status = 'yes' WHERE product_id = '".$product_id."'");
	}

	function getConfigrationProducts()
	{
		//$query = $this->db->query("SELECT * FROM ".$this->products_magenot_table." where visibility = 'no'");
		$query = $this->db->query("SELECT * FROM ".$this->products_magenot_table." WHERE type = 'virtual'  and status = 'no'");
		return $query->result_array();
	}

	function getGeneralSetting()
	{
		$query = $this->db->query("select * from ".$this->setting_table."");
		$setting_data  = $query->row_array();
		return $setting_data;
	}
	
	
	public function getStatusProduct(){
		//$query = $this->db->query("SELECT * from products WHERE type = 'configurable' AND `status` = 'yes' and shopify_product_id!='' and `is_enable` IS NULL");
		$query = $this->db->query("SELECT * from `products` where `status` = 'no'");
		return $query->result_array();
	}

	public function updateEnablestatus($product_id,$magento_url){
		//$query = $this->db->query("UPDATE ".$this->products_magenot_table." SET is_enable = '".$stat."' WHERE product_id = '".$product_id."'");
		$query = $this->db->query("UPDATE ".$this->products_magenot_table." SET shopify_url = '".$magento_url."' WHERE product_id = '".$product_id."'");
	}
	
	public function getProductsURL($simple_product_map)
	{
		$query = $this->db->query("SELECT shopify_url from products where `product_id` = '".$simple_product_map."'");
		return $query->row_array();
	}
	
	
}
?>