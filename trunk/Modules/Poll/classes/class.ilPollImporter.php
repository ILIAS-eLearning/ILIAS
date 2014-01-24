<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for poll
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ModulesPoll
 */
class ilPollImporter extends ilXmlImporter
{
	protected $ds;
	
	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/Poll/classes/class.ilPollDataSet.php");
		$this->ds = new ilPollDataSet();	
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
		include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
		$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
			$a_xml, $this->ds, $a_mapping);
	}
}

?>