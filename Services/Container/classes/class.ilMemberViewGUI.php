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
		global $ilAccess;
		
		$settings = ilMemberViewSettings::getInstance();
		if(!$settings->isEnabled())
		{
			return false;
		}
		global $tpl,$tree,$lng;
		
		// No course or group in path => aborting
		if(!$tree->checkForParentType($a_ref_id, 'crs') and
			!$tree->checkForParentType($a_ref_id, 'grp'))
		{
			return false;
		}
		
		// TODO: check edit_permission
		
		$active = $settings->isActive();
		
		/*
		if($active)
		{
			$tpl->setCurrentBlock('mem_view');
			$tpl->setVariable('MEM_VIEW_HREF','repository.php?ref_id='.$a_ref_id.'&mv=0');
			$tpl->setVariable('MEM_VIEW_IMG',ilUtil::getImagePath('icon_rolt.gif'));
			$tpl->setVariable('MEM_VIEW_ALT',$lng->txt('mem_view_deactivate'));
			$tpl->parseCurrentBlock();
			return true;
		}
		*/
		$type = ilObject::_lookupType(ilObject::_lookupObjId($a_ref_id));
		if(($type == 'crs' or $type == 'grp') and $ilAccess->checkAccess('edit_permission','',$a_ref_id))
		{
			$tpl->setCurrentBlock('mem_view');
			$tpl->setVariable('MEM_VIEW_HREF','repository.php?cmd=frameset&set_mode=flat&ref_id='.$a_ref_id.'&mv=1');
			$tpl->setVariable('MEM_VIEW_IMG',ilUtil::getImagePath('icon_role.gif'));
			$tpl->setVariable('MEM_VIEW_ALT',$lng->txt('mem_view_activate'));
			$tpl->parseCurrentBlock();
			return true;
		}
		return true;
	}
}
