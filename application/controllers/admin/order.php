<?php 
ini_set('display_errors','On');
error_reporting(E_ALL);
use Bigcommerce\Api\Client as Bigcommerce;

class Order extends CI_controller{
	
	public function __construct()
	{
		parent::__construct();	
	
		$this->load->library('mcurl');
		$this->load->model("admin/ordermodel");
		$this->load->model("admin/customermodel");

		include(APPPATH.'third_party/PHPExcel.php');
		include(APPPATH.'third_party/PHPExcel/Writer/Excel2007.php');
		include(APPPATH.'third_party/bcapi/vendor/autoload.php');
	}

	function index()
	{	
		$session_data = $this->session->userdata('admin_session');
		if(!isset($session_data) || empty($session_data))redirect('admin/login');
		
		$this->data["page_head"]  = 'Magento to Shopify order Import';
		$this->data["page_title"] = 'Magento to Shopify order Import';
		
		$order_data = $this->ordermodel->GetOrdersDB();
		$this->data['total_order'] = count($order_data);
		$this->data['order_data']  = $order_data;
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']=$this->load->view('admin/common/leftmenu',$this->data,true);	
		$this->load->view("admin/order/list",$this->data);
		$this->load->view("admin/common/footer");
	}
	
	function immportorderMagentotoDB()
	{
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');
		$orderlist = array();
		$orderlist = $proxy->call($sessionId, 'order.list');

		if(isset($orderlist) && !empty($orderlist))
		{
			$this->ordermodel->importorderDB($orderlist);
		}
	}
	
