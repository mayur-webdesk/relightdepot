<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
//we need to call PHP's session object to access it through CI
use Bigcommerce\Api\Client as Bigcommerce;

class Cms extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        ini_set('display_errors', 'on');
        //error_reporting(E_ALL);
        $this->load->model('admin/cmsmodel');

        $this->load->library('bigcommerceapi');
        $this->load->library('mcurl');

        include APPPATH.'third_party/PHPExcel.php';
        include APPPATH.'third_party/PHPExcel/Writer/Excel2007.php';
        include APPPATH.'third_party/bcapi/vendor/autoload.php';
    }

    public function index()
    {
        $this->data['error'] = '';
        $this->data['page_head'] = 'Bigcommerce Backup CMS Page';
        $this->data['page_title'] = 'Bigcommerce Backup CMS Page';

        $cms_data = $this->cmsmodel->getCmsdata();
        $this->data['total_cmspage'] = count($cms_data);
        $this->data['cmspage_data'] = $cms_data;

        $this->load->view('admin/common/header', $this->data);
        $this->data['left_nav'] = $this->load->view('admin/common/leftmenu', $this->data, true);
        $this->load->view('admin/cmspage_view', $this->data);
        $this->load->view('admin/common/footer');
    }

    public function empty_table()
    {
        $this->cmsmodel->EmptycmsTable();
        redirect('/admin/cms', 'refresh');
    }

    public function importcmsdb()
    {
        $config_data = $this->cmsmodel->getBcConfig();
		$bcstoreurl		= $config_data['storeurl'];
		$apiusername	= $config_data['apiusername'];
		$auth_token		= $config_data['apitoken'];
		$apipath		= $config_data['apipath'];
		$store_hash  	= $config_data['storehas'];
		$client_id  	= $config_data['client_id'];

		//   $store = new Bigcommerceapi($config_data['storehas'], $config_data['apitoken'], $config_data['client_secret']);
	
		// Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		
		 Bigcommerce::configure(array('store_url' => $bcstoreurl,'username'  => $apiusername,'api_key'   => $auth_token));
		
		Bigcommerce::verifyPeer(false);
		Bigcommerce::failOnError();
		
 		$api_limit = 250;
		$total_pages = Bigcommerce::getPage('72');
		
		// getCategoriesCount();
		// $total_pages = Bigcommerce::getPagesCount(); 
		
		echo "<pre>";
		print_r($total_pages);
		exit;
		
       
        // $total_pages = $store->get('/pages');
        $count = count($total_pages);
        $total_pages = ceil($count / $api_limit);
	
		echo "<pre>";
		print_r($total_pages);
		exit;
		
        if (isset($total_pages) && !empty($total_pages) && count($total_pages) > 0) {
            for ($i = 1; $i <= $total_pages; ++$i) {
                $vars = array(
                     'page' => $i,
                     'limit' => $api_limit,
                 );
                $api_url = $config_data['apipath'].'pages';
                $options = array(
                     CURLOPT_HTTPHEADER => array('Content-type: application/json', 'Accept: application/json', 'Content-Length: 0'),
                     CURLOPT_SSL_VERIFYPEER => false,
                     CURLOPT_USERPWD => ''.$config_data['apiusername'].':'.$config_data['apitoken'].'',
                     CURLOPT_SSL_VERIFYHOST => false,
                     CURLOPT_RETURNTRANSFER => true,
                 );
                $this->mcurl->add_call('call_'.$i, 'get', $api_url, $vars, $options);
                $data = $this->mcurl->execute();
                $multi_page_data = json_decode($data['call_'.$i]['response'], true);
                $this->cmsmodel->InsertBcCmspage($multi_page_data, $i, $api_limit);

                $import_category_details = $this->importcmsdetails($i);
            }
        }
        redirect('/admin/cms', 'refresh');
        exit;
    }

    public function importcmsdetails($pagenumber)
    {
        $get_data = $this->cmsmodel->GetBcpCmsData($pagenumber);
        $bc_cms_data = unserialize($get_data['data_details']);
        $res_p = $this->cmsmodel->InsertCmsMulti($bc_cms_data, $pagenumber);
        echo 			   $pagenumber.'- Category Import successfully...';
    }
}
