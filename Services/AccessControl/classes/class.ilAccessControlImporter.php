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
 * role role template xml importer
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilAccessControlImporter extends ilXmlImporter
{
    public function init(): void
    {
    }

    /**
     * Import XML
     * @param
     */
    public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void {
        $role_folder_id = $a_mapping->getMapping('Services/AccessControl', 'rolf', (string) 0);

        $importer = new ilRoleXmlImporter((int) $role_folder_id);
        $importer->setXml($a_xml);
        $importer->setRole(new ilObjRole());
        $importer->import();
    }
}
