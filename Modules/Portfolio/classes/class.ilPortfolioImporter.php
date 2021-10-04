<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Importer class for portfolio
 *
 * Only for portfolio templates!
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioImporter extends ilXmlImporter
{
    protected $ds;
    
    /**
     * Initialisation
     */
    public function init() : void
    {
        $this->ds = new ilPortfolioDataSet();
        $this->ds->setDSPrefix("ds");
    }

    /**
     * Import XML
     * @param
     * @return void
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        $this->ds->setImportDirectory($this->getImportDirectory());
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
    public function finalProcessing(ilImportMapping $a_mapping) : void
    {
        $prttpg_map = $a_mapping->getMappingsOfEntity("Services/COPage", "pg");
        foreach ($prttpg_map as $prttpg_id) {
            $prttpg_id = substr($prttpg_id, 5);
            $prtt_id = ilPortfolioTemplatePage::findPortfolioForPage($prttpg_id);
            ilPortfolioTemplatePage::_writeParentId("prtt", $prttpg_id, $prtt_id);
        }
    }
}
