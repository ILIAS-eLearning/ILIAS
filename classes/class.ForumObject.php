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
		require_once "class.Forum.php";
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
			"top_usr_id"      	=> $_SESSION["AccountId"],
            "top_date" 			=> date("Y-m-d H:i:s")			
        );
		
		// insert new forum as new topic into frm_data
		$q = "INSERT INTO frm_data ";
		$q .= "(top_frm_fk,top_name,top_description,top_num_posts,top_num_threads,top_last_post,top_mods,top_date,top_usr_id) ";
		$q .= "VALUES ";
		$q .= "('".$top_data["top_frm_fk"]."','".$top_data["top_name"]."','".$top_data["top_description"]."','".$top_data["top_num_posts"]."','".$top_data["top_num_threads"]."','".$top_data["top_last_post"]."','".$top_data["top_mods"]."','".$top_data["top_date"]."','".$top_data["top_usr_id"]."')";
		$result = $this->ilias->db->query($q);
		
		
		return $newFrm_ID;	
		
	}
	
	
			
	/**
	* update forum data
	* @access	public
	**/
	function updateObject()
	{		
		
		if (parent::updateObject())
		{
			$userData = Forum::getModerator($_SESSION["AccountId"]);			
			$a_obj_data = $_POST["Fobject"];
			
			$query = "UPDATE frm_data ".
					 "SET ".
					 "top_name = '".$a_obj_data["title"]."',".
					 "top_description = '".$a_obj_data["desc"]."',".
					 "top_update = '".date("Y-m-d H:i:s")."',".
					 "update_user = '".$userData["Id"]."' ".
					 "WHERE top_frm_fk = '".$this->id."'";
			$res = $this->ilias->db->query($query);
		
			return true;
		}	
		
	}
	
	
	
	/**
	* delete forum and all contents	
	* @access public
	*/
	function deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1)
	{		
		
		Forum::setWhereCondition("top_frm_fk = ".$a_obj_id);			
		$topData = Forum::getOneTopic();	
		
		$resThreads = Forum::getThreadList($topData["top_pk"]);	
		
		while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// delete tree
			$query = "DELETE FROM frm_posts_tree WHERE thr_fk = '".$thrData["thr_pk"]."'";
			$this->ilias->db->query($query);
			
			// delete posts
			$query = "DELETE FROM frm_posts WHERE pos_thr_fk = '".$thrData["thr_pk"]."'";
			$this->ilias->db->query($query);
			
			// delete thread
			$query = "DELETE FROM frm_threads WHERE thr_pk = '".$thrData["thr_pk"]."'";
			$this->ilias->db->query($query);
		}
		
		// delete topic
		$query = "DELETE FROM frm_data WHERE top_frm_fk = '".$a_obj_id."'";
		$this->ilias->db->query($query);
		
		
		return parent::deleteObject($a_obj_id, $a_parent_id, $a_tree_id);		
		
	}
	
	
	
	
} // END class.ForumObject
?>