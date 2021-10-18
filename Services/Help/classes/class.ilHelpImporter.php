<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Importer class for help
 *
 * @author Alex Killing <alex.killing@gmx.de>
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
    public function init() : void
    {
        $this->ds = new ilHelpDataSet();
        $this->ds->setDSPrefix("ds");

        $this->config = $this->getImport()->getConfig("Services/Help");
        $module_id = $this->config->getModuleId();
        if ($module_id > 0) {
            $this->getImport()->getMapping()->addMapping('Services/Help', 'help_module', 0, $module_id);
        }
    }


    /**
     * Import XML
     * @param
     * @return void
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $parser = new ilDataSetImportParser(
            $a_entity,
            $this->getSchemaVersion(),
            $a_xml,
            $this->ds,
            $a_mapping
        );
    }
}
