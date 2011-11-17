<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilRoleXmlImporter
{
    /**
	 * Constructor
	 */
	public function __construct()
	{


	}

	/**
	 * Get role
	 * @return ilObjRole
	 */
	public function getRole()
	{
		return $this->role;
	}


	/**
	 * Import using simplexml
	 * @param SimpleXMLElement $role
	 */
	public function importSimpleXml(SimpleXMLElement $role)
	{
		global $rbacadmin, $rbacreview;

		$import_id = (string) $role['id'];
		$GLOBALS['ilLog']->write(__METHOD__.' Importing role with import id '. $import_id);

		if(!$this->initRole($import_id))
		{
			return 0;
		}

		$this->getRole()->setTitle((string) $role->title);
		$this->getRole()->setDescription((string) $role->description);

		// Create or update
		if($this->getRole()->getId())
		{
			$this->getRole()->update();
		}
		else
		{
			$this->getRole()->create();
		}

		$rbacadmin->assignRoleToFolder(
			$this->getRole()->getId(),
			ROLE_FOLDER_ID,
			$this->getRole() instanceof ilObjRole ? 'y' : 'n'
		);

		// Add operations

		$ops = $rbacreview->getOperations();
		$operations = array();
		foreach($ops as $ope)
		{
			$operations[$ope['operation']] = $ope['ops_id'];
		}

		foreach($role->operations as $sxml_operations)
		{
			foreach($sxml_operations as $sxml_op)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': New operation for group '. (string) $sxml_op['group']);
				$GLOBALS['ilLog']->write(__METHOD__.': New operation '.trim((string) $sxml_op));
				$GLOBALS['ilLog']->write(__METHOD__.': New operation '. $operations[trim((string) $sxml_op)]);

				if(!strlen(trim((string) $sxml_op)))
				{
					continue;
				}

				$rbacadmin->setRolePermission(
					$this->getRole()->getId(),
					trim((string) $sxml_op['group']),
					array($operations[trim((string) $sxml_op)]),
					ROLE_FOLDER_ID
				);

			}
		}

		return $this->getRole()->getId();
	}


	protected function initRole($import_id)
	{
		$obj_id = ilObject::_lookupObjIdByImportId($import_id);
		include_once './classes/class.ilObjectFactory.php';
		if($obj_id)
		{
			$this->role = ilObjectFactory::getInstanceByObjId($obj_id,false);
		}
		if(!$this->getRole() instanceof ilObjRole or !$this->getRole() instanceof ilObjRoleTemplate)
		{
			include_once './Services/AccessControl/classes/class.ilObjRoleTemplate.php';
			$this->role = new ilObjRoleTemplate();
		}
		return true;
	}
}
?>
