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
                'component' => 'Services/Container',
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
            // service settings
            $res[] = [
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            ];
        }

        // container pages
        $pg_ids = [];
        $lso_ids = [];
        foreach ($a_ids as $id) {
            $lso_ids[] = (int)$id * ilObjLearningSequence::CP_INTRO;
            $lso_ids[] = (int)$id * ilObjLearningSequence::CP_EXTRO;
        }
        foreach ($lso_ids as $id) {
            if (ilContainerPage::_exists("cont", (int) $id)) {
                $pg_ids[] = "cont:" . $id;
            }
        }

        if (count($pg_ids)) {
            $res[] = [
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids
            ];
        }
        return $res;
    }
}
