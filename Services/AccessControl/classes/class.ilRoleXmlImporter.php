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
     * @var \ilLogger|null
     */
    private $logger = null;
    
    /**
     * Constructor
     */
    public function __construct($a_role_folder_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->otpl();
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
        
        if (!$root instanceof SimpleXMLElement) {
            throw new ilRoleImporterException($this->parseXmlErrors());
        }
        foreach ($root->role as $roleElement) {
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
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $lng = $DIC['lng'];

        $import_id = (string) $role['id'];
        $this->logger->info('Importing role with import_id: ' . $import_id);

        if (!$this->initRole($import_id)) {
            return 0;
        }
        
        $this->getRole()->setTitle(trim((string) $role->title));
        $this->getRole()->setDescription(trim((string) $role->description));

        $this->logger->info('Current role import id: ' . $this->getRole()->getImportId());
        
        $type = ilObject::_lookupType($this->getRoleFolderId(), true);
        $exp = explode("_", $this->getRole()->getTitle());

        if (count($exp) > 0 && $exp[0] === "il") {
            if (count($exp) > 1 && $exp[1] !== $type) {
                throw new ilRoleImporterException(sprintf(
                    $lng->txt("rbac_cant_import_role_wrong_type"),
                    $lng->txt('obj_' . $exp[1]),
                    $lng->txt('obj_' . $type)
                ));
            }

            $exp[3] = $this->getRoleFolderId();

            $id = ilObjRole::_getIdsForTitle(implode("_", $exp));

            if ($id[0]) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Overwrite role ' . implode("_", $exp));
                $this->getRole()->setId($id[0]);
                $this->getRole()->read();
            }
        }

        // Create or update
        if ($this->getRole()->getId()) {
            $rbacadmin->deleteRolePermission($this->getRole()->getId(), $this->getRoleFolderId());
            $this->getRole()->update();
        } else {
            $this->getRole()->create();
        }

        
        $this->assignToRoleFolder();

        $protected = (string) $role['protected'];
        if ($protected) {
            $rbacadmin->setProtected(0, $this->getRole()->getId(), 'y');
        }

        // Add operations
        $ops = $rbacreview->getOperations();
        $operations = array();
        foreach ($ops as $ope) {
            $operations[$ope['operation']] = $ope['ops_id'];
        }

        foreach ($role->operations as $sxml_operations) {
            foreach ($sxml_operations as $sxml_op) {
                $operation = trim((string) $sxml_op);
                if (!array_key_exists($operation, $operations)) {
                    continue;
                }
                $ops_group = (string) $sxml_op['group'];
                $ops_id = (int) $operations[$operation];
                $ops = trim((string) $sxml_op);
                
                if ($ops_group and $ops_id) {
                    $rbacadmin->setRolePermission(
                        $this->getRole()->getId(),
                        $ops_group,
                        array($ops_id),
                        $this->getRoleFolderId() // #10161
                    );
                } else {
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Cannot create operation for...');
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': New operation for group ' . $ops_group);
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': New operation ' . $ops);
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': New operation ' . $ops_id);
                }
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
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        
        if (!$this->getRoleFolderId()) {
            return;
        }

        if ($rbacreview->isRoleAssignedToObject($this->getRole()->getId(), $this->getRoleFolderId())) {
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
        if ($this->getRole()) {
            return true;
        }

        $this->logger->debug('Searching already imported role by import_id: ' . $import_id);
        $obj_id = 0;
        if ($import_id) {
            $obj_id = ilObject::_lookupObjIdByImportId($import_id);
        }
        $this->logger->debug('Found already imported obj_id: ' . $obj_id);


        if ($obj_id) {
            $this->role = ilObjectFactory::getInstanceByObjId($obj_id, false);
        }
        if (
            (!$this->getRole() instanceof ilObjRole) &&
            (!$this->getRole() instanceof ilObjRoleTemplate)
        ) {
            $this->logger->debug('Creating new role template');
            $this->role = new ilObjRoleTemplate();
        }
        $this->role->setImportId((string) $import_id);
        return true;
    }
    
    protected function parseXmlErrors()
    {
        $errors = '';
        
        foreach (libxml_get_errors() as $err) {
            $errors .= $err->code . '<br/>';
        }
        return $errors;
    }
}
