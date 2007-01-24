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
* Class ilObjSysUserTracking
*
* @author Arlon Yin <arlon_yin@hotmail.com>
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @extends ilObject
*/

require_once "class.ilObject.php";

class ilObjSysUserTracking extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSysUserTracking($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "trac";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		if (!parent::update())
		{
			return false;
		}

		// put here object specific stuff

		return true;
	}

	/**
	* copy all entries of your object.
	*
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);

		// get object instance of ilCloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");

		// always destroy objects in ilClone method because ilClone() is recursive and creates instances for each object in subtree!
		//unset($newObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete object and all related data
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

		//put here your module specific stuff

		return true;
	}

	/**
	* init default roles settings
	*
	* If your module does not require any default roles, delete this method
	* (For an example how this method is used, look at ilObjForum)
	*
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;

		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
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
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "cut":

				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "copy":

				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "new":

				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
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


	/**
	* get total number of tracking records
	*/
	function getRecordsTotal()
	{
		global $ilDB;

		$q = "SELECT count(*) AS cnt FROM ut_access";
		$cnt_set = $ilDB->query($q);

		$cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC);

		return $cnt_rec["cnt"];
	}

	/**
	* get total number of accesses per month
	*/
	function getMonthTotalOverview()
	{
		global $ilDB;

		$q = "SELECT count(*) as cnt, count(distinct user_id) as user_cnt, date_format(acc_time,'%Y-%m') AS month FROM ut_access".
			" GROUP BY month ORDER BY month DESC";
		$min_set = $ilDB->query($q);
		$months = array();
		while ($min_rec = $min_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$months[] = array("month" => $min_rec["month"],
				"cnt" => $min_rec["cnt"], "user_cnt" => $min_rec["user_cnt"]);
		}
		return $months;
	}

	/**
	* get total number of records older than given month (YYYY-MM)
	*/
	function getTotalOlderThanMonth($a_month)
	{
		global $ilDB;

		$q = "SELECT count(*) as cnt, date_add(".$ilDB->quote("$a_month-01").", INTERVAL 1 MONTH) as d FROM ut_access WHERE acc_time < ".
			"date_add(".$ilDB->quote("$a_month-01").", INTERVAL 1 MONTH)";

		$cnt_set = $ilDB->query($q);
		$cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC);
