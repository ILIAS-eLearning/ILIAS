<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ServicesLanguage 
*/
class ilLanguageFactory
{
	private static $languages = array();
	
	/**
	 * Get langauge object
	 *
	 * @access public
	 * @static
	 * @param string $a_lang_key
	 * @return ilLanguage
	 */
	public static function _getLanguage($a_lang_key = '')
	{
		global $lng;
		
		if(!$a_lang_key)
		{
			if(is_object($lng))
			{
				$a_lang_key = $lng->getDefaultLanguage();	
			}
			else
			{
				$a_lang_key = 'en';
			}
		}
		if(isset(self::$languages[$a_lang_key]))
		{
			return self::$languages[$a_lang_key];
		}
		return self::$languages[$a_lang_key] = new ilLanguage($a_lang_key);
	}
	
	/**
	 * Get language object of user
	 * @param int $a_usr_id
	 * @return ilLanguage
	 * @static
	 */
	public static function _getLanguageOfUser($a_usr_id)
	{
		return self::_getLanguage(ilObjUser::_lookupLanguage($a_usr_id));
	}
}


?>