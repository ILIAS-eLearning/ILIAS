<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilImport.php");

/**
 * Import2 class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolImport2 extends ilImport
{
	/**
	 * Init dataset
	 *
	 * @param
	 * @return
	 */
	function initDataset($a_component, $a_top_entity)
	{
		switch ($a_top_entity)
		{
			case "mob":
				include_once("./Services/MediaObjects/classes/class.ilMediaObjectDataSet.php");
				$this->setCurrentDataset(new ilMediaObjectDataSet()); 
				break;
				
			case "mep":
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolDataSet.php");
				$this->setCurrentDataset(new ilMediaPoolDataSet()); 
				break;
		}	
	}
	
}

?>