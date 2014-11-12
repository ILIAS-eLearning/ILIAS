<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for style
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ServicesStyle
 */
class ilStyleImporter extends ilXmlImporter
{	
	function init()
	{
			
	}

	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{				
		// see ilStyleExporter::getXmlRepresentation()
		if(preg_match("/<StyleSheetExport><ImagePath>(.+)<\/ImagePath>/", $a_xml, $hits))
		{
			$path = $hits[1];
			$a_xml = str_replace($hits[0], "", $a_xml);
			$a_xml = str_replace("</StyleSheetExport>", "", $a_xml);
		}
		
		// temp xml-file
		$tmp_file = $this->getImportDirectory()."/sty_".$a_id.".xml";
		file_put_contents($tmp_file, $a_xml);	
				
		include_once "./Services/Style/classes/class.ilObjStyleSheet.php";
		$style = new ilObjStyleSheet();
		$style->createFromXMLFile($tmp_file);
		$new_id = $style->getId();
		
		unlink($tmp_file);
		
		// images
		if($path)
		{
			$source = $this->getImportDirectory()."/".$path;
			if(is_dir($source))
			{						
				$target = $style->getImagesDirectory();
				if(!is_dir($target))
				{
					ilUtil::makeDirParents($target);		
				}			
				ilUtil::rCopy($source, $target);
			}				
		}
		
		$a_mapping->addMapping("Services/Style", "sty", $a_id, $new_id);
	}
}
