<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateAction.php';

/**
 * represents a creation of local roles action
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplates
 */
class ilDidacticTemplateLocalRoleAction extends ilDidacticTemplateAction
{

	private $role_template_id = 0;

	/**
	 * Constructor
	 * @param int $a_action_id
	 */
	public function __construct($a_action_id = 0)
	{
		parent::__construct($a_action_id);
	}

	/**
	 * Get action type
	 * @return int
	 */
	public function  getType()
	{
		return self::TYPE_LOCAL_ROLE;
	}

	/**
	 * Set role template id
	 * @param int $a_role_template_id
	 */
	public function setRoleTemplateId($a_role_template_id)
	{
		$this->role_template_id = $a_role_template_id;
	}

	/**
	 * get role template id
	 * @return int
	 */
	public function getRoleTemplateId()
	{
		return $this->role_template_id;
	}

	/**
	 * Apply action
	 */
	public function apply()
	{
		global $rbacreview, $rbacadmin;

		$source = $this->initSourceObject();

		// Check if role folder already exists

		// Create role
		
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role = new ilObjRole();
		$role->setTitle(ilObject::_lookupTitle($this->getRoleTemplateId()));
		$role->setDescription(ilObject::_lookupDescription($this->getRoleTemplateId()));
		$role->create();
		$rbacadmin->assignRoleToFolder($role->getId(),$source->getRefId(),"y");

		$GLOBALS['ilLog']->write(__METHOD__.': Using rolt: '.$this->getRoleTemplateId().' with title "'.ilObject::_lookupTitle($this->getRoleTemplateId().'". '));

		// Copy template permissions
		$rbacadmin->copyRoleTemplatePermissions(
			$this->getRoleTemplateId(),
			ROLE_FOLDER_ID,
			$source->getRefId(),
			$role->getId(),
			true
		);

		// Set permissions
		$ops = $rbacreview->getOperationsOfRole($role->getId(),$source->getType(),$source->getRefId());
		$rbacadmin->grantPermission($role->getId(),$ops,$source->getRefId());

		return true;
	}

	/**
	 * Revert action
	 */
	public function revert()
	{
		// @todo: revert could delete the generated local role. But on the other hand all users 
		// assigned to this local role would be deassigned. E.g. if course or group membership 
		// is handled by didactic templates, all members would get lost.
	}

	/**
	 * Create new action
	 */
	public function save()
	{
		global $ilDB;

		parent::save();

		$query = 'INSERT INTO didactic_tpl_alr (action_id,role_template_id) '.
			'VALUES ( '.
			$ilDB->quote($this->getActionId(),'integer').', '.
			$ilDB->quote($this->getRoleTemplateId(),'integer').' '.
			') ';
		$res = $ilDB->manipulate($query);

		return true;
	}

	/**
	 * Delete
	 * @global ilDB $ilDB
	 * @return bool
	 */
	public function delete()
	{
		global $ilDB;

		parent::delete();

		$query = 'DELETE FROM didactic_tpl_alr '.
			'WHERE action_id = '.$ilDB->quote($this->getActionId(),'integer');
		$ilDB->manipulate($query);

		return true;
	}

	/**
	 * Write xml of template action
	 * @param ilXmlWriter $writer
	 */
	public function  toXml(ilXmlWriter $writer)
	{
		$writer->xmlStartTag('localRoleAction');



		$il_id = 'il_'.IL_INST_ID.'_'.ilObject::_lookupType($this->getRoleTemplateId()).'_'.$this->getRoleTemplateId();

		$writer->xmlStartTag(
			'roleTemplate',
			array(
				'id'	=> $il_id
			)
		);

		include_once './Services/AccessControl/classes/class.ilRoleXmlExport.php';
		$exp = new ilRoleXmlExport();
		$exp->setMode(ilRoleXmlExport::MODE_DTPL);
		$exp->addRole($this->getRoleTemplateId(), ROLE_FOLDER_ID);
		$exp->write();
		$writer->appendXML($exp->xmlDumpMem(FALSE));
		$writer->xmlEndTag('roleTemplate');
		$writer->xmlEndTag('localRoleAction');
	}

	/**
	 * Read db entry
	 * @global ilDB $ilDB
	 * @return bool
	 */
	public function read()
	{
		global $ilDB;
		
		parent::read();
		
		$query = 'SELECT * FROM didactic_tpl_alr '.
			'WHERE action_id = '.$ilDB->quote($this->getActionId(),'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setRoleTemplateId($row->role_template_id);
		}
		return true;
	}

}