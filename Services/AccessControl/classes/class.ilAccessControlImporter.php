<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * role role template xml importer
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilAccessControlImporter extends ilXmlImporter
{
    public function init() : void
    {
    }

    /**
     * Import XML
     * @param
     * @return void
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ) : void {
        $role_folder_id = $a_mapping->getMapping('Services/AccessControl', 'rolf', (string) 0);

        $importer = new ilRoleXmlImporter((int) $role_folder_id);
        $importer->setXml($a_xml);
        $importer->setRole(new ilObjRole());
        $importer->import();
    }
}
