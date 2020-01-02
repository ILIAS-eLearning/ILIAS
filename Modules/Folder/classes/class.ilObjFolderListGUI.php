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


/**
* Class ilObjFolderListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/


include_once "Services/Object/classes/class.ilObjectListGUI.php";

class ilObjFolderListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "fold";
        $this->gui_class_name = "ilobjfoldergui";

        // general commands array
        include_once('./Modules/Folder/classes/class.ilObjFolderAccess.php');
        $this->commands = ilObjFolderAccess::_getCommands();
    }

    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties()
    {
        $props = parent::getProperties();

        return $props;
    }

    /**
    * Get command link url.
    *
    * @param	int			$a_ref_id		reference id
    * @param	string		$a_cmd			command
    *
    */
    public function getCommandLink($a_cmd)
    {
        $ilCtrl = $this->ctrl;
        
        // BEGIN WebDAV: Mount webfolder.
        switch ($a_cmd) {
            case 'mount_webfolder':
                require_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
                if (ilDAVActivationChecker::_isActive()) {
                    require_once('Services/WebDAV/classes/class.ilWebDAVUtil.php');
                    $dav_util = ilWebDAVUtil::getInstance();
                    
                    // XXX: The following is a very dirty, ugly trick.
                    //        To mount URI needs to be put into two attributes:
                    //        href and folder. This hack returns both attributes
                    //        like this:  http://...mount_uri..." folder="http://...folder_uri...
                    $cmd_link = $dav_util->getMountURI($this->ref_id, $this->title, $this->parent) .
                                '" folder="' . $dav_util->getFolderURI($this->ref_id, $this->title, $this->parent);
                    break;
                } // Fall through, when plugin is inactive.
                // no break
            default:
                // separate method for this line
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                break;
        }
        
        return $cmd_link;
        // END WebDAV: Mount Webfolder
    }

    // BEGIN WebDAV: mount_webfolder in _blank frame
    /**
    * Get command target frame.
    *
    * Overwrite this method if link frame is not current frame
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        // begin-patch fm
        return parent::getCommandFrame($a_cmd);
        // end-patch fm
    }
    // END WebDAV: mount_webfolder in _blank frame
} // END class.ilObjFolderListGUI
