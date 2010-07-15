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
		include_once("./Services/Export/classes/class.ilManifestParser.php");
		$parser = new ilManifestParser($dir."/manifest.xml");
		
		$first = true;
		
		// Handling single folders with subitems
		if(!$parser->getExportSets())
		{
			$new_id = parent::doImportObject($dir,$type);
			if($newObj = ilObjectFactory::getInstanceByObjId($new_id,false))
			{
				$newObj->createReference();
				$newObj->putInTree($this->getMapping()->getTargetId());
				$newObj->setPermissions($this->getMapping()->getTargetId());
				return $new_id;
			}
		}
		
		foreach($parser->getExportSets() as $set)
		{
			$GLOBALS['ilLog']->write(__METHOD__.': do import with dir '.$dir.DIRECTORY_SEPARATOR.$set['path'].' and type '.$set['type']);
			
			$new_id = parent::doImportObject($dir.DIRECTORY_SEPARATOR.$set['path'],$set['type']);
			
			if($first)
			{
				$ret = $new_id;
			}
		}
		return $ret;
	}
}
?>