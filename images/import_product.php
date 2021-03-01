<?php 
include('config.php');
ini_set('display_errors','On');
error_reporting(E_ALL);
class Product
{
	
	public function ImportProduct($productid,$child_data)
	{
		$product_gorup = array();
		$product_ids = '';
		$product_gorup = unserialize(base64_decode($child_data));
		$product_ids = implode('","',$product_gorup);
		$product_details = mysql_query('SELECT * FROM shopify_prouct_vetrix_tabel WHERE Item IN("'.$product_ids.'") GROUP BY Item_Description_2');
		$postdata = array();
		
		$i = 0;
		$product_category = '';
		$color = '';
		$imgurl = '';
		$image_color_a = array();
					
		while($product = mysql_fetch_object($product_details))
		{
			if(isset($product->Item_Description_2) && !empty($product->Item_Description_2))
			{
				$imageurl 			 = 'http://app.realcheapfloors.com/images/product_image/'.$product->Large_Image;
				if (@getimagesize($imageurl)) 
				{
					if($i == 0)
					{
						$product_description = '';
						$product_txable      = false;
						/*if(isset($product->Item_Description_1) && !empty($product->Item_Description_1)){
							$product_description .= "<div class='item_description_one'>".$product->Item_Description_1."</div>";
						}
						if(isset($product->Item_Description_2) && !empty($product->Item_Description_2)){
							$product_description .= "<div class='item_description_two'>".$product->Item_Description_2."</div>";
						}
						if(isset($product->French_Item_Description_1) && !empty($product->French_Item_Description_1)){
							$product_description .= "<div class='french_item_one'>".$product->French_Item_Description_1."</div>";
						}
						if(isset($product->French_Item_Description_2) && !empty($product->French_Item_Description_2)){
							$product_description .= "<div class='french_item_two'>".$product->French_Item_Description_2."</div>";
						}
						if(isset($product->Extended_Description_Plain_Text) && !empty($product->Extended_Description_Plain_Text)){
							$product_description .= "<div class='short_descritpion'>".$product->Extended_Description_Plain_Text."</div>";
						}*/
						
						/*if(isset($product->Extended_Description_HTML2) && !empty($product->Extended_Description_HTML2)){
							$product_description .= "<div class='short_descritpion_two'>".$product->Extended_Description_HTML2."</div>";
						}*/
						if(isset($product->Extended_Description_HTML) && !empty($product->Extended_Description_HTML)){
							$product_description .= "<div class='description'>".$product->Extended_Description_HTML."</div>";
						}
						
						/* Product tag customize */
						$product_tags = array();$arr = array();$vendor_name_remove = array();
						$product_tags = explode(" ",$product->Keywords);
						if(isset($product->Subcategory) && !empty($product->Subcategory)){
							$sub_cat = array();
							$sub_cat[] = $product->Subcategory;
							$product_tags = array_merge($product_tags,$sub_cat);
							$product_tags = array_map('strtolower', $product_tags);
						}
						$vendor_name_remove = explode(" ",$product->Manufacturer);
						if(isset($vendor_name_remove) && !empty($vendor_name_remove)){
							$vendor_name_remove = array_map('strtolower', $vendor_name_remove);
							$arr =  array_diff($product_tags,$vendor_name_remove);
						}
						
						$product_tag_f = '';
						if(isset($arr) && !empty($arr))
						{
							$product_tag_f = implode(',',$arr);
						}
						/* Product tag customize */
						
						$postdata['product']['title'] 	 						    = $product->Item_Description_1; 
						$postdata['product']['body_html']							= $product_description;
						$postdata['product']['vendor']	 	 						= @$product->Manufacturer; 
						$postdata['product']['product_type'] 					    = @$product->Catalog; 
						$postdata['product']['handle']	 	 						= @$product->Item; 
						$postdata['product']['published']	 	 					= "TRUE"; 
						$postdata['product']['published_scope'] 					= 'global'; 
						$postdata['product']['tags']	 	 						= $product_tag_f; 
						$postdata['product']['metafields_global_description_tag']	= @$product->Keywords; 
						
						$product_category = @$product->Category;
					}
					
					$option_1 = null;$option_2 = null;$option_3 = null;
					if(isset($product->Item_Description_2) && !empty($product->Item_Description_2)){
						$replace_item_description = str_replace(' / ','/',$product->Item_Description_2);
						$product_options = explode(" ",$replace_item_description);
						if(isset($product_options[0]) && !empty($product_options[0]))
						{
							$option_1 = $product_options[0];
						}if(isset($product_options[1]) && !empty($product_options[1]))
						{
							$option_2 = $product_options[1];
							
						}if(isset($product_options[2]) && !empty($product_options[2]))
						{
							$option_3 = $product_options[2];
							
						}
					}
					
					if(isset($option_1) && !empty($option_1) && isset($option_2) && !empty($option_2) && isset($option_3) && !empty($option_3)){
						$postdata['product']['options'][0]['name'] 	    			= 'Type'; 
						$postdata['product']['options'][0]['position'] 				= 1;
						$postdata['product']['options'][1]['name'] 	    			= 'Size'; 
						$postdata['product']['options'][1]['position'] 				= 2;
						$postdata['product']['options'][2]['name'] 	    			= 'Color'; 
						$postdata['product']['options'][2]['position'] 				= 3;
					}
					
					$postdata['product']['variants'][$i]['barcode'] 				= null; 
					$postdata['product']['variants'][$i]['grams'] 	  				= 0;
					$postdata['product']['variants'][$i]['fulfillment_service'] 	= 'manual'; 
					$postdata['product']['variants'][$i]['inventory_management'] 	= 'shopify';  
					$postdata['product']['variants'][$i]['inventory_policy'] 		= 'deny'; 
					$postdata['product']['variants'][$i]['option1'] 				= $option_1; 
					$postdata['product']['variants'][$i]['option2'] 				= $option_2; 
					$postdata['product']['variants'][$i]['option3'] 				= $option_3; 
					$postdata['product']['variants'][$i]['position'] 	  			= 0;
                                      
					$postdata['product']['variants'][$i]['price']  					= $product->Retail_Price;
					$postdata['product']['variants'][$i]['compare_at_price']  		= ''; 
					$postdata['product']['variants'][$i]['requires_shipping'] 		= true; 
					$postdata['product']['variants'][$i]['sku'] 	  				= $product->Item;
					$postdata['product']['variants'][$i]['taxable'] 	 		 	= $product_txable; 
					$postdata['product']['variants'][$i]['title'] 	  				= $product->Item_Description_1;
					$postdata['product']['variants'][$i]['inventory_quantity'] 		= $product->On_Hand;
					//$postdata['product']['variants'][$i]['image_id'] 				= $product->getproduct($product->id); 
					$postdata['product']['variants'][$i]['weight'] 					= 0; 
					$postdata['product']['variants'][$i]['weight_unit'] 			= 'g';
	
					$update = mysql_query('update shopify_prouct_vetrix_tabel SET image_status = "yes" WHERE Item = "'.$product->Item.'"');
					$image_color_a[$option_3] = $imageurl; 
					$i++;
				}
			}
		}
		
		$im = 0;
		if (isset($image_color_a) && !empty($image_color_a))
		{
			foreach($image_color_a as $image_color_a_s)
			{
				$postdata['product']['images'][$im]['position'] = $im; 
				$postdata['product']['images'][$im]['src'] 	   = $image_color_a_s;
				$im++;
			}
		}
		
		if(isset($postdata) && !empty($postdata)){
			
			$api_url = STORE_URL.'/admin/products.json';
			$ch4 = curl_init($api_url);
			$data_string_default = json_encode($postdata);  
			curl_setopt($ch4, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
			curl_setopt($ch4, CURLOPT_CUSTOMREQUEST, "POST");        
			curl_setopt($ch4, CURLOPT_HEADER, false);
			curl_setopt($ch4, CURLOPT_USERPWD, SHOPIFY_U_P ); 
			curl_setopt($ch4, CURLOPT_POSTFIELDS, $data_string_default);
			curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true); 
			$response_default = curl_exec($ch4);
			$response_defaults = json_decode($response_default);
			
			// Assign product collection 
			if(isset($response_defaults->product->id) && !empty($response_defaults->product->id)){
				$product_collection_a = $this->AssingnCollectionProducts($response_defaults->product->id,$product_category);
				$update = mysql_query('update shopify_prouct_vetrix_tabel SET status = "yes" WHERE Item IN("'.$product_ids.'")');
				echo $response_defaults->product->id.' Product import successfully...';
			
				$i = 0;
				$variants_ids_a = array();
				if(isset($response_defaults->product->variants) && !empty($response_defaults->product->variants))
				{
					foreach($response_defaults->product->variants as $variants_a)
					{
						$variants_ids_a[$variants_a->option3][] = $variants_a->id;
					}
					
					if(isset($variants_ids_a) && !empty($variants_ids_a))
					{
						$vi = 0;
						foreach($variants_ids_a as $variants_ids_a_s)
						{
							$postdata_default['image']['id'] = 	@$response_defaults->product->images[$vi]->id;
							if(isset($variants_ids_a_s) && !empty($variants_ids_a_s))
							{
								$vii = 0; 
								foreach($variants_ids_a_s as $variants_ids_a_s_s)
								{
									$postdata_default['image']['variant_ids'][$vii] = $variants_ids_a_s_s;
									$vii++;
								}
							}
							
							if(isset($postdata_default) && !empty($postdata_default))
							{
								$api_url_img = STORE_URL.'/admin/products/'.$response_defaults->product->id.'/images/'.@$response_defaults->product->images[$vi]->id.'.json';
								$ch4 = curl_init($api_url_img);
								$data_string_default22 = json_encode($postdata_default);
								curl_setopt($ch4, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
								curl_setopt($ch4, CURLOPT_CUSTOMREQUEST, "PUT");        
								curl_setopt($ch4, CURLOPT_HEADER, false);
								curl_setopt($ch4, CURLOPT_USERPWD, SHOPIFY_U_P ); 
								curl_setopt($ch4, CURLOPT_POSTFIELDS, $data_string_default22);
								curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true); 
								$response_default = curl_exec($ch4);
							}
							$vi++;
						}
					}
				}
			}else{
				echo json_encode($response_defaults);
				echo "<br>";
				echo 'product import error...';
			}
		}
		else
		{
			$postdata = array();
			$product_details = '';
			$product_description = '';
			$product_txable      = false;
			$product_tags = array();
			$arr = array();
			$vendor_name_remove = array();
			
			// Get product details
			$product_details = mysql_query('SELECT * FROM shopify_prouct_vetrix_tabel WHERE Item IN("'.$product_ids.'") GROUP BY Item_Description_2');
			$without_option_product_details = mysql_fetch_object($product_details);
			
			if(isset($without_option_product_details->Extended_Description_HTML) && !empty($without_option_product_details->Extended_Description_HTML)){
				$product_description .= "<div class='description'>".$without_option_product_details->Extended_Description_HTML."</div>";
			}
			
			/* Product tag customize */
			$product_tags = explode(" ",$without_option_product_details->Keywords);
			if(isset($without_option_product_details->Subcategory) && !empty($without_option_product_details->Subcategory)){
				$sub_cat = array();
				$sub_cat[] = $without_option_product_details->Subcategory;
				$product_tags = array_merge($product_tags,$sub_cat);
				$product_tags = array_map('strtolower', $product_tags);
			}
			$vendor_name_remove = explode(" ",$without_option_product_details->Manufacturer);
			if(isset($vendor_name_remove) && !empty($vendor_name_remove)){
				$vendor_name_remove = array_map('strtolower', $vendor_name_remove);
				$arr =  array_diff($product_tags,$vendor_name_remove);
			}
			$product_tag_f = '';
			if(isset($arr) && !empty($arr)){
				$product_tag_f = implode(',',$arr);
			}
			/* Product tag customize */
			
			$imageurl 			 = '';
			if (isset($without_option_product_details->Large_Image) && !empty($without_option_product_details->Large_Image))
			{
				$imageurl 			 = 'http://app.realcheapfloors.com/images/product_image/'.$without_option_product_details->Large_Image;
				if (@getimagesize($imageurl)) 
				{
					$postdata['product']['title'] 	 						    	= $without_option_product_details->Item_Description_1; 
					$postdata['product']['body_html']								= $product_description;
					$postdata['product']['vendor']	 	 							= @$without_option_product_details->Manufacturer; 
					$postdata['product']['product_type'] 					   	 	= @$without_option_product_details->Catalog; 
					$postdata['product']['handle']	 	 							= @$without_option_product_details->Item; 
					$postdata['product']['images'][$im]['position'] 				= 1; 
					$postdata['product']['images'][$im]['src'] 	   					= $imageurl;
					$postdata['product']['published']	 	 						= "TRUE"; 
					$postdata['product']['published_scope'] 						= 'global'; 
					$postdata['product']['tags']	 	 							= $product_tag_f; 
					$postdata['product']['metafields_global_description_tag']		= @$without_option_product_details->Keywords; 
					$postdata['product']['variants'][0]['barcode'] 					= null; 
					$postdata['product']['variants'][0]['grams'] 	  				= 0;
					$postdata['product']['variants'][0]['fulfillment_service'] 		= 'manual'; 
					$postdata['product']['variants'][0]['inventory_management'] 	= 'shopify';  
					$postdata['product']['variants'][0]['inventory_policy'] 		= 'deny'; 
					$postdata['product']['variants'][0]['option1'] 					= null; 
					$postdata['product']['variants'][0]['option2'] 					= null;
					$postdata['product']['variants'][0]['option3'] 					= null; 
					$postdata['product']['variants'][0]['position'] 	  			= 0;
					$postdata['product']['variants'][0]['price']  					= $without_option_product_details->Retail_Price;
					$postdata['product']['variants'][0]['compare_at_price']  		= ''; 
					$postdata['product']['variants'][0]['requires_shipping'] 		= true; 
					$postdata['product']['variants'][0]['sku'] 	  				    = $without_option_product_details->Item;
					$postdata['product']['variants'][0]['taxable'] 	 		 	    = $product_txable; 
					$postdata['product']['variants'][0]['title'] 	  				= $without_option_product_details->Item_Description_1;
					$postdata['product']['variants'][0]['inventory_quantity'] 		= $without_option_product_details->On_Hand;
					$postdata['product']['variants'][0]['weight'] 					= 0; 
					$postdata['product']['variants'][0]['weight_unit'] 			    = 'g';
					
					$api_url = STORE_URL.'/admin/products.json';
					$ch4 = curl_init($api_url);
					$data_string_default = json_encode($postdata);  
					curl_setopt($ch4, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
					curl_setopt($ch4, CURLOPT_CUSTOMREQUEST, "POST");        
					curl_setopt($ch4, CURLOPT_HEADER, false);
					curl_setopt($ch4, CURLOPT_USERPWD, SHOPIFY_U_P ); 
					curl_setopt($ch4, CURLOPT_POSTFIELDS, $data_string_default);
					curl_setopt($ch4, CURLOPT_RETURNTRANSFER, true); 
					$response_default = curl_exec($ch4);
					$response_defaults_without_option = json_decode($response_default);
					
					$product_category = @$without_option_product_details->Category;
					
					// Assign product collection 
					if(isset($response_defaults_without_option->product->id) && !empty($response_defaults_without_option->product->id)){
						$product_collection_a = $this->AssingnCollectionProducts($response_defaults_without_option->product->id,$product_category);
						$update = mysql_query('update shopify_prouct_vetrix_tabel SET status = "yes" WHERE Item IN("'.$product_ids.'")');
						echo $response_defaults_without_option->product->id.' Product import successfully without option...';
					}
					
				}else{
					echo $product_ids.' - product image not found...';
				}
			}else{
				echo $product_ids.' - product image empty in file...';
			}
		}
	}
	


	function CheckCollectionInShopify($product_collection)
	{
		$api_url = STORE_URL.'/admin/custom_collections.json?title='.str_replace(' ','%20',$product_collection);
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET'); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
		curl_setopt($ch, CURLOPT_USERPWD, SHOPIFY_U_P ); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );   
		$res_fulment = curl_exec($ch);
		$result_s_collection = json_decode($res_fulment); 
		$collection_id = '';
		if(isset($result_s_collection->custom_collections[0]->id) && !empty($result_s_collection->custom_collections[0]->id)){
			$collection_id = $result_s_collection->custom_collections[0]->id;
		}else{
			$data_colusion_add = array();
			$data_colusion_add['custom_collection']['title'] 	  = $product_collection;
			$data_colusion_add['custom_collection']['published'] = True;
			$data_colusion_add = json_encode($data_colusion_add);
			
			$api_url_i = STORE_URL.'/admin/custom_collections.json';
			$ch5 = curl_init($api_url_i);
			curl_setopt($ch5, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));	
			curl_setopt($ch5, CURLOPT_CUSTOMREQUEST, "POST");   
			curl_setopt($ch5, CURLOPT_SSL_VERIFYPEER, 0 ); 
			curl_setopt($ch5, CURLOPT_USERPWD, SHOPIFY_U_P ); 
			curl_setopt($ch5, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt($ch5, CURLOPT_RETURNTRANSFER, 1 );   			
			curl_setopt($ch5, CURLOPT_POSTFIELDS, $data_colusion_add);
			$res_inset_c = curl_exec($ch5);
			
			$result_n_collection = json_decode($res_inset_c); 
			$collection_id 		 = @$result_n_collection->custom_collections->id;
		}
		return $collection_id;
	}
	
	function AssingnCollectionProducts($proid,$product_collection)
	{
		$pro_collection_id = $this->CheckCollectionInShopify($product_collection);
		$product_collection = $pro_collection_id;
		$proid				= $proid;
		
		$array_pro 			= array();
		$array_pro['custom_collection']['id'] 						    = $product_collection;
		$array_pro['custom_collection']['collects'][0]['product_id']    = $proid;
			
		$api_url = STORE_URL.'/admin/custom_collections/'.$product_collection.'.json';
		$ch = curl_init($api_url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));		
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'PUT'); 
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 ); 
		curl_setopt($ch, CURLOPT_USERPWD, SHOPIFY_U_P ); 
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );   
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array_pro));
		$res_fulment = curl_exec($ch);
		$result = json_decode($res_fulment); 
		return $result;
	}

	
}
?>