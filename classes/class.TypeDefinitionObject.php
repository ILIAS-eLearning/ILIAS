<?php
/**
* Class TypeDefinitionObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/
class TypeDefinitionObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function TypeDefinitionObject($a_id)
	{
		$this->Object($a_id);
	}


	function editObject($a_order, $a_direction)
	{
		global $rbacsystem, $rbacadmin, $tpl;

		// RBAC deactived for testing
		//if ($rbacsystem->checkAccess('write',$_GET["parent"],$_GET["parent_parent"]))
		//{
			//prepare objectlist
			$this->objectList = array();
			$this->objectList["data"] = array();
			$this->objectList["ctrl"] = array();

			$this->objectList["cols"] = array("", "type", "operation", "description", "status");

			$ops_valid = $rbacadmin->getOperationsOnType($this->id);

			if ($ops_arr = getOperationList('', $a_order, $a_direction))
			{
				$options = array("e" => "enabled","d" => "disabled");
			
				foreach ($ops_arr as $key => $ops)
				{
					// BEGIN ROW
					if (in_array($ops["ops_id"],$ops_valid))
					{
						$ops_status = 'e';
					}
					else
					{
						$ops_status = 'd';
					}
					
					$obj = $ops["ops_id"];
					$ops_options = TUtil::formSelect($ops_status,"id[$obj]",$options);
	
					//visible data part
					$this->objectList["data"][] = array(
						"type" => "<img src=\"".$tpl->tplPath."/images/"."icon_perm_b.gif\" border=\"0\">",
						"title" => $ops["operation"],
						"description" => $ops["desc"],
						"status" => $ops_options
					);
	
					//control information
					$this->objectList["ctrl"][] = array(
						"type" => "perm",
						"obj_id" => $ops["ops_id"],
						"parent" => $this->id,
						"parent_parent" => $this->parent
					);
				}
			}
			return $this->objectList;
		//}
		//else
		//{
		//	$this->ilias->raiseError("No permission to edit operations",$this->ilias->error_obj->WARNING);
		//}
	}
	
	function saveObject($a_obj_id, $a_parent,$a_type, $a_new_type, $a_data)
	{
		$this->alterOperationsOnObject();
	}
	
	
} // END class.TypeDefinitionObject
?>
