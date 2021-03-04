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
	
	function updateStatusss($customer_id)
	{
		$query = $this->db->query('UPDATE `shopify_customer_order` SET `status`= "yes" WHERE ` bc_customer_id` = "'.$customer_id.'"');	
	}
	
	function getcountryname($code)
	{
		$query = $this->db->query("select nicename from ".$this->country_table." where iso3 = '".$code."'");
		$country_data  = $query->row_array();
		return $country_data;
	}
	
	function updatecustomerstatus($email,$customer_id)
	{
		$this->db->query("UPDATE ".$this->customer_table." SET status = 'yes',bc_customer_id = '".$customer_id."' WHERE email = '".$email."'");
	}
	
	function CustomerAddressError($createcustomer_address,$email)
	{
		$this->db->query("UPDATE ".$this->customer_table." SET address_error = '".$createcustomer_address."' WHERE email = '".$email."'");
	}
	
	function updatestatus($customer_id)
	{
		$this->db->query("UPDATE ".$this->customer_reset_password." SET status = 'yes' WHERE customer_id = '".$customer_id."'");
	}
	
	function getcustomerresetpassword()
	{
		$query = $this->db->query("select * from ".$this->customer_reset_password."");
		$customer_data  = $query->result_array();
		return $customer_data;
	}
	
	function customerupdate($bc_customer_id,$mg_customer_id,$status,$error)
	{
		//$this->db->query("UPDATE `shopify_customer_order` SET status = '".$status."' WHERE magento_id = '".$mg_customer_id."'");
		$this->db->query("UPDATE ".$this->customer_table." SET status = '".$status."', bc_customer_id = '".$bc_customer_id."',error= '".$error."' WHERE magento_id = '".$mg_customer_id."'");
		
	}
	
	function customernewupdate($bc_customer_id,$mg_customer_id,$status,$error)
	{
		$this->db->query("UPDATE customer_order_count SET status = '".$status."', bc_customer_id = '".$bc_customer_id."',error= '".$error."' WHERE magento_id = '".$mg_customer_id."'");
	}
	
	function customerinsert($data){
		$this->db->insert($this->customer_table,$data);
		return true;
	}

	function getMylcustomer(){
	 	$query = $this->db->query("select * from old_customer");
		$customer_data  = $query->result_array();
		return $customer_data;
	 }

	 function updateMycustomer($magento_id,$sp_id){
	 	$this->db->query("UPDATE ".$this->customer_table." SET bc_customer_id = '".$sp_id."' WHERE magento_id = '".$magento_id."'");
	 	echo $this->db->last_query();
		return true;
	 }
}
?>