<?php

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

declare(strict_types=1);

class ilLearningSequenceExporter extends ilXmlExporter
{
    protected ilSetting $settings;

    public function init(): void
    {
        global $DIC;
        $this->settings = $DIC["ilSetting"];
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $writer = $this->getWriter((int) $a_id);
        $writer->start();

        return $writer->getXml();
    }

    protected function getWriter(int $obj_id): ilLearningSequenceXMLWriter
    {
        if ($type = ilObject::_lookupType($obj_id) != "lso") {
            throw new Exception("Wrong type " . $type . " for lso export.");
        }

        $ref_ids = ilObject::_getAllReferences($obj_id);
        $ls_ref_id = end($ref_ids);

        /** @var ilObjLearningSequence $ls_object */
        $ls_object = ilObjectFactory::getInstanceByRefId($ls_ref_id, false);
        if (!$ls_object) {
            throw new Exception("Object for ref id " . $ls_ref_id . " not found.");
        }

        $lp_settings = new ilLPObjSettings($obj_id);

        return new ilLearningSequenceXMLWriter(
            $ls_object,
            $this->settings,
            $lp_settings
        );
    }

    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "9.0.0" => [
                "namespace" => "http://www.ilias.de/Modules/LearningSequence/lso/9_0",
                "xsd_file" => "ilias_lso_9_0.xsd",
                "uses_dataset" => false,
                "min" => "9.0",
                "max" => ""
            ]
        ];
    }

    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        return [
            [
                'component' => 'components/ILIAS/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        $res = [];
        if ($a_entity == "lso") {
            // advanced metadata
            $advmd_ids = [];
            foreach ($a_ids as $id) {
                $rec_ids = $this->getActiveAdvMDRecords((int) $id);
                foreach ($rec_ids as $rec_id) {
                    $advmd_ids[] = $id . ":" . $rec_id;
                }
            }
            if ($advmd_ids !== []) {
                $res[] = [
                    "component" => "components/ILIAS/AdvancedMetaData",
                    "entity" => "advmd",
                    "ids" => $advmd_ids
                ];
            }

            // service settings
            $res[] = [
                "component" => "components/ILIAS/ILIASObject",
                "entity" => "common",
                "ids" => $a_ids
            ];

            // metadata
            $md_ids = [];
            foreach ($a_ids as $id) {
                $md_ids[] = $id . ":0:lso";
            }
            $res[] = [
                "component" => "components/ILIAS/MetaData",
                "entity" => "md",
                "ids" => $md_ids
            ];

            // taxonomies
            $tax_ids = [];
            foreach ($a_ids as $id) {
                $t_ids = ilObjTaxonomy::getUsageOfObject((int) $id);
                foreach ($t_ids as $t_id) {
                    $tax_ids[$t_id] = $t_id;
                }
            }
            if ($tax_ids !== []) {
                $res[] = [
                    "component" => "components/ILIAS/Taxonomy",
                    "entity" => "tax",
                    "ids" => $tax_ids
                ];
            }
        }

        // container pages
        foreach ($a_ids as $id) {
            if (ilContainerPage::_exists(LSOPageType::INTRO->value, (int) $id)) {
                $res[] = [
                    "component" => "components/ILIAS/COPage",
                    "entity" => "pg",
                    "ids" => [LSOPageType::INTRO->value . ":" . $id]
                ];
            }

            if (ilContainerPage::_exists(LSOPageType::EXTRO->value, (int) $id)) {
                $res[] = [
                    "component" => "components/ILIAS/COPage",
                    "entity" => "pg",
                    "ids" => [LSOPageType::EXTRO->value . ":" . $id]
                ];
            }
        }

        return $res;
    }

    protected function getActiveAdvMDRecords(int $a_id): array
    {
        $active = [];

        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType('lso') as $record_obj) {
            foreach ($record_obj->getAssignedObjectTypes() as $obj_info) {
                if ($obj_info['obj_type'] === 'lso' && (int) $obj_info['optional'] === 0) {
                    $active[] = $record_obj->getRecordId();
                }

                // local activation
                if (
                    $obj_info['obj_type'] === 'lso' &&
                    (int) $obj_info['optional'] === 1 &&
                    $a_id === $record_obj->getParentObject()
                ) {
                    $active[] = $record_obj->getRecordId();
                }
            }
        }

        return $active;
    }
}
