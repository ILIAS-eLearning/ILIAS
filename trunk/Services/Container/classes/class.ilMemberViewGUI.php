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

include_once './Services/Container/classes/class.ilMemberViewSettings.php';

/**
 * @classDescription Show member view switch
 * @author Stefan Meyer <meyer.leifos.com>
 * @version $Id$
 */
class ilMemberViewGUI
{
	
	/**
	 * Show member view switch
	 * @return 
	 * @param int $a_ref_id
	 */
	public static function showMemberViewSwitch($a_ref_id)
	{
		global $ilAccess, $ilCtrl;
		
		$settings = ilMemberViewSettings::getInstance();
		if(!$settings->isEnabled())
		{
			return false;
		}
		global $tpl,$tree,$lng,$ilTabs;
		
		// No course or group in path => aborting
		if(!$tree->checkForParentType($a_ref_id, 'crs') and
			!$tree->checkForParentType($a_ref_id, 'grp'))
		{
			return false;
		}
		
		// TODO: check edit_permission
		
		$active = $settings->isActive();
		
		$type = ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id));
		if(($type == 'crs' or $type == 'grp') and $ilAccess->checkAccess('write','',$a_ref_id))
		{
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_ref_id);
			$ilCtrl->setParameterByClass("ilrepositorygui", "mv", "1");
			$ilCtrl->setParameterByClass("ilrepositorygui", "set_mode", "flat");
			$ilTabs->addNonTabbedLink("members_view",
				$lng->txt('mem_view_activate'),
				$ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset")
				);
			$ilCtrl->clearParametersByClass("ilrepositorygui");
			return true;
		}
		return true;
	}
}
