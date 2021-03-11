<?php // if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Magentoapi {
    
    public $admin_token;
    public $api_url;
    public $api_username;
    public $apiurl_default;
    public $api_password;
    private $CI;

    public function __construct(){
        //$this->CI =& get_instance();
        //$this->CI->load->database();
    }

    public function GetLooginCredientail(){
        //$query = $this->CI->db->query("SELECT * FROM `wds_app_setting` WHERE id = '1'");
        //$config_data = $query->row_array();

        $config_data=array(
                            "magento_api_url"=>'https://www.billskhakis.com/rest/all/V1/',
                            "magento_apiurl_default"=>'https://www.billskhakis.com/rest/all/V1/',
                            "magento_api_user"=>'DataMigration',
                            "magento_api_pass"=>'123456'
                            );
        $this->api_url = $config_data['magento_api_url'];
        $this->apiurl_default = $config_data['magento_apiurl_default'];
        $this->api_username = $config_data['magento_api_user'];
        $this->api_password = $config_data['magento_api_pass'];
    }
    

    public function GenerateAdminToken(){
        if(empty($this->api_url)){
            $this->GetLooginCredientail();
        }
        //echo $this->api_url."integration/admin/token";
        
          $userData = array("username" => $this->api_username, "password" => $this->api_password);
        $ch =   curl_init($this->api_url."integration/admin/token");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));
                $rest_token = curl_exec($ch);

         $rest_token = json_decode($rest_token);    
         if(isset($rest_token->message)){

         }else{
            $this->admin_token=(string)$rest_token;
         }
    }
    
    
    public function getOrderInfo($order_id){

        if(empty($this->admin_token))
                $this->GenerateAdminToken();

        if(empty($this->api_url)){
            $this->GetLooginCredientail();
        }
        $cho = curl_init($this->api_url."orders/".$order_id);
        curl_setopt($cho, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($cho, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cho, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
        $result = curl_exec($cho);
        $order_result = json_decode($result);
       // return $this->filter_message($customer_result);
        return $order_result;
    }

    public function getUserInfo($customer_id){

        if(empty($this->admin_token))
                $this->GenerateAdminToken();

        if(empty($this->api_url)){
            $this->GetLooginCredientail();
        }
        $cho = curl_init($this->api_url."customers/".$customer_id);
        curl_setopt($cho, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($cho, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cho, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
        $result = curl_exec($cho);
        $customer_result = json_decode($result);
       // return $this->filter_message($customer_result);
        return $customer_result;
    }
    
     public function getCategory($id){

        if(empty($this->admin_token))
                $this->GenerateAdminToken();

        if(empty($this->api_url)){
            $this->GetLooginCredientail();
        }
        $cho = curl_init($this->api_url."categories/".$id);
        curl_setopt($cho, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($cho, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cho, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
        $result = curl_exec($cho);
        $customer_result = json_decode($result);
        //return $this->filter_message($customer_result);
        return $customer_result;
    }

    public function getAllCategory(){

        if(empty($this->admin_token))
                $this->GenerateAdminToken();

        if(empty($this->api_url)){
            $this->GetLooginCredientail();
        }
        $cho = curl_init($this->api_url."categories");
        curl_setopt($cho, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($cho, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cho, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
        $result = curl_exec($cho);
        $customer_result = json_decode($result);
        //return $this->filter_message($customer_result);
        return $customer_result;
    }
    
    public function filter_message($red){
        echo "<pre>";
        print_r($red);
        if($red===null OR $red===''){
            $red = new stdClass();
            $red->message='We are having some technical issue. please try after some time';
        }else if(is_object($red)){
            if(isset($red->message)){
              $message=$red->message;   
                if(isset($red->parameters)&& !empty($red->parameters)){
                  foreach ($red->parameters as $key => $value) {
                    if(is_int($key))
                        $key=$key+1;
                    $message=str_replace('%'.$key, ucfirst($value), $message);
                  }
                }
              $red->message=$message;
            }
        }
        return $red;
    }
    public function catalogCategoryInfo($id){
        if(empty($this->admin_token))
                $this->GenerateAdminToken();
       
        if(empty($this->api_url))
                $this->GetLooginCredientail();

          $url=$this->api_url."customers/".$id;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); // Get method
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
            $res = curl_exec($ch);
            $res = json_decode($res);
            return $this->filter_message($res);       
    }
       
    public function updateCustomer($data,$org_data){
        if(empty($this->admin_token))
                $this->GenerateAdminToken();

        if(empty($this->api_url))
                $this->GetLooginCredientail();
    
           $url=$this->api_url."customers/".$org_data['magento_id'];
           $userData = [
                         'customer' => [
                            'id' => $org_data['magento_id'],
                            "email" =>$data['email'],
                            "firstname" => $data['firstname'],
                            "lastname" => $data['lastname'],
                            "group_id" => '1',
                            "website_id" => 1,
                          ]
                    ];
           $customer_info = $this->getCustomer($org_data['magento_id']);     
           if($customer_info->message){
               return $this->filter_message($customer_info);
           }

           if(count($customer_info->addresses)>0){
              //' Checking default address';
              $address=(array) $customer_info->addresses;
              foreach ($address as $key => $value) {
                $value=(array)$value;
                if($value['default_billing'])
                    $value['telephone']=$data['telephone'];  

                 $userData['customer']['addresses'][]=$value;
              }
            }   

           
           
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
            $res = curl_exec($ch);
            $res = json_decode($res);
            return $this->filter_message($res);
    }

    public function deleteCustomer($customer_id){
        if(empty($this->admin_token))
                $this->GenerateAdminToken();
         if(empty($this->api_url))
                $this->GetLooginCredientail();
    
           $url=$this->api_url."customers/".$customer_id;
           $userData = [
                         'customer' => [
                                            'id' => $customer_id
                                       ]
                       ];
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
            $res = curl_exec($ch);
            $res = json_decode($res);
            return $this->filter_message($res);
    }
    

    public function insertCustomer($query_string){
         if(empty($this->api_url))
                $this->GetLooginCredientail();
    
         /*   $userData = array("customer"=>
                                array(
                                    "email" =>$query_string['email'],
                                    "firstname" => $query_string['firstname'],
                                    "lastname" => $query_string['lastname'],
                                    "group_id" => '1',
                                    "website_id" => 1,
                                ),"password" => $query_string['password']
                            );*/
            
           
            $ch = curl_init($this->api_url.'customers');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query_string));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $res = curl_exec($ch);
            $res = json_decode($res);
            //return $this->filter_message($res);
        
            return $res;
    }

     public function insertOrder($query_string){
         if(empty($this->api_url))
                $this->GetLooginCredientail();
    
         /*   $userData = array("customer"=>
                                array(
                                    "email" =>$query_string['email'],
                                    "firstname" => $query_string['firstname'],
                                    "lastname" => $query_string['lastname'],
                                    "group_id" => '1',
                                    "website_id" => 1,
                                ),"password" => $query_string['password']
                            );*/
            
           
            $ch = curl_init($this->api_url.'orders');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query_string));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            $res = curl_exec($ch);
            $res = json_decode($res);
            //return $this->filter_message($res);
        
            return $res;
    }
    
    public function getProductBySku($sku){

        if(empty($this->admin_token))
                $this->GenerateAdminToken();
         if(empty($this->api_url))
                $this->GetLooginCredientail();
        
        $sku=utf8_decode(urlencode($sku));
        $url=$this->api_url.'products/'.$sku.'/';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); // Get method
        //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
        $res = curl_exec($ch);
        $res = json_decode($res);
        $product_info=$this->filter_message($res);
        $response='';
        if(isset($product_info->id)){
            $response=$product_info;
        }else{
            $response=$product_info;
        }
        return $response;
    }
    
    public function addProduct($query_string){
        if(empty($this->admin_token))
            $this->GenerateAdminToken();
         if(empty($this->api_url))
                $this->GetLooginCredientail();
    
        $product_data=$this->getProductBySku($query_string['sku']);
        if($product_data->message=="Requested product doesn't exist"){
            $userData = array("product"=>$query_string);
            $ch = curl_init($this->api_url.'products/');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
            $res = curl_exec($ch);
            $res = json_decode($res);
            return $this->filter_message($res);
        }else{
            if($product_data->message)
                $res['message']=$product_data->message;
            else
                $res['message']='product is exists';
            $res=json_decode(json_encode($res));
            return $res;
        }
    }


     public function CreateProduct($query_string){
        if(empty($this->admin_token))
            $this->GenerateAdminToken();
         if(empty($this->api_url))
                $this->GetLooginCredientail();

            $productData = array("product"=>$query_string);
            $ch = curl_init($this->api_url.'products/');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
            $res = curl_exec($ch);
            $res = json_decode($res);
            return $res;
    }


     public function CreateGroupProduct($query_string){
        if(empty($this->admin_token))
            $this->GenerateAdminToken();
         if(empty($this->api_url))
                $this->GetLooginCredientail();
    
        //$product_data=$this->getProductBySku($query_string['sku']);
     
        //if(!empty($product_data->message) && $product_data->message=="The product that was requested doesn't exist. Verify the product and try again."){
            $productData = array("product"=>$query_string);
            $ch = curl_init($this->api_url.'products/');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Get method
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($productData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $this->admin_token));
            $res = curl_exec($ch);
            $res = json_decode($res);
            return $res;
       // }else{
           
            //$res = 'Product is exitst';
            //return $res;
       // }
    }



}
