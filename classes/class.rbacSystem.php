<?php
/**
* class RbacSystem
* system function like checkAccess, addActiveRole ...
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends PEAR
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
 * checkAccess represents the main method of the RBAC-system in ILIAS3 developers want to use
 * With this method you check the permissions a use may have due to its roles
 * on an specific object.
 * The first parameter are the operation(s) the user must have
 * The second & third parameter specifies the object where the operation(s) may applie to
 * The last parameter is only required, if you ask for the 'create' operation. Here you specify
 * the object type which you want to create.
 * 
 * example: $rbacSystem->checkAccess("visible,read",23,5);
 * Here you ask if the user is allowed to see ('visible') and access the object by reading it ('read').
 * The object_id is 23 and it is located under object no. 5 under the tree structure.
 *  
 * @access public
 * @param string	$a_operations	one or more operations, separated by commas (i.e.: visible,read,join)
 * @param integer	$a_obj_id		the object_id of the object
 * @param integer	$a_parent		the object_id of the parent of the object
 * @param string	$a_type			the type definition abbreviation (i.e.: frm,grp,crs)
 * @return boolean	returns true if ALL passed operations are given, otherwise false
 */
    function checkAccess($a_operations,$a_obj_id,$a_parent,$a_type = "")
    {
		global $ilias;
		global $tree;
		
		$create = false;
		$operations = explode(",",$a_operations);
		$ops = array();

		$rbacadmin = new RbacAdminH($this->db);
		$rbacreview = new RbacReviewH($this->db);

		foreach ($operations as $operation)
		{
			// Abfrage der ops_id der gewünschten Operation
			$query = "SELECT ops_id FROM rbac_operations ".
				"WHERE operation ='".$operation."'";		    
			
			$res = $this->db->query($query);

			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$ops_id = $row->ops_id;
			}
			
			// Case 'create': naturally there is no rbac_pa entry
			// => looking for the next template and compare operation with template permission
			if ($operation == "create")
			{
				if (empty($a_type))
				{
					$ilias->raiseError("CheckAccess: Expect a type definition for checking 'create' permission",$ilias->error_obj->MESSAGE);
				}
				
				if (!isset($ilias->typedefinition[$a_type]))
				{
					$this->raiseError("CheckAccess: Unknown type definition given: '".$a_type."'",$this->error_class->MESSAGE);
				}

				$obj = new Object($ilias);
				
				// sometimes no tree-object was instated, therefore:
				if (!is_object($tree))
				{
					$tree = new Tree($a_obj_id,ROOT_FOLDER_ID);
				}

				$path_ids = $tree->getPathId($a_obj_id,$a_parent);
				array_unshift($path_ids,SYSTEM_FOLDER_ID);
				$parent_roles = $rbacadmin->getParentRoles($path_ids);
				foreach ($parent_roles as $par_rol)
				{
					if (in_array($par_rol["obj_id"],$_SESSION["RoleId"]))
					{
						$ops = $rbacreview->getOperations($par_rol["obj_id"],$a_type,$par_rol["parent"]);
						
						if (in_array($ops_id,$ops))
						{
							$create = true;
							break;
						}
					}
				}
				
				if ($create)
				{
					continue;
				}
				else
				{
					return false;
				}

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

			while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
			}
			
			if (in_array($ops_id,$ops))
			{
				continue;
			}
			else
			{
				return false;
			}
		}
		
		return true;
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

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ops = array_merge($ops,unserialize(stripslashes($row->ops_id)));
		}
		
		return in_array($ops_id,$ops);
	}
} // END class.RbacSystem


/**
* Class RbacSystemH
* extensions for hierachical Rbac (maybe later)
* 
* @author Stefan Meyer <smeyer@databay.de> 
* 
* @extends RbacSystem 
* @package rbac
*/
class RbacSystemH extends RbacSystem
{
	/**
	* Constructor
	* @param object ilias
	*/
    function RbacSystemH(&$a_ilias)
    {
        $this->RbacSystem($a_ilias);
    }

	/**
	* @access public
	*/
    function createSession()
    {
	
    }

	/**
	* @access public
	*/
    function addActiveRole()
    {

    }
} // END class.RbacSystemH
?>