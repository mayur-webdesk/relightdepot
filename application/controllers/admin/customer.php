<?php
ini_set('display_errors','On');
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

use Bigcommerce\Api\Client as Bigcommerce;
class Customer extends CI_controller{
	
	public function __construct() {

		parent::__construct();	
		$this->load->model("admin/customermodel");

		include(APPPATH.'third_party/PHPExcel.php');
		include(APPPATH.'third_party/PHPExcel/Writer/Excel2007.php');
		include(APPPATH.'third_party/bcapi/vendor/autoload.php');
	}

	function index() {	

		$session_data = $this->session->userdata('admin_session');
		if(!isset($session_data) || empty($session_data))redirect('admin/login');
		
		$this->data["page_head"]  = 'Magento to BigCommerce Customer Import';
		$this->data["page_title"] = 'Magento to BigCommerce Customer Import';
		
		$customer_data = $this->customermodel->getcustomer();
		$this->data['total_customer'] = count($customer_data);
		$this->data['customer_data']  = $customer_data;
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']=$this->load->view('admin/common/leftmenu',$this->data,true);	
		$this->load->view("admin/customer/list",$this->data);
		$this->load->view("admin/common/footer");
	}
			
	public function getMagenCustomer(){

		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');
		$attributeSets  = $proxy->call($sessionId, 'customer.list');
		$set = current($attributeSets);
		$customer_details = array();
		$customer_details = $proxy->call($sessionId, 'customer.list');
		

		$ins_customer = array();
		if(isset($customer_details) && !empty($customer_details)) {
			foreach($customer_details as $customer) {

				$customer_data = array(); 
				$customer_data['magento_id'] = 	$customer['customer_id'];

				$customer_data['email'] = '';
				if(isset($customer['email']) && !empty($customer['email'])){
					$customer_data['email'] = 	$customer['email'];
				}

				$customer_data['firstname'] = '';
				if(isset($customer['firstname']) && !empty($customer['firstname'])){
					$customer_data['firstname'] = 	$customer['firstname'];
				}

				$customer_data['prefix'] = '';
				if(isset($customer['prefix']) && !empty($customer['prefix'])){
					$customer_data['prefix'] = 	$customer['prefix'];
				}

				$customer_data['middlename'] = '';
				if(isset($customer['middlename']) && !empty($customer['middlename'])){
					$customer_data['middlename'] = 	$customer['middlename'];
				}

				$customer_data['lastname'] = '';
				if(isset($customer['lastname']) && !empty($customer['lastname'])){
					$customer_data['lastname'] = 	$customer['lastname'];
				}

				$customer_data['group_id'] = $customer['group_id'];
				
				$customer_data['bc_customer_id'] 	= '';
				$customer_data['status'] 			= 'no';
				$customer_data['error'] 			= '';
				$customer_data['add_error'] 		= '';

				$ins_customer[] = $customer_data;
			}
		} 
		
		if (isset($ins_customer) && !empty($ins_customer))  {	
			$record = 500;
			$data = array_chunk($ins_customer,$record,true);
		
			foreach ($data as $ins_customer_data)  {	
				$query = $this->db->insert_batch('customer', $ins_customer_data);
			} 
			
			return 1;
        }
		echo 'Customer import magento to database sucessfully';
	}

