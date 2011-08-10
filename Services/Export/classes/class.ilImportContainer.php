<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilImport.php';

/**
 * Import class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesExport
 */
class ilImportContainer extends ilImport
{
	/**
	 * Constructor
	 * @param int $a_target_id Id of parent node
	 * @return 
	 */
	public function __construct($a_target_id)
	{
		parent::__construct($a_target_id);
	}


	/**
	 * Import a container
	 * @param object $dir
	 * @param object $type
	 * @return 
	 */
	protected function doImportObject($dir, $type)
	{
		$manifest_file = $dir."/manifest.xml";
		if(!file_exists($manifest_file))
		{
			return false;
		}
		
		include_once("./Services/Export/classes/class.ilManifestParser.php");
		$parser = new ilManifestParser($manifest_file);		
		
		// Handling single containers without subitems
		if(!$parser->getExportSets())
		{
			$this->createDummy($type);
			$new_id = parent::doImportObject($dir,$type);
			return $new_id;
		}
		
		// Handling containers with subitems
		$first = true;
		foreach($parser->getExportSets() as $set)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': do import with dir '.$dir.DIRECTORY_SEPARATOR.$set['path'].' and type '.$set['type']);
			$new_id = parent::doImportObject($dir.DIRECTORY_SEPARATOR.$set['path'],$set['type']);
			
			if($first)
			{
				$ret = $new_id;
				$first = false;
			}
		}
		return $ret;
	}
	
	/**
	 * Create dummy object
	 * @param object $a_type
	 * @return 
	 */
	protected function createDummy($a_type)
	{
		global $objDefinition;

		$class_name = "ilObj".$objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);

		include_once($location."/class.".$class_name.".php");
		$new = new $class_name();
		$new->setTitle('Import');
		$new->create(true);
		$new->createReference();
		$new->putInTree($this->getMapping()->getTargetId());
		$new->setPermissions($this->getMapping()->getTargetId());
		
		$this->getMapping()->addMapping('Services/Container','objs', 0, $new->getId());
		$this->getMapping()->addMapping('Services/Container','refs', 0,$new->getRefId());
		
		return $new;
		
	}
}
?>