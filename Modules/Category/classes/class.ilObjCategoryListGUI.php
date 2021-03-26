<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilObjCategoryListGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
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

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        $this->commands = ilObjCategoryAccess::_getCommands();
    }

    /**
    *
    * @param bool
    * @return bool
    */
    public function getInfoScreenStatus()
    {
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
                if (ilDAVActivationChecker::_isActive()) {
                    global $DIC;
                    $uri_builder = new ilWebDAVUriBuilder($DIC->http()->request());
                    return $uri_builder->getUriToMountInstructionModalByRef($this->ref_id);
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

    /**
     * @inheritDoc
     */
    public function checkInfoPageOnAsynchronousRendering() : bool
    {
        return true;
    }
} // END class.ilObjCategoryGUI
