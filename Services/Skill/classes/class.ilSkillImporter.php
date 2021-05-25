<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Importer class for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillImporter extends ilXmlImporter
{
    /**
     * @var object
     */
    protected $ds;

    /**
     * Initialisation
     */
    public function init()
    {
        $this->ds = new ilSkillDataSet();
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Import XML
     *
     * @param $a_entity
     * @param $a_id
     * @param $a_xml
     * @param $a_mapping
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }

    /**
     * Final processing
     *
     * @param	array		mapping array
     */
    public function finalProcessing($a_mapping)
    {
        //$pg_map = $a_mapping->getMappingsOfEntity("Modules/MediaPool", "pg");
    }
}
