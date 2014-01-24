<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCParagraph.php");

/**
* Class ilPCParagraph
*
* Paragraph of ilPageObject
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id: class.ilPCParagraph.php 43464 2013-07-17 09:18:27Z akill $
*
* @ingroup ServicesCOPage
*/
class ilPCSourceCode extends ilPCParagraph
{
	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("src");
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_insert_code", "pc_code");
	}

}
?>
