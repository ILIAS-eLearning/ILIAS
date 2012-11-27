<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/AccessControl/exceptions/class.ilRoleImporterException.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilRoleXmlImporter
{
    protected $role_folder = 0;
	protected $role = null;
	
	protected $xml = '';
	
	/**
	 * Constructor
	 */
	public function __construct($a_role_folder_id = 0)
	{
		$this->role_folder = $a_role_folder_id;
	}
	
	public function setXml($a_xml)
	{
		$this->xml = $a_xml;
	}
	
	public function getXml()
	{
		return $this->xml;
	}
	
	/**
	 * Get role folder id
	 * @return int 
	 */
	public function getRoleFolderId()
	{
		return $this->role_folder;
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
	 * Set role or role template
	 * @param ilObject $role 
	 */
	public function setRole(ilObject $role)
	{
		$this->role = $role;
	}
	
	/**
	 * import role | role templatae
	 * @throws ilRoleXmlImporterException
	 */
	public function import()
	{
		libxml_use_internal_errors(true);
		
		$root = simplexml_load_string($this->getXml());
		
		if(!$root instanceof SimpleXMLElement)
		{
			throw new ilRoleImporterException($this->parseXmlErrors());
		}
		foreach($root->role as $roleElement)
		{
			$this->importSimpleXml($roleElement);
			// only one role is parsed
			break;
		}
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

		$this->assignToRoleFolder();

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
					$this->getRoleFolderId() // #10161
				);

			}
		}

		return $this->getRole()->getId();
	}
	
	/**
	 * Assign role to folder
	 * @global type $rbacadmin
	 * @return type 
	 */
	protected function assigntoRoleFolder()
	{
		global $rbacadmin;
		
		if(!$this->getRoleFolderId())
		{
			return;
		}
		
		$rbacadmin->assignRoleToFolder(
			$this->getRole()->getId(),
			$this->getRoleFolderId(),
			$this->getRole() instanceof ilObjRole ? 'y' : 'n'
		);
	}


	protected function initRole($import_id)
	{
		if($this->getRole())
		{
			return true;
		}
		
		$obj_id = ilObject::_lookupObjIdByImportId($import_id);
		include_once './Services/Object/classes/class.ilObjectFactory.php';
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
	
	protected function parseXmlErrors()
	{
		$errors = '';
		
		foreach(libxml_get_errors() as $err)
		{
			$errors .= $err->code.'<br/>';
		}
		return $errors;
	}
}
?>
