<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilImportConfig.php");
/**
 * Import configuration for glossaries
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesGlossary
 */
class ilGlossaryImportConfig extends ilImportConfig
{
	protected $pre_51_import = false;

	/**
	 * Set pre 51 import
	 *
	 * @param boolean $a_val import file is lower than 5.1	
	 */
	function setPre51Import($a_val)
	{
		$this->pre_51_import = $a_val;
	}
	
	/**
	 * Get pre 51 import
	 *
	 * @return boolean import file is lower than 5.1
	 */
	function getPre51Import()
	{
		return $this->pre_51_import;
	}
}

?>