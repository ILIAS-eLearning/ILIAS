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
 * Role Exporter
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAccessControl
 */
class ilAccessControlExporter extends ilXmlExporter
{
    public function init(): void
    {
    }

    /**
     * Get head dependencies
     * @param string        entity
     * @param string        target release
     * @param array        ids
     * @return        array        array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        return [];
    }

    /**
     * Get xml
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        global $DIC;

        $writer = new ilRoleXmlExport();

        $eo = ilExportOptions::getInstance();
        $eo->read();

        $rolf = $eo->getOptionByObjId((int) $a_id, ilExportOptions::KEY_ROOT);
        $writer->setRoles(array((int) $a_id => (int) $rolf));
        $writer->write();
        return $writer->xmlDumpMem(false);
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/AccessControl/Role/role/4_3",
                "xsd_file" => "ilias_role_4_3.xsd",
                "uses_dataset" => false,
                "min" => "4.3.0",
                "max" => ""
            )
        );
    }
}
