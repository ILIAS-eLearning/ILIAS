<?php
/**
* group class for ilias
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @package ilias-core
*/
class Group
{
	/**
	* ilias object
	* @var object ilias
	* @access private
	*/	
	var $ilias;

	/**
	* group_id
	* @var int group_id
	* @access private
	*/	
	var $group_id;
	
	/**
	* Constructor
	* @access	public
	* @param	integer group_id
	*/
	function Group($a_group_id = 0)
	{
		global $ilias;
		
		// init variables
		$this->ilias = &$ilias;
		
		$this->group_id = $a_group_id;
	}
	
	/**
	* check if group name exists
	* @access	public
	* @param	string group name
	*/
	function groupNameExists($a_group_name)
	{
		$query = "SELECT obj_id FROM object_data ".
			"WHERE title = '".$a_group_name ."' ".
			"AND type = 'grp'";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		return $row->obj_id ? $row->obj_id : 0;
	}
	/*
	* get the user_ids which correspond a search string 
	* @param	string search string
	* @access	public
	*/
	function searchGroups($a_search_str)
	{
		$query = "SELECT obj_id,title,description FROM object_data ".
			"WHERE (title LIKE '%".$a_search_str."%' ".
			"OR description LIKE '%".$a_search_str."%') ".
			"AND type = 'grp'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ids[] = array(
				"obj_id"        => $row->obj_id,
				"title"         => $row->title,
				"description"   => $row->description);
		}
		return $ids ? $ids : array();
	}
		

}
?>