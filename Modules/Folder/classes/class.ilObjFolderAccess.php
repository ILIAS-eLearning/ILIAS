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

include_once("classes/class.ilObjectAccess.php");

/**
* Class ilObjFileAccess
*
*
* @author 	Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/
class ilObjFolderAccess extends ilObjectAccess
{
    private static $folderSettings; 
    
    function _construct () {
        parent::__construct();
    }
    
    private static function getFolderSettings() {
        if (is_null (ilObjFolderAccess::$folderSettings))
        {
           ilObjFolderAccess::$folderSettings = new ilSetting('fold');
        }
        return ilObjFolderAccess::$folderSettings;
    }
     
    
	/**
	 * get commands
	 * 
	 * this method returns an array of all possible commands/permission combinations
	 * 
	 * example:	
	 * $commands = array
	 *	(
	 *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 *	);
	 */
	function _getCommands()
	{
		$commands = array();
		$commands[] = array("permission" => "read", "cmd" => "view", "lang_var" => "show", "default" => true);
		$commands[] = array("permission" => "read", "cmd" => "showSummary", "lang_var" => "info_short", "enable_anonymous" => "false");
		if (ilObjFolderAccess::hasDownloadAction($_GET["ref_id"]))
		{
		    $commands[] = array("permission" => "read", "cmd" => "downloadFolder", "lang_var" => "download", "enable_anonymous" => "false");
		}
		// BEGIN WebDAV: Mount Webfolder.
		require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
		if (ilDAVServer::_isActive() && ilDAVServer::_isActionsVisible())
		{
			$commands[] = array("permission" => "read", "cmd" => "mount_webfolder", "lang_var" => "mount_webfolder", "enable_anonymous" => "false");
		}
		$commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "edit");
		// END WebDAV: Mount Webfolder.
		
		return $commands;
	}

	
	private function hasDownloadAction ($ref_id)
	{
	    global $tree, $ilUser;
	    $settings = ilObjFolderAccess::getFolderSettings();
		// default value should reflect previous behaviour (-> 0)
	    if ($settings->get("enable_download_folder", 0) != 1)
	        return false;
	        
	    /*
	     * deactivated check for now, because wrong ref_id here!
	     
	    $children = $tree->getChildsByTypeFilter($ref_id, array("file","fold"));

	    // no children at all, so no download button
	    if (count ($children) == 0)
	        return false;
	    // check if at least one of the children has a read permission
	    foreach ($children as $child)
		{
			if ($rbacsystem->checkAccessOfUser($ilUser->getId(), "read", $child["ref_id"]))
				return true;
		}
		return false;
	    */
	    return true;
	}

}

?>
