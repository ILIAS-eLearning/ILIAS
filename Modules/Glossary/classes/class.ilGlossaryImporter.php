<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for files
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesGlossary
 */
class ilGlossaryImporter extends ilXmlImporter
{
	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		if ($a_entity == "glo")
		{
			// case i container
			if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_id))
			{
				$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
	
				$xml_file = $this->getImportDirectory().'/'.basename($this->getImportDirectory()).'.xml';
				$GLOBALS['ilLog']->write(__METHOD__.': Using XML file '.$xml_file);
	
			}
			else if ($new_id = $a_mapping->getMapping('Modules/Glossary','glo', "new_id"))	// this mapping is only set by ilObjGlossaryGUI
			{
				$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
	
				$xml_file = $this->getImportDirectory().'/'.basename($this->getImportDirectory()).'.xml';
				$GLOBALS['ilLog']->write(__METHOD__.': Using XML file '.$xml_file);
			}
			else
			{
				// Shouldn't happen
				$GLOBALS['ilLog']->write(__METHOD__.': Called in non container mode');
				$GLOBALS['ilLog']->logStack();
				return false;
			}
	
			if(!file_exists($xml_file))
			{
				$GLOBALS['ilLog']->write(__METHOD__.': ERROR Cannot find '.$xml_file);
				return false;
			}
	
			include_once './Modules/LearningModule/classes/class.ilContObjParser.php';
			$contParser = new ilContObjParser(
				$newObj, 
				$xml_file,
				basename($this->getImportDirectory())
			);
			
			$contParser->startParsing();
			ilObject::_writeImportId($newObj->getId(), $newObj->getImportId());
			
			// write term map for taxonomies to mapping object
			$term_map = $contParser->getGlossaryTermMap();
			foreach ($term_map as $k => $v)
			{
				$a_mapping->addMapping("Services/Taxonomy", "tax_item",
						"glo:term:".$k, $v);

				// this is since 4.3 does not export these ids but 4.4 tax node assignment needs it
				$a_mapping->addMapping("Services/Taxonomy", "tax_item_obj_id",
					"glo:term:".$k, $newObj->getId());

			}

			$a_mapping->addMapping("Services/Taxonomy", "tax_item",
				"glo:term:".$k, $v);

			$a_mapping->addMapping("Modules/Glossary", "glo", $a_id, $newObj->getId());
			
			$this->current_glo = $newObj;
		}
	}
	
	/**
	 * Final processing
	 *
	 * @param
	 * @return
	 */
	function finalProcessing($a_mapping)
	{
//echo "<pre>".print_r($a_mapping, true)."</pre>"; exit;
		// get all glossaries of the import
		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		$maps = $a_mapping->getMappingsOfEntity("Modules/Glossary", "glo");
		foreach ($maps as $old => $new)
		{
			if ($old != "new_id" && (int) $old > 0)
			{
				// get all new taxonomys of this object
				$new_tax_ids = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $old);
				$tax_ids = explode(":", $new_tax_ids);
				foreach ($tax_ids as $tid)
				{
					ilObjTaxonomy::saveUsage($tid, $new);
				}
			}
		}
	}
	
}
?>