<?php 
class Customer extends CI_controller
{
	
	function customer()
	{
		parent::__construct();	
		$this->load->library('bigcommerceapi');
		$this->load->model("admin/customermodel");
		
		include(APPPATH.'third_party/PHPExcel.php');
		include(APPPATH.'third_party/PHPExcel/Writer/Excel2007.php');
	}

	function index()
	{	
		
		$session_data = $this->session->userdata('admin_session');
		if(!isset($session_data) || empty($session_data))redirect('admin/login');
		
		$this->data["page_head"]  = 'Magento to BigCommerce customer Import';
		$this->data["page_title"] = 'Magento to BigCommerce customer Import';
		
		$customer_data = $this->customermodel->getcustomer();
		$this->data['total_customer'] = count($customer_data);
		$this->data['customer_data']  = $customer_data;
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']=$this->load->view('admin/common/leftmenu',$this->data,true);	
		$this->load->view("admin/customer/list",$this->data);
		$this->load->view("admin/common/footer");
		
		
		
	}

	
	
	function usernotification()
	{
		$session_data = $this->session->userdata('admin_session');
		if(!isset($session_data) || empty($session_data))redirect('admin/login');
		
		$this->data["page_head"]  = 'Magento to BigCommerce customer reset password';
		$this->data["page_title"] = 'Magento to BigCommerce customer reset password';
		
		$customer_data = $this->customermodel->getcustomerresetpassword();
		$this->data['total_customer'] = count($customer_data);
		$this->data['customer_data']  = $customer_data;
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']=$this->load->view('admin/common/leftmenu',$this->data,true);	
		$this->load->view("admin/customer/customernotification",$this->data);
		$this->load->view("admin/common/footer");
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
	
	
	function resetpasswrod()
	{
		$reset_password_error = APPPATH."third_party/customer/reset_password_error.xls";
		$spreadsheet_reset_password = PHPExcel_IOFactory::load($reset_password_error);
		$spreadsheet_reset_password->setActiveSheetIndex(0);
		$worksheet_reset_password = $spreadsheet_reset_password->getActiveSheet();
		
		$customer_id = $this->input->get('code');
		$column = $this->input->get('column');
		$config_data = $this->customermodel->getGeneralSetting();
		$store = '';
		if(isset($config_data[0]['apiusername']) && !empty($config_data[0]['apiusername']) && isset($config_data[0]['apipath']) && !empty($config_data[0]['apipath']) && isset($config_data[0]['apitoken']) && !empty($config_data[0]['apitoken'])){
			// BigCommerce API connection
			$store = new Bigcommerceapi($config_data[0]['apiusername'], $config_data[0]['apipath'] , $config_data[0]['apitoken']);
		}
		
		$password_g  = $this->randomPassword();
		
		$field_reset_password = array();
		$field_reset_password['_authentication']['password'] = $password_g;
		$reset_password = $store->put('/customers/'.$customer_id,$field_reset_password);
		
		
		if(isset($reset_password['email']) && !empty($reset_password['email']))
		{
			$this->customermodel->updatestatus($customer_id);
			
			$store_url = $config_data[0]['storeurl'];
			$username  = $reset_password['email'];
			$password  = $password_g;
			
			$subject_activation = 'Your Password Has Been Changed!';
						
			$html_plan = '<div style="background-color:#ffffff;font-family:Verdana,Arial,Helvetica,sans-serif;font-size:15px;width:800px;margin:0px auto">
				<div style="border-bottom:1px #0086c7 solid;margin-bottom:15px">
					<h1 style="display:block;text-align:center;padding:30px 0px 10px">
						<img src="http://cdn3.bigcommerce.com/s-fxjd74hwbl/product_images/logo_1466588892__47438.png">
					</h1>    
				</div>
				
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000"><b>Your Password Has Been Changed!</b></h2>
				</div>
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000">This email confirm that your password has been changed.</h2>
				</div>
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000">To log on to the site, use the following credentials:</h2>
				</div>
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000"><b>Store URL:</b> '.$store_url.'</h2>
				</div>
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000"><b>Username:</b> '.$username.'</h2>
				</div>
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000"><b>Password:</b> '.$password.'</h2>
				</div>
				<div style="width:100%;margin-bottom:15px">
					<h2 style="color:#444;font-weight:normal;font-size:15px"><span style="color:#000">if you have any questions or encounter any problems logging in, please contact a site administrator <a href="mailto:sales@evrmemories.com">sales@evrmemories.com</a>.</h2>
				</div>
				<div style="width:100%;border-top:1px #0086c7 solid;">
					<div style="clear:both"></div>
					<div style="float:left;margin-right:10px;margin-top:15px">
						<div style="float:left;font-size:14px;color:#333;font-weight:normal;">
							Thanks,<br/> 
							<span style="color:#0086c7;letter-spacing:1">
								 '.$config_data[0]['storename'].'
							</span>
						</div>
					</div>
				</div>
			</div>';
			
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= 'From: <sales@evrmemories.com>' . "\r\n";
			$to = $username;
			@mail($to,$subject_activation,$html_plan,$headers);		

			echo $username.' - Customer Password Has Been Changed!'	;
		}
		else
		{
			echo 'Customer Password Changed Error!!!';
			$commnet = 'Customer Password Changed Error!!!';
			
			$column = $column + 1;
			$worksheet_reset_password->setCellValueExplicit('A1','Customer ID', PHPExcel_Cell_DataType::TYPE_STRING);
			$worksheet_reset_password->setCellValueExplicit('B1','Comment', PHPExcel_Cell_DataType::TYPE_STRING);
			
			$worksheet_reset_password->setCellValueExplicit('A'.$column,$customer_id, PHPExcel_Cell_DataType::TYPE_STRING);
			$worksheet_reset_password->setCellValueExplicit('B'.$column,$commnet, PHPExcel_Cell_DataType::TYPE_STRING);
			
			$writer_reset_password = new PHPExcel_Writer_Excel2007($spreadsheet_reset_password);
			$writer_reset_password->save($reset_password_error);
			
		}	
			
	}
	
	
	
	function importmanully()
	{
			
		$filename_export = APPPATH."third_party/customer/customer_volusion_to_bc.csv";
		$filename_missing = APPPATH."third_party/customer/not_import_customer_list.xls";
		$filename_imported = APPPATH."third_party/customer/import_customer_list.xls";
		
		$spreadsheet = new PHPExcel();
		$spreadsheet->setActiveSheetIndex(0);
		$worksheet = $spreadsheet->getActiveSheet();
		
		$spreadsheet_missing = new PHPExcel();
		$spreadsheet_missing->setActiveSheetIndex(0);
		$worksheet_missing = $spreadsheet_missing->getActiveSheet();
		
		$spreadsheet_import = new PHPExcel();
		$spreadsheet_import->setActiveSheetIndex(0);
		$worksheet_import = $spreadsheet_import->getActiveSheet();
		
		$worksheet->setCellValueExplicit('A1','Email Address', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('B1','First Name', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('C1','Last Name', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('D1','Company', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('E1','Phone', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('F1','Notes', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('G1','Store Credit', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('H1','Customer Group', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('I1','Address ID - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('J1','Address First Name - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('K1','Address Last Name - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('L1','Address Company - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('M1','Address Line 1 - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('N1','Address Line 2 - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('O1','Address City - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('P1','Address State - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('Q1','Address Zip - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('R1','Address Country - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('S1','Address Phone - 1', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('T1','Receive Review/Abandoned Cart Emails?', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('U1','Tax Exempt Category', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet->setCellValueExplicit('V1','Customer ID', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$worksheet_missing->setCellValueExplicit('A1','Customer ID', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet_missing->setCellValueExplicit('B1','Customer Email', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet_missing->setCellValueExplicit('C1','Comment', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$worksheet_import->setCellValueExplicit('A1','Customer ID', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet_import->setCellValueExplicit('B1','Customer Email', PHPExcel_Cell_DataType::TYPE_STRING);
		$worksheet_import->setCellValueExplicit('C1','Comment', PHPExcel_Cell_DataType::TYPE_STRING);
		
		$all_customer = $this->customermodel->getcustomer();
		
		echo '<pre>';
		print_r($all_customer);
		exit;
		
			
		if(isset($all_customer) && !empty($all_customer))
		{
			$column = 2;
			foreach($all_customer as $all_customers)
			{
				$customer_details =  $all_customers;
				
				$customer_email = '';
				if(isset($customer_details['email']) && !empty($customer_details['email'])){
					$customer_email = trim($customer_details['email']);
				}
				$customer_firstname = '';
				if(isset($customer_details['firstname']) && !empty($customer_details['firstname'])){
					$customer_firstname = trim($customer_details['firstname']);
				}
				$customer_lastname = '';
				if(isset($customer_details['lastname']) && !empty($customer_details['lastname'])){
					$customer_lastname = trim($customer_details['lastname']);
				}
				$customer_companyname = '';
				if(isset($customer_details['billing_company']) && !empty($customer_details['billing_company'])){
					$customer_companyname = trim($customer_details['billing_company']);
				}
				$customer_phonenumber = '';
				if(isset($customer_details['billing_telephone']) && !empty($customer_details['billing_telephone'])){
					$customer_phonenumber = trim($customer_details['billing_telephone']);
				}
				$customer_note = '';
				if(isset($customer_details['Customer_Notes']) && !empty($customer_details['Customer_Notes'])){
					$customer_note = trim($customer_details['Customer_Notes']);
				}
				$customer_address1 = '';
				if(isset($customer_details['billing_street1']) && !empty($customer_details['billing_street1'])){
					$customer_address1 = trim($customer_details['billing_street1']);
				}
				$customer_address2 = '';
				if(isset($customer_details['billing_street2']) && !empty($customer_details['billing_street2'])){
					$customer_address2 = trim($customer_details['billing_street2']);
				}
				$customer_city = '';
				if(isset($customer_details['billing_city']) && !empty($customer_details['billing_city'])){
					$customer_city = trim($customer_details['billing_city']);
				}
				$customer_state = '';
				if(isset($customer_details['billing_region']) && !empty($customer_details['billing_region'])){
					$customer_state = trim($customer_details['billing_region']);
				}
				$customer_zipcode = '';
				if(isset($customer_details['PostalCode']) && !empty($customer_details['PostalCode'])){
					$customer_zipcode = trim($customer_details['PostalCode']);
				}
				$customer_country = '';
				if(isset($customer_details['Country']) && !empty($customer_details['Country'])){
					$customer_country = trim($customer_details['Country']);
				}
				$customer_emailsubscribe = 0;
				if(isset($customer_details['EmailSubscriber']) && !empty($customer_details['EmailSubscriber']) && $customer_details['EmailSubscriber'] == 'Y'){
					$customer_emailsubscribe = 1;
				}
				$customer_volusion_id = '';
				if(isset($customer_details['CustomerID']) && !empty($customer_details['CustomerID'])){
					$customer_volusion_id = trim($customer_details['CustomerID']);
				}
				
				if(isset($customer_details) && !empty($customer_details) && !empty($customer_email) && !empty($customer_firstname) && !empty($customer_lastname))
				{
					$worksheet->setCellValueExplicit('A'.$column,$customer_email, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('B'.$column,$customer_firstname, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('C'.$column,$customer_lastname, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('D'.$column,$customer_companyname, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('E'.$column,$customer_phonenumber, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('F'.$column,$customer_note, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('G'.$column,0, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('H'.$column,'', PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('I'.$column,'', PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('J'.$column,$customer_firstname, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('K'.$column,$customer_lastname, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('L'.$column,$customer_companyname, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('M'.$column,$customer_address1, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('N'.$column,$customer_address2, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('O'.$column,$customer_city, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('P'.$column,$customer_state, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('Q'.$column,$customer_zipcode, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('R'.$column,$customer_country, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('S'.$column,$customer_phonenumber, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('T'.$column,$customer_emailsubscribe, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('U'.$column,'', PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet->setCellValueExplicit('V'.$column,$customer_volusion_id, PHPExcel_Cell_DataType::TYPE_STRING);
					
					$commnet =  $customer_volusion_id.' - Customer import successfully';
					$worksheet_import->setCellValueExplicit('A'.$column,$customer_volusion_id, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet_import->setCellValueExplicit('B'.$column,$customer_email, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet_import->setCellValueExplicit('C'.$column,$commnet, PHPExcel_Cell_DataType::TYPE_STRING);
				}
				else
				{
					$commnet =  $customer_id.' - Customer required messing details (email,firstname,lastname)';
					$worksheet_missing->setCellValueExplicit('A'.$column,$customer_id, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet_missing->setCellValueExplicit('B'.$column,$customer_email, PHPExcel_Cell_DataType::TYPE_STRING);
					$worksheet_missing->setCellValueExplicit('C'.$column,$commnet, PHPExcel_Cell_DataType::TYPE_STRING);
				}
			}
			else
			{
				$commnet =  $customer_id.' - Customer ID not found';
				$worksheet_missing->setCellValueExplicit('A'.$column,$customer_id, PHPExcel_Cell_DataType::TYPE_STRING);
				$worksheet_missing->setCellValueExplicit('B'.$column,$customer_email, PHPExcel_Cell_DataType::TYPE_STRING);
				$worksheet_missing->setCellValueExplicit('C'.$column,$commnet, PHPExcel_Cell_DataType::TYPE_STRING);
			}
			$column++;
		}
		$writer = new PHPExcel_Writer_Excel2007($spreadsheet);
		$writer->save($filename_export);
		
		$writer_import = new PHPExcel_Writer_Excel2007($spreadsheet_import);
		$writer_import->save($filename_imported);
		
		$writer_missing = new PHPExcel_Writer_Excel2007($spreadsheet_missing);
		$writer_missing->save($filename_missing);
	 }
}
?>