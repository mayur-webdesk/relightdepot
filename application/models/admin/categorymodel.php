<?php
class Categorymodel extends CI_Model 
{
    public function __construct()
    {
       	$this->category_tree_table = "categories";
		$this->setting_table = "users";
    }
	
	function getGeneralSetting()
	{
		$query = $this->db->query("select * from ".$this->setting_table."");
		$setting_data  = $query->row_array();
		return $setting_data;
	}
	
	function getcategory()
	{
		$query = $this->db->query("select * from ".$this->category_tree_table." where status = 'no' Order by parent_id ASC");
		$setting_data  = $query->result_array();
		return $setting_data;
	}
	
	function getCategoryDetails($category_id)
	{
		$query		    = $this->db->query("select * from ".$this->category_tree_table." WHERE category_id = '".$category_id."'");
		$category_data  = $query->row_array();
		return $category_data;
	}
	
	function updateCategorystatus($bc_category_id,$bc_category_url,$category_id)
	{
		$query	= $this->db->query("UPDATE ".$this->category_tree_table." SET bc_category_id = '".$bc_category_id."', bc_category_url = '".$bc_category_url."', status = 'yes' WHERE category_id = '".$category_id."'");
	}
	
	function checkparentcategory($cat_parent_id)
	{
		if(isset($cat_parent_id) && !empty($cat_parent_id))
		{
			$query		    = $this->db->query("select bc_category_id from ".$this->category_tree_table." WHERE category_id = '".$cat_parent_id."'");
			$get_category_data  = $query->row_array();
			
			if(isset($get_category_data['bc_category_id']) && !empty($get_category_data['bc_category_id']))
			{
				return $get_category_data['bc_category_id'];
			}
			else
			{
				return 0;
			}
			
		}
		else
		{
			return 0;
		}
	}
}
?>