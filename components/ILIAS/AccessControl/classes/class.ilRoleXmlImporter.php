<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Description of class
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilRoleXmlImporter
{
    protected int $role_folder = 0;
    protected ?ilObject $role = null;

    protected string $xml = '';

    private ilLogger $logger;
    private ilRbacAdmin $rbacadmin;
    private ilRbacReview $rbacreview;
    private ilLanguage $language;

    /**
     * Constructor
     */
    public function __construct(int $a_role_folder_id = 0)
    {
        global $DIC;

        $this->logger = $DIC->logger()->ac();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->language = $DIC->language();

        $this->role_folder = $a_role_folder_id;
    }

    public function setXml(string $a_xml): void
    {
        $this->xml = $a_xml;
    }

    public function getXml(): string
    {
        return $this->xml;
    }

    public function getRoleFolderId(): int
    {
        return $this->role_folder;
    }

    public function getRole(): ?ilObject
    {
        return $this->role;
    }

    public function setRole(ilObject $role): void
    {
        $this->role = $role;
    }

    /**
     * import role | role templatae
     * @throws ilRoleImporterException
     */
    public function import(): void
    {
        $use_internal_errors = libxml_use_internal_errors(true);
        $root = simplexml_load_string($this->getXml());
        libxml_use_internal_errors($use_internal_errors);

        if (!$root instanceof SimpleXMLElement) {
            throw new ilRoleImporterException($this->parseXmlErrors());
        }
        foreach ($root->role as $roleElement) {
            $this->importSimpleXml($roleElement);
            // only one role is parsed
            break;
        }
    }

    public function importSimpleXml(SimpleXMLElement $role): int
    {
        $import_id = (string) $role['id'];
        $this->logger->info('Importing role with import_id: ' . $import_id);
        $this->initRole($import_id);
        $this->getRole()->setTitle(trim((string) $role->title));
        $this->getRole()->setDescription(trim((string) $role->description));

        $this->logger->info('Current role import id: ' . $this->getRole()->getImportId());

        $type = ilObject::_lookupType($this->getRoleFolderId(), true);
        $exp = explode("_", $this->getRole()->getTitle());

        if (count($exp) > 0 && $exp[0] === "il") {
            if (count($exp) > 1 && $exp[1] !== $type) {
                throw new ilRoleImporterException(sprintf(
                    $this->language->txt("rbac_cant_import_role_wrong_type"),
                    $this->language->txt('obj_' . $exp[1]),
                    $this->language->txt('obj_' . $type)
                ));
            }

            $exp[3] = $this->getRoleFolderId();

            $id = ilObjRole::_getIdsForTitle(implode("_", $exp));

            if ($id[0]) {
                $this->getRole()->setId($id[0]);
                $this->getRole()->read();
            }
        }

        // Create or update
        if ($this->getRole()->getId()) {
            $this->rbacadmin->deleteRolePermission($this->getRole()->getId(), $this->getRoleFolderId());
            $this->getRole()->update();
        } else {
            $this->getRole()->create();
        }

        $this->assignToRoleFolder();

        $protected = (string) $role['protected'];
        if ($protected) {
            $this->rbacadmin->setProtected(0, $this->getRole()->getId(), 'y');
        }

        // Add operations
        $ops = $this->rbacreview->getOperations();
        $operations = array();
        foreach ($ops as $ope) {
            $operations[$ope['operation']] = $ope['ops_id'];
        }

        foreach ($role->operations as $sxml_operations) {
            foreach ($sxml_operations as $sxml_op) {
                $ops_group = (string) $sxml_op['group'];
                $ops_id = (int) $operations[trim((string) $sxml_op)];
                $ops = trim((string) $sxml_op);

                if ($ops_group && $ops_id) {
                    $this->rbacadmin->setRolePermission(
                        $this->getRole()->getId(),
                        $ops_group,
                        array($ops_id),
                        $this->getRoleFolderId() // #10161
                    );
                }
            }
        }
        return $this->getRole()->getId();
    }

    protected function assignToRoleFolder(): void
    {
        if (!$this->getRoleFolderId()) {
            return;
        }

        if ($this->rbacreview->isRoleAssignedToObject($this->getRole()->getId(), $this->getRoleFolderId())) {
            return;
        }

        $this->rbacadmin->assignRoleToFolder(
            $this->getRole()->getId(),
            $this->getRoleFolderId(),
            $this->getRole() instanceof ilObjRole ? 'y' : 'n'
        );
    }

    protected function initRole(string $import_id): void
    {
        if ($this->getRole()) {
            return;
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
        $this->role->setImportId($import_id);
    }

    protected function parseXmlErrors(): string
    {
        $errors = '';

        foreach (libxml_get_errors() as $err) {
            $errors .= $err->code . '<br/>';
        }
        return $errors;
    }
}
