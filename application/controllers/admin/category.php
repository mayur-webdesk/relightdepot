<?php
use Bigcommerce\Api\Client as Bigcommerce;
class Category extends CI_Controller {
 
    public function __construct()
    {
        parent::__construct();

        ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

        $this->load->model('admin/categorymodel');
		$this->load->library('mcurl');
		
		include(APPPATH.'third_party/PHPExcel.php');
		include(APPPATH.'third_party/PHPExcel/Writer/Excel2007.php');
		include(APPPATH.'third_party/bcapi/vendor/autoload.php');
		
    }
	
    public function index()
    {		
		$this->data['error'] = '';
		
		$this->data["page_head"]  = 'Magento to BigCommerce Category Import';
		$this->data["page_title"] = 'Magento to BigCommerce Category Import';
		
		$this->data["category_tree_data"] = $this->categorymodel->getcategory();
		$this->data["total_category"]     = count($this->categorymodel->getcategory());
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']= $this->load->view('admin/common/leftmenu',$this->data,true);
		$this->load->view("admin/categoryimport/list", $this->data);
		$this->load->view("admin/common/footer");
	}

	//Category Import Magento to Database
	public function importMagetotoDB()
	{
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);
		
		$proxy 		  = new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    = $proxy->login('DataMigration', 'admin@321');
		$attributeSets  = $proxy->call($sessionId, 'catalog_category.tree');
		$set = current($attributeSets);
		$categorytree = array();
		$categorytree = $proxy->call($sessionId, 'catalog_category.tree');

		$my_tab = array();
		foreach($categorytree['children'] as $t1)
		{	
			$my_tab[]   = $t1;
			 
			foreach($t1['children'] as $t2)
			{
				$my_tab[] = $t2;
			
				foreach($t2['children'] as $t3)
				{
					$my_tab[] = $t3;
		
					foreach($t3['children'] as $t4){
		
						$my_tab[] = $t4;
			
						foreach($t4['children'] as $t5)
						{
							$my_tab[] = $t5;

							foreach($t5['children'] as $t6)
							{
								$my_tab[] = $t6;

								foreach($t6['children'] as $t7)
								{
									$my_tab[] = $t7;
								}
							}
						}
					}
				}
			}
		}
	
		foreach($my_tab as $cat)
		{
			echo '<pre>';
			$cat_id	= $cat['category_id'];
			$c_id[] = $cat_id;
		}
		sort($c_id);
		$category = array();
		$i = 0;
		foreach($c_id as $cid)
		{
			$result = $proxy->call($sessionId, 'catalog_category.info', $cid);
		
			$category[$i]['category_id'] 					= $result['category_id'];
			$category[$i]['is_active'] 						= $result['is_active'];
			$category[$i]['position'] 						= $result['position'];
			$category[$i]['level'] 							= $result['level'];
			$category[$i]['increment_id'] 					= $result['increment_id'];
			$category[$i]['parent_id'] 						= $result['parent_id'];
			$category[$i]['name'] 							= $this->db->escape_str($result['name']);
			$category[$i]['url_key'] 						= $result['url_key'];
			$category[$i]['description'] 					= $this->db->escape_str($result['description']);
			$category[$i]['image'] 							= $result['image'];
			$category[$i]['meta_title'] 					= $this->db->escape_str($result['meta_title']);
			$category[$i]['meta_keywords'] 					= $this->db->escape_str($result['meta_keywords']);
			$category[$i]['meta_description'] 				= $this->db->escape_str($result['meta_description']);
			$category[$i]['include_in_menu'] 				= $result['include_in_menu'];
			$category[$i]['path'] 							= $result['path'];
			$category[$i]['all_children'] 					= $result['all_children'];
			$category[$i]['path_in_store'] 					= $result['path_in_store'];
			$category[$i]['children'] 						= $result['children'];
			$category[$i]['url_path'] 						= $result['url_path'];
			$category[$i]['children_count'] 				= $result['children_count'];
			$category[$i]['display_mode'] 					= $result['display_mode'];
			$category[$i]['landing_page'] 					= $result['landing_page'];
			$category[$i]['is_anchor'] 						= $result['is_anchor'];
			$category[$i]['available_sort_by'] 				= $result['available_sort_by'];
			$category[$i]['default_sort_by'] 				= $result['default_sort_by'];
			$category[$i]['filter_price_range'] 			= $result['filter_price_range'];
			$category[$i]['custom_use_parent_settings'] 	= $result['custom_use_parent_settings'];
			$category[$i]['custom_apply_to_products'] 		= $result['custom_apply_to_products'];
			$category[$i]['custom_design'] 					= $result['custom_design'];
			$category[$i]['custom_design_from'] 			= $result['custom_design_from'];
			$category[$i]['custom_design_to'] 				= $result['custom_design_to'];
			$category[$i]['page_layout'] 					= $result['page_layout'];
			$category[$i]['custom_layout_update'] 			= $result['custom_layout_update'];
			$category[$i]['bc_category_id'] 				= '';
			$category[$i]['bc_category_url'] 				= '';
			$category[$i]['status'] 						= 'no';
			
		$i++;
		}

