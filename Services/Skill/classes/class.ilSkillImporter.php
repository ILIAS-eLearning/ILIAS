<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesSkill
 */
class ilSkillImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Services/Skill/classes/class.ilSkillDataSet.php");
        $this->ds = new ilSkillDataSet();
        $this->ds->setDSPrefix("ds");

        /*$this->config = $this->getImport()->getConfig("Modules/MediaPool");
        if ($this->config->getTranslationImportMode())
        {
            $this->ds->setTranslationImportMode(
                $this->config->getTranslationLM(),
                $this->config->getTranslationLang());
            $cop_config = $this->getImport()->getConfig("Services/COPage");
            $cop_config->setUpdateIfExists(true);
            $cop_config->setForceLanguage($this->config->getTranslationLang());
            $cop_config->setReuseOriginallyExportedMedia(true);
            $cop_config->setSkipInternalLinkResolve(true);

            $mob_config = $this->getImport()->getConfig("Services/MediaObjects");
            $mob_config->setUsePreviousImportIds(true);
        }*/
    }


    /**
     * Import XML
     *
     * @param
     * @return
     */
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
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
