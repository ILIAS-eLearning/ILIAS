<?php

declare(strict_types=1);

/**
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilLearningSequenceExporter extends ilXmlExporter
{
    public function init()
    {
        global $DIC;

        $this->settings = $DIC["ilSetting"];
        $this->rbac_review = $DIC["rbacreview"];
    }

    public function getXmlRepresentation($entity, $target_release, $obj_id)
    {
        $writer = $this->getWriter((int) $obj_id);
        $writer->start();

        return $writer->getXml();
    }

    protected function getWriter(int $obj_id) : ilLearningSequenceXMLWriter
    {
        if ($type = ilObject::_lookupType($obj_id) != "lso") {
            throw new Exception("Wrong type " . $type . " for lso export.");
        }

        $ls_ref_id = end(ilObject::_getAllReferences($obj_id));
        $ls_object = ilObjectFactory::getInstanceByRefId($ls_ref_id, false);
        $lp_settings = new ilLPObjSettings($obj_id);

        return new ilLearningSequenceXMLWriter(
            $ls_object,
            $this->settings,
            $lp_settings,
            $this->rbac_review
        );
    }

    public function getValidSchemaVersions($entity)
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

    public function getXmlExportHeadDependencies($entity, $target_release, $ids)
    {
        return array(
            array(
                'component' => 'Services/Container',
                'entity' => 'struct',
                'ids' => $ids
            )
        );
    }
}