		$this->db->insert_batch("categories",$category);
		
		echo "Category Import in database succesfully..";
	}

	// Category import PHP to BC
	public function ImportCategory()
	{
		$categoryID = $this->input->get("code");

		$config_data 	= $this->categorymodel->getGeneralSetting();
		$client_id 	  = $config_data['client_id'];
		$auth_token   = $config_data['apitoken'];
		$store_hash   = $config_data['storehash'];

		// Bc class connection		
		Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		// SSL verify False
		Bigcommerce::verifyPeer(false);
		// Display error exception on
		Bigcommerce::failOnError();

		//$get_category = Bigcommerce::getCategory('11742');
		
		$options = array(
			'trace' => true,
			'connection_timeout' => 120000000000,
			'wsdl_cache' => WSDL_CACHE_NONE,
		);

		
		$proxy 		  	= new SoapClient('https://relightdepot.com/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');
		
		$categoryDetails = $proxy->call($sessionId, 'catalog_category.info', $categoryID);

		$category_array = array();

		$category_array['name']        = '';
		if(isset($categoryDetails['name']) && !empty($categoryDetails['name']))
		{
			$category_array['name']    = str_replace('\\','',$categoryDetails['name']);
		}	

		$category_array['description']  = '';
		if(isset($categoryDetails['description']) && !empty($categoryDetails['description']))	{
			
			if(isset($categoryDetails['image']) && !empty($categoryDetails['image']))	{
			
				$banner_description = '<div class="top_baner_image">';
				$banner_image = '/content/catalog/category/'.$categoryDetails['image'];
				$banner_description .= '<img src="'.$banner_image.'" alt="'.$category_array['name'].'" title="'.$category_array['name'].'">';
				$banner_description .= '</div>';
			}	
			$cat_description = str_replace('src="https://relightdepot.com/media/', 'src="/content/', $categoryDetails['description']);
			$cat_description = str_replace('https://relightdepot.com/', '/', $cat_description);

			$description = @$banner_description;
			$description .= $cat_description;
			
			$category_array['description']    =  '<div class="main_description">'.$description.'</div>';
		}

		if(isset($categoryDetails['parent_id']) && !empty($categoryDetails['parent_id']) && $categoryDetails['parent_id'] != '2') {	
				
			$parentcat = $this->categorymodel->checkparentcategory($categoryDetails['parent_id']);  // Check category parent category exist or not
			
			if(isset($parentcat) && !empty($parentcat)) {
				$category_array['parent_id']  	  = $parentcat;
			}
			else {
				$category_array['parent_id']  	  = '';
			}
		}

		$category_array['page_title']        = '';
		if(isset($categoryDetails['meta_title']) && !empty($categoryDetails['meta_title']))	{

			$category_array['page_title']    = trim($categoryDetails['meta_title']);
		}	

		$category_array['meta_description']        = '';
		if(isset($categoryDetails['meta_description']) && !empty($categoryDetails['meta_description']))	{

			$category_array['meta_description']    = trim($categoryDetails['meta_description']);
		}	

		$category_array['meta_keywords']        = '';
		if(isset($categoryDetails['meta_keywords']) && !empty($categoryDetails['meta_keywords']))	{

			$category_array['meta_keywords']    = trim($categoryDetails['meta_keywords']);
		}	

		if(isset($categoryDetails['thumbnail']) && !empty($categoryDetails['thumbnail']))	{

			$category_array['image_file']    = 'https://relightdepot.com/media/catalog/category/'.$categoryDetails['thumbnail'];
		}		

		if(isset($categoryDetails['url_key']) && !empty($categoryDetails['url_key']))	{

			//$category_array['url']    = '/'.$categoryDetails['url_key'].'/';
		}

		$category_array['is_visible'] = false;
		if(isset($categoryDetails['is_active']) && !empty($categoryDetails['is_active']) && $categoryDetails['is_active'] == '1')	{

			$category_array['is_visible']    = true;
		}	

		if(isset($category_array) && !empty($category_array))
		{
			$importcategory_bc = Bigcommerce::createCategory($category_array);

			$this->categorymodel->updateCategorystatus($importcategory_bc->id, $importcategory_bc->url, $categoryID);
			echo $importcategory_bc->id.' - BC category import successfully...';
		}
	}
}
?>