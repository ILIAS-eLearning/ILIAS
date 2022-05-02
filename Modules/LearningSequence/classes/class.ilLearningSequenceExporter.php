<?php declare(strict_types=1);

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
 
class ilLearningSequenceExporter extends ilXmlExporter
{
    protected ilSetting $settings;
    protected ilRbacReview $rbac_review;

    public function init() : void
    {
        global $DIC;

        $this->settings = $DIC["ilSetting"];
        $this->rbac_review = $DIC["rbacreview"];
    }

    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $writer = $this->getWriter((int) $a_id);
        $writer->start();

        return $writer->getXml();
    }

    protected function getWriter(int $obj_id) : ilLearningSequenceXMLWriter
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
            $lp_settings,
            $this->rbac_review
        );
    }

    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "5.4.0" => array(
                "namespace" => "http://www.ilias.de/Modules/LearningSequence/lso/5_4",
                "xsd_file" => "ilias_lso_5_4.xsd",
                "uses_dataset" => false,
                "min" => "5.4.0",
                "max" => ""
            )
        );
    }

    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        return array(
            array(
                'component' => 'Services/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $res = [];

        if ($a_entity == "lso") {
            // service settings
            $res[] = array(
                "component" => "Services/Object",
                "entity" => "common",
                "ids" => $a_ids
            );
        }

        return $res;
    }
}
