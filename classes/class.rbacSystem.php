<?php

include_once "classes/class.Object.php";


/**
 * system
 * system function like checkAccess, addActiveRole ...
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package rbac
 */
class RbacSystem extends PEAR
{
	/**
	* database handle
	* @var object db
	*/
    var $db;
	
	/**
	* error handle
	* @var object error_class
	*/
	var $error_class;


// PUBLIC METHODS

	/**
	* constructor
	* @param object db
	*/
    function RbacSystem(&$dbhandle)
    {
		$this->PEAR();
		$this->error_class = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK,array($this->error_class,'errorHandler'));

        $this->db =& $dbhandle;
    }
	
	/**
	* @access public
	*/
    function getErrorMessage()
    {
        return $this->Error;
    }
	
	/**
	* @access public
	*/
    function createSession()
    {
    }
// @access public
// @params 
// @return 
    function deleteSession()
    {
    }
/**
 * adds an active role in $_SESSION["RoleId"]
 * @return bool
 */
    function addActiveRole()
    {
    }
	/**
	* @access public
	*/
	function dropActiveRole()
    {
    }

/**	
 * @access public
 * @param integer ObjectId, das abzufragende Recht
 * @param integer
 * @oaram integer
 * @param string
 * @return boolean true false
 */
    function checkAccess($Aoperation,$a_obj_id,$a_parent,$a_type = "")
    {
		global $ilias;
		global $tree;
		
		$ops = array();

		$rbacadmin = new RbacAdminH($this->db);
		$rbacreview = new RbacReviewH($this->db);

		// Abfrage der ops_id der gewünschten Operation
		$query = "SELECT ops_id FROM rbac_operations ".
			"WHERE operation ='".$Aoperation."'";

		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			//echo $row->ops_id."<br>";
			$ops_id = $row->ops_id;
		}
		// Case 'create': naturally there is no rbac_pa entry
		// => looking for the next template and compare operation with template permission
		if($Aoperation == 'create')
		{
			$obj = new Object($ilias);
			$tree = new Tree($a_obj_id,$obj->ROOT_FOLDER_ID);
			$path_ids = $tree->getPathId($a_obj_id,$obj->ROOT_FOLDER_ID);
			array_unshift($path_ids,$obj->SYSTEM_FOLDER_ID);
			$parent_roles = $rbacadmin->getParentRoles($path_ids);
			foreach($parent_roles as $par_rol)
			{
				if(in_array($par_rol["obj_id"],$_SESSION["RoleId"]))
				{
					$ops = $rbacreview->getOperations($par_rol["obj_id"],$a_type,$par_rol["parent"]);
					if(in_array($ops_id,$ops))
					{
						return true;
					}
				}
			}
			return false;
		} // END CASE 'create'

		// Um nur eine Abfrage zu haben
		$in = " IN ('";
		$in .= implode("','",$_SESSION["RoleId"]);
		$in .= "')";

		$query = "SELECT * FROM rbac_pa ".
			"WHERE rol_id ".$in." ".
			"AND obj_id = '".$a_obj_id."' ".
			"AND set_id = '".$a_parent."'";
		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
		}
		return in_array($ops_id,$ops);
    }
	
/**
 * @access public
 * @param integer ObjectId,
 * @param integer RoleIds, 
 * @param integer das abzufragende Recht
 * @param string
 * @return boolean true false
 */
	function checkPermission($Aobj_id,$Arol_id,$Aoperation,$Aset_id="")
	{
		$ops = array();

		// Abfrage der ops_id der gewünschten Operation
		$query = "SELECT ops_id FROM rbac_operations ".
			"WHERE operation ='".$Aoperation."'";

		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			//echo $row->ops_id."<br>";
			$ops_id = $row->ops_id;
		}
	
		// ABFRAGE DER OPS_ID
		if(!$Aset_id)
		{
			$and = "";
		}
		else
		{
			$and = " AND set_id = '".$Aset_id."'";
		}
		
		$query = "SELECT * FROM rbac_pa ".
			"WHERE rol_id = '".$Arol_id."' ".
			"AND obj_id = '".$Aobj_id."' ".
			$and;
		
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
		}
		
		return in_array($ops_id,$ops);
	}
} // END CLASS RbacSystem
?>