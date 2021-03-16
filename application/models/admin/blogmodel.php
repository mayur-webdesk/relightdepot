<?php

class Blogmodel extends CI_Model
{
    public function __construct()
    {
        $this->setting_table = 'users';
        $this->bc_blog_table = 'wp_posts';
        $this->wp_term_relationships = 'wp_term_relationships';
        $this->wp_terms = 'wp_terms';
        $this->wp_postmeta = 'wp_postmeta';
        $this->wp_usermeta = 'wp_usermeta';
    }

    public function getBcConfig()
    {
        $query = $this->db->query('SELECT * FROM '.$this->setting_table.'');

        return $query->row_array();
    }

    public function getBlogDetails($blog_id)
    {
        $query_cms_data = $this->db->query('SELECT * FROM '.$this->bc_blog_table." WHERE ID = '".$blog_id."'");
        return $query_cms_data->row_array();
    }

    public function getBlogTags($blog_id)
    {
        $query = $this->db->query('SELECT * FROM '.$this->wp_term_relationships." WHERE object_id = '".$blog_id."'");
        $data = $query->result_array();
       
       if(isset($data) && !empty($data))
        {
            $tags = array();
            foreach($data as $datas)
            {
                $gettagname =  $this->db->query('SELECT * FROM '.$this->wp_terms." WHERE term_id = '".$datas['term_taxonomy_id']."'");
                $datas = $gettagname->row_array();
               
                if(isset($datas['name']) && !empty($datas['name']))
                {
                    $tags[] = $datas['name'];
                }else{
                    $tags[] = '';
                }
            }
            return $tags;
        }
        return array();
    }

    public function getMetaDescription($blogid)
    {
        $query_cms_data = $this->db->query('SELECT * FROM '.$this->wp_postmeta." WHERE post_id = '".$blogid."' and meta_key = '_yoast_wpseo_metadesc'");

        return $query_cms_data->row_array();
    }

    public function getMetakeywords($blogid)
    {
        $query_cms_data = $this->db->query('SELECT * FROM '.$this->wp_postmeta." WHERE post_id = '".$blogid."' and meta_key = '_yoast_wpseo_focuskw'");

        return $query_cms_data->row_array();
    }

    public function getImagePath($blogid)
    {
        $query = $this->db->query('SELECT * FROM '.$this->wp_postmeta." WHERE post_id = '".$blogid."' and meta_key = '_thumbnail_id'");
        $get_id = $query->row_array();
       
        if(isset($get_id) && !empty($get_id))
        {
            
            $query = $this->db->query('SELECT guid FROM '.$this->bc_blog_table." WHERE ID = '".$get_id['meta_value']."'");
            $details = $query->row_array();
            return $details;
        }
    }

    public function getblogdata()
    {
        $query_cms_data = $this->db->query('SELECT * FROM '.$this->bc_blog_table." WHERE status = 'no' and `post_type` = 'post'");

        return $query_cms_data->result_array();
    }

    public function updateblogstatus($blogid,$new_blog_id,$new_blog_url)
    {
        $query_cms_data = $this->db->query('UPDATE '.$this->bc_blog_table." SET bc_blog_id = '".$new_blog_id."', bc_blog_url = '".$new_blog_url."', status = 'yes' WHERE ID = '".$blogid."'");

        return 'yes';
    }
}
