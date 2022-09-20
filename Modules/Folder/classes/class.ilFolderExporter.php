<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Folder export
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilFolderExporter extends ilXmlExporter
{
    public function init(): void
    {
    }

    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        // always trigger container because of co-page(s)
        return [
            [
                'component' => 'Services/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            ]
        ];
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        try {
            $writer = null;
            $writer = new ilFolderXmlWriter(false);
            $writer->setObjId((int) $a_id);
            $writer->write();
            return $writer->xmlDumpMem(false);
        } catch (UnexpectedValueException $e) {
            $GLOBALS['ilLog']->write("Caught error: " . $e->getMessage());
            return '';
        }
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "4.1.0" => [
                "namespace" => "https://www.ilias.de/Modules/Folder/fold/4_1",
                "xsd_file" => "ilias_fold_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => ""
            ]
        ];
    }
}
