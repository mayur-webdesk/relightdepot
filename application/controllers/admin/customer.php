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
		
		$proxy 		  = new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    = $proxy->login('DataMigration', 'admin@321');
		$attributeSets  = $proxy->call($sessionId, 'customer.list');
		$set = current($attributeSets);
		$customer_details = array();
		$customer_details = $proxy->call($sessionId, 'customer.list');
		
		// echo '<pre>';
		// print_r($customer_details);
		// exit;

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
				$customer_array['email'] = '';
				if(isset($customer_details['email']) && !empty($customer_details['email'])){
					$customer_array['email'] = trim($customer_details['email']);
				}
				// email
				$customer_array['customer_group_id'] = 0;
				if(isset($customer_details['email']) && !empty($customer_details['email'])){
										
					$customer_array['customer_group_id'] = trim($customer_details['email']);
				}
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
				$customer_array['notes']						  = $notes;	




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
			
				if(isset($customer_check->customers[0]->id) && !empty($customer_check->customers[0]->id)) {
					
					echo $customer_check->customers[0]->id. ' - Customer Allredy exist';
					$error =  'Customer Allredy exist';
					$status = 'no';
					$this->customermodel->customerupdate($customer_check->customers[0]->id,$code,$status,$error);
				
				} else {
				
					$customerdata = array();
					$customerdata['customer']['first_name'] 			  	  = $customer_firstname;
					$customerdata['customer']['last_name'] 				  	  = $customer_lastname;
					$customerdata['customer']['email'] 					 	  = $customer_email;
					//$customerdata['customer']['phone'] 					 	  = $customer_phonenumber;
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
					$customerdata['customer']['created_at'] 			  	  = date('Y-m-d H:i:s',strtotime($customer_details['created_at']));
					 
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
				//echo "<pre>";
				//print_r($response);
					if(isset($response->customer->id) && !empty($response->customer->id))
					{
						$status = "yes";
						$this->customermodel->customerupdate($response->customer->id,$code,$status,'');
						$this->customermodel->customernewupdate($response->customer->id,$code,$status,'');
						echo  $response->customer->id.' - Customer import sucessfully';
					}else{
						$shopify_customer_id = 0;
						$status = "no";
						$error = json_encode($response);
						$this->customermodel->customerupdate($shopify_customer_id,$code,$status,$error);
						$this->customermodel->customernewupdate($shopify_customer_id,$code,$status,$error);
						echo $error;
					}
				}
		  	}else{
	  				$shopify_customer_id = 0;
					$status = "no";
					$error = "No Customer Found";
					$this->customermodel->customerupdate($shopify_customer_id,$code,$status,$error);

		  		echo "No Customer Found";
		  	}
			exit;
			// try	{
			// 	$Customer = Bigcommerce::createCustomer($customer_data);
	        // 		if(isset($Customer) && empty($Customer)) {
	        //     	throw new Exception('Bigcommerce\Api\Error');
	       	//  	} else {

			// 		echo $Customer->id.' - Customer import succesfully..<br>';
			// 		$message = 'Customer import succesfully...';
			// 		$this->customermodel->updatecustomerstatus($customer_id,$Customer->id,$message);
					
			// 		$customer_address = array();
			// 		if(isset($Customer->id) && !empty($Customer->id)){
						
			// 			$customer_address['first_name'] = $customer_firstname;
			// 			$customer_address['last_name']	= $customer_lastname;
			// 			$customer_address['company']	= $customer_companyname;
			// 			$customer_address['street_1'] 	= $customer_address1;
			// 			$customer_address['street_2'] 	= $customer_address2;
			// 			$customer_address['city']		= $customer_city;
			// 			$customer_address['state']		= $customer_state;
			// 			$customer_address['zip']		= $customer_zipcode;
			// 			$customer_address['country']	= $customer_country;
			// 			$customer_address['phone']		= $customer_phonenumber;
						
			// 			try	{
			// 				$Customeradd = Bigcommerce::createCustomeraddress($Customer->id,$customer_address);
			// 	        	if(isset($Customeradd) && empty($Customeradd)) {
			// 	            	throw new Exception('Bigcommerce\Api\Error');
			// 	       	 	} else {
			// 	       	 		$error2 = 'Customer import successfully with address...';
			// 	       	 		echo $Customer->id.' - Customer import successfully with address...<br>';

			// 	       	 		$this->customermodel->updateCustoAddMessage($customer_id, $error2);					       	 		
			// 				}
			//        	 	} catch(Exception $error) {
			// 				$error1 = $error->getMessage();
			// 				$error2 = 'Customer - '.$this->db->escape_str($error1);

			// 				echo $error2.'<br>';

			// 				$this->customermodel->updateCustoAddMessage($customer_id, $error2);							
			// 			}
			// 		}
			// 	}		
			// } catch(Exception $error) {
			// 	$error1 = $error->getMessage();
			// 	$error2 = $this->db->escape_str($error1);
			// 	$this->customermodel->updatecustomerMessage($customer_id, $error2);
			
			// 	echo $error1.'<br>';
			// }
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