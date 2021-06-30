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

	function getBrands()
	{
		//$query = $this->db->query("select * from brand where status = 'no'");
		$query = $this->db->query("select * from brand ");
		return $query->result_array();
	}

	function updateProductBrand($brand_id,$bc_brand_id)
	{
		$query = $this->db->query("UPDATE `brand` SET status = 'yes', bc_brand_id = '".$bc_brand_id."' where brand_id = '".$brand_id."'");
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

	function getAttrubuteLable($attribute_id,$attribute_code)
	{
		$query = $this->db->query("select * from ".$attribute_code." where value = '".$attribute_id."'");
		$data = $query->row_array();

		if(isset($data['label']) && !empty($data['label']))
		{
			return $data;
		}else{
			return '';
		}
	}

	function getmanufacturer_multiselect($manufacturer_multiselect)
	{
		$query = $this->db->query("select label from manufacturer_multiselect where value = '".$manufacturer_multiselect."'");
		$data = $query->row_array();
		return $data['label'];
	
	}

	function getballast_technology($ballast_technology)
	{
		$explode_ball = explode(',',$ballast_technology);

		$data = array();
		foreach($explode_ball as $explode_balls)
		{
			$query = $this->db->query("select label from ballast_technology where value = '".$explode_balls."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getballast_type($ballast_type)
	{
		$explode_balltype = explode(',',$ballast_type);

		$data = array();
		foreach($explode_balltype as $explode_balltypes)
		{
			$query = $this->db->query("select label from ballast_type where value = '".$explode_balltypes."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getlamp_count($lamp_count)
	{
		$explode_lamp = explode(',',$lamp_count);

		$data = array();
		foreach($explode_lamp as $explode_lamps)
		{
			$query = $this->db->query("select label from lamp_count where value = '".$explode_lamps."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getlamp_types($lamp_types)
	{
		$explode_lamptypes = explode(',',$lamp_types);

		$data = array();
		foreach($explode_lamptypes as $explode_lamptypes_s)
		{
			$query = $this->db->query("select label from lamp_types where value = '".$explode_lamptypes_s."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getlamp_type($lamp_type)
	{
		$explode_lamptype = explode(',',$lamp_type);

		$data = array();
		foreach($explode_lamptype as $explode_lamptypes)
		{
			$query = $this->db->query("select label from lamp_type where value = '".$explode_lamptypes."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getlamp_wattage($lamp_wattage)
	{
		$explode_watt = explode(',',$lamp_wattage);

		$data = array();
		foreach($explode_watt as $explode_watts)
		{
			$query = $this->db->query("select label from lamp_wattage where value = '".$explode_watts."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getline_voltage($line_voltage)
	{
		$explode_volt = explode(',',$line_voltage);

		$data = array();
		foreach($explode_volt as $explode_volts)
		{
			$query = $this->db->query("select label from line_voltage where value = '".$explode_volts."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getstarting_type($starting_type)
	{
		$explode_start = explode(',',$starting_type);

		$data = array();
		foreach($explode_start as $explode_starts)
		{
			$query = $this->db->query("select label from starting_type where value = '".$explode_starts."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getballast_factor($ballast_factor)
	{
		$explode_factor = explode(',',$ballast_factor);

		$data = array();
		foreach($explode_factor as $explode_factors)
		{
			$query = $this->db->query("select label from ballast_factor where value = '".$explode_factors."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getlight_source_texas($light_source_texas)
	{
		$explode_light_source = explode(',',$light_source_texas);

		$data = array();
		foreach($explode_light_source as $explode_light_sources)
		{
			$query = $this->db->query("select label from light_source_texas where value = '".$explode_light_sources."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getfixture_size($fixture_size)
	{
		$explode_fixture_size = explode(',',$fixture_size);

		$data = array();
		foreach($explode_fixture_size as $explode_fixture_sizes)
		{
			$query = $this->db->query("select label from fixture_size where value = '".$explode_fixture_sizes."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getballast_and_voltage($ballast_and_voltage)
	{
		$query = $this->db->query("select label from ballast_and_voltage where value = '".$ballast_and_voltage."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getlistings_and_ratings_taxas($listings_and_ratings_taxas)
	{
		$explode_rating = explode(',',$listings_and_ratings_taxas);

		$data = array();
		foreach($explode_rating as $explode_ratings)
		{
			$query = $this->db->query("select label from listings_and_ratings_taxas where value = '".$explode_ratings."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getrc_housing_type($rc_housing_type)
	{
		$query = $this->db->query("select label from rc_housing_type where value = '".$rc_housing_type."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getrc_insulation_rating($rc_insulation_rating)
	{
		$query = $this->db->query("select label from rc_insulation_rating where value = '".$rc_insulation_rating."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getrc_lamp_position($rc_lamp_position)
	{
		$query = $this->db->query("select label from rc_lamp_position where value = '".$rc_lamp_position."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getManufactures($manufacturer)
	{
		$query = $this->db->query("select label from manufacturer where value = '".$manufacturer."'");
		$data = $query->row_array();
		return $data['label'];
	}

	function getapplication($application)
	{
		$explode_app = explode(',',$application);

		$data = array();
		foreach($explode_app as $explode_apps)
		{
			$query = $this->db->query("select label from application where value = '".$explode_apps."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}

	function getmounting_height($mounting_height)
	{
		$explode_mounting_height = explode(',',$mounting_height);

		$data = array();
		foreach($explode_mounting_height as $explode_mounting_heights)
		{
			$query = $this->db->query("select label from mounting_height where value = '".$explode_mounting_heights."'");
			$querydata = $query->row_array();
			$data[] = $querydata['label'];
		}

		return implode(',',$data);
	}


	function getOptionValue($table,$value,$bcproductid)
	{
		$query = $this->db->query("select * from `option_values` where option_name = '".$this->db->escape_str($table)."' and option_value = '".$this->db->escape_str($value)."' and product_id = '".$bcproductid."'");
		return $query->row_array();
	}

	
	function getOptionValue1($table,$value,$bcproductid)
	{
		$query = $this->db->query("select * from `option_values` where option_name = '".$this->db->escape_str($table)."' and option_value = '".$this->db->escape_str($value)."' and product_id = '".$bcproductid."'");
		return $query->row_array();
	}

	function getOptionValue2($table,$value,$bcproductid)
	{
		$query = $this->db->query("select * from `option_values` where option_name = '".$this->db->escape_str($table)."' and option_value = '".$this->db->escape_str($value)."' and product_id = '".$bcproductid."'");
		return $query->row_array();
	}

	function getOptionValue3($table,$value,$bcproductid)
	{
		$query = $this->db->query("select * from `option_values` where option_name = '".$this->db->escape_str($table)."' and option_value = '".$this->db->escape_str($value)."' and product_id = '".$bcproductid."'");
		return $query->row_array();
	}

	function getOptionValue4($table,$value,$bcproductid)
	{
		$query = $this->db->query("select * from `option_values` where option_name = '".$this->db->escape_str($table)."' and option_value = '".$this->db->escape_str($value)."' and product_id = '".$bcproductid."'");
		return $query->row_array();
	}

	function getOptionValue5($table,$value,$bcproductid)
	{
		$query = $this->db->query("select * from `option_values` where option_name = '".$this->db->escape_str($table)."' and option_value = '".$this->db->escape_str($value)."' and product_id = '".$bcproductid."'");
		return $query->row_array();
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

	function updateOptionCount($product_id,$count)
	{
		$query = $this->db->query("UPDATE `products` SET count = '".$count."' where product_id = '".$product_id."'");
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
		$query = $this->db->query("UPDATE `products` SET status = 'yes' WHERE product_id = '".$product_id."'");
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
		$query = $this->db->query("SELECT * FROM ".$this->products_magenot_table." WHERE type = 'simple'  and status = 'no'");
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