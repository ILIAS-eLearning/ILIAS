<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Xml/classes/class.ilXmlWriter.php';

/**
 * Xml export of roles and role templates
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesAccessControl
 */
class ilRoleXmlExport extends ilXmlWriter
{
	const MODE_DTPL = 1;


	private $roles = array();
	private $operations = array();

	private $mode = 0;


	/**
	 *  Constructor
	 */
	public function  __construct()
	{
		parent::__construct();

		$this->initRbacOperations();
	}


	/**
	 * Set roles
	 * Format is: array(role_id => array(role_folder_id))
	 *
	 * @param array $a_roles
	 * @return void
	 */
	public function setRoles($a_roles)
	{
		$this->roles = (array) $a_roles;
	}

	/**
	 * Get roles
	 * @return array
	 */
	public function getRoles()
	{
		return (array) $this->roles;
	}

	/**
	 * Add one role
	 * @param int $a_role_id
	 * @param int $a_ref_id of source object
	 */
	public function addRole($a_role_id, $a_rolf_id)
	{
		$this->roles[$a_role_id][] = $a_rolf_id;
	}

	public function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	public function getMode()
	{
		return $this->mode;
	}


	/**
	 * Write xml header
	 */
	public function writeHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Roles PUBLIC \"-//ILIAS//DTD ILIAS Roles//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_role_definition_4_2.dtd\">");
		$this->xmlSetGenCmt("Role Definition");
		$this->xmlHeader();
		return true;
	}

	/**
	 * Write xml presentation of chosen roles
	 * @return bool
	 */
	public function write()
	{

		if($this->getMode() != self::MODE_DTPL)
		{
			$this->xmlStartTag('roles');
		}

		foreach($this->getRoles() as $role_id => $role_folder_ids)
		{
			foreach((array) $role_folder_ids as $rolf)
			{
				$this->writeRole($role_id, $rolf);
			}
		}

		if($this->getMode() != self::MODE_DTPL)
		{
			$this->xmlEndTag('roles');
		}
	}
	
	/**
	 * Write xml presentation of one role
	 * @param int $a_role_id
	 * @param int $a_rolf 
	 */
	private function writeRole($a_role_id, $a_rolf)
	{
		global $rbacreview;
		
		$attributes = array(
			'type'	=> ilObject::_lookupType($a_role_id),
			'id'	=> 'il_'.IL_INST_ID.'_'.ilObject::_lookupType($a_role_id).'_'.$a_role_id,
			'protected' => ($GLOBALS['rbacreview']->isProtected($a_rolf,$a_role_id) ? 1 : 0)
		);

		$this->xmlStartTag('role',$attributes);

		$this->xmlElement('title',array(),ilObject::_lookupTitle($a_role_id));
		$this->xmlElement('description', array(), ilObject::_lookupDescription($a_role_id));

		$this->xmlStartTag('operations');
		foreach($rbacreview->getAllOperationsOfRole($a_role_id, $a_rolf) as $obj_group => $operations)
		{
			foreach($operations as $ops_id)
			{
				$this->xmlElement('operation', array('group' => $obj_group), trim($this->operations[$ops_id]));
			}
		}
		$this->xmlEndTag('operations');
		$this->xmlEndTag('role');
		return true;
	}

	/**
	 * Cache rbac operations
	 */
	private function initRbacOperations()
	{
		global $rbacreview;

		foreach($rbacreview->getOperations() as $operation)
		{
			$this->operations[$operation['ops_id']] = $operation['operation'];
		}
		return true;
	}


}
?>
