<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesStyle
 */
class ilSystemStyleSettings
{
	/**
	 * SYSTEM
	 * lookup if a style is activated
	 */
	static function _lookupActivatedStyle($a_skin, $a_style)
	{
		global $ilDB;

		$q = "SELECT count(*) cnt FROM settings_deactivated_s".
			" WHERE skin = ".$ilDB->quote($a_skin, "text").
			" AND style = ".$ilDB->quote($a_style, "text")." ";

		$cnt_set = $ilDB->query($q);
		$cnt_rec = $ilDB->fetchAssoc($cnt_set);

		if ($cnt_rec["cnt"] > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * * SYSTEM
	 * deactivate style
	 */
	static function _deactivateStyle($a_skin, $a_style)
	{
		global $ilDB;

		ilSystemStyleSettings::_activateStyle($a_skin, $a_style);
		$q = "INSERT into settings_deactivated_s".
			" (skin, style) VALUES ".
			" (".$ilDB->quote($a_skin, "text").",".
			" ".$ilDB->quote($a_style, "text").")";

		$ilDB->manipulate($q);
	}

	/**
	 * * SYSTEM
	 * activate style
	 */
	static function _activateStyle($a_skin, $a_style)
	{
		global $ilDB;

		$q = "DELETE FROM settings_deactivated_s".
			" WHERE skin = ".$ilDB->quote($a_skin, "text").
			" AND style = ".$ilDB->quote($a_style, "text");

		$ilDB->manipulate($q);
	}

}

?>