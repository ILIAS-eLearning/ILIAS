<?php
/**
* Class ForumObject
*
* @author Wolfgang Merkens <wmerkens@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class ForumObject extends Object
{
	/**
	* Constructor
	* @access public
	*/
	function ForumObject()
	{
		$this->Object();
	}
	
	
	/**
	* saves new object in admin interface
	* 
	* @param	integer		obj_id
	* @param	integer		parent_id
	* @param	string		obj_type
	* @param	string		new_obj_type
	* @param	array		title & description
	* @return	integer		new obj_id
	* @access	public
	**/
	function saveObject($a_obj_id = '', $a_parent = '' ,$a_type = '' , $a_new_type = '' , $a_data = '')
	{
		global $tree;
		
		$newFrm_ID = parent::saveObject($a_obj_id = '', $a_parent = '' ,$a_type = '' , $a_new_type = '' , $a_data = '');
		
		// create new forum tree
		$tree->addTree($newFrm_ID);
		
		$frm_data = getObject($newFrm_ID);
		
		$top_data = array(
            "top_frm_fk"   		=> $newFrm_ID,
			"top_name"   		=> $frm_data["title"],
            "top_description" 	=> $frm_data["desc"],
            "top_num_posts"     => 0,
            "top_num_threads"   => 0,
            "top_last_post"     => "",
			"top_mods"      	=> $frm_data["owner"],
            "top_last_modified" => date("Y-m-d H:i:s")
        );
		
		// insert new forum as new topic into frm_data
		$q = "INSERT INTO frm_data ";
		$q .= "(top_frm_fk,top_name,top_description,top_num_posts,top_num_threads,top_last_post,top_mods,top_last_modified) ";
		$q .= "VALUES ";
		$q .= "('".$top_data["top_frm_fk"]."','".$top_data["top_name"]."','".$top_data["top_description"]."','".$top_data["top_num_posts"]."','".$top_data["top_num_threads"]."','".$top_data["top_last_post"]."','".$top_data["top_mods"]."','".$top_data["top_last_modified"]."')";
		$result = $this->ilias->db->query($q);
				
		/*	
	 	// get last insert id and return it
		$query = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($query);
		$lastInsert = $res->fetchRow();	
		*/
		
		return $newFrm_ID;	
		
	}
	
	/**
	* delete forum and all contents	
	* @access public
	*/
	function deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1)
	{
		//global $tree, $rbacsystem, $rbacadmin;
		
		/*
		1. SELECT from frm_data where top_frm_fk = $a_obj_id (numrow=1)
		2. SELECT from threads where thr_top_fk = frm_data.top_pk (numrow>1)
			3. DELETE from posts where pos_thr_fk =  threads.thr_pk
			4. DELETE from threads this thr_pk
		5. DELETE from frm_data this top_pk
		*/
		/*
		$q = "DELETE FROM frm_data WHERE top_frm_fk = '".$a_obj_id."'";
		$this->ilias->db->query($q);
		echo "q:".$q;
		*/
		return parent::deleteObject($a_obj_id, $a_parent_id, $a_tree_id);		
		
	}
	
	
	
	
} // END class.ForumObject
?>