<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

define('IL_INTERNAL_LINK_SCRIPT','goto.php');


/**
* Class for creating internal links on e.g repostory items.
* This class uses goto.php to create permanent links
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/
class ilLink
{
	public function _getLink($a_ref_id,$a_type = '',$a_params = array(), $append = "")
	{
		global $ilObjDataCache;

		if(!strlen($a_type))
		{
			$a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
		}
		if(count($a_params))
		{
			$param_string = '&';
			foreach($a_params as $name => $value)
			{
				$param_string = '&'.$name.'='.$value;
			}
		}
		else
		{
			$param_string = '';
		}

		switch($a_type)
		{
			case 'git':
			
			default:
				return ILIAS_HTTP_PATH.'/'.IL_INTERNAL_LINK_SCRIPT.'?target='.$a_type.'_'.$a_ref_id.$append.'&client_id='.CLIENT_ID.$param_string;
		}
	}

	/**
	 * Get static link
	 *
	 * @access public
	 * @static
	 *
	 * @param int reference id
	 * @param string object type
	 * @param bool fallback to goto.php if robots are disabled
	 * @return string goto.html or goto.php link
	 */
	public static function _getStaticLink($a_ref_id,$a_type = '',$a_fallback_goto = true,
		$append = "")
	{
		global $ilObjDataCache;

		if(!strlen($a_type))
		{
			$a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
		}
		
		include_once('Services/PrivacySecurity/classes/class.ilRobotSettings.php');
		$robot_settings = ilRobotSettings::_getInstance();
		if(!$robot_settings->robotSupportEnabled())
		{
			if($a_fallback_goto)
			{
				return ilLink::_getLink($a_ref_id,$a_type,array(),$append);
			}
			else
			{
				return false;
			}
		}
		
		return ILIAS_HTTP_PATH.'/goto_'.urlencode(CLIENT_ID).'_'.$a_type.'_'.$a_ref_id.$append.'.html';
	}	
}
?>
