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
	function init()
	{
		$this->ds = new ilPortfolioDataSet();
		$this->ds->setDSPrefix("ds");		
	}

	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		$this->ds->setImportDirectory($this->getImportDirectory());
		$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
			$a_xml, $this->ds, $a_mapping);
	}
	
	/**
	 * Final processing
	 *
	 * @param	array		mapping array
	 */
	function finalProcessing($a_mapping)
	{
		$prttpg_map = $a_mapping->getMappingsOfEntity("Services/COPage", "pg");
		foreach ($prttpg_map as $prttpg_id)
		{
			$prttpg_id = substr($prttpg_id, 5);
			$prtt_id = ilPortfolioTemplatePage::findPortfolioForPage($prttpg_id);
			ilPortfolioTemplatePage::_writeParentId("prtt", $prttpg_id, $prtt_id);
		}
	}
}