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
		
		//$this->immportorderMagentotoDB();
		
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
		$proxy = new SoapClient('https://www.billskhakis.com/index3.php/api/soap/?wsdl=1',$options);
		$sessionId = $proxy->login('DataMigration', '123456');
		$orderlist = array();
		$orderlist = $proxy->call($sessionId, 'order.list');
		
		if(isset($orderlist) && !empty($orderlist))
		{
			$this->ordermodel->importorderDB($orderlist);
		}
	}
	
	function getCustomerID($customer_email)
	{
		$customer_email_a		   = array();
		$customer_email_a['email'] = $customer_email;
		$customer_details 		   = Bigcommerce::getCustomers($customer_email_a);
		
		if(isset($customer_details[0]->id) && !empty($customer_details[0]->id))
		{
			return $customer_details[0]->id;
		}
		return '';
	}
	
	

	
	function getmagenotoOrderDetails($orderid)
	{
		//$order_id  				 = $this->input->get('code');	
		//$order_details 			 = $this->ordermodel->getOrderDetails($order_id);
		//$subtotal 				 = $order_details->subtotal;
		//$create_order['subtotal_ex_tax'] 	    						 = number_format($subtotal,2,'.','');
		//$create_order['subtotal_inc_tax'] 	   							 = number_format($subtotal,2,'.','');

		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		$proxy  = new SoapClient('https://www.billskhakis.com/index3.php/api/soap/?wsdl=1',$options);
		$sessionId    = $proxy->login('DataMigration', '123456');
		$customer_details = array();


		$order_details = $proxy->call($sessionId,'sales_order.info', $orderid);
		echo "<pre>";
		//echo strtotime("now");
		//$product_option = $order_details['items'];

		//echo $order_details['status'];
		/*
		print_r($product_option);
		foreach ($product_option as $alloption) {
			 echo print_r(unserialize($alloption['product_options']));
		}*/
		echo number_format(abs($order_details['base_discount_amount']),'2','.','');
		print_r($order_details);
		exit;
		
	}
