<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilObjForum
*
* @author Wolfgang Merkens <wmerkens@databay.de> 
* @version $Id$
*
* @extends Object
* @package ilias-core
*/

require_once "class.ilForum.php";
require_once "class.ilObject.php";
require_once "./classes/class.ilFileDataForum.php";

class ilObjForum extends ilObject
{
	/**
	* Default view ( 1 means 'order by answers', 2 => 'order by date')
	* @var		object Forum
	* @access	private
	*/
	var $default_view = 1;

	/**
	* Forum object
	* @var		object Forum
	* @access	private
	*/
	var $Forum;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjForum($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "frm";
		$this->ilObject($a_id,$a_call_by_reference);
		
		// TODO: needs to rewrite scripts that are using Forum outside this class
		$this->Forum =& new ilForum();

		if($a_id)
		{
			$this->__read();
		}
	}

	function read($a_force_db = false)
	{
		parent::read($a_force_db);

		$this->__read();
	}


	function getDefaultView()
	{
		return $this->default_view;
	}
	function setDefaultView($a_default_view)
	{
		return $this->default_view = (int) $a_default_view;
	}

	// METHODS FOR UN-READ STATUS
	function getCountUnread($a_usr_id,$a_thread_id = 0)
	{
		if(!$a_thread_id)
		{
			// Get topic_id
			$query = "SELECT top_pk FROM frm_data ".
				"WHERE top_frm_fk = '".$this->getId()."'";

			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$topic_id = $row->top_pk;
			}

			// Get number of posts
			$query = "SELECT COUNT(pos_pk) as num_posts FROM frm_posts ".
				"WHERE pos_top_fk = '".$topic_id."'";

			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$num_posts = $row->num_posts;
			}

