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
		$this->data["page_title"] = 'Magento to shopify Category Import';
		
		$this->data["category_tree_data"] = $this->categorymodel->getcategory();
		$this->data["total_category"]     = count($this->categorymodel->getcategory());
		
		$this->load->view("admin/common/header",$this->data);
		$this->data['left_nav']= $this->load->view('admin/common/leftmenu',$this->data,true);
		$this->load->view("admin/categoryimport/list", $this->data);
		$this->load->view("admin/common/footer");
	}

	// Category import PHP to BC
	public function ImportCategory()
	{
		$categoryID = $this->input->get("code");

		$config_data 	= $this->categorymodel->getGeneralSetting();
		$client_id 	  = $config_data['client_id'];
		$auth_token   = $config_data['apitoken'];
		$store_hash   = $config_data['storehas'];

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

		
		$proxy 		  	= new SoapClient('https://www.hansenwheel.com/store/api/soap/?wsdl=1',$options);
		$sessionId    	= $proxy->login('DataMigration', 'admin@321');
		
		$categoryDetails = $proxy->call($sessionId, 'catalog_category.info', $categoryID);

		$category_array = array();

		$category_array['name']        = '';
		if(isset($categoryDetails['name']) && !empty($categoryDetails['name']))
		{
			$category_array['name']    = trim($categoryDetails['name']);
		}	

		$category_array['description']        = '';
		if(isset($categoryDetails['description']) && !empty($categoryDetails['description']))	{
		
			$description = str_replace('http://www.hansenwheel.com/store/', '/', $categoryDetails['description']);
			$category_array['description']    =  '<div class="main_description">'.$description.'</div>';
		}	


		$category_array['parent_id']  	  	  	  =  0;
		if(isset($categoryDetails['parent_id']) && !empty($categoryDetails['parent_id'])) {	
				
			$parentcat = $this->categorymodel->checkparentcategory($categoryDetails['parent_id']);  // Check category parent category exist or not
			
			if(isset($parentcat) && !empty($parentcat)) {
				$category_array['parent_id']  	  = $parentcat;
			}
			else {
				$category_array['parent_id']  	  = '0';
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

		if(isset($categoryDetails['image']) && !empty($categoryDetails['image']))	{

			$category_array['image_file']    = 'https://www.hansenwheel.com/store/media/catalog/category/'.$categoryDetails['image'];
		}		

		if(isset($categoryDetails['url_key']) && !empty($categoryDetails['url_key']))	{

			$category_array['url']    = '/'.$categoryDetails['url_key'].'/';
		}

		$category_array['is_visible'] = false;
		if(isset($categoryDetails['is_active']) && !empty($categoryDetails['is_active']) && $categoryDetails['is_active'] == '1')	{

			$category_array['is_visible']    = true;
		}	

		$CheckCategory = $this->categorymodel->CheckCategories($categoryID);

		if(isset($CheckCategory['bc_category_id']) && !empty($CheckCategory['bc_category_id']))
		{
			
			$importcategory_bc = Bigcommerce::updateCategory($CheckCategory['bc_category_id'],$category_array);

			$this->categorymodel->updateCategorystatus($importcategory_bc->id, $importcategory_bc->url, $categoryID);
			echo $importcategory_bc->id.' - BC category Update successfully...';
		}else{
			$importcategory_bc = Bigcommerce::createCategory($category_array);

			$this->categorymodel->updateCategorystatus($importcategory_bc->id, $importcategory_bc->url, $categoryID);
			echo $importcategory_bc->id.' - BC category import successfully...';
		}
	}
}
?>