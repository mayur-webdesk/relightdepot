<?php 

use Bigcommerce\Api\Client as Bigcommerce;

class Product extends CI_controller{
	
	function __construct()
	{
		parent::__construct();	
		
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		$this->load->model("admin/productmodel");
		$this->load->library('magentoapi');
		$this->load->library('mcurl');
			
		include(APPPATH.'third_party/PHPExcel.php');
		include(APPPATH.'third_party/PHPExcel/Writer/Excel2007.php');
		include(APPPATH.'third_party/bcapi/vendor/autoload.php');
	}

	function index()
	{	
		
		$this->data["page_head"]  = 'Magento to BigCommerce Product Import';
		$this->data["page_title"] = 'Magento to BigCommerce Product Import';
		
		$product_detail = $this->productmodel->getConfigrationProducts();
		$this->data['total_product'] = count($product_detail);
		$this->data['product_data']  = $product_detail;
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']=$this->load->view('admin/common/leftmenu',$this->data,true);	
		$this->load->view("admin/product/list",$this->data);
		$this->load->view("admin/common/footer");
	}

	Public function importBrands()
	{
		$config_data 	= $this->productmodel->getGeneralSetting();
		$client_id 	  = $config_data['client_id'];
		$auth_token   = $config_data['apitoken'];
		$store_hash   = $config_data['storehash'];

		// Bc class connection		
		Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		// SSL verify False
		Bigcommerce::verifyPeer(false);
		// Display error exception on
		Bigcommerce::failOnError();

		$getBrand = $this->productmodel->getBrands();
		
		/* if(isset($getBrand) && !empty($getBrand))
		{
			foreach($getBrand as $getBrand_s)
			{
				$brand_data = array();

				$brand_data['name'] = '';
				if(isset($getBrand_s['name']) && !empty($getBrand_s['name']))
				{
					$brand_data['name'] = $getBrand_s['name'];
				}
				
				$brand_data['page_title'] = '';
				if(isset($getBrand_s['page_title']) && !empty($getBrand_s['page_title']))
				{
					$brand_data['page_title'] = $getBrand_s['page_title'];
				}

				$brand_data['meta_description'] = '';
				if(isset($getBrand_s['meta_description']) && !empty($getBrand_s['meta_description']))
				{
					$brand_data['meta_description'] = $getBrand_s['meta_description'];
				}

				$brand_data['meta_keywords'] = '';
				if(isset($getBrand_s['meta_keywords']) && !empty($getBrand_s['meta_keywords']))
				{
					$brand_data['meta_keywords'] = $getBrand_s['meta_keywords'];
				}

				if(isset($getBrand_s['thumbnail_image']) && !empty($getBrand_s['thumbnail_image']))
				{
					$brand_data['image_file'] = 'https://relightdepot.com/media/brands/thumbnail/'.$getBrand_s['brand_id'].'/'.$getBrand_s['thumbnail_image'];
				}

				try {
					$createBrand = Bigcommerce::createBrand($brand_data);
					if(isset($createBrand) && empty($createBrand)) {
						throw new Exception('Bigcommerce\Api\Error');
					} else {
						$this->productmodel->updateProductBrand($getBrand_s['brand_id'],$createBrand->id);
					}
				} catch(Exception $e) {
					$error = $e->getMessage();
					echo $error.'<br>';
				}
			}
		} */

		/* if(isset($getBrand) && !empty($getBrand))
		{
			foreach($getBrand as $getBrand_s)
			{
				$product_id = explode(',',$getBrand_s['product_ids']);
				
				$import_product = array();
				if(isset($product_id) && !empty($product_id))
				{
					$i = 0;
					foreach($product_id as $product_ids)
					{
						$import_product[$i]['brand_id'] 	= $getBrand_s['brand_id'];
						$import_product[$i]['name'] 		= $getBrand_s['name'];
						$import_product[$i]['product_id'] 	= $product_ids;
						$import_product[$i]['bc_brand_id'] 	= $getBrand_s['bc_brand_id'];

					$i++;
					}
				}
				
				if(isset($import_product) && !empty($import_product))
				{
					$this->db->insert_batch('brand_mapping',$import_product);
				}
			}
		} */
	}

	public function updateUrls()
	{
		$getUrl = $this->productmodel->getProductUrls();

		foreach ($getUrl as $getUrls) {

			$checkeURL = $this->productmodel->CheckProducts($getUrls['old_url']);
			
			if(isset($checkeURL) && !empty($checkeURL))
			{
				$updateURL = $this->productmodel->updateUrlRecord($getUrls);
			}else{

			}
		}
	}

	public function getMagentoproduct(){
		
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');

		$product = $proxy->call($sessionId, 'catalog_product.list');

		$data = array();
		$i = 0;
		foreach ($product  as $product_data) {

			//$product_data = $proxy->call($sessionId, 'catalog_product.info', $products['product_id']);

			$data[$i]['product_id']			= $product_data['product_id'];
			$data[$i]['sku']				= $this->db->escape_str($product_data['sku']);
			$data[$i]['name']				= $this->db->escape_str($product_data['name']);
			$data[$i]['set']				= $product_data['set'];
			$data[$i]['type']				= $product_data['type'];
			$data[$i]['simple_product_map']	= '';
			$data[$i]['image_status']		= 'no';
			$data[$i]['href_status']		= 'no';
			//$data[$i]['magento_url']		= $product_data['url_key'];
			$data[$i]['magento_url']		= '';
			$data[$i]['bc_product_id']		= '';
			$data[$i]['bc_product_url']		= '';
			$data[$i]['status']				= 'no';
			$data[$i]['sku_status']			= 'no';
			$data[$i]['tire_price']			= 'no';
			/* $ProductStatus = 'no';
			if(isset($product_data->status) && !empty($product_data->status) && $product_data->status == '1'){
				$ProductStatus = 'yes';
			} */
			//$data[$i]['visibility']			= $ProductStatus;
			$data[$i]['visibility']			= 'no';

		$i++;	
		}

		$this->db->insert_batch("products",$data);
	}