			$query = "SELECT COUNT(post_id) count_read FROM frm_user_read ".
				"WHERE obj_id = '".$this->getId()."' ".
				"AND usr_id = '".$a_usr_id."'";

			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$count_read = $row->count_read;
			}
			$unread = $num_posts - $count_read;

			return $unread > 0 ? $unread : 0;
		}
		else
		{
			$query = "SELECT COUNT(pos_pk) as num_posts FROM frm_posts ".
				"WHERE pos_thr_fk = '".$a_thread_id."'";

			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$num_posts = $row->num_posts;
			}


			$query = "SELECT COUNT(post_id) as count_read FROM frm_user_read ".
				"WHERE obj_id = '".$this->getId()."' ".
				"AND usr_id = '".$a_usr_id."' ".
				"AND thread_id = '".$a_thread_id."'";

			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$count_read = $row->count_read;
			}
			$unread = $num_posts - $count_read;

			return $unread > 0 ? $unread : 0;
		}
		return false;
	}
	function markThreadRead($a_usr_id,$a_thread_id)
	{
		// Get all post ids
		$query = "SELECT * FROM frm_posts ".
			"WHERE pos_thr_fk = '".$a_thread_id."'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->markPostRead($a_usr_id,$a_thread_id,$row->pos_pk);
		}
		return true;
	}
		

	function markPostRead($a_usr_id,$a_thread_id,$a_post_id)
	{
		// CHECK IF ENTRY EXISTS
		$query = "SELECT * FROM frm_user_read ".
			"WHERE usr_id = '".$a_usr_id."' ".
			"AND obj_id = '".$this->getId()."' ".
			"AND thread_id = '".$a_thread_id."' ".
			"AND post_id = '".$a_post_id."'";

		$res = $this->ilias->db->query($query);
		if($res->numRows())
		{
			return true;
		}
		

		$query = "INSERT INTO frm_user_read ".
			"SET usr_id = '".$a_usr_id."', ".
			"obj_id = '".$this->getId()."', ".
			"thread_id = '".$a_thread_id."', ".
			"post_id = '".$a_post_id."'";

		$this->ilias->db->query($query);

		return true;
	}

	function isRead($a_usr_id,$a_post_id)
	{
		$query = "SELECT * FROM frm_user_read ".
			"WHERE usr_id = '".$a_usr_id."' ".
			"AND post_id = '".$a_post_id."'";

		$res = $this->ilias->db->query($query);

		return $res->numRows() ? true : false;
	}

	// STATIC
	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM frm_user_read ".
			"WHERE usr_id = '".$a_usr_id."'";

		$ilDB->query($query);

		return true;
	}


	function _deleteReadEntries($a_post_id)
	{
		global $ilDB;

		$query = "DELETE FROM frm_user_read ".
			"WHERE post_id = '".$a_post_id."'";

		$ilDB->query($query);

		return true;
	}
	
	/**
	* update forum data
	*
	* @access	public
	*/
	function update()
	{
		if (parent::update())
		{			
			$query = "UPDATE frm_data ".
					 "SET ".
					 "top_name = '".ilUtil::prepareDBString($this->getTitle())."',".
					 "top_description = '".ilUtil::prepareDBString($this->getDescription())."',".
					 "top_update = '".date("Y-m-d H:i:s")."',".
					 "update_user = '".(int) $_SESSION["AccountId"]."' ".
					 "WHERE top_frm_fk = '".(int) $this->getId()."'";
			$res = $this->ilias->db->query($query);

			$query = "REPLACE INTO frm_settings ".
				"SET obj_id = '".$this->getId()."', ".
				"default_view = '".$this->getDefaultView()."'";

			$this->ilias->db->query($query);
		
			return true;
		}

		return false;
	}
	
	/**
	* copy all entries of a forum object.
	* attention: frm_data is linked with ILIAS system (object_data) with the obj_id and NOT ref_id! 
	* 
	* @access	public
	* @param integer ref_id of parent object
	* @param boolean copy with or without content (threads)
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref,$a_with_content = true)
	{		
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// get object instance of cloned forum
		$forumObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// COPY settings
		$query = "INSERT INTO frm_settings ".
			"SET obj_id = '".$forumObj->getId()."', ".
			"default_view = '".$this->getDefaultView()."'";
		$this->ilias->db->query($query);
		

		// COPY ATTACHMENTS
		$tmp_file_obj =& new ilFileDataForum($this->getId());

		// create a local role folder & default roles
		$roles = $forumObj->initDefaultRoles();

		// ...finally assign moderator role to creator of forum object
		$rbacadmin->assignUser($roles[0], $forumObj->getOwner(), "n");
		ilObjUser::updateActiveRoles($forumObj->getOwner());

		// STOP HERE if without_content is selected
		if(!$a_with_content)
		{
			$this->Forum->setWhereCondition("top_frm_fk = ".$this->getId());
			$topData = $this->Forum->getOneTopic();

			$query = "INSERT INTO frm_data ".
				"VALUES('0','".$forumObj->getId()."','".ilUtil::prepareDBString($topData['top_name'])."','".
				ilUtil::prepareDBString($topData['top_description'])."','0','0','','".$roles[0]."',NOW(),'0',NOW(),'0','".
				$this->ilias->account->getId()."')";

			$this->ilias->db->query($query);

			return $new_ref_id;
		}
		

		// get forum data
		$this->Forum->setWhereCondition("top_frm_fk = ".$this->getId());
		$topData = $this->Forum->getOneTopic();
		
		// insert new forum as a copy 
		$q = "INSERT INTO frm_data ";
		$q .= "(top_frm_fk,top_name,top_description,top_num_posts,top_num_threads,top_last_post,top_mods,top_date,".
			"top_usr_id,visits,top_update,update_user) ";
		$q .= "VALUES ";
		$q .= "('".$forumObj->getId()."','".addslashes($topData["top_name"])."','".addslashes($topData["top_description"])."','".
			$topData["top_num_posts"]."','".$topData["top_num_threads"]."','".$topData["top_last_post"]."','".$roles[0]."','".
			$topData["top_date"]."','".$topData["top_usr_id"]."','".$topData["visits"]."','".$topData["top_update"]."','".
			$topData["update_user"]."')";
		$this->ilias->db->query($q);

		// get last insert id and return it
		$new_top_pk = $this->ilias->db->getLastInsertId();

		// get threads from old forum and insert them as copys
		$resThreads = $this->Forum->getThreadList($topData["top_pk"]);	
		
		while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$q = "INSERT INTO frm_threads ";
			$q .= "(thr_top_fk,thr_usr_id,thr_subject,thr_date,thr_update,thr_num_posts,thr_last_post,visits) ";
			$q .= "VALUES ";
			$q .= "('".$new_top_pk."','".$thrData["thr_usr_id"]."','".addslashes($thrData["thr_subject"])."','".
				$thrData["thr_date"]."','".$thrData["thr_update"]."','".$thrData["thr_num_posts"]."','".
				$thrData["thr_last_post"]."','".$thrData["visits"]."')";
			$this->ilias->db->query($q);
			
			// get last insert id and return it
			$new_thr_pk = $this->ilias->db->getLastInsertId();
						
			// get posts from old thread and insert them as copys
			$resPosts = $this->Forum->getPostList($topData["top_pk"], $thrData["thr_pk"]);
			
			while ($posData = $resPosts->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$q2 = "INSERT INTO frm_posts ";
				$q2 .= "(pos_top_fk,pos_thr_fk,pos_usr_id,pos_message,pos_date,pos_update) ";
				$q2 .= "VALUES ";
				$q2 .= "('".$new_top_pk."','".$new_thr_pk."','".$posData["pos_usr_id"]."','".
					addslashes($posData["pos_message"])."','".$posData["pos_date"]."','".$posData["pos_update"]."')";
				$this->ilias->db->query($q2);

				// get last insert id and return it
				$new_pos_pk = $this->ilias->db->getLastInsertId();

				// CLONE POST ATTACHMENTS
				$tmp_file_obj->setPosId($posData["pos_pk"]);
				$tmp_file_obj->ilClone($forumObj->getId(),$new_pos_pk);
				
				// get tree data from old post and insert copy
			    $q3 = "SELECT * FROM frm_posts_tree ";
				$q3 .= "WHERE pos_fk = '".$posData["pos_pk"]."' ";	   
				$q3 .= "AND thr_fk = '".$thrData["thr_pk"]."'";
				$treeData = $this->ilias->db->getRow($q3, DB_FETCHMODE_ASSOC);
								
				$q4 = "INSERT INTO frm_posts_tree (thr_fk,pos_fk,parent_pos,lft,rgt,depth,date) ";
				$q4 .= "VALUES ";
				$q4 .= "('".$new_thr_pk."','".$new_pos_pk."','".$treeData["parent_pos"]."','".
					$treeData["lft"]."','".$treeData["rgt"]."','".$treeData["depth"]."','".$treeData["date"]."')";
				$this->ilias->db->query($q4);
			}
		}

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
		unset($forumObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete forum and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		// DELETE ATTACHMENTS
		$tmp_file_obj =& new ilFileDataForum($this->getId());
		$tmp_file_obj->delete();
		unset($tmp_file_obj);
		
		$this->Forum->setWhereCondition("top_frm_fk = ".$this->getId());
		$topData = $this->Forum->getOneTopic();	
		
		$resThreads = $this->Forum->getThreadList($topData["top_pk"]);	
		
		while ($thrData = $resThreads->fetchRow(DB_FETCHMODE_ASSOC))
		{
			// delete tree
			$query = "DELETE FROM frm_posts_tree WHERE thr_fk = '".$thrData["thr_pk"]."'";
			$this->ilias->db->query($query);
			
			// delete posts
			$query = "DELETE FROM frm_posts WHERE pos_thr_fk = '".$thrData["thr_pk"]."'";
			$this->ilias->db->query($query);
			
			// delete threads
			$query = "DELETE FROM frm_threads WHERE thr_pk = '".$thrData["thr_pk"]."'";
			$this->ilias->db->query($query);
		}
		// delete topic
		$query = "DELETE FROM frm_data WHERE top_frm_fk = '".$this->getId()."'";
		$this->ilias->db->query($query);

		// delete settings
		$query = "DELETE FROM frm_settings WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($query);

		// delete read infos
		$query = "DELETE FROM frm_user_read WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($query);

		return true;
	}

	/**
	* init default roles settings
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;
		
		// create a local role folder
		$rfoldObj = $this->createRoleFolder();

		// create moderator role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_frm_moderator_".$this->getRefId(),"Moderator of forum obj_no.".$this->getId());
		$roles[] = $roleObj->getId();
		
		// grant permissions: visible,read,write,edit_post,delete_post
		$permissions = array(1,2,3,4,6,9,10);
		$rbacadmin->grantPermission($roles[0],$permissions,$this->getRefId());

		unset($rfoldObj);
		unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Forum ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				
				break;
			
			case "cut":
				
				//echo "Forum ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Forum ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				
				break;

			case "paste":
				
				//echo "Forum ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				
				break;
			
			case "new":
				
				//echo "Forum ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				
				break;
		}
		
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
		
	}

	function createSettings()
	{
		$query = "INSERT INTO frm_settings ".
			"SET obj_id = '".$this->getId()."', ".
			"default_view = '".$this->getDefaultView()."'";

		$this->ilias->db->query($query);

		return true;
	}

	
	// PRIVATE
	function __read()
	{
		$query = "SELECT * FROM frm_settings WHERE ".
			"obj_id = '".$this->getId()."'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->default_view = $row->default_view;
		}
		return true;
	}


} // END class.ilObjForum
?>
