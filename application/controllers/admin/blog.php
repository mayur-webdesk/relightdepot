<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
//we need to call PHP's session object to access it through CI
use Bigcommerce\Api\Client as Bigcommerce;

class Blog extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        ini_set('display_errors', 'on');
        //error_reporting(E_ALL);
        $this->load->model('admin/blogmodel');

        $this->load->library('bigcommerceapi');
        $this->load->library('mcurl');

        include APPPATH.'third_party/PHPExcel.php';
        include APPPATH.'third_party/PHPExcel/Writer/Excel2007.php';
        include APPPATH.'third_party/bcapi/vendor/autoload.php';
    }

    public function index()
    {
        $this->data['error'] = '';
        $this->data['page_head'] = 'Bigcommerce to Bigcommerce Blog Page';
        $this->data['page_title'] = 'Bigcommerce to Bigcommerce Blog Page';

        $blog_data = $this->blogmodel->getblogdata();
        $this->data['total_blogs'] = count($blog_data);
        $this->data['blogs_data'] = $blog_data;

        $this->load->view('admin/common/header', $this->data);
        $this->data['left_nav'] = $this->load->view('admin/common/leftmenu', $this->data, true);
        $this->load->view('admin/blog_view', $this->data);
        $this->load->view('admin/common/footer');
    }

    public function empty_table()
    {
        $this->blogmodel->empty_blog_table();
        redirect('/admin/blog', 'refresh');
    }

    public function Importblogs()
    {
        $blog_id = $this->input->get('code');

        $config_data = $this->blogmodel->getBcConfig();
        $client_id 	  = $config_data['client_id'];
		$auth_token   = $config_data['apitoken'];
		$store_hash   = $config_data['storehash'];

		// Bc class connection		
		Bigcommerce::configure(array( 'client_id' => $client_id, 'auth_token' => $auth_token, 'store_hash' => $store_hash ));
		// SSL verify False
		Bigcommerce::verifyPeer(false);
		// Display error exception on
		Bigcommerce::failOnError();

        $getblogs = $this->blogmodel->getBlogDetails($blog_id);

        $blogs_array = array();

        $blogs_array['title'] = '';
        if (isset($getblogs['post_title']) && !empty($getblogs['post_title'])) {
            $blogs_array['title'] = $getblogs['post_title'];
        }

        $blogs_array['body'] = '';
        if (isset($getblogs['post_content']) && !empty($getblogs['post_content'])) {
            $blog_body = str_replace('https://relightdepot.com/','https://relightdepot.com/', $getblogs['post_content']);
            $blog_body = str_replace('src="https://relightdepot.com/wp/wp-content/uploads/', 'src="/product_images/uploads/', $blog_body);
            $blog_body = str_replace('src="https://relightdepot.com/news/wp-content/uploads/', 'src="/product_images/uploads/', $blog_body);
            $blogs_array['body'] = $blog_body;
        }

        $getTags = $this->blogmodel->getBlogTags($blog_id);

        $blogs_array['tags'] = array('');
        if (isset($getTags) && !empty($getTags)) {
            $blogs_array['tags'] = $getTags;
        }

        $blogs_array['is_published'] = false;
        if (isset($getblogs['post_status']) && !empty($getblogs['post_status']) && $getblogs['post_status'] == 'publish') {
            $blogs_array['is_published'] = true;
        }

        $blogs_array['published_date'] = '';
        if (isset($getblogs['post_date']) && !empty($getblogs['post_date'])) {
            $blogs_array['published_date'] = date('D, d M Y H:i:s O', strtotime($getblogs['post_date']));
        }

        $getmetaDescription = $this->blogmodel->getMetaDescription($blog_id);
      
        $blogs_array['meta_description'] = '';
        if (isset($getmetaDescription['meta_value']) && !empty($getmetaDescription['meta_value'])) {
            $blogs_array['meta_description'] = $getmetaDescription['meta_value'];
        }

        $getmetakeywords = $this->blogmodel->getMetakeywords($blog_id);
        $blogs_array['meta_keywords'] = '';
        if (isset($getmetakeywords['meta_value']) && !empty($getmetakeywords['meta_value'])) {
            $blogs_array['meta_keywords'] = $getmetakeywords['meta_value'];
        }

        $blogs_array['author'] = 'Ray De Varona';

        $getthumbnail_path = $this->blogmodel->getImagePath($blog_id);
      
        $blogs_array['thumbnail_path'] = '';
        if (isset($getthumbnail_path['guid']) && !empty($getthumbnail_path['guid'])) {
            $blog_thumbnail_path = str_replace('https://relightdepot.com/wp/wp-content/uploads/', '/product_images/uploads/', $getthumbnail_path['guid']);
            $blog_thumbnail_path = str_replace('https://relightdepot.com/news/wp-content/uploads/', '/product_images/uploads/', $blog_thumbnail_path);
            //$blogs_array['thumbnail_path'] = $getthumbnail_path['guid'];
            $blogs_array['thumbnail_path'] = $blog_thumbnail_path;
        }
      
        $createBlog = Bigcommerce::createBlogs($blogs_array);

        if(isset($createBlog) && !empty($createBlog))
        {
            $this->blogmodel->updateblogstatus($blog_id,$createBlog->id,$createBlog->url);
        }
        echo 'order import successfully...';

    }

}
