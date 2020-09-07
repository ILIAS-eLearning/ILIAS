<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for help
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ServicesHelp
 */
class ilHelpImporter extends ilXmlImporter
{
    /**
     * ilHelpImporterConfig
     */
    protected $config = null;

    /**
     * Initialisation
     */
    public function init()
    {
        include_once("./Services/Help/classes/class.ilHelpDataSet.php");
        $this->ds = new ilHelpDataSet();
        $this->ds->setDSPrefix("ds");

        $this->config = $this->getImport()->getConfig("Services/Help");
        $module_id = $this->config->getModuleId();
        if ($module_id > 0) {
            include_once("./Services/Export/classes/class.ilImport.php");
            $this->getImport()->getMapping()->addMapping('Services/Help', 'help_module', 0, $module_id);
            /* not needed anymore, we now get mapping from learning module
            include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
            $chaps = ilLMObject::getObjectList($this->getId(), "st");
            foreach ($chaps as $chap)
            {
                $chap_arr = explode("_", $chap["import_id"]);
                $imp->getMapping()->addMapping('Services/Help', 'help_chap',
                    $chap_arr[count($chap_arr) - 1], $chap["obj_id"]);
            }*/
        }
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