	function getCustomerID($customer_email)
	{
		$config_data 	= $this->ordermodel->getGeneralSetting();
		$bcstoreurl = $config_data[0]['storeurl'];
        $client_id 	= $config_data[0]['client_id'];
        $auth_token = $config_data[0]['apitoken'];
        $store_hash = $config_data[0]['storehash'];
		
        // Bc class connection
        Bigcommerce::configure(array('client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash));
		
        // SSL verify False
        Bigcommerce::verifyPeer(false);
        // Display error exception on
        Bigcommerce::failOnError();

		$customer_email_a		   = array();
		$customer_email_a['email'] = $customer_email;
		$customer_details 		   = Bigcommerce::getCustomers($customer_email_a);
		
		if(isset($customer_details[0]->id) && !empty($customer_details[0]->id))
		{
			return $customer_details[0]->id;
		}
		return '';
	}

	public function getBCOrderStatus($orderstatus_name)
	{
		
		$orderstatusa = array();
		$orderstatusa = array(
			'canceled' 			=> 5,  // Cancelled
			'closed'			=> 4,  // Refunded
			'committed' 		=> 2,  // Shipped
			'complete'			=> 2,  // Shipped
			'exception'			=> 9,  // Awaiting Shipment
			'expired' 			=> 5,  // Cancelled
			'holded' 			=> 1,  // Pending
			'paypal_canceled_reversal' 	=> 2, // Shipped
			'pending' 			=> 1,  // Pending
			'pending_paypal' 	=> 1,  // Pending
			'processing' 		=> 11  // Awaiting Fulfillment
		);

		if(isset($orderstatus_name) && !empty($orderstatus_name))
		{
			if(isset($orderstatus_name) && !empty($orderstatus_name) && $orderstatus_name == 'paypal_canceled_reversal')
			{
				return 2;
			}else if(isset($orderstatus_name) && !empty($orderstatus_name) && $orderstatus_name == 'pending_paypal')
			{
				return 1;
			}else{
				$bc_order_status = str_replace(array_keys($orderstatusa),$orderstatusa,$orderstatus_name);
			
				return $bc_order_status;
			}				
		}
		else
		{
			return 10;
		}	
	}
	
	function importorder()
	{
	
		$orderid = $this->input->get('code');
				
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');
		$order_details = array();
		$order_details = $proxy->call($sessionId,'sales_order.info', $orderid);

		$getEQid = $this->ordermodel->getOrderEQid($orderid);

		$api_url = 'https://relightdepot.com/getOrderPids.php?pid='.$getEQid;
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res_fulment = curl_exec($ch);
		$order_payment = json_decode($res_fulment);
	

		if(isset($order_details) && !empty($order_details)) {
			
		
			// Order Status
			$bc_order_status = $this->getBCOrderStatus($order_details['status']);
			
			// Payment Method
			$bc_order_payment_method = $order_payment->payment_method_title;
		
			$create_order = array();
			$create_order['status_id'] 	= $bc_order_status;

			// Order Items
			$order_product_data      = $order_details['items'];
			
			if(isset($order_product_data) && !empty($order_product_data))
			{
				$op = 0;
				foreach($order_product_data as $order_product_data_s)
				{	
					$data = preg_replace_callback('!s:\d+:"(.*?)";!s', function($m) { return "s:" . strlen($m[1]) . ':"'.$m[1].'";'; }, $order_product_data_s['product_options']);
					@$product_data = @unserialize(@$data);
					
					$option_p = '';
					if(isset($product_data['attributes_info']) && !empty($product_data['attributes_info']))
					{
						foreach($product_data['attributes_info'] as $key => $product_data_attributes)
						{
							$option_p .= '('.$product_data_attributes['label'].':'.$product_data_attributes['value'].')';
						}
					}
					if(isset($product_data['bundle_options']) && !empty($product_data['bundle_options'])){
						$option_p .= '(Description:1 x Custom Quotation $0.00)'."\n";
					}
					if(isset($product_data['options']) && !empty($product_data['options'])){

						foreach($product_data['options'] as $options)
						{
							$option_p .= '('.$options['label'].':'.$options['value'].')'."\n";
						}
					}
					if(isset($option_p) && !empty($option_p))
					{
						$create_order['products'][$op]['name']			= substr($order_product_data_s['name']."\n".$option_p, 0, 255);
					}else{
						$create_order['products'][$op]['name']			= substr($order_product_data_s['name'], 0, 255);
					}
					
					$create_order['products'][$op]['sku']				= $order_product_data_s['sku'];
					$create_order['products'][$op]['quantity']			= (int)$order_product_data_s['qty_ordered'];
					$create_order['products'][$op]['price_ex_tax']		= number_format($order_product_data_s['price'],2,'.','');
					$create_order['products'][$op]['price_inc_tax']		= number_format($order_product_data_s['price'],2,'.','');
					
					$op++;
				}
			}
			
			$subtotal = '';
			$subtotal = $order_details['subtotal'];
			// Get Customer ID
			$customer_bc_id = $this->getCustomerID($order_details['customer_email']);	
			
			$create_order['customer_id'] 	= 0;
			if(isset($customer_bc_id) && !empty($customer_bc_id))
			{
				$create_order['customer_id'] 					= $customer_bc_id;
			}else if(isset($order_details['customer_id']) && !empty($order_details['customer_id']))
			{
				$customer_default_billing_city			   = @$order_details['billing_address']['city'];
				$customer_default_billing_state = '';
				if(isset($order_details['billing_address']['region']) && !empty($order_details['billing_address']['region']))
				{
					$customer_default_billing_state			   = @$order_details['billing_address']['region'];
				}
				$create_order['billing_address']['street_2']		   = '';
				$getcountry = $this->ordermodel->getcountryname($order_details['billing_address']['country_id']);
				$customer_default_billing_country 		= trim($getcountry['nicename']);

				$customer_lastname	        = @$order_details['billing_address']['lastname'];
				$customer_firstname         = @$order_details['billing_address']['firstname'];
				$customer_email			    = @$order_details['customer_email'];

				$customer_default_billing_company = '';
				if(isset($order_details['billing_address']['company']) && !empty($order_details['billing_address']['company']))
				{
					$customer_default_billing_company		   = $order_details['billing_address']['company'];
				}
				$customer_default_billing_street_1 = '';
				if(isset($order_details['billing_address']['street']) && !empty($order_details['billing_address']['street']))
				{
					$customer_default_billing_street_1		   = @$order_details['billing_address']['street'];
				}
				$customer_default_billing_zip				   = @$order_details['billing_address']['postcode'];
				$customer_default_billing_phonenumber			= @$order_details['billing_address']['telephone'];
				
				$group_id = 0;
				if(isset($order_details['customer_group_id']) && !empty($order_details['customer_group_id'])){
					if ($order_details['customer_group_id'] == 0) {
						$group_id = 3;
					} elseif($order_details['customer_group_id'] == 1) {
						$group_id = 1;
					} elseif($order_details['customer_group_id'] == 2) {
						$group_id = 2;
					} elseif($order_details['customer_group_id'] == 3) {
						$group_id = 4;
					}
				}

				$customer_create = array();
				$customer_create['first_name']    =  $customer_firstname;
				$customer_create['last_name']     =  $customer_lastname;
				$customer_create['email']         =  $customer_email;
				$customer_create['company']   	  =  $customer_default_billing_company;
				$customer_create['phone']         =  $customer_default_billing_phonenumber;
				$customer_create['customer_group_id'] = trim($group_id);

				try {
					$Customer = Bigcommerce::createCustomer($customer_create);

					$create_order['customer_id'] 					= $Customer->id;
					if(isset($Customer) && empty($Customer)){
						throw new Exception('Bigcommerce\Api\Error');
					}
					else 
					{
						$customer_default_address = array();
						$customer_default_address['first_name']     = $customer_firstname;
						$customer_default_address['last_name']      = $customer_lastname;
						$customer_default_address['company']        = $customer_default_billing_company;
						$customer_default_address['street_1']       = $customer_default_billing_street_1;
						$customer_default_address['city']           = $customer_default_billing_city;
						$customer_default_address['state']          = $customer_default_billing_state;
						$customer_default_address['zip']            = $customer_default_billing_zip;
						$customer_default_address['country']        = $customer_default_billing_country;
						$customer_default_address['phone']          = $customer_default_billing_phonenumber;

						if(isset($customer_default_address) && !empty($customer_default_address))
						{
							try	{
								$cust_def_add = Bigcommerce::createCustomeraddress($Customer->id,$customer_default_address);
								if(isset($cust_def_add) && empty($cust_def_add)){
									throw new Exception('Bigcommerce\Api\Error');
								}
								else
								{
								
								}
							}
							catch(Exception $error) {
								$error = $error->getMessage();
								$errorss = $this->db->escape_str($error);
								echo $error.'<br>';
							}
						}
					}
				}
				catch(Exception $error) {
					$error = $error->getMessage();
					$errorss = $this->db->escape_str($error);
					//$this->customermodel->updatecustomerMessage($customer_id,$errorss);
					echo $error.'<br>';
				}
			}

			// Order Billing Address
			$create_order['billing_address']['zip']				   = @$order_details['billing_address']['postcode'];
			$create_order['billing_address']['city']			   = @$order_details['billing_address']['city'];
			$create_order['billing_address']['email']			   = @$order_details['customer_email'];
			$create_order['billing_address']['phone']			   = @$order_details['billing_address']['telephone'];
			if(isset($order_details['billing_address']['region']) && !empty($order_details['billing_address']['region']))
			{
				$create_order['billing_address']['state']			   = @$order_details['billing_address']['region'];
			}
			if(isset($order_details['billing_address']['company']) && !empty($order_details['billing_address']['company']))
			{
				$create_order['billing_address']['company']			   = $order_details['billing_address']['company'];
			}
			if(isset($order_details['billing_address']['street']) && !empty($order_details['billing_address']['street']))
			{
				$create_order['billing_address']['street_1']		   = @$order_details['billing_address']['street'];
			}
			//$create_order['billing_address']['country']          = @$order_details->billing_address->country_id;
			$create_order['billing_address']['street_2']		   = '';
			$create_order['billing_address']['last_name']	       = @$order_details['billing_address']['lastname'];
			$create_order['billing_address']['first_name']         = @$order_details['billing_address']['firstname'];
			$create_order['billing_address']['country_iso2']  	   = @$order_details['billing_address']['country_id'];
			
			$coupon_cost     = '';
			$coupon_title    = '';
			// Discount 
			if(isset($order_details['discount_amount']) && !empty($order_details['discount_amount']) && $order_details['discount_amount'] != '0.0000')
			{
				$coupon_cost		 = number_format($order_details['discount_amount'],2,'.','');
				$coupon_title 		 = 'Discount:';
				$coupon_title 		 = $coupon_title.' '.rand();
			}

			// Get Shipping cost
			$shipping_cost   = '';
			$shipping_title  = '';
			if(isset($order_details['shipping_amount']) && !empty($order_details['shipping_amount'])){
				$shipping_cost  = number_format($order_details['shipping_amount'],2,'.','');
				$shipping_title = $order_details['shipping_description'];
			}
		
			// Order Shipping Address
			$create_order['shipping_addresses'][0]['zip']					= @$order_details['shipping_address']['postcode'];
			$create_order['shipping_addresses'][0]['city']					= @$order_details['shipping_address']['city'];
			$create_order['shipping_addresses'][0]['email']					= @$order_details['customer_email'];
			$create_order['shipping_addresses'][0]['phone']					= @$order_details['shipping_address']['telephone'];
			
			
			if(isset($order_details['shipping_address']['region']) && !empty($order_details['shipping_address']['region']))
			{
				$create_order['shipping_addresses'][0]['state']		 			= @$order_details['shipping_address']['region'];
			}
			if(isset($order_details['shipping_address']['company']) && !empty($order_details['shipping_address']['company']))
			{
				$create_order['shipping_addresses'][0]['company']	 			= @$order_details['shipping_address']['company'];
			}
			if(isset($order_details['shipping_address']['street']) && !empty($order_details['shipping_address']['street']))
			{
				$create_order['shipping_addresses'][0]['street_1']			    = @$order_details['shipping_address']['street'];
			}
			
			//$create_order['shipping_addresses'][0]['country']	 			= @$order_details['shipping_address']['country_id'];
			$create_order['shipping_addresses'][0]['street_2']				= '';
			$create_order['shipping_addresses'][0]['last_name']  			= @$order_details['shipping_address']['lastname'];
			$create_order['shipping_addresses'][0]['first_name'] 			= @$order_details['shipping_address']['firstname'];
			$create_order['shipping_addresses'][0]['country_iso2']  	  	= @$order_details['shipping_address']['country_id'];
			$create_order['shipping_addresses'][0]['shipping_method'] 		= $shipping_title;
			$create_order['subtotal_ex_tax'] 	    						= number_format($subtotal,2,'.','');
			$create_order['subtotal_inc_tax'] 	   							= number_format($subtotal,2,'.','');
			$create_order['total_ex_tax']		    						= number_format($order_details['grand_total'],2,'.','');
			$create_order['total_inc_tax']		    						= number_format($order_details['grand_total'],2,'.','');
			$create_order['shipping_cost_ex_tax']   						= $shipping_cost;
			$create_order['shipping_cost_inc_tax']  						= $shipping_cost;
			$create_order['handling_cost_ex_tax']    						= number_format($order_details['base_tax_amount'],2,'.','');
			$create_order['handling_cost_inc_tax']  						= number_format($order_details['base_tax_amount'],2,'.','');
			$create_order['payment_method']									= $bc_order_payment_method;
			$create_order['external_source'] 								= $order_details['order_id'];
			$create_order['date_created']									= date('D, d M Y H:i:s +0000',strtotime($order_details['created_at']));

			if(isset($coupon_cost) && !empty($coupon_cost))
			{
				$create_order['discount_amount'] 		 					 = str_replace('-', '', $coupon_cost);
			}

			// Customer Comment
			$customer_comment = '';
			if(isset($order_details['order_id']) && !empty($order_details['order_id'])){
				$customer_comment .= 'Magento Order id: '.$order_details['order_id']."\n";
			}
			if(isset($order_details['status_history']) && !empty($order_details['status_history'])){
				foreach($order_details['status_history'] as $order_status_history_s){
					
					$customer_comment .= 'Date: '.date('Y-m-d H:i:s A',strtotime($order_status_history_s['created_at']))."\n";
					$customer_comment .= 'Status: '.$order_status_history_s['status']."\n";
					if(isset($order_status_history_s['comment']) && !empty($order_status_history_s['comment']))
					{
						$customer_comment .= 'Comment: '.$order_status_history_s['comment']."\n";
					}
				}
			}
			$bling_fax  = '';
			if(isset($order_details['billing_address']['fax']) && !empty($order_details['billing_address']['fax'])){
				$customer_comment .= 'Fax No.: '.$order_details['billing_address']['fax']."\n";
				$bling_fax = $order_details['billing_address']['fax'];
			}
			if(isset($order_details['shipping_address']['fax']) && !empty($order_details['shipping_address']['fax']) && $bling_fax != $order_details['shipping_address']['fax']){
				$customer_comment .= 'Shipping Fax No.: '.$order_details['shipping_address']['fax']."\n";
			}
			if(isset($order_details['remote_ip']) && !empty($order_details['remote_ip'])){
				$customer_comment .= 'Remote IP: '.$order_details['remote_ip']."\n";
			}
			if(isset($order_details['customer_note']) && !empty($order_details['customer_note'])){
				$customer_comment .= 'Customer Note: '.$order_details['customer_note']."\n";
			}
			if(isset($order_details['comment']) && !empty($order_details['comment'])){
				$customer_comment .= 'Order Comment: '.$order_details['comment']."\n";
			}
			
			//$create_order['staff_notes']			 						= $admin_comment;
			$create_order['customer_message'] 		 						= $customer_comment;

			$filters = array(
				array('order_id' => array('eq' => $getEQid)) // Entity ID, not Increment ID
			);

		
			/** @var array $orderShipments */
			$orderShipments = $proxy->call($sessionId, 'sales_order_shipment.list', $filters);

			// Create order
			try	{
					$create_orders_bc 	= Bigcommerce::createOrder($create_order);
					$bc_order_id 	 	= $create_orders_bc->id;
					$this->ordermodel->UpdateOrderStatus($orderid,$bc_order_id);
					if(isset($create_orders_bc) && empty($create_orders_bc)){
		            	throw new Exception('Bigcommerce\Api\Error');
		       	 	}
		       	 	else {
			        	echo $create_orders_bc->id.' - Order Import Successfully...<br>';
			     	}
	 			}
				catch(Exception $error) {
					$error = $error->getMessage();

					//$error1 = $this->db->escape_str($error);
					$error1 = str_replace("'", "",$error);

					$this->ordermodel->updateErrorMessage($orderid,$error1);
					echo $error.'<br>';
				}

			// Ship Order
			if(isset($orderShipments) && !empty($orderShipments) && isset($bc_order_id) && !empty($bc_order_id))
			{
				$shipping_create 			  = array();
				$order_items			      = Bigcommerce::getOrderProducts($bc_order_id);
				$shipping_address_order_items = Bigcommerce::getOrderShippingAddresses($bc_order_id);
				// Order Items
				if(isset($order_items) && !empty($order_items))
				{
					$opa = 1;
					foreach($order_items as $order_items_s)
					{
						$shipping_create['items'][$opa]['quantity']			= $order_items_s->quantity;
						$shipping_create['items'][$opa]['order_product_id']	= $order_items_s->id;
						$opa++;
					}
				}
				$shipping_create['order_address_id'] 	= $shipping_address_order_items[0]->id;
				$shipping_create['tracking_number'] 	= strtotime("now");
				$shipping_create['shipping_method'] 	= $shipping_title;
				$shipping_assinf_s = Bigcommerce::createShipment($bc_order_id,$shipping_create);

				if(isset($shipping_assinf_s->id) && !empty($shipping_assinf_s->id))
				{
					echo $shipping_assinf_s->id.' - Shipping Assign Successfully...';
					
				}
				
				$update_order = array();
				$update_order['status_id'] 	 = $bc_order_status;
				$update_status = Bigcommerce::updateOrder($bc_order_id,$update_order);
				
				if(isset($update_status->id) && !empty($update_status->id))
				{
					echo '<br>'.$update_status->id.' - Update Order Status Successfully...';
					
				}
			}
		}
		
	}

	 function getcountryname($c_code){
		$country_name = $this->customermodel->getcountryname($c_code);
		if(isset($country_name) && !empty($country_name)){
			return $country_name['nicename'];
		}else{
			return $c_code;
		}

	}

	function randomPassword() {
		 $password = '';
		 $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		 for($i = 0; $i < 8; $i++)
		 {
			 $random_int = mt_rand();
			 $password .= $charset[$random_int % strlen($charset)];
		 }
		 return $password;
	}

}

?>