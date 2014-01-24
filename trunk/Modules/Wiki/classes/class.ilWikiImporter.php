<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for wikis
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesWiki
 */
class ilWikiImporter extends ilXmlImporter
{

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/Wiki/classes/class.ilWikiDataSet.php");
		$this->ds = new ilWikiDataSet();
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
		include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
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
		$wpg_map = $a_mapping->getMappingsOfEntity("Modules/Wiki", "wpg");

		include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
		foreach ($wpg_map as $wpg_id)
		{
			$wiki_id = ilWikiPage::lookupWikiId($wpg_id);
			ilWikiPage::_writeParentId("wpg", $wpg_id, $wiki_id);
		}
	}

}

?>