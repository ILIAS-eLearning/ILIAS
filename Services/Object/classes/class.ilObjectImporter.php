<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for objects (currently focused on translation information)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesObject
 */
class ilObjectImporter extends ilXmlImporter
{
	private $logger = null;
	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->logger = $GLOBALS['DIC']->logger()->obj();
	}

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Services/Object/classes/class.ilObjectDataSet.php");
		$this->ds = new ilObjectDataSet();
		$this->ds->setDSPrefix("ds");
        $this->ds->setImportDirectory($this->getImportDirectory());
	}


	/**
	 * Import XML
	 *
	 * @param
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
		$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
			$a_xml, $this->ds, $a_mapping);
	}

}

?>