<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for user data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserImporter extends ilXmlImporter
{

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Services/User/classes/class.ilUserDataSet.php");
		$this->ds = new ilUserDataSet();
		$this->ds->setDSPrefix("ds");
		$this->ds->setImportDirectory($this->getImportDirectory());
	}


	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
		$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
			$a_xml, $this->ds, $a_mapping);
	}

	
}

?>