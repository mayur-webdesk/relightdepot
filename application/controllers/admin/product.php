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

	public function getProductsData()
	{
		$client_id 	  = 'e9rwf2tjq14c9xplxr1a16u7z0vmrdx';
		$auth_token   = 'ood2m1g6uxhj853meycwevro1im5viu';
		$store_hash   = 'f5wn3qdqza';

		// Bc class connection		
		Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		// SSL verify False
		Bigcommerce::verifyPeer(false);
		// Display error exception on
		Bigcommerce::failOnError();

		$getProduct = $this->productmodel->getAllProducts();

		foreach ($getProduct as $getProducts) {
			
			try {
					$getBcProduct = Bigcommerce::getProduct($getProducts['ProductID']);
					if(isset($getBcProduct) && empty($getBcProduct)) {
						throw new Exception('Bigcommerce\Api\Error');
					} else {
						$update = $this->productmodel->updateHrefStatus($getProducts['ProductID']);
					}
			} catch(Exception $e) {
				$error = $e->getMessage();
				echo $error.'<br>';
			}
		}


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
		$proxy 		  	= new SoapClient('https://www.hansenwheel.com/store/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');

		$product = $proxy->call($sessionId, 'catalog_product.list');

		$data = array();
		$i = 0;
		foreach ($product  as $products) {

			$product_data = $proxy->call($sessionId, 'catalog_product.info', $products['product_id']);

			$data[$i]['product_id']			= $product_data['product_id'];
			$data[$i]['sku']				= $product_data['sku'];
			$data[$i]['name']				= $product_data['name'];
			$data[$i]['set']				= $product_data['set'];
			$data[$i]['type']				= $product_data['type'];
			$data[$i]['simple_product_map']	= '';
			$data[$i]['image_status']		= 'no';
			$data[$i]['href_status']		= 'no';
			$data[$i]['magento_url']		= $product_data['url_key'];
			$data[$i]['bc_product_id']		= '';
			$data[$i]['bc_product_url']		= '';
			$data[$i]['status']				= 'no';
			$data[$i]['sku_status']			= 'no';
			$data[$i]['tire_price']			= 'no';
			$ProductStatus = 'no';
			if(isset($product_data->status) && !empty($product_data->status) && $product_data->status == '1'){
				$ProductStatus = 'yes';
			}
			$data[$i]['visibility']			= $ProductStatus;

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
		
		$proxy 		  	= new SoapClient('https://www.hansenwheel.com/store/api/soap/?wsdl=1',$options);
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
	
	function importproduct()
	{
		header('Content-Type: text/html; charset=utf-8');
		
		$config_data 	= $this->productmodel->getGeneralSetting();
		$client_id 	  = $config_data['client_id'];
		$auth_token   = $config_data['apitoken'];
		$store_hash   = $config_data['storehas'];

		// Bc class connection		
		Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		// SSL verify False
		Bigcommerce::verifyPeer(false);
		// Display error exception on
		Bigcommerce::failOnError();
	
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
	
		$proxy 		  	= new SoapClient('https://www.hansenwheel.com/store/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');

		$product_id = $this->input->get('code');

		$api_url = 'https://www.hansenwheel.com/store/getPids.php?pid='.$product_id;
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$res_fulment = curl_exec($ch);
		$product = json_decode($res_fulment);
		$product_details = $product->product_info;

		
		/*if(isset($product_details->is_salable) && !empty($product_details->is_salable))
		{
			$ProductStatus = 'no';
			if(isset($product_details->status) && !empty($product_details->status) && $product_details->status == '1'){
				$ProductStatus = 'yes';
			}

			$this->productmodel->updateProductdata($product_id,$ProductStatus);
			echo "update Successfully..";
		}else{
			$this->productmodel->deleteProduct($product_id);

			echo "delete Successfully..";
		}*/

		
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
				$ProductCategory = array(105);
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
				$ProductPrice 	= number_format($product_details->price,2);
			}	

			$SalePrice = 0.00;
			if(isset($product_details->special_price) && !empty($product_details->special_price))
			{
				$SalePrice 	= number_format($product_details->special_price,2);
			}

			$ListPrice = 0.00;
			if(isset($product_details->msrp) && !empty($product_details->msrp)){
				$ListPrice = number_format($product_details->msrp,2);
			}

			$ProductWeight = 0;
			if(isset($product_details->weight) && !empty($product_details->weight)){
				$ProductWeight = $product_details->weight;
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
				$ProductDescription .= '<div class="ShortDescription">'.$product_details->short_description.'</div>';
			}

			if(isset($product_details->description) && !empty($product_details->description)){
				$description_p = str_replace('http://www.hansenwheel.com/store/', '/', $product_details->description);
				$description_p = str_replace('https://www.hansenwheel.com/store/', '/', $description_p);
				$description_p = str_replace('http://www.hansenwheel.com/', '/', $description_p);
				$description_p = str_replace('https://www.hansenwheel.com/', '/', $description_p);
				$ProductDescription .= '<div class="ProductDescription">'.$description_p.'</div>';
			}

			if(isset($product_details->specifications) && !empty($product_details->specifications)){
				$description_sort = str_replace('http://www.hansenwheel.com/store/', '/', $product_details->specifications);
				$description_sort = str_replace('https://www.hansenwheel.com/store/', '/', $description_sort);
				$description_sort = str_replace('http://www.hansenwheel.com/', '/', $description_sort);
				$description_sort = str_replace('https://www.hansenwheel.com/', '/', $description_sort);
				$ProductDescription .= '<div class="tab-items">
					<h3 class="tab-items-title">Specifications</h3>
					<div class="tab-items-content">'.$description_sort.'</div>';
			}

			$Productwarranty = '';
			if(isset($product_details->qa) && !empty($product_details->qa)){
				$Productwarranty .= '<div class="tab-items"><h3 class="tab-items-title">Q & A</h3><div class="tab-items-content">';
				$ProductDescription .= '<h3 class="tab-items-title">Q & A</h3><div class="tab-items-content"><div class="Description_qa">'.$product_details->qa.'</div></div></div>';
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

			// Get Option set id in DB
			$product_detailsp['name'] 						= $ProductName;
			$product_detailsp['sku'] 						= $ProductCode;
			$product_detailsp['type'] 						= 'physical';
			$product_detailsp['price']						= str_replace(',', '', $ProductPrice);
			$product_detailsp['sale_price'] 				= str_replace(',', '', $SalePrice);
			$product_detailsp['retail_price'] 				= str_replace(',', '', $ListPrice);
			$product_detailsp['weight']						= $ProductWeight;
			$product_detailsp['width'] 						= $Productwidth; 
			$product_detailsp['height'] 					= $Productheight; 
			$product_detailsp['depth'] 						= $Productlength; 
			$product_detailsp['is_visible'] 				= $ProductStatus;
			$product_detailsp['categories'] 				= $ProductCategory;
			$product_detailsp['description'] 				= $ProductDescription;
			$product_detailsp['warranty'] 					= $Productwarranty;
			$product_detailsp['availability'] 				= 'available';
			$product_detailsp['inventory_tracking'] 		= 'none';
			$product_detailsp['inventory_level'] 			= $StockStatus;
			/*if(isset($mg_product_url) && !empty($mg_product_url))
			{
				$product_detailsp['custom_url'] 					= $mg_product_url;
			}*/

			
			try {

				$product_create 	= Bigcommerce::createProduct($product_detailsp);
				
				if(isset($product_create) && empty($product_create)){
					throw new Exception('Bigcommerce\Api\Error');
				}else{
					$bcproductid 		= $product_create->id;
					$product_url 		= $product_create->custom_url;
				
					$this->productmodel->UpdateProductStatusp($bcproductid,$product_url,$product_id);	
					echo "Product Create Successfully...<br>";
				}

			}catch(Exception $error) {
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
						$image_data['image_file'] 	= 'https://www.hansenwheel.com/store/media/catalog/product'.$images->file;
						$image_data['description'] 	= $images->label;
						$image_data['sort_order'] 	= $images->position;
						if($i == 0)
						{
							$image_data['is_thumbnail'] = true;
						}else{
							$image_data['is_thumbnail'] = false;
						}

						$create_image	= Bigcommerce::createProductImage($bcproductid,$image_data);
						
					$i++;
					}
					echo "Image Create Successfully...<br>";
				}

				if(isset($product_details->account) && !empty($product_details->account))
				{
					$data = array();
					$data['name'] = 'Account';
					$data['text'] = $this->productmodel->getAccount($product_details->account);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->cogs_account) && !empty($product_details->cogs_account))
				{
					$data = array();
					$data['name'] = 'COGS Account';
					$data['text'] = $this->productmodel->getCogs_account($product_details->cogs_account);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->size) && !empty($product_details->size))
				{
					$data = array();
					$data['name'] = 'Size';
					$data['text'] = $this->productmodel->getSize($product_details->size);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->product_options) && !empty($product_details->product_options))
				{
					$data = array();
					$data['name'] = 'Product Options';
					$data['text'] = $this->productmodel->getProduct_options($product_details->product_options);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->wagon_size) && !empty($product_details->wagon_size))
				{
					$data = array();
					$data['name'] = 'Fits Wagon Box Width';
					$data['text'] = $this->productmodel->getWagon_size($product_details->wagon_size);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->spoke_size) && !empty($product_details->spoke_size))
				{
					$data = array();
					$data['name'] = 'Spoke Size';
					$data['text'] = $this->productmodel->getSpoke_size($product_details->spoke_size);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->tire_width) && !empty($product_details->tire_width))
				{
					$data = array();
					$data['name'] = 'Tire Width';
					$data['text'] = $this->productmodel->getTire_width($product_details->tire_width);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->finish) && !empty($product_details->finish))
				{
					$data = array();
					$data['name'] = 'Finish';
					$data['text'] = $this->productmodel->getFinish($product_details->finish);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->specify_mount) && !empty($product_details->specify_mount))
				{
					$data = array();
					$data['name'] = 'Specify Mount';
					$data['text'] = $this->productmodel->getSpecifymount($product_details->specify_mount);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->lamp_style) && !empty($product_details->lamp_style))
				{
					$data = array();
					$data['name'] = 'Style';
					$data['text'] = $this->productmodel->getStyle($product_details->lamp_style);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
				}

				if(isset($product_details->axle_size) && !empty($product_details->axle_size))
				{
					$data = array();
					$data['name'] = 'Axle Size';
					$data['text'] = $this->productmodel->getAxle_size($product_details->axle_size);
					$create_image	= Bigcommerce::createProductCustomField($bcproductid,$data);
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

				if(isset($product->associatedProducts) && !empty($product->associatedProducts))
				{
					$product_options = $product->associatedProducts;
					
					$o = 0;
					foreach ($product_options as $attributes) {
						
						$options[$o]['name'] 			= $bcproductid.'_'.trim($attributes->label);
						$options[$o]['sort_order'] 		= $attributes->position;
						$options[$o]['display_name'] 	= $attributes->label;
						$options[$o]['type'] 			= 'dropdown';
					
						$ov = 0;
						$options_value = array();
						foreach ($attributes->values as $option) {
							
							$options_value[$ov]['label'] 		= trim($option->label);
							$options_value[$ov]['sort_order'] 	= $ov;
							$options_value[$ov]['is_default'] 	= false;
						$ov++;
						}

						if(isset($options_value) && !empty($options_value))
						{
							$options[$o]['option_values'] 	= $options_value;
						}
					$o++;
					}

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
				}

				if(isset($product->associatedProductsinfo) && !empty($product->associatedProductsinfo))
				{	
					$product_inventory_tracking = array();
					$product_inventory_tracking['inventory_tracking'] = 'sku';
					Bigcommerce::updateProduct($bcproductid, $product_inventory_tracking);

					$attribute = array();
					foreach ($product->associatedProducts as $options) {

						$attribute[] = $options->attribute_code;

					}
					foreach ($product->associatedProductsinfo as $associatedProductsinfo) {

						$simple_product = $this->productmodel->UpdateSimpleProduct($associatedProductsinfo->entity_id,$product_id);

						$api_url = 'https://www.hansenwheel.com/store/getPids.php?pid='.$associatedProductsinfo->entity_id;
						$ch = curl_init($api_url);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						$res_fulment = curl_exec($ch);
						$product = json_decode($res_fulment);
						$product_data = $product->product_info;

						$optionstockdata = $proxy->call($sessionId,'cataloginventory_stock_item.list', $associatedProductsinfo->entity_id);
						$optionstockdata = $optionstockdata[0];
					
						$optionstock = 0;
						if(isset($optionstockdata['qty']) && !empty($optionstockdata['qty'])){
						
							$optionstock =  (int)$optionstockdata['qty'];
						}
						$optionSalePrice = 0;
						$optionProductPrice = '0.00';
						if(isset($product_data->special_price) && !empty($product_data->special_price))
						{
							$optionSalePrice = number_format($product_data->special_price,2,'.','');
							$optionProductPrice 	= number_format($product_data->price,2,'.','');
						}else if(isset($product_data->price) && !empty($product_data->price))
						{
							$optionProductPrice 	= number_format($product_data->price,2,'.','');
						}
						
						$optionListPrice = 0;
						if(isset($product_data->msrp) && !empty($product_data->msrp))
						{
							$optionListPrice = number_format($product_data->msrp,2,'.','');
						}

						$OptionProductdepth = 0;
						if(isset($product_data->ship_length) && !empty($product_data->ship_length))
						{
							$OptionProductdepth = $product_data->ship_length;
						}

						$OptionProductwidth = 0;
						if(isset($product_data->ship_width) && !empty($product_data->ship_width))
						{
							$OptionProductwidth = $product_data->ship_width;
						}

						$OptionProductheight = 0;
						if(isset($product_data->ship_height) && !empty($product_data->ship_height))
						{
							$OptionProductheight = $product_data->ship_height;
						}
						
						$Count = count($attribute);
						$product_sku = array();
						if($Count == 1)
						{
							$attribute_name1 = $attribute[0];
							$getBCOptionValue = $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo->$attribute_name1,$bcproductid);

							$product_sku['sku'] 						= $product_data->sku;
							$product_sku['inventory_level'] 			= $optionstock;
							$product_sku['price'] 						= $optionProductPrice;
							$product_sku['sale_price'] 					= $optionSalePrice;
							$product_sku['retail_price'] 				= $optionListPrice;
							$product_sku['weight'] 						= $product_data->weight;
							$product_sku['inventory_warning_level'] 	= 1;
							$product_sku['width']						= $OptionProductwidth;
							$product_sku['depth']						= $OptionProductdepth;
							$product_sku['height']						= $OptionProductheight;
							if(isset($product_data->image) && !empty($product_data->image) &&  $product_data->image != 'no_selection')
							{
								$product_sku['image_url']					= 'https://www.hansenwheel.com/store/media/catalog/product'.$product_data->image;
							}	
							$product_sku['option_values'][0]['id']		= $getBCOptionValue['option_value_id'];
							$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
						}else if ($Count == 2) {
							
							$attribute_name1 	= $attribute[0];
							$attribute_name2 	= $attribute[1];
							$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo->$attribute_name1,$bcproductid);
							$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo->$attribute_name2,$bcproductid);

							$product_sku['sku'] 						 = $product_data->sku;
							$product_sku['inventory_level'] 			 = $optionstock;
							$product_sku['price'] 						 = $optionProductPrice;
							$product_sku['sale_price'] 					 = $optionSalePrice;
							$product_sku['retail_price'] 				 = $optionListPrice;
							$product_sku['weight'] 						 = $product_data->weight;
							$product_sku['inventory_warning_level'] 	 = 1;
							$product_sku['width']						 = $OptionProductwidth;
							$product_sku['depth']						 = $OptionProductdepth;
							$product_sku['height']						 = $OptionProductheight;
							if(isset($product_data->image) && !empty($product_data->image) &&  $product_data->image != 'no_selection')
							{
								$product_sku['image_url']					= 'https://www.hansenwheel.com/store/media/catalog/product'.$product_data->image;
							}	
							$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
							$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
							$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
							$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];

						}else if ($Count == 3) {
							
							$attribute_name1 	= $attribute[0];
							$attribute_name2 	= $attribute[1];
							$attribute_name3 	= $attribute[2];
							$getBCOptionValue 	= $this->productmodel->getOptionValue($attribute_name1,$associatedProductsinfo->$attribute_name1,$bcproductid);
							$getBCOptionValue1 	= $this->productmodel->getOptionValue1($attribute_name2,$associatedProductsinfo->$attribute_name2,$bcproductid);
							$getBCOptionValue2 	= $this->productmodel->getOptionValue2($attribute_name3,$associatedProductsinfo->$attribute_name3,$bcproductid);

							$product_sku['sku'] 						 = $product_data->sku;
							$product_sku['inventory_level'] 			 = $optionstock;
							$product_sku['price'] 						 = $optionProductPrice;
							$product_sku['sale_price'] 					 = $optionSalePrice;
							$product_sku['retail_price'] 				 = $optionListPrice;
							$product_sku['weight'] 						 = $product_data->weight;
							$product_sku['inventory_warning_level'] 	 = 1;
							$product_sku['width']						 = $OptionProductwidth;
							$product_sku['depth']						 = $OptionProductdepth;
							$product_sku['height']						 = $OptionProductheight;
							if(isset($product_data->image) && !empty($product_data->image) &&  $product_data->image != 'no_selection')
							{
								$product_sku['image_url']					= 'https://www.hansenwheel.com/store/media/catalog/product'.$product_data->image;
							}	
							$product_sku['option_values'][0]['id']		 = $getBCOptionValue['option_value_id'];
							$product_sku['option_values'][0]['option_id']= $getBCOptionValue['option_id'];
							$product_sku['option_values'][1]['id']		 = $getBCOptionValue1['option_value_id'];
							$product_sku['option_values'][1]['option_id']= $getBCOptionValue1['option_id'];
							$product_sku['option_values'][2]['id']		 = $getBCOptionValue2['option_value_id'];
							$product_sku['option_values'][2]['option_id']= $getBCOptionValue2['option_id'];
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