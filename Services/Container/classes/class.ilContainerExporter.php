<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Container/classes/class.ilContainerXmlWriter.php';
include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
* container structure export
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesContainer
*/
class ilContainerExporter extends ilXmlExporter
{
	private $writer = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
			
	}
	
	/**
	 * Init export
	 * @return 
	 */
	public function init()
	{
	}
	
	public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{		
		if($a_entity != 'struct')
		{
			return;
		}
		
		
		$res = array();
		
		// pages
		
		$pg_ids = array();
		
		// container pages
		include_once("./Services/Container/classes/class.ilContainerPage.php");		
		foreach($a_ids as $id)
		{
			if(ilContainerPage::_exists("cont", $id))
			{
				$pg_ids[] = "cont:".$id;
			}
		}
		
		// container start objects pages
		include_once("./Services/Container/classes/class.ilContainerStartObjectsPage.php");		
		foreach($a_ids as $id)
		{
			if(ilContainerStartObjectsPage::_exists("cstr", $id))
			{
				$pg_ids[] = "cstr:".$id;
			}
		}
		
		if(sizeof($pg_ids))
		{
			$res[] = array(
				"component" => "Services/COPage",
				"entity" => "pg",
				"ids" => $pg_ids
			);
		}
		
		// style
		$style_ids = array();
		foreach($a_ids as $id)
		{
			include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
			$style_id = ilObjStyleSheet::lookupObjectStyle($id);
			// see #24888
			$style_id = ilObjStyleSheet::getEffectiveContentStyleId($style_id);
			if($style_id > 0)
			{
				$style_ids[] = $style_id;				
			}	
		}
		if(sizeof($style_ids))
		{
			$res[] = array(
				"component" => "Services/Style",
				"entity" => "sty",
				"ids" => $style_ids
			);
		}

        // service settings
        $res[] = array(
            "component" => "Services/Object",
            "entity" => "common",
            "ids" => $a_ids);

        return $res;
	}
	
	/**
	 * Get xml
	 * @param object $a_entity
	 * @param object $a_schema_version
	 * @param object $a_id
	 * @return 
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		global $DIC;

		$log = $DIC->logger()->root();
		if($a_entity == 'struct')
		{
			$log->debug(__METHOD__.': Received id = '.$a_id);
			$writer = new ilContainerXmlWriter(end(ilObject::_getAllReferences($a_id)));
			$writer->write();
			return $writer->xmlDumpMem(false);
		}
	}
	
	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	public function getValidSchemaVersions($a_entity)
	{
		return array (
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Folder/fold/4_1",
				"xsd_file" => "ilias_fold_4_1.xsd",
				"uses_dataset" => false,
				"min" => "4.1.0",
				"max" => "")
		);
	}
}
?>