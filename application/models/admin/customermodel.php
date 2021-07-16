<?php
class customermodel extends CI_Model{
	var $table_name	= "";
	function __construct()
	{
		$this->customer_table = "customer";
		$this->country_table  = "country";
		$this->setting_table  = "users";
	}
	
	function getGeneralSetting() {

		$query = $this->db->query("select * from ".$this->setting_table."");
		return $query->row_array();
	}

	function getcustomer() {

		$query = $this->db->query("select * from ".$this->customer_table." where status = 'no' and bc_customer_id = '' and error='' ORDER BY `magento_id` ASC");
		return $query->result_array();
	}
		
	function getcountryname($code)
	{
		$query = $this->db->query("select nicename from ".$this->country_table." where iso = '".$code."'");
		$country_data  = $query->row_array();
		return $country_data;
	}
	
	function updateCustomerStatus($magento_id,$bc_customer_id) {

		$this->db->query("UPDATE ".$this->customer_table." SET status = 'yes', bc_customer_id = '".$bc_customer_id."' WHERE magento_id = '".$magento_id."'");
	}
	
	function CustomerAddressError($magento_id,$error) {

		$this->db->query("UPDATE ".$this->customer_table." SET add_error = '".$error."' WHERE magento_id = '".$magento_id."'");
	}
		
	function updateCustomerError($magento_id, $error) {
		
		$this->db->query("UPDATE ".$this->customer_table." SET error = '".$error."' WHERE magento_id = '".$magento_id."'");
		
	}
		
}
?>