	function customerImport() {

		$customer_id = $this->input->get('code');

		$config_data = $this->customermodel->getGeneralSetting();

		$client_id		= $config_data['client_id'];
		$access_token	= $config_data['apitoken'];
		$store_hash		= $config_data['storehash'];	

		Bigcommerce::configure(array('client_id' => $client_id, 'auth_token' => $access_token, 'store_hash' => $store_hash)); // Bc class connection
		Bigcommerce::verifyPeer(false); // SSL verify False 		
		Bigcommerce::failOnError(); 	// Display error exception on

		if(isset($customer_id) && !empty($customer_id)) {

			$options = array(
				'trace' => true,
				'connection_timeout' => 120000000000,
				'wsdl_cache' => WSDL_CACHE_NONE,
			);

			$proxy 	   = new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
			$sessionId = $proxy->login('DataMigration', 'admin@321');
			$customer_details = array();
			
			$customer_details = $proxy->call($sessionId,'customer.info', $customer_id);
			
			// echo '<pre>';
			// print_r($customer_details);
			// exit;

			$getCustomerAddress = array(); 

			if(isset($customer_details) && !empty($customer_details)){
				
				$customer_array = array();
				
				// default_billing and default_shipping
				$default_billing   = '';
				$default_shipping  = '';
				if(isset($customer_details['default_billing']) && !empty($customer_details['default_billing'])){
					$default_billing  = $customer_details['default_billing'];
				}
				if(isset($customer_details['default_shipping']) && !empty($customer_details['default_shipping'])){
					$default_shipping  = $customer_details['default_shipping'];
				}

				if(isset($default_billing) && !empty($default_billing) && isset($default_shipping) && !empty($default_shipping) && $default_billing == $default_shipping) {
					
					$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_billing);
				
				} elseif(isset($default_billing) && !empty($default_billing) && isset($default_shipping) && !empty($default_shipping) && $default_billing != $default_shipping) {  

					$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_billing);
					
					$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_shipping);
				}
				elseif(isset($default_billing) && !empty($default_billing)) { 
					
					$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_billing);
				} elseif(isset($default_shipping) && !empty($default_shipping)) {  

					$getCustomerAddress[] = $proxy->call($sessionId,'customer_address.info', $default_shipping);
				}

				// firstname and middlename
				$firstname = '';
				if(isset($customer_details['firstname']) && !empty($customer_details['firstname'])){
					$firstname = trim($customer_details['firstname']);
				}
				$middlename = '';
				if(isset($customer_details['middlename']) && !empty($customer_details['middlename'])){
					$middlename = trim($customer_details['middlename']);
				}
				$customer_array['first_name'] = '';
				$customer_array['first_name'] = $firstname . ' ' .  $middlename;
				// lastname
				$customer_array['last_name'] = '';
				if(isset($customer_details['lastname']) && !empty($customer_details['lastname'])){
					$customer_array['last_name'] = trim($customer_details['lastname']);
				}
				// company
				$customer_array['company'] = '';
				if(isset($getCustomerAddress[0]['company']) && !empty($getCustomerAddress[0]['company'])){
					$customer_array['company'] = trim($getCustomerAddress[0]['company']);
				}
				// email
				// $customer_array['email'] = 'development.qatesting@gmail.com';
				$customer_array['email'] = '';
				if(isset($customer_details['email']) && !empty($customer_details['email'])){
					$customer_array['email'] = trim($customer_details['email']);
				}
				// customer_group_id
				$group_id = 0;
				if(isset($customer_details['group_id']) && !empty($customer_details['group_id'])){
										
					if ($customer_details['group_id'] == 0) {
						$group_id = 3;
					} elseif($customer_details['group_id'] == 1) {
						$group_id = 1;
					} elseif($customer_details['group_id'] == 2) {
						$group_id = 2;
					} elseif($customer_details['group_id'] == 3) {
						$group_id = 4;
					}				
				}
				$customer_array['customer_group_id'] = trim($group_id);
				
				// phone				
				$customer_array['phone'] = '';
				if(isset($getCustomerAddress[0]['telephone']) && !empty($getCustomerAddress[0]['telephone'])){
					$customer_array['phone'] = trim($getCustomerAddress[0]['telephone']);
				}

				$notes = '';			
				if(isset($customer_id) && !empty($customer_id)){
					$notes	  .=  "Magento Customer ID: ".$customer_id."\n";
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
				$customer_array['notes'] = $notes;	

				$CustomerAddrdata = array();

				if(isset($getCustomerAddress) && !empty($getCustomerAddress)){

					$i = 0;
					foreach($getCustomerAddress as $Address) {

						$CustomerAddrdata[$i]['first_name']   = $Address['firstname'];
						$CustomerAddrdata[$i]['last_name']    = $Address['lastname'];
						$CustomerAddrdata[$i]['company'] 	  = $Address['company'];
						$CustomerAddrdata[$i]['street_1'] 	  = $Address['street'];
						$CustomerAddrdata[$i]['street_2'] 	  = '';
						$CustomerAddrdata[$i]['city'] 		  = $Address['city'];			
						$CustomerAddrdata[$i]['state'] 	  	  = $Address['region'];
						$CustomerAddrdata[$i]['zip']		  = $Address['postcode'];
						$CustomerAddrdata[$i]['country'] 	  = $this->getcountryname($Address['country_id']);
						$CustomerAddrdata[$i]['phone'] 	  	  = $Address['telephone'];
					 	
						$i++;
					}
				}	

				// if(isset($CustomerAddrdata) && !empty($CustomerAddrdata)){
				// 	foreach($CustomerAddrdata as $CustomerAddress){
				// 		echo '<pre>';
				// 		print_r($CustomerAddress);
				// 	}
				// }

				// echo '<pre>';
				// print_r($customer_array); 
				// echo '<pre>';
				// print_r($getCustomerAddress); 
				// exit;

				try	{
					$Customer = Bigcommerce::createCustomer($customer_array);
						if(isset($Customer) && empty($Customer)) {
							throw new Exception('Bigcommerce\Api\Error');
						} else {
	
						echo $Customer->id.' - Customer import succesfully..<br>';
					
						$this->customermodel->updateCustomerStatus($customer_id,$Customer->id);
						
						$customer_address = array();
						if(isset($Customer->id) && !empty($Customer->id)){
							
							if(isset($CustomerAddrdata) && !empty($CustomerAddrdata)) {

								foreach($CustomerAddrdata as $CustomerAddress) {
									
									try	{

										$Customeradd = Bigcommerce::createCustomeraddress($Customer->id,$CustomerAddress);
										if(isset($Customeradd) && empty($Customeradd)) {
											throw new Exception('Bigcommerce\Api\Error');
										} else {
											
											echo $Customer->id.' - Customer import successfully with address...<br>';
										}
									} catch(Exception $error) {

										$error1 = $error->getMessage();
										$error2 = 'Customer - '. $this->db->escape_str($error1);

										echo $error2.'<br>';

										$this->customermodel->CustomerAddressError($customer_id, $error2);							
									}
								}

							} else {

								$error1 = 'No Customer Address Found';
								
								echo $error1.'<br>';

								$this->customermodel->CustomerAddressError($customer_id, $error1);
							} 
						}
					}
			
				} catch(Exception $error) {
					
					$error1 = $error->getMessage();
					$error2 = $this->db->escape_str($error1);					

					$this->customermodel->updateCustomerError($customer_id, $error2);
				
					echo $error1.'<br>';
				}

		  	} else {

				$error = "No Customer Found";
				$this->customermodel->updateCustomerError($customer_id, $error);

		  		echo $error;
		  	}
			exit;
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
	
	function updateMycustomer(){
		$customer = $this->customermodel->getMylcustomer();
		foreach ($customer as $cus) {
			$magento_id = $cus['magento_id'];
			$sp_id = $cus['shopify_customer_id'];
			$update = $this->customermodel->updateMycustomer($magento_id,$sp_id);

		}
	} 
}

?>