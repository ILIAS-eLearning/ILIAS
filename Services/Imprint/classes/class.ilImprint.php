<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilImprint
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @ingroup ModulesImprint
*/
class ilImprint extends ilPageObject
{
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "impr";
	}	

	public static function isActive()
	{
		return self::_lookupActive(1, "impr");
	}
}

?>