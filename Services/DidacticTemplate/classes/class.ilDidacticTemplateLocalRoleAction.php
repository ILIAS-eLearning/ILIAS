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
		parent::__construct($action_id);
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
		$rolf_id = $rbacreview->getRoleFolderIdOfObject($source->getRefId());
		if(!$rolf_id)
		{
			$source->createRoleFolder();
		}
		$rolf_id = $rbacreview->getRoleFolderIdOfObject($source->getRefId());

		// Create role
		$rolf = ilObjectFactory::getInstanceByRefId($rolf_id,false);
		$role = $rolf->createRole(
			ilObject::_lookupTitle($this->getRoleTemplateId()),
			ilObject::_lookupDescription($this->getRoleTemplateId())
		);


		// Copy template permissions
		$rbacadmin->copyRoleTemplatePermissions(
			$this->getRoleTemplateId(),
			ROLE_FOLDER_ID,
			$rolf->getId(),
			$role->getId()
		);


		// Set permissions
		$ops = $rbacreview->getOperationsOfRole($role->getId(),$source->getType(),$rolf->getRefId());
		$rbacadmin->grantPermission($role->getId(),$ops,$source->getRefId());

		return true;
	}

	/**
	 * Revert action
	 */
	public function revert()
	{
		;
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

		$writer->xmlElement(
			'roleTemplate',
			array(
				'id'	=> 'il_rolt_'.IL_INST_ID.'_'.$this->getRoleTemplateId()
			)
		);
		
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