<?php
class ordermodel extends CI_Model{
	function __construct()
	{
		$this->setting_table 		= "users";
		$this->order_table          = "orders";
		$this->customer_table       = "customer";
		$this->country       		= "country";
	
	}
	public function UpdateOrderStatus($magento_order_id,$bc_order_id)
	{
		$query = $this->db->query("UPDATE ".$this->order_table." SET `bc_order_id` = '".$bc_order_id."',`order_update_status` = 'yes' WHERE orderIncrementId = '".$magento_order_id."'");
	}

	public function updateErrorMessage($magento_order_id,$error)
	{
		$query = $this->db->query("UPDATE ".$this->order_table." SET `error` = '".$error."' WHERE orderIncrementId = '".$magento_order_id."'");
	}

	public function updateorderShopifystatus($shopify_order_id,$status)
	{
		$query = $this->db->query("UPDATE `shopify_customer_order` SET `status` = '".$status."' WHERE `shopify_order_id` = '".$shopify_order_id."'");
	}
	
	public function importorderDB($orderdata)
	{
		$import_order = array();
		foreach($orderdata as $orderdata_s)
		{
			$import_order[] = array(
				"order_id"            => $orderdata_s['order_id'],
				"email"        		  => $orderdata_s['customer_email'],
				"order_status" 		  => $orderdata_s['status'],
				"orderIncrementId"    => $orderdata_s['increment_id'],
				"order_update_status" => "no",
				"bc_order_id"         => ""
			);
		}
		if(isset($import_order) && !empty($import_order)){
			$this->db->insert_batch($this->order_table,$import_order);
		}

	}
	public function GetOrdersDB()
	{
		//$query = $this->db->query("select * from ".$this->order_table." WHERE order_update_status IN('yes') ORDER BY order_status ASC");
		$query = $this->db->query("select * from ".$this->order_table." WHERE `order_update_status`='no' and error='' ");
		$order_data  = $query->result_array();
		return $order_data;
	}
	
	public function getMagentoId($shopifyorder)
	{
		$query = $this->db->query("select magento_order_id from ".$this->order_table." WHERE `shopify_order_id`='".$shopifyorder."' ");
		$order  = $query->row_array();
		return  $order['magento_order_id'];
	}

	public function GetOrdersDB1()
	{
		//$query = $this->db->query("select * from ".$this->order_table." WHERE order_update_status IN('yes') ORDER BY order_status ASC");
		$query = $this->db->query("select * from ".$this->order_table." WHERE `status`='no' and error='' order by id ASC limit 1401,3000");
		$order_data  = $query->result_array();
		return $order_data;
	}

	public function GetOrdersDB2()
	{
		//$query = $this->db->query("select * from ".$this->order_table." WHERE order_update_status IN('yes') ORDER BY order_status ASC");
		//$query = $this->db->query("select * from ".$this->order_table." WHERE `status`='no' and error='' order by id asc limit 40001,10000");
		$order_data  = $query->result_array();
		return $order_data;
	}

	public function getOrderEQid($order_id)
	{
		$query = $this->db->query("select order_id from ".$this->order_table." WHERE `orderIncrementId`= '".$order_id."'");
		$data = $query->row_array();
		return $data['order_id'];
	}

	public function getGeneralSetting()
	{
		$query = $this->db->query("select * from ".$this->setting_table."");
		$setting_data  = $query->result_array();
		return $setting_data;
	}
	public function getOrderDetails($orderid)
	{
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		
		$proxy = new SoapClient('https://www.alphabiosciences.com/api/v2_soap/?wsdl=1',$options);
		$sessionId = $proxy->login('alphabiosciences', 'AB12345');
		$orderinfo = array();
		$orderinfo = $proxy->salesOrderInfo($sessionId, $orderid);
		return $orderinfo;
	}

	public function getCustomerID($magento_id){
		$query = $this->db->query("select shopify_customer_id from ".$this->customer_table." WHERE magento_id ='".$magento_id."'");
		$shopify_customer_id = $query->row_array();

		if(isset($shopify_customer_id) && $shopify_customer_id!=0 )
		{	
			if(!empty($shopify_customer_id)){
				return $shopify_customer_id['shopify_customer_id'];
			}else{
				return '';
			}
			
		}
		return '';
	}

	public function get_country_name($country_code){
		$query = $this->db->query("select nicename from ".$this->country." WHERE `iso` = '".$country_code."'");
		return $query->row_array();

	}

	function getcountryname($code)
	{
		$query = $this->db->query("select nicename from ".$this->country." where iso = '".$code."'");
		$country_data  = $query->row_array();
		return $country_data;
	}
	

	public function getDeleteOrder(){
		$query = $this->db->query("select * from ".$this->order_table." WHERE `status`='yes'");
		$order_data  = $query->result_array();
		return $order_data;
	}

	public function UpdateOrderDeleteStatus($order_id)
	{
		$query = $this->db->query("UPDATE ".$this->order_table." SET `status` = 'no' WHERE shopify_order_id = '".$order_id."'");
		echo $this->db->last_query();
		return true;
	}

	public function getDeleteOrder1(){
		$query = $this->db->query("select * from ".$this->order_table." WHERE `status`='yes' order by id desc");
		$order_data  = $query->result_array();
		return $order_data;
	}

	public function UpdateOrderDeleteStatus1($order_id)
	{
		$query = $this->db->query("UPDATE ".$this->order_table." SET `status` = 'no' WHERE shopify_order_id = '".$order_id."'");
		echo $this->db->last_query();
		exit;
		return true;
	}
}
?>