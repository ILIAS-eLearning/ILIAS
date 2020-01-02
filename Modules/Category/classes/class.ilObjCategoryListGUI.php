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

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjCategoryListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesCategory
*/
class ilObjCategoryListGUI extends ilObjectListGUI
{

    /**
     * Constructor
     */
    public function __construct($a_context = self::CONTEXT_REPOSITORY)
    {
        global $DIC;

        parent::__construct($a_context);
        $this->ctrl = $DIC->ctrl();
    }

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

        $this->type = "cat";
        $this->gui_class_name = "ilobjcategorygui";

        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        include_once('./Modules/Category/classes/class.ilObjCategoryAccess.php');
        $this->commands = ilObjCategoryAccess::_getCommands();
    }

    /**
    *
    * @param bool
    * @return bool
    */
    public function getInfoScreenStatus()
    {
        include_once("./Services/Container/classes/class.ilContainer.php");
        include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
        if (ilContainer::_lookupContainerSetting(
            $this->obj_id,
            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
            true
        )) {
            return $this->info_screen_enabled;
        }

        return false;
    }

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
        
        // BEGIN WebDAV
        switch ($a_cmd) {
            case 'mount_webfolder':
                require_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
                if (ilDAVActivationChecker::_isActive()) {
                    require_once('Services/WebDAV/classes/class.ilWebDAVUtil.php');
                    $dav_util = ilWebDAVUtil::getInstance();
                    
                    // FIXME: The following is a very dirty, ugly trick.
                    //        To mount URI needs to be put into two attributes:
                    //        href and folder. This hack returns both attributes
                    //        like this:  http://...mount_uri..." folder="http://...folder_uri...
                    $cmd_link = $dav_util->getMountURI($this->ref_id) .
                                '" folder="' . $dav_util->getFolderURI($this->ref_id);
                }
                break;
            default:
                // separate method for this line
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
                break;
        }
        // END WebDAV

        return $cmd_link;
    }
} // END class.ilObjCategoryGUI