	function importAttribute($code)
	{
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');

		$datas = $proxy->call($sessionId,'product_attribute.info',$code);
	
		echo $datas['attribute_code'].'<br>';
		if ($this->db->table_exists($datas['attribute_code']))
		{
			echo 'table found...';
		}else {

			$sql = "CREATE TABLE ".$datas['attribute_code']." (
			  id INT(11) AUTO_INCREMENT PRIMARY KEY, 
			  value VARCHAR(255) NOT NULL,
			  label VARCHAR(255) NOT NULL
			  )";
			  $query = $this->db->query($sql);

			$this->db->insert_batch($datas['attribute_code'],$datas['options']);
			echo 'table not found...';
		}
	}

	function uniqnumberop() {
		 $password = '';
		 $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		 for($i = 0; $i < 8; $i++)
		 {
			 $random_int = mt_rand();
			 $password .= $charset[$random_int % strlen($charset)];
		 }
		 return $password;
	}

	function get_combinations($arrays) {
		$result = array();
		$arrays = array_values($arrays);
		$sizeIn = sizeof($arrays);
		$size = $sizeIn > 0 ? 1 : 0;
		foreach ($arrays as $array)
			$size = $size * sizeof($array);
		for ($i = 0; $i < $size; $i ++)
		{
			$result[$i] = array();
			for ($j = 0; $j < $sizeIn; $j ++)
				array_push($result[$i], current($arrays[$j]));
			for ($j = ($sizeIn -1); $j >= 0; $j --)
			{
				if (next($arrays[$j]))
					break;
				elseif (isset ($arrays[$j]))
					reset($arrays[$j]);
			}
		}
		return $result;
	}

	function getAttributes()
	{
		$api_url = 'https://relightdepot.com/getAttributeSetIdAttributeList.php?attrsetid=4';
				
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res_fulment = curl_exec($ch);
		$get_option_data = json_decode($res_fulment);

		echo '<pre>';
		print_r($get_option_data);
		exit;
	}


	function getProductOptionSKUs($product_id,$option_lable)
	{
		$api_url = 'https://relightdepot.com/getCustomDropDownOptInfoByIdAndValue.php?pid='.$product_id.'&optvalue='.str_replace(' ','%20',$option_lable);
				
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res_fulment = curl_exec($ch);
		$get_option_data = json_decode($res_fulment);
		$option_result = $get_option_data->values;

		return $option_result;
	}
	
	function importproduct()
	{
		header('Content-Type: text/html; charset=utf-8');
		
		$config_data 	= $this->productmodel->getGeneralSetting();
		$client_id 	  = $config_data['client_id'];
		$auth_token   = $config_data['apitoken'];
		$store_hash   = $config_data['storehash'];

		// Bc class connection		
		Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		// SSL verify False
		Bigcommerce::verifyPeer(false);
		// Display error exception on
		Bigcommerce::failOnError();
	
		$options_data = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
	
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options_data);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');

		$product_id = $this->input->get('code');

		//$api_url = 'https://relightdepot.com/getCustomDropDownOptInfo.php?pid='.$product_id;
		$api_url = 'https://relightdepot.com/getPids.php?pid='.$product_id;
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res_fulment = curl_exec($ch);
		$product = json_decode($res_fulment);
		$product_details = $product->product_info;

		/* echo '<pre>';
		print_r($product_details);
		exit; */

		$category = $proxy->call($sessionId, 'catalog_product.info', $product_id);

		$ProductCategory = array();
		foreach ($category['categories'] as $categorys) {
			$category_data = $this->productmodel->getCategory($categorys);
			if(isset($category_data) && !empty($category_data))
			{
				$ProductCategory[] = $category_data;
			}
		}

		if(empty($ProductCategory))
		{
			$ProductCategory = array(987);
		}
	
		$ProductName = '';
		if(isset($product_details->name) && !empty($product_details->name))
		{
			$ProductName = substr($product_details->name,0,249);
		}

		$ProductCode = '';
		if(isset($product_details->sku) && !empty($product_details->sku))
		{
			$ProductCode = $product_details->sku;
		}

		$ProductPrice 	= 0.00;
		if(isset($product_details->price) && !empty($product_details->price))
		{
			$ProductPrice 	= number_format($product_details->price,2,'.','');
		}	

		$SalePrice = 0.00;
		if(isset($product_details->special_price) && !empty($product_details->special_price))
		{
			$SalePrice 	= number_format($product_details->special_price,2,'.','');
		}

		$CostPrice = 0.00;
		if(isset($product_details->cost) && !empty($product_details->cost))
		{
			$CostPrice 	= number_format($product_details->cost,2,'.','');
		}

		$ListPrice = 0.00;
		if(isset($product_details->msrp) && !empty($product_details->msrp)){

			$ListPrice = number_format($product_details->msrp,2,'.','');
		}

		$ProductWeight = 0;
		if(isset($product_details->weight) && !empty($product_details->weight)){
			$ProductWeight = number_format($product_details->weight,2,'.','');
		}

		$Productwidth = '0';
		if(isset($product_details->width) && !empty($product_details->width)){
				$Productwidth = $product_details->width;
		}

		$Productlength = '0';
		if(isset($product_details->length) && !empty($product_details->length)){
				$Productlength = $product_details->length;
		}

		$Productheight = '0';
		if(isset($product_details->height) && !empty($product_details->height)){
				$Productheight = $product_details->height;
		}

		$ProductStatus = false;
		if(isset($product_details->status) && !empty($product_details->status) && $product_details->status == '1'){
			$ProductStatus = true;
		}

		$ProductDescription = '';
		if(isset($product_details->short_description) && !empty($product_details->short_description)){
			$ProductDescription .= '<div class="short-description">'.$product_details->short_description.'</div>';
		}

		if(isset($product_details->description) && !empty($product_details->description)){
			$description_p = str_replace('http://relightdepot.com/', '/', $product_details->description);
			$description_p = str_replace('https://relightdepot.com/', '/', $description_p);
			$description_p = str_replace('https://relightdepot.com/', '/', $description_p);
			$ProductDescription .= '<div class="ProductDescription">'.$description_p.'</div>';
		}

		
		if(isset($product_details->product_custom_links) && !empty($product_details->product_custom_links)){
			
			$product_custom_links = str_replace('<a href="https://relightdepot.com/','<a href="/content/resources/', $product_details->product_custom_links);
			$product_custom_links = str_replace('<a href="http://relightdepot.com/','<a href="/content/resources/', $product_custom_links);
			$product_custom_links = str_replace('<a href="/specs/','<a href="/content/resources/specs/', $product_custom_links);
			
			$resources_html = '<div class="block_ResDown">
			<h2 class="res_down_title">RESOURCES AND DOWNLOADS</h2>
			<p class="resourses">'.$product_custom_links.'</p></div>';
			$ProductDescription .= '<div class="resources_and_downloads">'.$resources_html.'</div>';
		}else if(isset($product_details->resources) && !empty($product_details->resources))
		{
			$product_custom_links = str_replace('<a href="https://relightdepot.com/','<a href="/content/resources/', $product_details->resources);
			$product_custom_links = str_replace('<a href="http://relightdepot.com/','<a href="/content/resources/', $product_custom_links);
			$product_custom_links = str_replace('<a href="/specs/','<a href="/content/resources/specs/', $product_custom_links);
			$resources_html = '<div class="block_ResDown">
			<h2 class="res_down_title">RESOURCES AND DOWNLOADS</h2>
			<p class="resourses">'.$product_custom_links.'</p></div>';
			$ProductDescription .= '<div class="resources_and_downloads">'.$resources_html.'</div>';
		}


		/*$Productwarranty = '<table class="data-table table"><tbody>';
		 $Productwarranty .= '<tr><th>SKU</th>
							<td>'.$ProductCode.'</td></tr>';
		$Productwarranty .= '<tr><th>Weight</th>
							<td>'.$ProductWeight.'</td></tr>'; */

		$Productwarranty = '';
		if($product_details->attribute_set_id == '4')
		{
			$resources = 'No';
			if(isset($product_details->resources) && !empty($product_details->resources))
			{
				$resources_html = str_replace('<a href="https://relightdepot.com/','<a href="/content/resources/', $product_details->resources);
				$resources_html = str_replace('<a href="http://relightdepot.com/','<a href="/content/resources/', $resources_html);
				$resources_html = str_replace('<a href="/specs/','<a href="/content/resources/specs/', $resources_html);
				$resources = $resources_html;
			}
			$Productwarranty = '<p><strong>Resources<br></strong>'.$resources.'</p>';
		}
		
		if($product_details->attribute_set_id == '31')
		{
			
			/* $manufacturer_multiselect = 'No';
			if(isset($product_details->manufacturer_multiselect) && !empty($product_details->manufacturer_multiselect))
			{
				$manufacturer_multiselect = $this->productmodel->getmanufacturer_multiselect($product_details->manufacturer_multiselect);
			}
			$Productwarranty .= '<tr><th>Manufacturer</th>
			<td>'.$manufacturer_multiselect.'</td></tr>';

			$ballast_technology = 'No';
			if(isset($product_details->ballast_technology) && !empty($product_details->ballast_technology))
			{
				$ballast_technology = $this->productmodel->getballast_technology($product_details->ballast_technology);
			}
			$Productwarranty .= '<tr><th>Ballast Technology</th>
			<td>'.$ballast_technology.'</td></tr>';

			$ballast_type = 'No';
			if(isset($product_details->ballast_type) && !empty($product_details->ballast_type))
			{
				$ballast_type = $this->productmodel->getballast_type($product_details->ballast_type);
			}
			$Productwarranty .= '<tr><th>Ballast Type</th>
			<td>'.$ballast_type.'</td></tr>';

			$lamp_count = 'No';
			if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
			{
				$lamp_count = $this->productmodel->getlamp_count($product_details->lamp_count);
			}
			$Productwarranty .= '<tr><th>Lamp Count</th>
			<td>'.$lamp_count.'</td></tr>';

			$lamp_types = 'No';
			if(isset($product_details->lamp_types) && !empty($product_details->lamp_types))
			{
				$lamp_types = $this->productmodel->getlamp_types($product_details->lamp_types);
			}
			$Productwarranty .= '<tr><th>Lamp Type</th>
			<td>'.$lamp_types.'</td></tr>';

			$lamp_wattage = 'No';
			if(isset($product_details->lamp_wattage) && !empty($product_details->lamp_wattage))
			{
				$lamp_wattage = $this->productmodel->getlamp_wattage($product_details->lamp_wattage);
			}
			$Productwarranty .= '<tr><th>Lamp Wattage</th>
			<td>'.$lamp_wattage.'</td></tr>';

			$line_voltage = 'No';
			if(isset($product_details->line_voltage) && !empty($product_details->line_voltage))
			{
				$line_voltage = $this->productmodel->getline_voltage($product_details->line_voltage);
			}
			$Productwarranty .= '<tr><th>Line Voltage</th>
			<td>'.$line_voltage.'</td></tr>';

			$starting_type = 'No';
			if(isset($product_details->starting_type) && !empty($product_details->starting_type))
			{
				$starting_type = $this->productmodel->getstarting_type($product_details->starting_type);
			}
			$Productwarranty .= '<tr><th>Starting Type</th>
			<td>'.$starting_type.'</td></tr>';

			$ballast_factor = 'No';
			if(isset($product_details->ballast_factor) && !empty($product_details->ballast_factor))
			{
				$ballast_factor = $this->productmodel->getballast_factor($product_details->ballast_factor);
			}
			$Productwarranty .= '<tr><th>Ballast Factor</th>
			<td>'.$ballast_factor.'</td></tr>'; */

			$resources = 'No';
			if(isset($product_details->resources) && !empty($product_details->resources))
			{
				$resources_html = str_replace('<a href="https://relightdepot.com/','<a href="/content/resources/', $product_details->resources);
				$resources_html = str_replace('<a href="http://relightdepot.com/','<a href="/content/resources/', $resources_html);
				$resources_html = str_replace('<a href="/specs/','<a href="/content/resources/specs/', $resources_html);
				$resources = $resources_html;
			}
			$Productwarranty = '<p><strong>Resources<br></strong>'.$resources.'</p>';
		}	

		if($product_details->attribute_set_id == '26')
		{
			
			$housing = 'No';
			if(isset($product_details->housing) && !empty($product_details->housing))
			{
				$housing = $product_details->housing;
			}
			$Productwarranty = '<p><strong>Housing<br></strong>'.$housing.'</p>';

			$reflector = 'No';
			if(isset($product_details->reflector) && !empty($product_details->reflector))
			{
				$reflector = $product_details->reflector;
			}
			$Productwarranty .= '<p><strong>Reflector<br></strong>'.$reflector.'</p>';

			/* $lens = 'No';
			if(isset($product_details->lens) && !empty($product_details->lens))
			{
				$lens = $product_details->lens;
			}
			$Productwarranty .= '<tr><th>Lens</th>
			<td>'.$lens.'</td></tr>';

			$socket = 'No';
			if(isset($product_details->socket) && !empty($product_details->socket))
			{
				$socket = $product_details->socket;
			}
			$Productwarranty .= '<tr><th>Socket</th>
			<td>'.$socket.'</td></tr>'; */

			$mounting = 'No';
			if(isset($product_details->mounting) && !empty($product_details->mounting))
			{
				$mounting = $product_details->mounting;
			}
			$Productwarranty .= '<p><strong>Mounting<br></strong>'.$mounting.'</p>';

			$resources = 'No';
			if(isset($product_details->resources) && !empty($product_details->resources))
			{
				$resources_html = str_replace('<a href="https://relightdepot.com/','<a href="/content/resources/', $product_details->resources);
				$resources_html = str_replace('<a href="http://relightdepot.com/','<a href="/content/resources/', $resources_html);
				$resources_html = str_replace('<a href="/specs/','<a href="/content/resources/specs/', $resources_html);
				$resources = $resources_html;
			}
			$Productwarranty .= '<p><strong>Resources<br></strong>'.$resources.'</p>';

		/* 	$lamp = 'No';
			if(isset($product_details->lamp) && !empty($product_details->lamp))
			{
				$lamp = $product_details->lamp;
			}
			$Productwarranty .= '<tr><th>Lamp Details</th>
			<td>'.$lamp.'</td></tr>';

			$ballast = 'No';
			if(isset($product_details->ballast) && !empty($product_details->ballast))
			{
				$ballast = $product_details->ballast;
			}
			$Productwarranty .= '<tr><th>Ballast Details</th>
			<td>'.$ballast.'</td></tr>';

			$wattage = 'No';
			if(isset($product_details->wattage) && !empty($product_details->wattage))
			{
				$wattage = $product_details->wattage;
			}
			$Productwarranty .= '<tr><th>Wattage</th>
			<td>'.$wattage.'</td></tr>';

			$voltage = 'No';
			if(isset($product_details->voltage) && !empty($product_details->voltage))
			{
				$voltage = $product_details->voltage;
			}
			$Productwarranty .= '<tr><th>Voltage</th>
			<td>'.$voltage.'</td></tr>';

			$listings_and_ratings = 'No';
			if(isset($product_details->listings_and_ratings) && !empty($product_details->listings_and_ratings))
			{
				$listings_and_ratings = $product_details->listings_and_ratings;
			}
			$Productwarranty .= '<tr><th>Listings and Ratings</th>
			<td>'.$listings_and_ratings.'</td></tr>'; */

		
			/* $light_source = 'No';
			if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
			{
				$light_source = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
			}
			$Productwarranty .= '<tr><th>Light Source</th>
			<td>'.$light_source.'</td></tr>';

			$lamp_count = 'No';
			if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
			{
				$lamp_count = $this->productmodel->getlamp_count($product_details->lamp_count);
			}
			$Productwarranty .= '<tr><th>Lamp Count</th>
			<td>'.$lamp_count.'</td></tr>';

			$application = 'No';
			if(isset($product_details->application) && !empty($product_details->application))
			{
				$lamp_count = $this->productmodel->getapplication($product_details->application);
			}
			$Productwarranty .= '<tr><th>Application</th>
			<td>'.$application.'</td></tr>';

			$mounting_height = 'No';
			if(isset($product_details->mounting_height) && !empty($product_details->mounting_height))
			{
				$mounting_height = $product_details->mounting_height;
				$mounting_height = $this->productmodel->getmounting_height($product_details->mounting_height);
			}
			$Productwarranty .= '<tr><th>Mounting Height Range</th>
			<td>'.$mounting_height.'</td></tr>';

			$est_operating_cost = 'No';
			if(isset($product_details->est_operating_cost) && !empty($product_details->est_operating_cost))
			{
				$est_operating_cost = $product_details->est_operating_cost;
			}
			$Productwarranty .= '<tr><th>Est. Operating Cost</th>
			<td>'.$est_operating_cost.'</td></tr>'; */
		}	

		/* if($product_details->attribute_set_id == '30')
		{
			$light_source_texas = 'No';
			if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
			{
				$light_source_texas = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
			}
			$Productwarranty .= '<tr><th>Light Source</th>
			<td>'.$light_source_texas.'</td></tr>';

			$total_input_watts = 'No';
			if(isset($product_details->total_input_watts) && !empty($product_details->total_input_watts))
			{
				$total_input_watts = $product_details->total_input_watts;
			}
			$Productwarranty .= '<tr><th>Total Input Watts</th>
			<td>'.$total_input_watts.'</td></tr>';

			$fixture_size = 'No';
			if(isset($product_details->fixture_size) && !empty($product_details->fixture_size))
			{
				$fixture_size = $this->productmodel->getfixture_size($product_details->fixture_size);
			}
			$Productwarranty .= '<tr><th>Fixture Size</th>
			<td>'.$fixture_size.'</td></tr>';

			$delivered_lumens = 'No';
			if(isset($product_details->delivered_lumens) && !empty($product_details->delivered_lumens))
			{
				$delivered_lumens = $product_details->delivered_lumens;
			}
			$Productwarranty .= '<tr><th>Lumen Output</th>
			<td>'.$delivered_lumens.'</td></tr>';

			$calculated_l70 = 'No';
			if(isset($product_details->calculated_l70) && !empty($product_details->calculated_l70))
			{
				$calculated_l70 = $product_details->calculated_l70;
			}
			$Productwarranty .= '<tr><th>L70 Expected Life (hours)</th>
			<td>'.$calculated_l70.'</td></tr>';

			$luminaire_efficacy_rating = 'No';
			if(isset($product_details->luminaire_efficacy_rating) && !empty($product_details->luminaire_efficacy_rating))
			{
				$luminaire_efficacy_rating = $product_details->luminaire_efficacy_rating;
			}
			$Productwarranty .= '<tr><th>Luminaire Efficacy Rating (LER)</th>
			<td>'.$luminaire_efficacy_rating.'</td></tr>';

			$correlated_color_temperature = 'No';
			if(isset($product_details->correlated_color_temperature) && !empty($product_details->correlated_color_temperature))
			{
				$correlated_color_temperature = $product_details->correlated_color_temperature;
			}
			$Productwarranty .= '<tr><th>Color Temperature (CCT)</th>
			<td>'.$correlated_color_temperature.'</td></tr>';

			$color_rendering_index = 'No';
			if(isset($product_details->color_rendering_index) && !empty($product_details->color_rendering_index))
			{
				$color_rendering_index = $product_details->color_rendering_index;
			}
			$Productwarranty .= '<tr><th>Color Rendering Index (CRI)</th>
			<td>'.$color_rendering_index.'</td></tr>';

			$max_ambient_temp = 'No';
			if(isset($product_details->max_ambient_temp) && !empty($product_details->max_ambient_temp))
			{
				$max_ambient_temp = $product_details->max_ambient_temp;
			}
			$Productwarranty .= '<tr><th>Max Ambient Temp</th>
			<td>'.$max_ambient_temp.'</td></tr>';

			$universal_dimmable_driver = 'No';
			if(isset($product_details->universal_dimmable_driver) && !empty($product_details->universal_dimmable_driver))
			{
				$universal_dimmable_driver = $product_details->universal_dimmable_driver;
			}
			$Productwarranty .= '<tr><th>Driver Type</th>
			<td>'.$universal_dimmable_driver.'</td></tr>';
		} */

		if($product_details->attribute_set_id == '34')
		{
			
			/* $ballast_and_voltage = 'No';
			if(isset($product_details->ballast_and_voltage) && !empty($product_details->ballast_and_voltage))
			{
				$ballast_and_voltage = $this->productmodel->getballast_and_voltage($product_details->ballast_and_voltage);
			}
			$Productwarranty .= '<tr><th>Ballast and Voltage</th>
			<td>'.$ballast_and_voltage.'</td></tr>';

			$listings_and_ratings_taxas = 'No';
			if(isset($product_details->listings_and_ratings_taxas) && !empty($product_details->listings_and_ratings_taxas))
			{
				$listings_and_ratings_taxas = $this->productmodel->getlistings_and_ratings_taxas($product_details->listings_and_ratings_taxas);
			}
			$Productwarranty .= '<tr><th>Listings and Ratings</th>
			<td>'.$listings_and_ratings_taxas.'</td></tr>'; */

			$housing = 'No';
			if(isset($product_details->housing) && !empty($product_details->housing))
			{
				$housing = $product_details->housing;
			}
			$Productwarranty = '<p><strong>Housing<br></strong>'.$housing.'</p>';

			/* $light_source_texas = 'No';
			if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
			{
				$light_source_texas = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
			}
			$Productwarranty .= '<tr><th>Light Source</th>
			<td>'.$light_source_texas.'</td></tr>';

			$lamp_count = 'No';
			if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
			{
				$lamp_count = $this->productmodel->getlamp_count($product_details->lamp_count);
			}
			$Productwarranty .= '<tr><th>Lamp Count</th>
			<td>'.$lamp_count.'</td></tr>';
		 */
		}

	/* 	if($product_details->attribute_set_id == '33')
		{
			$rc_housing_type = 'No';
			if(isset($product_details->rc_housing_type) && !empty($product_details->rc_housing_type))
			{
				$rc_housing_type = $this->productmodel->getrc_housing_type($product_details->rc_housing_type);
			}
			$Productwarranty .= '<tr><th>Housing Type</th>
			<td>'.$rc_housing_type.'</td></tr>';

			$rc_insulation_rating = 'No';
			if(isset($product_details->rc_insulation_rating) && !empty($product_details->rc_insulation_rating))
			{
				$rc_insulation_rating = $this->productmodel->getrc_insulation_rating($product_details->rc_insulation_rating);
			}
			$Productwarranty .= '<tr><th>Insulation Rating</th>
			<td>'.$rc_insulation_rating.'</td></tr>';

			$ballast_and_voltage = 'No';
			if(isset($product_details->ballast_and_voltage) && !empty($product_details->ballast_and_voltage))
			{
				$ballast_and_voltage = $this->productmodel->getballast_and_voltage($product_details->ballast_and_voltage);
			}
			$Productwarranty .= '<tr><th>Ballast and Voltage</th>
			<td>'.$ballast_and_voltage.'</td></tr>';

			$lamp_count = 'No';
			if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
			{
				$lamp_count = $this->productmodel->getlamp_count($product_details->lamp_count);
			}
			$Productwarranty .= '<tr><th>Lamp Count</th>
			<td>'.$lamp_count.'</td></tr>';

			$light_source_texas = 'No';
			if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
			{
				$light_source_texas = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
			}
			$Productwarranty .= '<tr><th>Light Source</th>
			<td>'.$light_source_texas.'</td></tr>';

			$rc_lamp_position = 'No';
			if(isset($product_details->rc_lamp_position) && !empty($product_details->rc_lamp_position))
			{
				$rc_lamp_position = $this->productmodel->getrc_lamp_position($product_details->rc_lamp_position);
			}
			$Productwarranty .= '<tr><th>Lamp Position</th>
			<td>'.$rc_lamp_position.'</td></tr>';

			$listings_and_ratings_taxas = 'No';
			if(isset($product_details->listings_and_ratings_taxas) && !empty($product_details->listings_and_ratings_taxas))
			{
				$listings_and_ratings_taxas = $this->productmodel->getlistings_and_ratings_taxas($product_details->listings_and_ratings_taxas);
			}
			$Productwarranty .= '<tr><th>Listings and Ratings</th>
			<td>'.$listings_and_ratings_taxas.'</td></tr>';
			
		} */

		if($product_details->attribute_set_id == '35')
		{
			/* $lamp_cri = 'No';
			if(isset($product_details->lamp_cri) && !empty($product_details->lamp_cri))
			{
				$lamp_cri = $product_details->lamp_cri;
			}
			$Productwarranty .= '<tr><th>Color Rendition Index (CRI)</th>
			<td>'.$lamp_cri.'</td></tr>';

			$lamp_ctemp = 'No';
			if(isset($product_details->lamp_ctemp) && !empty($product_details->lamp_ctemp))
			{
				$lamp_ctemp = $product_details->lamp_ctemp;
			}
			$Productwarranty .= '<tr><th>Color Temp. (deg K)</th>
			<td>'.$lamp_ctemp.'</td></tr>';

			$lamp_init_lumens = 'No';
			if(isset($product_details->lamp_init_lumens) && !empty($product_details->lamp_init_lumens))
			{
				$lamp_init_lumens = $product_details->lamp_init_lumens;
			}
			$Productwarranty .= '<tr><th>Initial Lumens</th>
			<td>'.$lamp_init_lumens.'</td></tr>'; */

			$lamp_wattage = 'No';
			if(isset($product_details->lamp_wattage) && !empty($product_details->lamp_wattage))
			{
				$lamp_wattage = $this->productmodel->getlamp_wattage($product_details->lamp_wattage);
			}
			
			$Productwarranty = '<p><strong>Lamp Wattage<br></strong>'.$lamp_wattage.'</p>';
			
		}

		/* if($product_details->attribute_set_id == '27')
		{
			$lamp_type = 'No';
			if(isset($product_details->lamp_type) && !empty($product_details->lamp_type))
			{
				$lamp_type = $product_details->lamp_type;
			}
			$Productwarranty .= '<tr><th>Lamp Type</th>
			<td>'.$lamp_type.'</td></tr>';

			$lamp_base = 'No';
			if(isset($product_details->lamp_base) && !empty($product_details->lamp_base))
			{
				$lamp_base = $product_details->lamp_base;
			}
			$Productwarranty .= '<tr><th>Base</th>
			<td>'.$lamp_base.'</td></tr>';

			$lamp_bulb = 'No';
			if(isset($product_details->lamp_bulb) && !empty($product_details->lamp_bulb))
			{
				$lamp_bulb = $product_details->lamp_bulb;
			}
			$Productwarranty .= '<tr><th>Bulb</th>
			<td>'.$lamp_bulb.'</td></tr>';

			$lamp_watts = 'No';
			if(isset($product_details->lamp_watts) && !empty($product_details->lamp_watts))
			{
				$lamp_watts = $product_details->lamp_watts;
			}
			$Productwarranty .= '<tr><th>Watts</th>
			<td>'.$lamp_watts.'</td></tr>';

			$lamp_init_lumens = 'No';
			if(isset($product_details->lamp_init_lumens) && !empty($product_details->lamp_init_lumens))
			{
				$lamp_init_lumens = $product_details->lamp_init_lumens;
			}
			$Productwarranty .= '<tr><th>Initial Lumens</th>
			<td>'.$lamp_init_lumens.'</td></tr>';

			$lamp_mean_lumens = 'No';
			if(isset($product_details->lamp_mean_lumens) && !empty($product_details->lamp_mean_lumens))
			{
				$lamp_mean_lumens = $product_details->lamp_mean_lumens;
			}
			$Productwarranty .= '<tr><th>Mean Lumens</th>
			<td>'.$lamp_mean_lumens.'</td></tr>';

			$lamp_ctemp = 'No';
			if(isset($product_details->lamp_ctemp) && !empty($product_details->lamp_ctemp))
			{
				$lamp_ctemp = $product_details->lamp_ctemp;
			}
			$Productwarranty .= '<tr><th>Color Temp. (deg K)</th>
			<td>'.$lamp_ctemp.'</td></tr>';

			$lamp_cri = 'No';
			if(isset($product_details->lamp_cri) && !empty($product_details->lamp_cri))
			{
				$lamp_cri = $product_details->lamp_cri;
			}
			$Productwarranty .= '<tr><th>Color Rendition Index (CRI)</th>
			<td>'.$lamp_cri.'</td></tr>';

			$lamp_coating = 'No';
			if(isset($product_details->lamp_coating) && !empty($product_details->lamp_coating))
			{
				$lamp_coating = $product_details->lamp_coating;
			}
			$Productwarranty .= '<tr><th>Coating</th>
			<td>'.$lamp_coating.'</td></tr>';

			$lamp_life_3hr = 'No';
			if(isset($product_details->lamp_life_3hr) && !empty($product_details->lamp_life_3hr))
			{
				$lamp_life_3hr = $product_details->lamp_life_3hr;
			}
			$Productwarranty .= '<tr><th>Lamp Life (3hrs. per start)</th>
			<td>'.$lamp_life_3hr.'</td></tr>';

			$lamp_life_12hr = 'No';
			if(isset($product_details->lamp_life_12hr) && !empty($product_details->lamp_life_12hr))
			{
				$lamp_life_12hr = $product_details->lamp_life_12hr;
			}
			$Productwarranty .= '<tr><th>Lamp Life (12hrs. per start)</th>
			<td>'.$lamp_life_12hr.'</td></tr>';

			$lamp_mol = 'No';
			if(isset($product_details->lamp_mol) && !empty($product_details->lamp_mol))
			{
				$lamp_mol = $product_details->lamp_mol;
			}
			$Productwarranty .= '<tr><th>MOL (in / mm)</th>
			<td>'.$lamp_mol.'</td></tr>';

			$lamp_mod = 'No';
			if(isset($product_details->lamp_mod) && !empty($product_details->lamp_mod))
			{
				$lamp_mod = $product_details->lamp_mod;
			}
			$Productwarranty .= '<tr><th>MOD (in / mm)</th>
			<td>'.$lamp_mod.'</td></tr>';

			$lamp_coating = 'No';
			if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
			{
				$lamp_coating = $product_details->lamp_count;
			}
			$Productwarranty .= '<tr><th>Lamp Count</th>
			<td>'.$lamp_coating.'</td></tr>';

		} */

		if($product_details->attribute_set_id == '29')
		{
		
			/* $fixture_size = 'No';
			if(isset($product_details->fixture_size) && !empty($product_details->fixture_size))
			{
				$fixture_size = $this->productmodel->getfixture_size($product_details->fixture_size);
			}
			$Productwarranty .= '<tr><th>Fixture Size</th>
			<td>'.$fixture_size.'</td></tr>';

			$ballast_and_voltage = 'No';
			if(isset($product_details->ballast_and_voltage) && !empty($product_details->ballast_and_voltage))
			{
				$ballast_and_voltage = $this->productmodel->getballast_and_voltage($product_details->ballast_and_voltage);
			}
			$Productwarranty .= '<tr><th>Ballast and Voltage</th>
			<td>'.$ballast_and_voltage.'</td></tr>';

			$listings_and_ratings_taxas = 'No';
			if(isset($product_details->listings_and_ratings_taxas) && !empty($product_details->listings_and_ratings_taxas))
			{
				$listings_and_ratings_taxas = $this->productmodel->getlistings_and_ratings_taxas($product_details->listings_and_ratings_taxas);
			}
			$Productwarranty .= '<tr><th>Listings and Ratings</th>
			<td>'.$listings_and_ratings_taxas.'</td></tr>'; */

			$housing = 'No';
			if(isset($product_details->housing) && !empty($product_details->housing))
			{
				$housing = $product_details->housing;
			}
			$Productwarranty = '<p><strong>Housing<br></strong>'.$housing.'</p>';

			/* $light_source_texas = 'No';
			if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
			{
				$light_source_texas = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
			}
			$Productwarranty .= '<tr><th>Light Source</th>
			<td>'.$light_source_texas.'</td></tr>';

			$lamp_count = 'No';
			if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
			{
				$lamp_count = $product_details->lamp_count;
			}
			$Productwarranty .= '<tr><th>Lamp Count</th>
			<td>'.$lamp_count.'</td></tr>';

			$lens = 'No';
			if(isset($product_details->lens) && !empty($product_details->lens))
			{
				$lens = $product_details->lens;
			}
			$Productwarranty .= '<tr><th>Lens</th>
			<td>'.$lens.'</td></tr>'; */

			$mounting = 'No';
			if(isset($product_details->mounting) && !empty($product_details->mounting))
			{
				$mounting = $product_details->mounting;
			}
			$Productwarranty .= '<p><strong>Mounting<br></strong>'.$mounting.'</p>';

			$reflector = 'No';
			if(isset($product_details->reflector) && !empty($product_details->reflector))
			{
				$reflector = $product_details->reflector;
			}
			$Productwarranty .= '<p><strong>Reflector<br></strong>'.$reflector.'</p>';

			$resources = 'No';
			if(isset($product_details->resources) && !empty($product_details->resources))
			{
				$resources_html = str_replace('<a href="https://relightdepot.com/','<a href="/content/resources/', $product_details->resources);
				$resources_html = str_replace('<a href="http://relightdepot.com/','<a href="/content/resources/', $resources_html);
				$resources_html = str_replace('<a href="/specs/','<a href="/content/resources/specs/', $resources_html);
				$resources = $resources_html;
			}
			$Productwarranty .= '<tr><th>Resources</th>
			<td>'.$resources.'</td></tr>';
			$Productwarranty .= '<p><strong>Resources<br></strong>'.$resources.'</p>';

		/* 	$socket = 'No';
			if(isset($product_details->socket) && !empty($product_details->socket))
			{
				$socket = $product_details->socket;
			}
			$Productwarranty .= '<tr><th>Socket</th>
			<td>'.$socket.'</td></tr>';

			$application = 'No';
			if(isset($product_details->application) && !empty($product_details->application))
			{
				$application = $this->productmodel->getapplication($product_details->application);
			}
			$Productwarranty .= '<tr><th>Application</th>
			<td>'.$application.'</td></tr>'; */

			
		}

		$dom_doc = new DomDocument();
		@$dom_doc->loadHTML($ProductDescription);
		
		$img =  $dom_doc->getElementsByTagName('img');
		$ProductDescription = $ProductDescription;
		foreach ($img as $div) {
			$images = $div->getAttribute('src');
			
			if(isset($images) && !empty($images))
			{
				$this->productmodel->updateImageStatus($product_id);
			}
		}
		
		$ProductDescription = $ProductDescription;
		$a =  $dom_doc->getElementsByTagName('a');
		foreach ($a as $div) {
			$href = $div->getAttribute('href');
			if(isset($href) && !empty($href))
			{
				$this->productmodel->updateHrefStatus($product_id);
			}
		}

		$stockdata = $proxy->call($sessionId,'cataloginventory_stock_item.list', $product_id);
		$stockdata = $stockdata[0];
		
		$StockStatus = 0;
		if(isset($stockdata['qty']) && !empty($stockdata['qty'])){
		
			$StockStatus =  (int)$stockdata['qty'];
		}

		if(isset($product_details->url_key) && !empty($product_details->url_key))
		{
			$mg_product_url = '/'.$product_details->url_key.'/';
		}

		$inventory_tracking = 'none';
		if(isset($product->stock_details[0]->manage_stock) && !empty($product->stock_details[0]->manage_stock))
		{
			$inventory_tracking = 'simple';
		}

		
		$BrandID = 0;
		if(isset($product_details->manufacturer) && !empty($product_details->manufacturer))
		{
			$getmanufacture = $this->productmodel->getManufactures($product_details->manufacturer);
			
			$getBrand = Bigcommerce::getBrands(array('name' => $getmanufacture));
		
			if(isset($getBrand) && !empty($getBrand)) 
			{
				$BrandID = $getBrand[0]->id;
			}else if(isset($getmanufacture) && !empty($getmanufacture)){

				$data = array();
				$data['name'] = $getmanufacture;

				$CreateBrand = Bigcommerce::createBrand($data);
				$BrandID = $CreateBrand->id;
			}
		}

		$meta_title = '';
		if(isset($product_details->meta_title) && !empty($product_details->meta_title))
		{
			$meta_title = $product_details->meta_title;
		}
		
		$meta_keyword = '';
		if(isset($product_details->meta_keyword) && !empty($product_details->meta_keyword))
		{
			$meta_keyword = $product_details->meta_keyword;
		}
		
		$meta_description = '';
		if(isset($product_details->meta_description) && !empty($product_details->meta_description))
		{
			$meta_description = $product_details->meta_description;
		}

		// Get Option set id in DB
		$product_detailsp['name'] 						= $ProductName;
		$product_detailsp['sku'] 						= $ProductCode;
		$product_detailsp['type'] 						= 'physical';
		$product_detailsp['price']						= $ProductPrice;
		$product_detailsp['sale_price'] 				= $SalePrice;
		$product_detailsp['cost_price'] 				= $CostPrice;
		$product_detailsp['retail_price'] 				= $ListPrice;
		$product_detailsp['weight']						= $ProductWeight;
		$product_detailsp['width'] 						= $Productwidth; 
		$product_detailsp['height'] 					= $Productheight; 
		$product_detailsp['depth'] 						= $Productlength; 
		$product_detailsp['is_visible'] 				= $ProductStatus;
		$product_detailsp['categories'] 				= $ProductCategory;
		$product_detailsp['description'] 				= $ProductDescription;

		if(isset($Productwarranty) && !empty($Productwarranty))
		{	
			$product_detailsp['warranty'] 					= $Productwarranty;
		}
		
		$product_detailsp['availability'] 				= 'available';
		$product_detailsp['inventory_tracking'] 		= $inventory_tracking;
		$product_detailsp['inventory_level'] 			= $StockStatus;
		$product_detailsp['brand_id'] 					= $BrandID;
		$product_detailsp['page_title'] 				= $meta_title;
		$product_detailsp['meta_keywords'] 				= $meta_keyword;
		$product_detailsp['meta_description'] 			= $meta_description;
		if(isset($mg_product_url) && !empty($mg_product_url))
		{
			$product_detailsp['custom_url'] 			= $mg_product_url;
		}

		$bcproductid = '3531';
		if(isset($product->custom_options) && !empty($product->custom_options))
		{
			$product_options = $product->custom_options;
			
			$o = 0;
			$option_values = array();
			foreach ($product_options as $key => $attributes) {

				$options[$o]['name'] 			= $bcproductid.'_'.trim($key);
				$options[$o]['sort_order'] 		= $o;
				$options[$o]['display_name'] 	= $key;
				$options[$o]['type'] 			= 'dropdown';
			
				$ov = 0;
				$options_value = array();
				foreach ($attributes as $option) {
					
					$add_price = '';
					if(isset($option->price) && !empty($option->price) && $option->price != '0.0000')
					{
						$add_price = $option->price;
					}
					
					/*if(isset($add_price) && !empty($add_price))
					{
						$title = trim($option->title);
						$options_value[$ov]['label'] 		= $title.' +$'.number_format($add_price,2,'.','');;
					}else{*/
						$options_value[$ov]['label'] 		= trim($option->title);
				//	}
					$options_value[$ov]['sort_order'] 	= $option->sort_order;
					$options_value[$ov]['is_default'] 	= false;
				$ov++;
				}

				if(isset($options_value) && !empty($options_value))
				{
					$options[$o]['option_values'] 	= $options_value;

					$option_values[$o] = $options_value;
				}
			$o++;
			}

			$optiondata = $this->get_combinations($option_values);

			$count = count($optiondata);
			
		/* 	if($count < 600)
			{
				if(isset($options) && !empty($options))
				{
					foreach ($options as $options_array) 
					{						
						$encodedToken = base64_encode("".$config_data['client_id'].":".$config_data['apitoken']."");
						$authHeaderString = 'Authorization: Basic ' . $encodedToken;
						$options_data = json_encode($options_array);
						
						$curl = curl_init();
						curl_setopt_array($curl, array(
							CURLOPT_URL => "https://api.bigcommerce.com/stores/".$store_hash."/v3/catalog/products/".$bcproductid."/options",
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_ENCODING => "",
							CURLOPT_MAXREDIRS => 10,
							CURLOPT_TIMEOUT => 30,
							CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST => "POST",
							CURLOPT_POSTFIELDS => $options_data,
							CURLOPT_HTTPHEADER => array($authHeaderString,'Accept: application/json','Content-Type: application/json','X-Auth-Client: '.$config_data['client_id'].'','X-Auth-Token: '.$config_data['apitoken'].''),

						));

						$response = curl_exec($curl);
						$err = curl_error($curl);
						curl_close($curl);

						if($err)
						{
							echo $err;
						} 
						else 
						{
							$create_options = json_decode($response);

							if(isset($create_options->data) && !empty($create_options->data))
							{
								$value_data = array();
								if(isset($create_options->data->option_values) && !empty($create_options->data->option_values))
								{
									$v = 0;
									foreach ($create_options->data->option_values as $option_values_s) {

										$value_data[$v]['option_id'] 		= $create_options->data->id;
										$value_data[$v]['option_name']		= $create_options->data->display_name;
										$value_data[$v]['product_id'] 		= $bcproductid;
										$value_data[$v]['option_value']		= $option_values_s->label;
										$value_data[$v]['option_value_id']	= $option_values_s->id;
										
									$v++;
									}
								}
								if(isset($value_data) && !empty($value_data))
								{
									$this->db->insert_batch('option_values',$value_data);
								}
							}
						}
					}
					echo $bcproductid." - Options Create Successfully..<br>";					
				}
			}else{
				$this->productmodel->updateOptionCount($product_id,$count);
			}	 */		
		}
		
		if(isset($option_values) && !empty($option_values))
		{
			$optiondata = $this->get_combinations($option_values);
		}

		
		if(isset($optiondata) && !empty($optiondata))
		{	
			if(isset($inventory_tracking) && !empty($inventory_tracking) && $inventory_tracking == 'simple')
			{
				$product_inventory_tracking = array();
				$product_inventory_tracking['inventory_tracking'] = 'sku';
				Bigcommerce::updateProduct($bcproductid, $product_inventory_tracking);
			}
			
			$attribute = array();
			foreach ($product->custom_options as $key => $attributes) {

				$attribute[] = $key;
			}

			foreach ($optiondata as $associatedProductsinfo) {

				$Count = count($attribute);
				
				$product_sku = array();
				if($Count == 1)
				{
					$attribute_name1 	= $attribute[0];
					$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
				
					$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
					$optionSKU = $getBCOption1->sku;
					$optionp = $getBCOption1->price;
					
					$optionprice = $ProductPrice + $optionp;
					if(empty($optionp))
					{
						$optionprice = $ProductPrice;
					}
					$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
					$product_sku['inventory_level'] 			 = $StockStatus;
					$product_sku['price'] 						 = number_format($optionprice,2,'.','');
					$product_sku['weight'] 						 = $ProductWeight;
					$product_sku['inventory_warning_level'] 	 = 1;
					$product_sku['width']						 = $Productwidth;
					$product_sku['depth']						 = $Productlength;
					$product_sku['height']						 = $Productheight;
					$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
					$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
					

				}else if ($Count == 2) {
					
					$attribute_name1 	= $attribute[0];
					$attribute_name2 	= $attribute[1];
					$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
					$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
					
					$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
					$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
					
					$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku;
					$optionp = $getBCOption1->price + $getBCOption2->price;
					
					$optionprice = $ProductPrice + $optionp;
					if(empty($optionp))
					{
						$optionprice = $ProductPrice;
					}

					$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
					$product_sku['inventory_level'] 			 = $StockStatus;
					$product_sku['price'] 						 = number_format($optionprice,2,'.','');
					$product_sku['weight'] 						 = $ProductWeight;
					$product_sku['inventory_warning_level'] 	 = 1;
					$product_sku['width']						 = $Productwidth;
					$product_sku['depth']						 = $Productlength;
					$product_sku['height']						 = $Productheight;
					$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
					$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
					$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
					$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
					
				}else if ($Count == 3) {
					
					$attribute_name1 	= $attribute[0];
					$attribute_name2 	= $attribute[1];
					$attribute_name3 	= $attribute[2];
					$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
					$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
					$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
				
					$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
					$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
					$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
					
					$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku;
					$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price;
					
					$optionprice = $ProductPrice + $optionp;
					if(empty($optionp))
					{
						$optionprice = $ProductPrice;
					}

					$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
					$product_sku['inventory_level'] 			 = $StockStatus;
					$product_sku['price'] 						 = number_format($optionprice,2,'.','');
					$product_sku['weight'] 						 = $ProductWeight;
					$product_sku['inventory_warning_level'] 	 = 1;
					$product_sku['width']						 = $Productwidth;
					$product_sku['depth']						 = $Productlength;
					$product_sku['height']						 = $Productheight;
					$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
					$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
					$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
					$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
					$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
					$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
					
				}else if ($Count == 4) {
					
					$attribute_name1 	= $attribute[0];
					$attribute_name2 	= $attribute[1];
					$attribute_name3 	= $attribute[2];
					$attribute_name4 	= $attribute[3];
					$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
					$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
					$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
					$getBCOptionValue3 	= $this->productmodel->getOptionValue3($attribute_name4,$associatedProductsinfo[3]['label'],$bcproductid);

					$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
					$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
					$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
					$getBCOption4 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[3]['label']);
					
					$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku.'-'.$getBCOption4->sku;
					$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price + $getBCOption4->price;
					
					$optionprice = $ProductPrice + $optionp;
					if(empty($optionp))
					{
						$optionprice = $ProductPrice;
					}

					$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
					$product_sku['inventory_level'] 			 = $StockStatus;
					$product_sku['price'] 						 = number_format($optionprice,2,'.','');
					$product_sku['weight'] 						 = $ProductWeight;
					$product_sku['inventory_warning_level'] 	 = 1;
					$product_sku['width']						 = $Productwidth;
					$product_sku['depth']						 = $Productlength;
					$product_sku['height']						 = $Productheight;
					$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
					$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
					$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
					$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
					$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
					$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
					$product_sku['option_values'][3]['id']		 = $getBCOptionValue3['option_value_id'];
					$product_sku['option_values'][3]['option_id']= $getBCOptionValue3['option_id'];

				}else if ($Count == 5) {
					
					$attribute_name1 	= $attribute[0];
					$attribute_name2 	= $attribute[1];
					$attribute_name3 	= $attribute[2];
					$attribute_name4 	= $attribute[3];
					$attribute_name5 	= $attribute[4];
					$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
					$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
					$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
					$getBCOptionValue3 	= $this->productmodel->getOptionValue3($attribute_name4,$associatedProductsinfo[3]['label'],$bcproductid);
					$getBCOptionValue4 	= $this->productmodel->getOptionValue4($attribute_name4,$associatedProductsinfo[4]['label'],$bcproductid);

					$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
					$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
					$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
					$getBCOption4 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[3]['label']);
					$getBCOption5 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[4]['label']);
					
					$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku.'-'.$getBCOption4->sku.'-'.$getBCOption5->sku;
					$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price + $getBCOption4->price  + $getBCOption5->price;
				
					$optionprice = $ProductPrice + $optionp;
					if(empty($optionp))
					{
						$optionprice = $ProductPrice;
					}

					$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
					$product_sku['inventory_level'] 			 = $StockStatus;
					$product_sku['price'] 						 = number_format($optionprice,2,'.','');
					$product_sku['weight'] 						 = $ProductWeight;
					$product_sku['inventory_warning_level'] 	 = 1;
					$product_sku['width']						 = $Productwidth;
					$product_sku['depth']						 = $Productlength;
					$product_sku['height']						 = $Productheight;
					$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
					$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
					$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
					$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
					$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
					$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
					$product_sku['option_values'][3]['id']		 = $getBCOptionValue3['option_value_id'];
					$product_sku['option_values'][3]['option_id']= $getBCOptionValue3['option_id'];
					$product_sku['option_values'][4]['id']		 = $getBCOptionValue4['option_value_id'];
					$product_sku['option_values'][4]['option_id']= $getBCOptionValue4['option_id'];
					
				}else if ($Count == 6) {
					
					$attribute_name1 	= $attribute[0];
					$attribute_name2 	= $attribute[1];
					$attribute_name3 	= $attribute[2];
					$attribute_name4 	= $attribute[3];
					$attribute_name5 	= $attribute[4];
					$attribute_name5 	= $attribute[5];
					$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
					$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
					$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
					$getBCOptionValue3 	= $this->productmodel->getOptionValue3($attribute_name4,$associatedProductsinfo[3]['label'],$bcproductid);
					$getBCOptionValue4 	= $this->productmodel->getOptionValue4($attribute_name4,$associatedProductsinfo[4]['label'],$bcproductid);
					$getBCOptionValue5 	= $this->productmodel->getOptionValue5($attribute_name4,$associatedProductsinfo[5]['label'],$bcproductid);

					$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
					$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
					$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
					$getBCOption4 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[3]['label']);
					$getBCOption5 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[4]['label']);
					$getBCOption6 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[5]['label']);
					
					$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku.'-'.$getBCOption4->sku.'-'.$getBCOption5->sku.'-'.$getBCOption6->sku;
					$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price + $getBCOption4->price  + $getBCOption5->price   + $getBCOption6->price;
					
					$optionprice = $ProductPrice + $optionp;
					if(empty($optionp))
					{
						$optionprice = $ProductPrice;
					}

					$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
					$product_sku['inventory_level'] 			 = $StockStatus;
					$product_sku['price'] 						 = number_format($optionprice,2,'.','');
					$product_sku['weight'] 						 = $ProductWeight;
					$product_sku['inventory_warning_level'] 	 = 1;
					$product_sku['width']						 = $Productwidth;
					$product_sku['depth']						 = $Productlength;
					$product_sku['height']						 = $Productheight;
					$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
					$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
					$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
					$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
					$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
					$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
					$product_sku['option_values'][3]['id']		 = $getBCOptionValue3['option_value_id'];
					$product_sku['option_values'][3]['option_id']= $getBCOptionValue3['option_id'];
					$product_sku['option_values'][4]['id']		 = $getBCOptionValue4['option_value_id'];
					$product_sku['option_values'][4]['option_id']= $getBCOptionValue4['option_id'];
					$product_sku['option_values'][5]['id']		 = $getBCOptionValue5['option_value_id'];
					$product_sku['option_values'][5]['option_id']= $getBCOptionValue5['option_id'];
					
				}
				
				echo '<pre>';
				print_r($product_sku);
				exit;
				/* $encodedToken = base64_encode("".$config_data['client_id'].":".$config_data['apitoken']."");
				$authHeaderString = 'Authorization: Basic ' . $encodedToken;
				$sku_data = json_encode($product_sku);
				
				$curl = curl_init();
				curl_setopt_array($curl, array(
					CURLOPT_URL => "https://api.bigcommerce.com/stores/".$store_hash."/v3/catalog/products/".$bcproductid."/variants",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 30,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $sku_data,
					CURLOPT_HTTPHEADER => array($authHeaderString,'Accept: application/json','Content-Type: application/json','X-Auth-Client: '.$config_data['client_id'].'','X-Auth-Token: '.$config_data['apitoken'].''),

				));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);

				if($err)
				{
					$this->productmodel->updateSKUstatus($product_id);
				} 
				else 
				{

				} */
			}
			echo $bcproductid.' - Options Sku Successfully..';	
		}

		exit;

		try {

			$product_create 	= Bigcommerce::createProduct($product_detailsp);
			
			if(isset($product_create) && empty($product_create)){
				throw new Exception('Bigcommerce\Api\Error');
			}else{
				$bcproductid 		= $product_create->id;
				$product_url 		= $product_create->custom_url;
			
				$this->productmodel->UpdateProductStatusp($bcproductid,$product_url,$product_id);	
				echo $bcproductid." - Product Create Successfully...<br>";
			}

		}catch(Exception $error) {

			$this->productmodel->UpdateProductStatusp('','',$product_id);	
			echo $error->getMessage();
		}

		if(isset($bcproductid) && !empty($bcproductid))
		{	
			// Product Images
			if(isset($product_details->media_gallery->images) && !empty($product_details->media_gallery->images))
			{
				$i = 0;
				foreach ($product_details->media_gallery->images as $images) {
					
					$image_data = array();
					$image_data['image_file'] 	= 'https://relightdepot.com/media/catalog/product'.$images->file;
					$image_data['description'] 	= $images->label;
					$image_data['sort_order'] 	= $images->position;
					
					if($product_details->thumbnail == $images->file)
					{
						$image_data['is_thumbnail'] = true;
					}else{
						$image_data['is_thumbnail'] = false;
					}

					$create_image	= Bigcommerce::createProductImage($bcproductid,$image_data);
					
				$i++;
				}
				echo $bcproductid." - Image Create Successfully...<br>";
			}

			if($product_details->attribute_set_id == '31')
			{
				if(isset($product_details->manufacturer_multiselect) && !empty($product_details->manufacturer_multiselect))
				{
					$data = array();
					$data['name'] = 'Manufacturer';
					$data['text'] = $this->productmodel->getmanufacturer_multiselect($product_details->manufacturer_multiselect);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Manufacturer';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
	
				if(isset($product_details->ballast_technology) && !empty($product_details->ballast_technology))
				{
					$data = array();
					$data['name'] = 'Ballast Technology';
					$data['text'] = $this->productmodel->getballast_technology($product_details->ballast_technology);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Ballast Technology';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
	
				if(isset($product_details->ballast_type) && !empty($product_details->ballast_type))
				{
					$data = array();
					$data['name'] = 'Ballast Type';
					$data['text'] = $this->productmodel->getballast_type($product_details->ballast_type);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Ballast Type';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
	
				if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
				{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = $this->productmodel->getlamp_count($product_details->lamp_count);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_types) && !empty($product_details->lamp_types))
				{
					$data = array();
					$data['name'] = 'Lamp Type';
					$data['text'] = $this->productmodel->getlamp_types($product_details->lamp_types);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Type';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_wattage) && !empty($product_details->lamp_wattage))
				{
					$data = array();
					$data['name'] = 'Lamp Wattage';
					$data['text'] = $this->productmodel->getlamp_wattage($product_details->lamp_wattage);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Wattage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->line_voltage) && !empty($product_details->line_voltage))
				{
					$data = array();
					$data['name'] = 'Line Voltage';
					$data['text'] = $this->productmodel->getline_voltage($product_details->line_voltage);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Line Voltage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->starting_type) && !empty($product_details->starting_type))
				{
					$data = array();
					$data['name'] = 'Starting Type';
					$data['text'] = $this->productmodel->getstarting_type($product_details->starting_type);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Starting Type';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
	
				if(isset($product_details->ballast_factor) && !empty($product_details->ballast_factor))
				{
					$data = array();
					$data['name'] = 'Ballast Factor';
					$data['text'] = $this->productmodel->getballast_factor($product_details->ballast_factor);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Ballast Factor';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}

			if($product_details->attribute_set_id == '30')
			{
				if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
				{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->total_input_watts) && !empty($product_details->total_input_watts))
				{
					$data = array();
					$data['name'] = 'Total Input Watts';
					$data['text'] = $product_details->total_input_watts;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Total Input Watts';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->fixture_size) && !empty($product_details->fixture_size))
				{
					$data = array();
					$data['name'] = 'Fixture Size';
					$data['text'] = $this->productmodel->getfixture_size($product_details->fixture_size);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Fixture Size';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->delivered_lumens) && !empty($product_details->delivered_lumens))
				{
					$data = array();
					$data['name'] = 'Lumen Output';
					$data['text'] = $product_details->delivered_lumens;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lumen Output';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->calculated_l70) && !empty($product_details->calculated_l70))
				{
					$data = array();
					$data['name'] = 'L70 Expected Life (hours)';
					$data['text'] = $product_details->calculated_l70;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'L70 Expected Life (hours)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->luminaire_efficacy_rating) && !empty($product_details->luminaire_efficacy_rating))
				{
					$data = array();
					$data['name'] = 'Luminaire Efficacy Rating (LER)';
					$data['text'] = $product_details->luminaire_efficacy_rating;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Luminaire Efficacy Rating (LER)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->correlated_color_temperature) && !empty($product_details->correlated_color_temperature))
				{
					$data = array();
					$data['name'] = 'Color Temperature (CCT)';
					$data['text'] = $product_details->correlated_color_temperature;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Color Temperature (CCT)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->color_rendering_index) && !empty($product_details->color_rendering_index))
				{
					$data = array();
					$data['name'] = 'Color Rendering Index (CRI)';
					$data['text'] = $product_details->color_rendering_index;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Color Rendering Index (CRI)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->max_ambient_temp) && !empty($product_details->max_ambient_temp))
				{
					$data = array();
					$data['name'] = 'Max Ambient Temp';
					$data['text'] = $product_details->max_ambient_temp;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Max Ambient Temp';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->universal_dimmable_driver) && !empty($product_details->universal_dimmable_driver))
				{
					$data = array();
					$data['name'] = 'Driver Type';
					$data['text'] = $product_details->universal_dimmable_driver;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Driver Type';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}
			
			if($product_details->attribute_set_id == '34')
			{
				if(isset($product_details->ballast_and_voltage) && !empty($product_details->ballast_and_voltage))
				{
					$data = array();
					$data['name'] = 'Ballast and Voltage';
					$data['text'] = $this->productmodel->getballast_and_voltage($product_details->ballast_and_voltage);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name']  = 'Ballast and Voltage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->listings_and_ratings_taxas) && !empty($product_details->listings_and_ratings_taxas))
				{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = $this->productmodel->getlistings_and_ratings_taxas($product_details->listings_and_ratings_taxas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name']  = 'Listings and Ratings';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
				{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name']  = 'Light Source';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
				{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = $this->productmodel->getlamp_count($product_details->lamp_count);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name']  = 'Lamp Count';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}

			if($product_details->attribute_set_id == '33')
			{
				if(isset($product_details->rc_housing_type) && !empty($product_details->rc_housing_type))
				{
					$data = array();
					$data['name'] = 'Housing Type';
					$data['text'] = $this->productmodel->getrc_housing_type($product_details->rc_housing_type);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name']  = 'Housing Type';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->rc_insulation_rating) && !empty($product_details->rc_insulation_rating))
				{
					$data = array();
					$data['name'] = 'Insulation Rating';
					$data['text'] = $this->productmodel->getrc_insulation_rating($product_details->rc_insulation_rating);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Insulation Rating';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->ballast_and_voltage) && !empty($product_details->ballast_and_voltage))
				{
					$data = array();
					$data['name'] = 'Ballast and Voltage';
					$data['text'] = $this->productmodel->getballast_and_voltage($product_details->ballast_and_voltage);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Ballast and Voltage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
				{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = $this->productmodel->getlamp_count($product_details->lamp_count);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
				{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->rc_lamp_position) && !empty($product_details->rc_lamp_position))
				{
					$data = array();
					$data['name'] = 'Lamp Position';
					$data['text'] = $this->productmodel->getrc_lamp_position($product_details->rc_lamp_position);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Lamp Position';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->listings_and_ratings_taxas) && !empty($product_details->listings_and_ratings_taxas))
				{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = $this->productmodel->getlistings_and_ratings_taxas($product_details->listings_and_ratings_taxas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}
			
			if($product_details->attribute_set_id == '26')
			{
				if(isset($product_details->lens) && !empty($product_details->lens))
				{
					$data = array();
					$data['name'] = 'Lens';
					$data['text'] = $product_details->lens;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Lens';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->socket) && !empty($product_details->socket))
				{
					$data = array();
					$data['name'] = 'Socket';
					$data['text'] = $product_details->socket;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Socket';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp) && !empty($product_details->lamp))
				{
					$data = array();
					$data['name'] = 'Lamp Details';
					$data['text'] = $product_details->lamp;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Lamp Details';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->ballast) && !empty($product_details->ballast))
				{
					$data = array();
					$data['name'] = 'Ballast Details';
					$data['text'] = $product_details->ballast;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Ballast Details';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->wattage) && !empty($product_details->wattage))
				{
					$data = array();
					$data['name'] = 'Wattage';
					$data['text'] = $product_details->wattage;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Wattage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->voltage) && !empty($product_details->voltage))
				{
					$data = array();
					$data['name'] = 'Voltage';
					$data['text'] = $product_details->voltage;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Voltage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->listings_and_ratings) && !empty($product_details->listings_and_ratings))
				{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = $product_details->listings_and_ratings;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}

			if($product_details->attribute_set_id == '35')
			{
				if(isset($product_details->lamp_cri) && !empty($product_details->lamp_cri))
				{
					$data = array();
					$data['name'] = 'Color Rendition Index (CRI)';
					$data['text'] = $product_details->lamp_cri;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Color Rendition Index (CRI)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_ctemp) && !empty($product_details->lamp_ctemp))
				{
					$data = array();
					$data['name'] = 'Color Temp. (deg K)';
					$data['text'] = $product_details->lamp_ctemp;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Color Temp. (deg K)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_init_lumens) && !empty($product_details->lamp_init_lumens))
				{
					$data = array();
					$data['name'] = 'Initial Lumens';
					$data['text'] = $product_details->lamp_init_lumens;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Initial Lumens';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_wattage) && !empty($product_details->lamp_wattage))
				{
					$data = array();
					$data['name'] = 'Lamp Wattage';
					$data['text'] = $this->productmodel->getlamp_wattage($product_details->lamp_wattage);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Wattage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

			}

			if($product_details->attribute_set_id == '27')
			{
				if(isset($product_details->lamp_type) && !empty($product_details->lamp_type))
				{		
					$data = array();
					$data['name'] = 'Lamp Type';
					$data['text'] = $this->productmodel->getlamp_type($product_details->lamp_type);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Type';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_base) && !empty($product_details->lamp_base))
				{
					$data = array();
					$data['name'] = 'Base';
					$data['text'] = $product_details->lamp_base;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Base';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_bulb) && !empty($product_details->lamp_bulb))
				{
					$data = array();
					$data['name'] = 'Bulb';
					$data['text'] = $product_details->lamp_bulb;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Bulb';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_watts) && !empty($product_details->lamp_watts))
				{
					$data = array();
					$data['name'] = 'Watts';
					$data['text'] = $product_details->lamp_watts;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Watts';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_init_lumens) && !empty($product_details->lamp_init_lumens))
				{
					$data = array();
					$data['name'] = 'Initial Lumens';
					$data['text'] = $product_details->lamp_init_lumens;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Initial Lumens';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_mean_lumens) && !empty($product_details->lamp_mean_lumens))
				{
					$data = array();
					$data['name'] = 'Mean Lumens';
					$data['text'] = $product_details->lamp_mean_lumens;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Mean Lumens';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_ctemp) && !empty($product_details->lamp_ctemp))
				{
					$data = array();
					$data['name'] = 'Color Temp. (deg K)';
					$data['text'] = $product_details->lamp_ctemp;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Color Temp. (deg K)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_cri) && !empty($product_details->lamp_cri))
				{
					$data = array();
					$data['name'] = 'Color Rendition Index (CRI)';
					$data['text'] = $product_details->lamp_cri;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Color Rendition Index (CRI)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_coating) && !empty($product_details->lamp_coating))
				{
					$data = array();
					$data['name'] = 'Coating';
					$data['text'] = $product_details->lamp_coating;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Coating';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_life_3hr) && !empty($product_details->lamp_life_3hr))
				{
					$data = array();
					$data['name'] = 'Lamp Life (3hrs. per start)';
					$data['text'] = $product_details->lamp_life_3hr;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Life (3hrs. per start)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_life_12hr) && !empty($product_details->lamp_life_12hr))
				{
					$data = array();
					$data['name'] = 'Lamp Life (12hrs. per start)';
					$data['text'] = $product_details->lamp_life_12hr;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Life (12hrs. per start)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_mol) && !empty($product_details->lamp_mol))
				{
					$data = array();
					$data['name'] = 'MOL (in / mm)';
					$data['text'] = $product_details->lamp_mol;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'MOL (in / mm)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->lamp_mod) && !empty($product_details->lamp_mod))
				{
					$data = array();
					$data['name'] = 'MOD (in / mm)';
					$data['text'] = $product_details->lamp_mod;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'MOD (in / mm)';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
				{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = $this->productmodel->getlamp_count($product_details->lamp_count);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}

			if($product_details->attribute_set_id == '29')
			{
				if(isset($product_details->fixture_size) && !empty($product_details->fixture_size))
				{
					$data = array();
					$data['name'] = 'Fixture Size';
					$data['text'] = $this->productmodel->getfixture_size($product_details->fixture_size);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Fixture Size';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
				
				if(isset($product_details->ballast_and_voltage) && !empty($product_details->ballast_and_voltage))
				{
					$data = array();
					$data['name'] = 'Ballast and Voltage';
					$data['text'] = $this->productmodel->getballast_and_voltage($product_details->ballast_and_voltage);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Ballast and Voltage';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->listings_and_ratings_taxas) && !empty($product_details->listings_and_ratings_taxas))
				{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = $this->productmodel->getlistings_and_ratings_taxas($product_details->listings_and_ratings_taxas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Listings and Ratings';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->light_source_texas) && !empty($product_details->light_source_texas))
				{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = $this->productmodel->getlight_source_texas($product_details->light_source_texas);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Light Source';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_count) && !empty($product_details->lamp_count))
				{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = $this->productmodel->getlamp_count($product_details->lamp_count);
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Lamp Count';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lens) && !empty($product_details->lens))
				{
					$data = array();
					$data['name'] = 'Lens';
					$data['text'] = $product_details->lens;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Lens';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->socket) && !empty($product_details->socket))
				{
					$data = array();
					$data['name'] = 'Socket';
					$data['text'] = $product_details->socket;
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data); 
				}else{
					$data = array();
					$data['name'] = 'Socket';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->application) && !empty($product_details->application))
				{
					$data = array();
					$data['name'] = 'Application';
					$data['text'] = $this->productmodel->getapplication($product_details->application);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}else{
					$data = array();
					$data['name'] = 'Application';
					$data['text'] = 'NO';
					$create_image = Bigcommerce::createProductCustomField($bcproductid,$data);
				}
			}

			$related_product = $proxy->call($sessionId, 'catalog_product_link.list', array('type' => 'related', 'product' => $product_id));
	
			if(isset($related_product) && !empty($related_product))
			{
				$data = array();
				$i = 0;
				foreach($related_product as $related_products)
				{
					$data[$i]['main_product_id'] = $product_id;
					$data[$i]['product_id'] 	 = $related_products['product_id'];
					$data[$i]['sku'] 			 = $related_products['sku'];
				$i++;
				}
				if(isset($data) && !empty($data))
				{
					$this->db->insert_batch("related_products",$data);
				}				
			}

			if(isset($product_details->tier_price) && !empty($product_details->tier_price))
			{
				$this->productmodel->updateTireProduct($product_id);
			}

			if(isset($product->custom_options) && !empty($product->custom_options))
			{
				$product_options = $product->custom_options;
				
				$o = 0;
				$option_values = array();
				foreach ($product_options as $key => $attributes) {

					$options[$o]['name'] 			= $bcproductid.'_'.trim($key);
					$options[$o]['sort_order'] 		= $o;
					$options[$o]['display_name'] 	= $key;
					$options[$o]['type'] 			= 'dropdown';
				
					$ov = 0;
					$options_value = array();
					foreach ($attributes as $option) {
						
						$add_price = '';
						if(isset($option->price) && !empty($option->price) && $option->price != '0.0000')
						{
							$add_price = $option->price;
						}
						
						/*if(isset($add_price) && !empty($add_price))
						{
							$title = trim($option->title);
							$options_value[$ov]['label'] 		= $title.' +$'.number_format($add_price,2,'.','');;
						}else{*/
							$options_value[$ov]['label'] 		= trim($option->title);
					//	}
						$options_value[$ov]['sort_order'] 	= $option->sort_order;
						$options_value[$ov]['is_default'] 	= false;
					$ov++;
					}

					if(isset($options_value) && !empty($options_value))
					{
						$options[$o]['option_values'] 	= $options_value;

						$option_values[$o] = $options_value;
					}
				$o++;
				}

				$optiondata = $this->get_combinations($option_values);

				$count = count($optiondata);
				
				if($count < 600)
				{
					if(isset($options) && !empty($options))
					{
						foreach ($options as $options_array) 
						{						
							$encodedToken = base64_encode("".$config_data['client_id'].":".$config_data['apitoken']."");
							$authHeaderString = 'Authorization: Basic ' . $encodedToken;
							$options_data = json_encode($options_array);
							
							$curl = curl_init();
							curl_setopt_array($curl, array(
								CURLOPT_URL => "https://api.bigcommerce.com/stores/".$store_hash."/v3/catalog/products/".$bcproductid."/options",
								CURLOPT_RETURNTRANSFER => true,
								CURLOPT_ENCODING => "",
								CURLOPT_MAXREDIRS => 10,
								CURLOPT_TIMEOUT => 30,
								CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
								CURLOPT_CUSTOMREQUEST => "POST",
								CURLOPT_POSTFIELDS => $options_data,
								CURLOPT_HTTPHEADER => array($authHeaderString,'Accept: application/json','Content-Type: application/json','X-Auth-Client: '.$config_data['client_id'].'','X-Auth-Token: '.$config_data['apitoken'].''),

							));

							$response = curl_exec($curl);
							$err = curl_error($curl);
							curl_close($curl);

							if($err)
							{
								echo $err;
							} 
							else 
							{
								$create_options = json_decode($response);

								if(isset($create_options->data) && !empty($create_options->data))
								{
									$value_data = array();
									if(isset($create_options->data->option_values) && !empty($create_options->data->option_values))
									{
										$v = 0;
										foreach ($create_options->data->option_values as $option_values_s) {

											$value_data[$v]['option_id'] 		= $create_options->data->id;
											$value_data[$v]['option_name']		= $create_options->data->display_name;
											$value_data[$v]['product_id'] 		= $bcproductid;
											$value_data[$v]['option_value']		= $option_values_s->label;
											$value_data[$v]['option_value_id']	= $option_values_s->id;
											
										$v++;
										}
									}
									if(isset($value_data) && !empty($value_data))
									{
										$this->db->insert_batch('option_values',$value_data);
									}
								}
							}
						}
						echo $bcproductid." - Options Create Successfully..<br>";					
					}
				}else{
					$this->productmodel->updateOptionCount($product_id,$count);
				}			
			}

			if(isset($option_values) && !empty($option_values))
			{
				$optiondata = $this->get_combinations($option_values);
			}
			
			if(isset($optiondata) && !empty($optiondata))
			{	
				if(isset($inventory_tracking) && !empty($inventory_tracking) && $inventory_tracking == 'simple')
				{
					$product_inventory_tracking = array();
					$product_inventory_tracking['inventory_tracking'] = 'sku';
					Bigcommerce::updateProduct($bcproductid, $product_inventory_tracking);
				}
				
				$attribute = array();
				foreach ($product->custom_options as $key => $attributes) {

					$attribute[] = $key;
				}

				foreach ($optiondata as $associatedProductsinfo) {

					$Count = count($attribute);
					
					$product_sku = array();
					if($Count == 1)
					{
						$attribute_name1 	= $attribute[0];
						$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
					
						$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
						$optionSKU = $getBCOption1->sku;
						$optionp = $getBCOption1->price;
						
						$optionprice = $ProductPrice + $optionp;
						if(empty($optionp))
						{
							$optionprice = $ProductPrice;
						}
						$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
						$product_sku['inventory_level'] 			 = $StockStatus;
						$product_sku['price'] 						 = number_format($optionprice,2,'.','');
						$product_sku['weight'] 						 = $ProductWeight;
						$product_sku['inventory_warning_level'] 	 = 1;
						$product_sku['width']						 = $Productwidth;
						$product_sku['depth']						 = $Productlength;
						$product_sku['height']						 = $Productheight;
						$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
						$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						

					}else if ($Count == 2) {
						
						$attribute_name1 	= $attribute[0];
						$attribute_name2 	= $attribute[1];
						$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
						$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
						
						$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
						$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
						
						$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku;
						$optionp = $getBCOption1->price + $getBCOption2->price;
						
						$optionprice = $ProductPrice + $optionp;
						if(empty($optionp))
						{
							$optionprice = $ProductPrice;
						}

						$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
						$product_sku['inventory_level'] 			 = $StockStatus;
						$product_sku['price'] 						 = number_format($optionprice,2,'.','');
						$product_sku['weight'] 						 = $ProductWeight;
						$product_sku['inventory_warning_level'] 	 = 1;
						$product_sku['width']						 = $Productwidth;
						$product_sku['depth']						 = $Productlength;
						$product_sku['height']						 = $Productheight;
						$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
						$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
						$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
						
					}else if ($Count == 3) {
						
						$attribute_name1 	= $attribute[0];
						$attribute_name2 	= $attribute[1];
						$attribute_name3 	= $attribute[2];
						$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
						$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
						$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
					
						$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
						$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
						$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
						
						$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku;
						$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price;
						
						$optionprice = $ProductPrice + $optionp;
						if(empty($optionp))
						{
							$optionprice = $ProductPrice;
						}

						$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
						$product_sku['inventory_level'] 			 = $StockStatus;
						$product_sku['price'] 						 = number_format($optionprice,2,'.','');
						$product_sku['weight'] 						 = $ProductWeight;
						$product_sku['inventory_warning_level'] 	 = 1;
						$product_sku['width']						 = $Productwidth;
						$product_sku['depth']						 = $Productlength;
						$product_sku['height']						 = $Productheight;
						$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
						$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
						$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
						$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
						$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
						
					}else if ($Count == 4) {
						
						$attribute_name1 	= $attribute[0];
						$attribute_name2 	= $attribute[1];
						$attribute_name3 	= $attribute[2];
						$attribute_name4 	= $attribute[3];
						$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
						$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
						$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
						$getBCOptionValue3 	= $this->productmodel->getOptionValue3($attribute_name4,$associatedProductsinfo[3]['label'],$bcproductid);

						$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
						$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
						$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
						$getBCOption4 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[3]['label']);
						
						$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku.'-'.$getBCOption4->sku;
						$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price + $getBCOption4->price;
						
						$optionprice = $ProductPrice + $optionp;
						if(empty($optionp))
						{
							$optionprice = $ProductPrice;
						}

						$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
						$product_sku['inventory_level'] 			 = $StockStatus;
						$product_sku['price'] 						 = number_format($optionprice,2,'.','');
						$product_sku['weight'] 						 = $ProductWeight;
						$product_sku['inventory_warning_level'] 	 = 1;
						$product_sku['width']						 = $Productwidth;
						$product_sku['depth']						 = $Productlength;
						$product_sku['height']						 = $Productheight;
						$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
						$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
						$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
						$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
						$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
						$product_sku['option_values'][3]['id']		 = $getBCOptionValue3['option_value_id'];
						$product_sku['option_values'][3]['option_id']= $getBCOptionValue3['option_id'];

					}else if ($Count == 5) {
						
						$attribute_name1 	= $attribute[0];
						$attribute_name2 	= $attribute[1];
						$attribute_name3 	= $attribute[2];
						$attribute_name4 	= $attribute[3];
						$attribute_name5 	= $attribute[4];
						$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
						$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
						$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
						$getBCOptionValue3 	= $this->productmodel->getOptionValue3($attribute_name4,$associatedProductsinfo[3]['label'],$bcproductid);
						$getBCOptionValue4 	= $this->productmodel->getOptionValue4($attribute_name4,$associatedProductsinfo[4]['label'],$bcproductid);

						$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
						$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
						$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
						$getBCOption4 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[3]['label']);
						$getBCOption5 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[4]['label']);
						
						$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku.'-'.$getBCOption4->sku.'-'.$getBCOption5->sku;
						$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price + $getBCOption4->price  + $getBCOption5->price;
					
						$optionprice = $ProductPrice + $optionp;
						if(empty($optionp))
						{
							$optionprice = $ProductPrice;
						}

						$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
						$product_sku['inventory_level'] 			 = $StockStatus;
						$product_sku['price'] 						 = number_format($optionprice,2,'.','');
						$product_sku['weight'] 						 = $ProductWeight;
						$product_sku['inventory_warning_level'] 	 = 1;
						$product_sku['width']						 = $Productwidth;
						$product_sku['depth']						 = $Productlength;
						$product_sku['height']						 = $Productheight;
						$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
						$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
						$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
						$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
						$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
						$product_sku['option_values'][3]['id']		 = $getBCOptionValue3['option_value_id'];
						$product_sku['option_values'][3]['option_id']= $getBCOptionValue3['option_id'];
						$product_sku['option_values'][4]['id']		 = $getBCOptionValue4['option_value_id'];
						$product_sku['option_values'][4]['option_id']= $getBCOptionValue4['option_id'];
						
					}else if ($Count == 6) {
						
						$attribute_name1 	= $attribute[0];
						$attribute_name2 	= $attribute[1];
						$attribute_name3 	= $attribute[2];
						$attribute_name4 	= $attribute[3];
						$attribute_name5 	= $attribute[4];
						$attribute_name5 	= $attribute[5];
						$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo[0]['label'],$bcproductid);
						$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo[1]['label'],$bcproductid);
						$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo[2]['label'],$bcproductid);
						$getBCOptionValue3 	= $this->productmodel->getOptionValue3($attribute_name4,$associatedProductsinfo[3]['label'],$bcproductid);
						$getBCOptionValue4 	= $this->productmodel->getOptionValue4($attribute_name4,$associatedProductsinfo[4]['label'],$bcproductid);
						$getBCOptionValue5 	= $this->productmodel->getOptionValue5($attribute_name4,$associatedProductsinfo[5]['label'],$bcproductid);

						$getBCOption1 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[0]['label']);
						$getBCOption2 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[1]['label']);
						$getBCOption3 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[2]['label']);
						$getBCOption4 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[3]['label']);
						$getBCOption5 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[4]['label']);
						$getBCOption6 = $this->getProductOptionSKUs($product_id,$associatedProductsinfo[5]['label']);
						
						$optionSKU = $getBCOption1->sku.'-'.$getBCOption2->sku.'-'.$getBCOption3->sku.'-'.$getBCOption4->sku.'-'.$getBCOption5->sku.'-'.$getBCOption6->sku;
						$optionp = $getBCOption1->price + $getBCOption2->price + $getBCOption3->price + $getBCOption4->price  + $getBCOption5->price   + $getBCOption6->price;
						
						$optionprice = $ProductPrice + $optionp;
						if(empty($optionp))
						{
							$optionprice = $ProductPrice;
						}

						$product_sku['sku'] 						 = $ProductCode.'-'.$optionSKU;
						$product_sku['inventory_level'] 			 = $StockStatus;
						$product_sku['price'] 						 = number_format($optionprice,2,'.','');
						$product_sku['weight'] 						 = $ProductWeight;
						$product_sku['inventory_warning_level'] 	 = 1;
						$product_sku['width']						 = $Productwidth;
						$product_sku['depth']						 = $Productlength;
						$product_sku['height']						 = $Productheight;
						$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
						$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
						$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
						$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
						$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
						$product_sku['option_values'][3]['id']		 = $getBCOptionValue3['option_value_id'];
						$product_sku['option_values'][3]['option_id']= $getBCOptionValue3['option_id'];
						$product_sku['option_values'][4]['id']		 = $getBCOptionValue4['option_value_id'];
						$product_sku['option_values'][4]['option_id']= $getBCOptionValue4['option_id'];
						$product_sku['option_values'][5]['id']		 = $getBCOptionValue5['option_value_id'];
						$product_sku['option_values'][5]['option_id']= $getBCOptionValue5['option_id'];
						
					}
					
					$encodedToken = base64_encode("".$config_data['client_id'].":".$config_data['apitoken']."");
					$authHeaderString = 'Authorization: Basic ' . $encodedToken;
					$sku_data = json_encode($product_sku);
					
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => "https://api.bigcommerce.com/stores/".$store_hash."/v3/catalog/products/".$bcproductid."/variants",
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_POSTFIELDS => $sku_data,
						CURLOPT_HTTPHEADER => array($authHeaderString,'Accept: application/json','Content-Type: application/json','X-Auth-Client: '.$config_data['client_id'].'','X-Auth-Token: '.$config_data['apitoken'].''),

					));

					$response = curl_exec($curl);
					$err = curl_error($curl);
					curl_close($curl);

					if($err)
					{
						$this->productmodel->updateSKUstatus($product_id);
					} 
					else 
					{

					}
				}
				echo $bcproductid.' - Options Sku Successfully..';	
			}
		}
	}
}
?>