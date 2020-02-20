<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for html learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesHTMLLearningModule
 */
class ilHTMLLearningModuleImporter extends ilXmlImporter
{

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Modules/HTMLLearningModule/classes/class.ilHTMLLearningModuleDataSet.php");
        $this->ds = new ilHTMLLearningModuleDataSet();
        $this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
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
}