//echo "cnt:".$cnt_rec["cnt"].":date:".$cnt_rec["d"].":";

		return $cnt_rec["cnt"];
	}

	/**
	* get total number of records older than given month (YYYY-MM)
	*/
	function getAccessTotalPerUser($a_condition)
	{
		global $ilDB;

		$q = "SELECT count(*) AS cnt, user_id ".
			"FROM ut_access WHERE ".$a_condition.
			" GROUP BY user_id";
		$cnt_set = $ilDB->query($q);

		$acc = array();
		while ($cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$name = ilObjUser::_lookupName($cnt_rec["user_id"]);

			if ($cnt_rec["user_id"] != 0)
			{
				$acc[] = array("user_id" => $cnt_rec["user_id"],
					"name" => $name["lastname"].", ".$name["firstname"],
					"cnt" => $cnt_rec["cnt"]);
			}
		}
		return $acc;
	}
	
	/**
	* get total number of records older than given month (YYYY-MM)
	*/
	function getAccessTotalPerObj($a_condition)
	{
		global $ilDB;
		$q = "SELECT count(*) as cnt,acc_obj_id	from ut_access where ".$a_condition.
			" GROUP BY acc_obj_id";
		$cnt_set = $ilDB->query($q);
		//echo "q:".$q;

		$acc = array();
		while ($cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($cnt_rec["cnt"] != "")
			{
				
				$acc[] = array("title" => ilObject::_lookupTitle($cnt_rec["acc_obj_id"]),
					"author" => $this->getOwnerName($cnt_rec["acc_obj_id"]),
					"cnt" => $cnt_rec["cnt"]);
			}
		}
		return $acc;
	}
	/**
	* get per user of records older than given month (YYYY-MM)
	*/
	function getAccessPerUserDetail($a_condition)
	{
		global $ilDB;

		$q ="SELECT user_id,client_ip,acc_obj_id,language ,acc_time ".
			"FROM ut_access WHERE ".$a_condition;
		$cnt_set = $ilDB->query($q);
		$acc = array();
		while($cnt_rec = $cnt_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$name = ilObjUser::_lookupName($cnt_rec["user_id"]);

			if ($cnt_rec["user_id"] != 0)
			{
				$acc[] = array("user_id" => $cnt_rec["user_id"],
					"name" => $name["lastname"].", ".$name["firstname"],
					"client_ip" => $cnt_rec["client_ip"],
					"acc_obj_id" => ilObject::_lookupTitle($cnt_rec["acc_obj_id"]),
					"language" => $cnt_rec["language"],
					"acc_time" => $cnt_rec["acc_time"]
					);
			}
		}

		return $acc;
	}
	/**
	* delete tracking data of month (YYYY-MM) and before
	*/
	function deleteTrackingDataBeforeMonth($a_month)
	{
		global $ilDB;

		$q = "DELETE FROM ut_access WHERE acc_time < ".
			"date_add(".$ilDB->quote("$a_month-01").", INTERVAL 1 MONTH)";

		$ilDB->query($q);
	}

	/**
	* enable user tracking
	*/
	function enableTracking($a_enable)
	{
		global $ilias;

		if ($a_enable)
		{
			$ilias->setSetting("enable_tracking", 1);
		}
		else
		{
			$ilias->setSetting("enable_tracking", 0);
		}
	}

	/**
	* check wether user tracking is enabled or not
	*/
	function _enabledTracking()
	{
		global $ilias;

		return (boolean) $ilias->getSetting("enable_tracking");
	}

	/**
	* enable tracking of user related data
	*/
	function enableUserRelatedData($a_enable)
	{
		global $ilias;

		if ($a_enable)
		{
			$ilias->setSetting("save_user_related_data", 1);
		}
		else
		{
			$ilias->setSetting("save_user_related_data", 0);
		}
	}

	/**
	* check wether user related tracking is enabled or not
	*/
	function _enabledUserRelatedData()
	{
		global $ilias;

		return (boolean) $ilias->getSetting("save_user_related_data");
	}

	/**
	* get all author
	*/
	function allAuthor($a_type,$type)
	{
		global $ilDB;

		$q = "SELECT distinct A.obj_id,A.type,A.title FROM object_data as A,object_data as B WHERE A.type = ".$ilDB->quote($a_type)." AND A.obj_id = B.owner AND B.type=".$ilDB->quote($type);
		//echo $q;
		$author = $ilDB->query($q);
		$all = array();
		while ($aauthor = $author->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all[] = array("title" => $aauthor["title"],
					"obj_id" =>$aauthor["obj_id"]);
		}
		return $all;
	}

	/**
	* get author's all lm or tst
	*/
	function authorLms($id,$type)
	{
		global $ilDB;

		$q = "SELECT title,obj_id FROM object_data WHERE owner = ".$ilDB->quote($id)." and type=".$ilDB->quote($type);
		//echo $q."<br>";
		$lms = $ilDB->query($q);
		$all = array();
		while ($alms = $lms->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all[] = array("title" => $alms["title"],
					"obj_id" =>$alms["obj_id"]);
		}
		return $all;
		
	}

	/**
	* get obj_id of some object
	*/
	function getObjId($title,$type)
	{
		global $ilDB;
		$q ="SELECT obj_id FROM object_data WHERE type = ".$ilDB->quote($type)." and title=".$ilDB->quote($title);
		$id = $ilDB->query($q);
		$obj_id = $id->fetchRow(DB_FETCHMODE_ASSOC);
		return $obj_id["obj_id"];
	}
	
	/**
	* get Test_id of some test
	*/
	function getTestId($id)
	{
		global $ilDB;
		
		$q = "select obj_id from object_data "
		." where type = 'tst' and "
		." owner = ".$ilDB->quote($id);
		$res = $this->ilias->db->query($q);
		for ($i=0;$i<$res->numRows();$i++)
		{
			$result[$i]=$res->fetchRow();
		}
		return $result;
	}
	
	/**
	* Return the counts of search results
	*/
	function countResults($condition)
	{
		$q = "SELECT count(*) from ut_access "
		." WHERE "
		.$condition;
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
	
	/**
	* Return the owner name of the object
	*/
	function getOwnerName($id)
	{
		$q =" select A.login from usr_data as A, object_data as B where A.usr_id=B.owner and B.obj_id = ".
			$ilDB->quote($id);
		$res = $this->ilias->db->query($q);
		$result = $res->fetchRow();
		return $result[0];
	}
} // END class.ilObjSysUserTracking
?>
