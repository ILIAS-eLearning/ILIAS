<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */



/**
* Folder export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesBooking
*/
class ilGroupExporter extends ilXmlExporter
{
    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->grp();
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids): array
    {
        // always trigger container because of co-page(s)
        return array(
            array(
                'component' => 'components/ILIAS/Container',
                'entity' => 'struct',
                'ids' => $a_ids
            )
        );
    }

    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $deps = [];
        $advmd_ids = [];
        foreach ($a_ids as $id) {
            $rec_ids = $this->getActiveAdvMDRecords($id);
            foreach ($rec_ids as $rec_id) {
                $advmd_ids[] = $id . ":" . $rec_id;
            }
        }

        if ($advmd_ids !== []) {
            $deps[] = [
                "component" => "components/ILIAS/AdvancedMetaData",
                "entity" => "advmd",
                "ids" => $advmd_ids
            ];
        }

        $md_ids = [];
        foreach ($a_ids as $grp_id) {
            $md_ids[] = $grp_id . ":0:grp";
        }
        if ($md_ids !== []) {
            $deps[] =
                array(
                    "component" => "components/ILIAS/MetaData",
                    "entity" => "md",
                    "ids" => $md_ids
                );
        }
        return $deps;
    }

    /**
     * @inheritDoc
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id): string
    {
        $refs = ilObject::_getAllReferences((int) $a_id);
        $group_ref_id = end($refs);
        $group = ilObjectFactory::getInstanceByRefId($group_ref_id, false);

        if (!$group instanceof ilObjGroup) {
            $this->logger->warning($a_id . ' is not instance of type group');
            return '';
        }

        $writer = new ilGroupXMLWriter($group);
        $writer->setMode(ilGroupXMLWriter::MODE_EXPORT);
        $writer->start();
        return $writer->getXML();
    }

    protected function getActiveAdvMDRecords(int $a_id): array
    {
        $active = [];

        foreach (ilAdvancedMDRecord::_getActivatedRecordsByObjectType('grp') as $record_obj) {
            foreach ($record_obj->getAssignedObjectTypes() as $obj_info) {
                if ($obj_info['obj_type'] == 'grp' && $obj_info['optional'] == 0) {
                    $active[] = $record_obj->getRecordId();
                }
                // local activation
                if (
                    $obj_info['obj_type'] == 'grp' &&
                    $obj_info['optional'] == 1 &&
                    $a_id == $record_obj->getParentObject()
                ) {
                    $active[] = $record_obj->getRecordId();
                }
            }
        }
        return $active;
    }


    /**
     * @inheritDoc
     */
    public function getValidSchemaVersions(string $a_entity): array
    {
        return [
            "9.0" => [
                "namespace" => 'http://www.ilias.de/Modules/Group/grp/9',
                "xsd_file" => 'ilias_grp_9_0.xsd',
                "uses_dataset" => false,
                "min" => "9.0",
                "max" => "9.99"
            ],
            "4.1.0" => [
                "namespace" => "http://www.ilias.de/Modules/Group/grp/4_1",
                "xsd_file" => "ilias_grp_4_1.xsd",
                "uses_dataset" => false,
                "min" => "4.1.0",
                "max" => "4.4.999"
            ],
            "5.0.0" => [
                "namespace" => "http://www.ilias.de/Modules/Group/grp/5_0",
                "xsd_file" => "ilias_grp_5_0.xsd",
                "uses_dataset" => false,
                "min" => "5.0.0",
                "max" => ""
            ]
        ];
    }
}
