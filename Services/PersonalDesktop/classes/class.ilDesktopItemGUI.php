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
*  Handles add/remove to/from desktop requests
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesPersonalDesktop
*/
class ilDesktopItemGUI
{
	/**
	 * Add desktop item
	 * @access public 
	 */
	public static function addToDesktop()
	{
		global $ilUser;
		
		if ($_GET["item_ref_id"] and $_GET["type"])
		{
			ilObjUser::_addDesktopItem($ilUser->getId(),(int) $_GET['item_ref_id'], $_GET['type']);
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$type = ilObject::_lookupType($item, true);
					ilObjUser::_addDesktopItem($ilUser->getId(),$item,$type);
				}
			}
		}
		return true;
	}
	
	/**
	 * Remove item from personal desktop
	 * @access public
	 */
	public static function removeFromDesktop()
	{
		global $ilUser;

		if ($_GET["item_ref_id"] and $_GET["type"])
		{
			ilObjUser::_dropDesktopItem($ilUser->getId(),(int) $_GET['item_ref_id'], $_GET['type']);
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$type = ilObject::_lookupType($item, true);
					ilObjUser::_dropDesktopItem($ilUser->getId(),$item,$type);
				}
			}
		}
		return true;
	}
}
?>