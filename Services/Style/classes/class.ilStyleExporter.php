<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
 * Style export definition
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesStyle
 */
class ilStyleExporter extends ilXmlExporter
{		
	public function init()
	{
		
	}
	
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{	
		include_once "Services/Style/classes/class.ilObjStyleSheet.php";
		$style = new ilObjStyleSheet($a_id, false);
				
		// images
		$target = $this->getAbsoluteExportDirectory();
		if($target && !is_dir($target))
		{
			ilUtil::makeDirParents($target);		
		}
		ilUtil::rCopy($style->getImagesDirectory(), $target);
			
		return "<StyleSheetExport>".
			"<ImagePath>".$this->getRelativeExportDirectory()."</ImagePath>".
			$style->getXML().
			"</StyleSheetExport>";
	}
	
	public function getValidSchemaVersions($a_entity)
	{
		return array (              
				"5.0.0" => array(
                        "namespace" => "http://www.ilias.de/Services/Style/5_0",
                        "xsd_file" => "ilias_style_5_0.xsd",
                        "uses_dataset" => false,
                        "min" => "5.0.0",
                        "max" => "")			
        );
	}
}