/*
	public function getShopifyOrder(){
			$setting_shppoify 		= $this->ordermodel->getGeneralSetting();
			$storeurl_shopify   	= $setting_shppoify[0]['storeurl_volusion'];
			$shopify_key		  	= $setting_shppoify[0]['loginemail'];
			$shopify_pw		  		= $setting_shppoify[0]['encryptedpassword'];

			$get_delete_order = $this->ordermodel->getDeleteOrder();


			foreach ($get_delete_order as  $dorder) {
			$order =  $dorder['shopify_order_id'];
			$api_url = $storeurl_shopify.'admin/orders/'.$order.'.json';
			
				$ch = curl_init($api_url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
				curl_setopt($ch, CURLOPT_USERPWD, $shopify_key.':'.$shopify_pw ); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );   
				$res_fulment = curl_exec($ch);
				$Order = json_decode($res_fulment); 
				if(isset($Order) && empty($Order)){
					$this->ordermodel->UpdateOrderDeleteStatus($order);
					exit;
				}
				//exit;
			}		
	}
*/
	function importorder()
	{
		//$shopifyorder = $this->input->get('code');
		
		$orderid = $this->input->get('code');
				
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		$proxy  = new SoapClient('https://www.billskhakis.com/index3.php/api/soap/?wsdl=1',$options);
		$sessionId    = $proxy->login('DataMigration', '123456');
		$order_details = array();
		$order_details = $proxy->call($sessionId,'sales_order.info', $orderid);
		
		if(isset($order_details) && !empty($order_details))
		{
			$response_message = array();
		
			$postdata = array();
			$discount_a = array();
			//verify
			$postdata['order']['name'] = "#".$order_details['increment_id'];			
			$postdata['order']['send_receipt']	  = false;
			$postdata['order']['send_fulfillment_receipt'] = false;
						
			$order_items = $order_details['items'];;

			//cart items 
			if(isset($order_items) && !empty($order_items))
			{
				$oritm = 0;
				$i = 0; 
				foreach($order_items as $order_items_s)
				{
					if($order_items_s['product_type'] == 'configurable'){
						$qty = $order_items_s['qty_ordered'];
						if(empty($order_items_s['qty_ordered'])){
							$qty = 1;
						}
						$postdata['order']['line_items'][$oritm]['price']				= number_format($order_items_s['base_original_price'],'2','.','');
						$postdata['order']['line_items'][$oritm]['quantity']			= intval($qty);

						if(isset($order_items_s['sku']) && !empty($order_items_s['sku'])){
							$order_items_sku =$order_items_s['sku'];
						}else{
							$order_items_sku = chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90));
						}
						$postdata['order']['line_items'][$oritm]['sku']					= $order_items_sku;
						$postdata['order']['line_items'][$oritm]['title']				= $order_items_s['name'];

						if(isset($order_items_s['product_options']) && !empty($order_items_s['product_options']))
						{
							$product_options  =	 unserialize($order_items_s['product_options']);

							$option = array();
							if(isset($product_options['options']) && !empty($product_options['options'])){

								$s=0;
								foreach($product_options['options'] as $options)
								{
									$option[$s]['name']  = $options['label'];
									$option[$s]['value'] = $options['value'];
								$s++;
								}
							}
							$attribute_option = array();
							if(isset($product_options['attributes_info']) && !empty($product_options['attributes_info'])){
								$m=0;
								foreach($product_options['attributes_info'] as $attribute_options)
								{	
									$attribute_option[$m]['name'] = $attribute_options['label'];
									$attribute_option[$m]['value'] = $attribute_options['value'];
								$m++;
								}
							}

						}
						$alloption = array_merge($option,$attribute_option);

					
						if(isset($alloption) && !empty($alloption)){
							$f=0;
							foreach ($alloption as $main_option) {
								
								$postdata['order']['line_items'][$oritm]['properties'][$f]['name']		= $main_option['name'];
			

								$postdata['order']['line_items'][$oritm]['properties'][$f]['value']		= 	str_replace ("&quot;",'"',$main_option['value']);;

								
								$f++;
							}

						}				
						$oritm++;
					}
				}
			}
			
			//customer detail  
			
			$shopify_customer_id = '';
			if(isset($order_details['customer_id']) && !empty($order_details['customer_id'])){
				$shopify_customer_id = $this->ordermodel->getCustomerID($order_details['customer_id']);
			}
			
			//$shopify_customer_id = '1352564277302';
			// Check shopify customer exist or not customer not exist to import customer in shopify.

			if(isset($shopify_customer_id) && !empty($shopify_customer_id)){

				$postdata['order']['customer']["id"]	= $shopify_customer_id;
				
			}else if(isset($order_details['customer_id']) && !empty($order_details['customer_id'])){
				//echo "have id";
				//exit;
				$postdata['order']['customer']["id"]	= $this->customerimport($order_details['customer_id']);

			}
			else
			{
				//echo "No id";
				//exit;
				$postdata['order']['customer']["email"]					    = $order_details['billing_address']['email'];
				$postdata['order']['customer']["first_name"]				= $order_details['billing_address']['firstname'];
				$postdata['order']['customer']["last_name"]					= $order_details['billing_address']['lastname'];
				$postdata['order']['customer']["verified_email"]			= false;
				$postdata['order']['customer']['address1']					= $order_details['billing_address']['street'];
				$postdata['order']['customer']['address2']					= '';
				$postdata['order']['customer']['city']						= $order_details['billing_address']['city'];
				$postdata['order']['customer']['province']					= $order_details['billing_address']['region'];
				$postdata['order']['customer']['phone']						= $order_details['billing_address']['telephone'];
				$postdata['order']['customer']['zip']						= $order_details['billing_address']['postcode'];
				$postdata['order']['customer']['country']					= $order_details['billing_address']['country_id'];
			}
			
			$order_shipping_details = $order_details['shipping_address'];
			
			//Billing address
			if(isset($order_details['billing_address']['street']) && !empty($order_details['billing_address']['street'])){
				$postdata['order']['billing_address']['address1'] 			= $order_details['billing_address']['street'];
			}else{
				$postdata['order']['billing_address']['address1'] 			= $order_shipping_details['street'];
			}

			$postdata['order']['billing_address']['address2'] 				= '';
			
			if(isset($order_details['billing_address']['city']) && !empty($order_details['billing_address']['city'])){
				$postdata['order']['billing_address']['city'] 			= $order_details['billing_address']['city'];
			}else{
				$postdata['order']['billing_address']['city'] 			= $order_shipping_details['city'];
			}
			if(isset($order_details['billing_address']['company']) && !empty($order_details['billing_address']['company'])){
				$postdata['order']['billing_address']['company'] 			= $order_details['billing_address']['company'];
			}else{
				$postdata['order']['billing_address']['company'] 			= $order_shipping_details['company'];
			}
			if(isset($order_details['billing_address']['country_id']) && !empty($order_details['billing_address']['country_id'])){
				$postdata['order']['billing_address']['country_code'] 			= $order_details['billing_address']['country_id'];
			}else{
				$postdata['order']['billing_address']['country_code'] 			= $order_shipping_details['country_id'];
			}
			if(isset($order_shipping_details['country_id']) && !empty($order_shipping_details['country_id']))
			{
				$country = $this->ordermodel->get_country_name($order_shipping_details['country_id']);
				$postdata['order']['billing_address']['country'] 	=  $country['nicename'];
			}else{
				$country = $this->ordermodel->get_country_name($order_shipping_details['country_id']);
				$postdata['order']['billing_address']['country'] 	= $country['nicename'];
			}
			if(isset($order_details['billing_address']['firstname']) && !empty($order_details['billing_address']['firstname'])){
				$postdata['order']['billing_address']['first_name'] 			= $order_details['billing_address']['firstname'];;
			}else{
				$postdata['order']['billing_address']['first_name'] 			= $order_shipping_details['firstname'];
			}
			if(isset($order_details['billing_address']['lastname']) && !empty($order_details['billing_address']['lastname'])){
				$postdata['order']['billing_address']['last_name'] 			= $order_details['billing_address']['lastname'];
			}else{
				$postdata['order']['billing_address']['last_name'] 			= $order_shipping_details['lastname'];
			}
			if(isset($order_details['billing_address']['telephone']) && !empty($order_details['billing_address']['telephone'])){
				$postdata['order']['billing_address']['phone'] = $order_details['billing_address']['telephone'];
			}else{
				$postdata['order']['billing_address']['phone'] = $order_shipping_details['telephone'];
			}
			if(isset($order_details['billing_address']['region']) && !empty($order_details['billing_address']['region'])){
				$postdata['order']['billing_address']['province'] 			= $order_details['billing_address']['region'];
			}else{
				$postdata['order']['billing_address']['province'] 			= $order_shipping_details['region'];
			}
			if(isset($order_details['billing_address']['postcode']) && !empty($order_details['billing_address']['postcode'])){
				$postdata['order']['billing_address']['zip'] 			= $order_details['billing_address']['postcode'];
			}else{
				$postdata['order']['billing_address']['zip'] 			= $order_shipping_details['postcode'];
			}
						
			//Shipping address 
			if(isset($order_shipping_details['street']) && !empty($order_shipping_details['street']))
			{
				$postdata['order']['shipping_address']['address1'] 			= $order_shipping_details['street'];
			}else{
				$postdata['order']['shipping_address']['address1'] 			= $order_details['billing_address']['street'];
			}

			$postdata['order']['shipping_address']['address2'] 				= '';
			
			if(isset($order_shipping_details['city']) && !empty($order_shipping_details['city']))
			{
				$postdata['order']['shipping_address']['city'] 			= $order_shipping_details['city'];
			}else{
				$postdata['order']['shipping_address']['city'] 			= $order_details['billing_address']['city'];
			}
			if(isset($order_shipping_details['company']) && !empty($order_shipping_details['company']))
			{
				$postdata['order']['shipping_address']['company'] 			= $order_shipping_details['company'];
			}else{
				$postdata['order']['shipping_address']['company'] 			= $order_details['billing_address']['company'];
			}

			if(isset($order_shipping_details['country_id']) && !empty($order_shipping_details['country_id']))
			{
				$postdata['order']['shipping_address']['country_code'] 	= $order_shipping_details['country_id'];
			}else{
				$postdata['order']['shipping_address']['country_code'] 	= $order_details['billing_address']['country_id'];
			}
			if(isset($order_shipping_details['country_id']) && !empty($order_shipping_details['country_id']))
			{
				$country = $this->ordermodel->get_country_name($order_shipping_details['country_id']);
				$postdata['order']['shipping_address']['country'] 	=  $country['nicename'];
			}else{
				$country = $this->ordermodel->get_country_name($order_shipping_details['country_id']);
				$postdata['order']['shipping_address']['country'] 	= $country['nicename'];;
			}

			if(isset($order_shipping_details['firstname']) && !empty($order_shipping_details['firstname']))
			{
				$postdata['order']['shipping_address']['first_name'] 	= $order_shipping_details['firstname'];
			}else{
				$postdata['order']['shipping_address']['first_name'] 	= $order_details['billing_address']['firstname'];
			}
			if(isset($order_shipping_details['lastname']) && !empty($order_shipping_details['lastname']))
			{
				$postdata['order']['shipping_address']['last_name'] 	= $order_shipping_details['lastname'];
			}else{
				$postdata['order']['shipping_address']['last_name'] 	= $order_details['billing_address']['lastname'];
			}
			if(isset($order_shipping_details['telephone']) && !empty($order_shipping_details['telephone']))
			{	
				$postdata['order']['shipping_address']['phone'] 		= $order_shipping_details['telephone'];
			}else{
				$postdata['order']['shipping_address']['phone'] 		=  $order_details['billing_address']['telephone'];
			}
			if(isset($order_shipping_details['region']) && !empty($order_shipping_details['region']))
			{
				$postdata['order']['shipping_address']['province'] 		= $order_shipping_details['region'];
			}else{
				$postdata['order']['shipping_address']['province'] 		= $order_details['billing_address']['region'];
			}
			if(isset($order_shipping_details['postcode']) && !empty($order_shipping_details['postcode']))
			{
				$postdata['order']['shipping_address']['zip'] 			= $order_shipping_details['postcode'];
			}else{
				$postdata['order']['shipping_address']['zip'] 			= $order_details['billing_address']['postcode'];
			}
			
			//order email 
			$postdata['order']['email']									= $order_details['customer_email'];


			$customer_phonenumber = '';
			if(isset($order_details['billing_address']['telephone']) && !empty($order_details['billing_address']['telephone'])){
				$postdata['order']['phone'] = trim($order_details['billing_address']['telephone']);
			}

			//$postdata['order']['email']									= "testing@1digitalagency.com";
			$postdata['order']['currency']								= 'USD';
			

			if(isset($order_details['remote_ip']) && !empty($order_details['remote_ip'])){
				$postdata['order']['browser_ip']						= $order_details['remote_ip'];
			}

			//order transaction
			if($order_details['status'] == 'pending'){
				$postdata['order']['transaction'][0]['amount']			=  number_format($order_details['base_grand_total'],'2','.','');	
				$postdata['order']['transaction'][0]['kind']			= "authorization" ;
				$postdata['order']['transaction'][0]['status']			= "success" ;
				$postdata['order']['financial_status']					= "pending";
			}else if($order_details['status'] == 'Complete'){				
				$postdata['order']['transaction'][0]['amount']			=  number_format($order_details['base_grand_total'],'2','.','');
				$postdata['order']['transaction'][0]['kind']			= "authorization" ;
				$postdata['order']['transaction'][0]['status']			= "success" ;
				$postdata['order']['financial_status']					= "authorized";			
			}else if($order_details['status'] == 'Shipped'){
				$postdata['order']['transaction'][0]['amount']			= number_format($order_details['base_grand_total'],'2','.','');
				$postdata['order']['transaction'][0]['kind']			= "authorization" ;
				$postdata['order']['transaction'][0]['status']			= "success" ;
				$postdata['order']['financial_status']					= "authorized";
			}else if($order_details['status'] == 'Cancelled'){
				$postdata['order']['transaction'][0]['kind']			= "void" ;
				$postdata['order']['transaction'][0]['status']			= "failure" ;
				$postdata['order']['financial_status']					= "voided";
			}else if($order_details['status'] == 'Refunded'){
				$postdata['order']['transaction'][0]['kind']			= "refund" ;
				$postdata['order']['transaction'][0]['status']			= "failure" ;
				$postdata['order']['financial_status']					= "refunded";
			}else if($order_details['status'] == 'processing') {
	           $postdata['order']['transaction'][0]['amount'] 			= number_format($order_details['base_grand_total'], '2', '.', '');
	           $postdata['order']['transaction'][0]['kind'] 			= 'authorization';
	           $postdata['order']['transaction'][0]['status'] 			= 'success';
	           $postdata['order']['financial_status'] 					= 'authorized';
			}else if($order_details['status'] == 'fraud') {
				$postdata['order']['transaction'][0]['amount']			=  number_format($order_details['base_grand_total'],'2','.','');	
				$postdata['order']['transaction'][0]['kind']			= "authorization" ;
				$postdata['order']['transaction'][0]['status']			= "success" ;
				$postdata['order']['financial_status']					= "pending";
			}
			
			$getCoupon =  $order_details['discount_description'];
			$base_discount = $order_details['base_discount_amount'];

			if (isset($getCoupon) && !empty($getCoupon)) {
				
				if($order_details['base_discount_amount'] !=0.00){
				$postdata['order']['discount_codes'][0]['code'] =  $order_details['discount_description'];
				$postdata['order']['discount_codes'][0]['amount'] = number_format(abs($order_details['base_discount_amount']),'2','.','');
				$postdata['order']['discount_codes'][0]['type'] = 'fixed_amount';
				$postdata['order']['total_discounts'] = number_format(abs($order_details['base_discount_amount']),'2','.','');
				}
			}elseif(isset($base_discount) && !empty($base_discount)){
				$postdata['order']['total_discounts'] = number_format(abs($order_details['base_discount_amount']),'2','.','');
			}
			/*
			if(isset($order_details->store_credit_amount) && !empty($order_details->store_credit_amount) && $order_details->store_credit_amount != '0.0000')
			{
				$this->order_model->updateStoreCredit($order_id);
			}
			*/
			
			
			$postdata['order']['created_at']	=  date('Y-m-d H:i:s',strtotime($order_details['created_at']));
			

			//subtotal_price(add By d)
			if(isset($order_details['base_subtotal']) && !empty($order_details['base_subtotal'])){
				$postdata['order']['subtotal_price']	= number_format($order_details['base_subtotal'],'2','.','');
			}


			//total_price
			if(isset($order_details['base_grand_total']) && !empty($order_details['base_grand_total'])){
				$postdata['order']['total_price']	= number_format($order_details['base_grand_total'],'2','.','');
			}

			// Order Tax set
			if(isset($order_details['base_tax_amount']) && !empty($order_details['base_tax_amount'])){
				$postdata['order']['total_tax']	= number_format($order_details['base_tax_amount'],'2','.','');
			}
				
			// Shipping method and shipping cost set.
			$shipping_method = 'FREE SHIPPING';
			$shipping_cost   = 0.00;
			if(isset($order_details['shipping_description']) && !empty($order_details['shipping_description'])){
				$shipping_method = $order_details['shipping_description'];
			}else{
				if(isset($order_details['shipping_method']) && !empty($order_details['shipping_method'])){
					$shipping_method = $order_details['shipping_method'];
				}
			}
			
			if(isset($order_details['shipping_incl_tax']) && !empty($order_details['shipping_incl_tax'])){
				$shipping_cost = number_format($order_details['shipping_incl_tax'],'2','.','');
			}
			$postdata['order']['shipping_lines'][0]['title'] = $shipping_method;
			$postdata['order']['shipping_lines'][0]['price'] = $shipping_cost;
			
			//Order Note
			$postdata['order']['note']	=  $order_details['customer_note'];


			
			$cus['name']				=	"Magento Order ID";
			$cus['value']				=	$order_details['order_id'];
			$cus_field[]  				= 	$cus;					
			
			$cus['name']				= 	"Customer IP";
			$cus['value']				=	$order_details['remote_ip'];
			$cus_field[]  				= 	$cus;

			$cus['name']				= 	"Order Date";
			$cus['value']				= 	date('Y-m-d H:i:s',strtotime($order_details['created_at']));
			$cus_field[]  				=	$cus;
			
			$cus['name']				= 	"Order Status";
			$cus['value']				=	$order_details['status'];
			$cus_field[]  				= 	$cus;
			
			$cus['name']				=  "Store Name";
			$cus['value']				=  $order_details['store_name'];
			$cus_field[]  				= 	$cus;

			$cus['name']				=  "Last trans id";
			$cus['value']				=  @$order_details['last_trans_id'];
			$cus_field[]  				= 	$cus;

			$cus['name']				=  "Coupon code";
			$cus['value']				=  @$order_details['coupon_code'];
			$cus_field[]  				=  $cus;


			$customergroup 				= $order_details['customer_group_id'];
			if(isset($customergroup) && !empty($customergroup)&& $customergroup ==1 ){
				$customergroup 			= 'General';
			}else{
				$customergroup 			= 'NOT LOGGED IN';
			}

			$cus['name']				=  "Customer Group";
			$cus['value']				=  $customergroup;
			$cus_field[]  				=  $cus;

			$additional_information = $order_details['payment']['additional_information'];
			if(isset($additional_information) && !empty($additional_information)){
				foreach($additional_information as $key => $additional_info){
						if(!empty($additional_information) && $key =='authorize_cards'){
							foreach($additional_info as $other_information){
									if(isset($other_information) && !empty($other_information)){
									foreach($other_information as $keys => $cart_info){	
										if(!empty($cart_info)){
										$cus['name'] 	=	$keys;
										$cus['value']	=	$cart_info;
										$cus_field[]  	=	$cus;
										}
									}
								}
							}	
						}else{
										$cus['name'] 	=  $key;
										$cus['value']	=  $additional_info;
										$cus_field[]  	= $cus;

						}
				}
			}

			if(isset($order_details['status_history']) && !empty($order_details['status_history'])) {
					$h=1;
				foreach ($order_details['status_history'] as $history) {
					if(!empty($history['comment'])){
						$cus['name'] 	=	'Staus Comment '.$h;
						$cus['value']	=	$history['comment'];
						$cus_field[]  	=	$cus;
						$h++;
					}
				}

			}

			if(isset($cus_field) && !empty($cus_field)){
				$i=0;
				foreach($cus_field as  $cart_info){	
					if(!empty($cart_info['value'])){
	
						$postdata['order']['note_attributes'][$i]['name'] 	=  $cart_info['name'];
						$postdata['order']['note_attributes'][$i]['value']	=  $cart_info['value'];
						$i++;
					}
				}
			}

			echo '<pre>';
			print_r($postdata);
			exit;

			$setting_shppoify 		= $this->ordermodel->getGeneralSetting();
			$storeurl_shopify   	= $setting_shppoify[0]['storeurl_volusion'];
			$shopify_key		  	= $setting_shppoify[0]['loginemail'];
			$shopify_pw		  		= $setting_shppoify[0]['encryptedpassword'];
					 

			//Import order in shopify
			$api_url = $storeurl_shopify.'/admin/orders.json';
			$headr = array();
			$headr[] = 'Content-Type: application/json';
			$ch = curl_init($api_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
			curl_setopt($ch, CURLOPT_USERPWD, $shopify_key.':'.$shopify_pw );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
			$res = curl_exec($ch);
			$response = json_decode($res);



			//order transaction 
			if(isset($response->order->id) && !empty($response->order->id)){
				$status = "yes";

				$response_message['success'][] =  $response->order->id. ' Order has been import successfully...';
				
				$this->ordermodel->updateorderstatus($response->order->id,$orderid,$order_details['customer_email'],$status,'');
				//$this->ordermodel->updateorderShopifystatus($shopifyorder,$status);	
				$getshipment = $order_details['shipping_method'];
				if(isset($getshipment) && !empty($getshipment))
				{
					$postdata_ful = array();					
					$postdata_ful['fulfillment']['status']				= "fulfilled" ;
					$postdata_ful['fulfillment']['tracking_company']	=  $shipping_method;
					$postdata_ful['fulfillment']['location_id']			=  "23142826038";//need to verif
					$postdata_ful['fulfillment']["tracking_number"]		=  strtotime("now");
					$postdata_ful['fulfillment']["notify_customer"]		=  false;
				
					$api_url = $storeurl_shopify.'/admin/orders/'.$response->order->id.'/fulfillments.json';
					$headr = array();
					$headr[] = 'Content-Type: application/json';
					$ch = curl_init($api_url);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
					curl_setopt($ch, CURLOPT_USERPWD,  $shopify_key.':'.$shopify_pw );
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata_ful));
					$res_fulment = curl_exec($ch);
					$response_fulment = json_decode($res_fulment);

					//exit;
					
					if(isset($response_fulment->fulfillment->id) && !empty($response_fulment->fulfillment->id)){
						$response_message['success'][] = $response_fulment->fulfillment->id. ' Order fulfilment set successfully...';
					}else{
						$response_message['error'][] =  json_encode($response_fulment);
					}
					
					if(isset($getshipment) && !empty($getshipment))
					{
						if($order_details['status'] == 'Refunded')
						{
							$datat			    = array(); 
							$datat['orderId'] 	= $response->order->id;
							$datat['amount'] 	= $order_details['grand_total'];
							$datat['Method']   	= $order_details->payment_method;
							$transactions 		= $this->createTransaction($datat);
						}
						
					}
					
					// Transaction set
					if(($order_details['status'] == 'complete' && $order_details['state'] == 'complete')  || $order_details['status'] == 'Partially Shipped' || $order_details['status'] == 'Shipped')
					{

						$datat			    = array(); 
						$datat['orderId'] 	= $response->order->id;
						$datat['amount'] 	= $order_details['grand_total'];//need to verify
						$datat['Method']   	= $order_details['payment']['method'];
						$transactions 		= $this->createTransaction($datat);
					}

					
				}else{
					echo  'Order status - '.$order_details->status;
					$response_message['success'][] = 'Order status - '.$order_details->status;
				}
				echo $response->order->id. "- Order Import Successfully";
				
			}
			else
			{
				$status = "no";
				$shopify_order_id = 0;
				$response_message['error'][] =  json_encode($response->errors); 
				$response_message['error'][] =  $orderid.' - Order import error...'; 
				echo $orderid.' - Order import error...';
				$this->ordermodel->updateorderstatus($shopify_order_id,$orderid,$order_details['customer_email'],$status,json_encode($response_message));	
			}

		}else{
				echo "Magento Order detail not found";
				$status = "no";
				$shopify_order_id = 0;
				$response_message = "Magento Order detail not found";
				$this->ordermodel->updateorderstatus($shopify_order_id,$orderid,$order_details['customer_email'],$status,$response_message);	
		}
	
	}



	public function createTransaction($data = array())
	{
	
		$setting_shppoify 		= $this->ordermodel->getGeneralSetting();

		$storeurl_shopify   	= $setting_shppoify[0]['storeurl_volusion'];
		$shopify_key		  	= $setting_shppoify[0]['loginemail'];
		$shopify_pw		  		= $setting_shppoify[0]['encryptedpassword'];
		
		$amount  = '';	
		$orderId = $data['orderId'];
		$amount  = $data['amount'];
		$Method  = $data['Method'];
		
		$postdata_t['transaction']['amount']			= $amount;
		$postdata_t['transaction']['kind']				= "authorization";
		$postdata_t['transaction']['gateway']			= $Method;
		$postdata_t['transaction']['order_id']			= $orderId;
		$postdata_t['transaction']['source']			= "external";
		
		$api_url = $storeurl_shopify.'/admin/orders/'.$orderId.'/transactions.json';
		$headr = array();
		$headr[] = 'Content-Type: application/json';
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_USERPWD,  $shopify_key.':'.$shopify_pw );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata_t));
		$res_fulment = curl_exec($ch);
		$response_fulment = json_decode($res_fulment);
		//echo "<pre>";
		//print_r($response_fulment);
		
		// Complete Transaction 
		$postdata_tc['transaction']['kind']				= "capture";
		$postdata_tc['transaction']['source']			= "external";
		$postdata_tc['transaction']['message']			= 'transaction successfully completed';
		
		$api_url = $storeurl_shopify.'/admin/orders/'.$orderId.'/transactions.json';
		$headr = array();
		$headr[] = 'Content-Type: application/json';
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_USERPWD,  $shopify_key.':'.$shopify_pw );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata_tc));
		$res_fulment_tc = curl_exec($ch);
		$res_fulment_tc_d = json_decode($res_fulment_tc);
				//echo "<pre>";
		//print_r($res_fulment_tc_d);
		return $res_fulment_tc_d;
	}

	public function createTransactionref($data = array())
	{
		$setting_shppoify 		= $this->ordermodel->getGeneralSetting();

		$storeurl_shopify   	= $setting_shppoify[0]['storeurl_volusion'];
		$shopify_key		  	= $setting_shppoify[0]['loginemail'];
		$shopify_pw		  		= $setting_shppoify[0]['encryptedpassword'];
		
		$amount  = '';	
		$orderId = $data['orderId'];
		$amount  = $data['amount'];
		$Method  = $data['Method'];
		
		$postdata_t['transaction']['amount']			= $amount;
		$postdata_t['transaction']['kind']				= "refund";
		$postdata_t['transaction']['gateway']			= $Method;
		$postdata_t['transaction']['order_id']			= $orderId;
		$postdata_t['transaction']['source']			= "external";
		
		$api_url = $storeurl_shopify.'/admin/orders/'.$orderId.'/transactions.json';
		$headr = array();
		$headr[] = 'Content-Type: application/json';
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		curl_setopt($ch, CURLOPT_USERPWD,  $shopify_key.':'.$shopify_pw );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata_t));
		$res_fulment_t = curl_exec($ch);
		$res_fulment_t_d = json_decode($res_fulment_t);
		return $res_fulment_t_d;
	}

	function customerimport($code)
	{

		$setting_shppoify 		= $this->customermodel->getGeneralSetting();
		$storeurl_shopify   	= $setting_shppoify[0]['storeurl_volusion'];
		$shopify_key		  	= $setting_shppoify[0]['loginemail'];
		$shopify_pw		  		= $setting_shppoify[0]['encryptedpassword'];

		if(isset($code) && !empty($code))
		{
			$options = array(
				'trace' => true,
				'connection_timeout' => 120000000000,
				'wsdl_cache' => WSDL_CACHE_NONE,
			);

			$proxy  = new SoapClient('https://www.billskhakis.com/index3.php/api/soap/?wsdl=1',$options);
			$sessionId    = $proxy->login('DataMigration', '123456');
			$customer_details = array();

			$customer_id   = $code;
			$customer_details = $proxy->call($sessionId,'customer.info', $customer_id);
			$getCustomerAddress = array(); 

			$default_billing = '';
			if(isset($customer_details['default_billing']) && !empty($customer_details['default_billing'])){
				$default_billing  = $customer_details['default_billing'];
				$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_billing);
			}

			$default_shipping  = '';
			if(isset($customer_details['default_shipping']) && !empty($customer_details['default_shipping'])){
				$default_shipping  = $customer_details['default_shipping'];
				$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_shipping);

			}

			$customer_firstname = '';
			if(isset($customer_details['firstname']) && !empty($customer_details['firstname'])){
				$customer_firstname = trim($customer_details['firstname']);
			}
			$customer_lastname = '';
			if(isset($customer_details['lastname']) && !empty($customer_details['lastname'])){
				$customer_lastname = trim($customer_details['lastname']);
			}
			$customer_email = '';
			if(isset($customer_details['email']) && !empty($customer_details['email'])){
				$customer_email = trim($customer_details['email']);
				//$customer_email = "113@gmail.com";
			}
		
			$password = $this->randomPassword();

			$customer_phonenumber = '';
			if(isset($getCustomerAddress[0]) && !empty($getCustomerAddress[0])){
				$customer_phonenumber = trim($getCustomerAddress[0]['telephone']);
			}


	

			$api_url = $storeurl_shopify.'/admin/customers/search.json?query=email:'.$customer_email.'';
			$ch = curl_init($api_url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
			curl_setopt($ch, CURLOPT_USERPWD, $shopify_key.':'.$shopify_pw ); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );   
			$res_fulment = curl_exec($ch);
			$customer_check = json_decode($res_fulment); 
		
			if(isset($customer_check->customers[0]->id) && !empty($customer_check->customers[0]->id))
			{
				//echo $customer_check->customers[0]->id. ' - Custom Allredy exist';
				//$this->customermodel->customerupdate($customer_check->customers[0]->id,$customer_bc_id,'');
				return $customer_check->customers[0]->id;
			}
			else
			{
				$customerdata = array();
				$customerdata['customer']['first_name'] 			  	  = $customer_firstname;
				$customerdata['customer']['last_name'] 				  	  = $customer_lastname;
				$customerdata['customer']['email'] 					 	  = $customer_email;
				$customerdata['customer']['phone'] 					 	  = $customer_phonenumber;
				$customerdata['customer']['tags'] 					 	  = 'General';

				$customerdata['customer']['password'] 					  = $password;
				$customerdata['customer']['password_confirmation'] 		  = $password;
				$customerdata['customer']['send_email_welcome'] 		  = false;
				
				
				$notes = '';
				if(isset($customer_note) && !empty($customer_note)){
					$notes	  .=  $customer_note."\n";
				}

				if(isset($code) && !empty($code)){
					$notes	  .=  "Magento Customer ID: ".$code."\n";
				}

				if(isset($customer_details['authnetcim_profile_version']) && !empty($customer_details['authnetcim_profile_version'])){
					$notes	  .=  "Authnetcim profile version: ".$customer_details['authnetcim_profile_version']."\n";
				}
				if(isset($customer_details['dob']) && !empty($customer_details['dob'])){
					$notes	  .= "Customer DOB: ".$customer_details['dob']."\n";
				}	
				
				if(isset($customer_details['gender']) && !empty($customer_details['gender'])){
					$notes	  .= "Gender: ".$customer_details['gender']."\n";
				}	

				if(isset($customer_details['rp_token']) && !empty($customer_details['rp_token'])){
					$notes	  .= "rp_token: ".$customer_details['rp_token']."\n";
				}	

				if(isset($customer_details['rp_customer_id']) && !empty($customer_details['rp_customer_id'])){
					$notes	  .= "rp_customer_id: ".$customer_details['rp_customer_id']."\n";
				}	

				if(isset($customer_details['rp_token_created_at']) && !empty($customer_details['rp_token_created_at'])){
					$notes	  .= "rp_token_created_at: ".date('Y-m-d H:i:s',strtotime($customer_details['rp_token_created_at']))."\n";
				}
				
				$customerdata['customer']['note']						  = $notes;	
				$customerdata['customer']['verified_email'] 			  = false;
				$customerdata['customer']['created_at'] 			  	  = date('c',strtotime($customer_details['created_at']));
				 
				if(isset($getCustomerAddress) && !empty($getCustomerAddress))
				{
					$i = 0;
					foreach($getCustomerAddress as $getCustomerAddresss)
					{
						$customerdata['customer']['addresses'][$i]['first_name']  = $getCustomerAddresss['firstname'];
						$customerdata['customer']['addresses'][$i]['last_name']   = $getCustomerAddresss['lastname'];
						$customerdata['customer']['addresses'][$i]['company'] 	  = $getCustomerAddresss['company'];
						$customerdata['customer']['addresses'][$i]['address1'] 	  = $getCustomerAddresss['street'];
						$customerdata['customer']['addresses'][$i]['address2'] 	  = '';
						$customerdata['customer']['addresses'][$i]['city'] 		  = $getCustomerAddresss['city'];			
						$customerdata['customer']['addresses'][$i]['country'] 	  = $this->getcountryname($getCustomerAddresss['country_id']);
						$customerdata['customer']['addresses'][$i]['province'] 	  = $getCustomerAddresss['region'];
						$customerdata['customer']['addresses'][$i]['phone'] 	  = $getCustomerAddresss['telephone'];
						$customerdata['customer']['addresses'][$i]['zip']		  = $getCustomerAddresss['postcode'];
					$i++;
					}
				}

			
				$api_url = $storeurl_shopify.'/admin/customers.json';
				$ch = curl_init($api_url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));	
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
				curl_setopt($ch, CURLOPT_USERPWD, $shopify_key.':'.$shopify_pw ); 
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($customerdata));
				$res = curl_exec($ch);
				$response = json_decode($res);

				//exit;
			
				if(isset($response->customer->id) && !empty($response->customer->id))
				{
					
					$status = "yes";
					$data = array(
						'magento_id' 			=> $code,
						'name'		 			=> $response->customer->first_name.''.$response->customer->last_name,
						'email'		 			=> $response->customer->email,
						'shopify_customer_id'	=> $response->customer->id,
						'status'				=> $status,
						'error'					=> ''
					);
					
					$this->customermodel->customerinsert($data);
					return  $response->customer->id;
					echo  $response->customer->id.' - Customer import sucessfully';
				}else{
					$error = json_encode($response);
					//$this->customermodel->customerinsert($shopify_customer_id,$code,$status,$error);
					echo $error;
					return false;
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