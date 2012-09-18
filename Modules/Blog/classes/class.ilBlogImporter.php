<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for blog
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ModulesBlogs
 */
class ilBlogImporter extends ilXmlImporter
{
	protected $ds;
	
	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/Blog/classes/class.ilBlogDataSet.php");
		$this->ds = new ilBlogDataSet();	
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
	
	/**
	 * Final processing
	 *
	 * @param	array		mapping array
	 */
	function finalProcessing($a_mapping)
	{
		$blp_map = $a_mapping->getMappingsOfEntity("Services/COPage", "pg");
		foreach ($blp_map as $blp_id)
		{
			$blp_id = substr($blp_id, 4);
			$blog_id = ilBlogPosting::lookupBlogId($blp_id);
			ilPageObject::_writeParentId("blp", $blp_id, $blog_id);
		}
	}
}

?>