<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once "Services/Object/classes/class.ilObjectListGUI.php";
include_once('./Modules/WebResource/classes/class.ilObjLinkResourceAccess.php');

/**
* Class ilObjLinkResourceListGUI
*
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesWebResource
*/
class ilObjLinkResourceListGUI extends ilObjectListGUI
{
    public $link_data = array();

    /**
    * overwritten from base class
    */
    public function getTitle() : string
    {
        if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !ilLinkResourceList::checkListStatus($this->obj_id)) {
            $this->__readLink();

            return $this->link_data['title'];
        }
        return parent::getTitle();
    }
    /**
    * overwritten from base class
    */
    public function getDescription() : string
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !ilLinkResourceList::checkListStatus($this->obj_id)) {
            $this->__readLink();

            $desc = $this->link_data['description'];

            // #10682
            if ($ilSetting->get("rep_shorten_description")) {
                $desc = ilStr::shortenTextExtended(
                    $desc,
                    $ilSetting->get("rep_shorten_description_length"),
                    true
                );
            }

            return $desc;
        }
        return parent::getDescription();
    }

    /**
    * initialisation
    */
    public function init() : void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->type = "webr";
        $this->gui_class_name = "ilobjlinkresourcegui";
        $this->info_screen_enabled = true;

        // general commands array
        $this->commands = ilObjLinkResourceAccess::_getCommands();
    }

    /**
    * Get command target frame
    */
    public function getCommandFrame(string $cmd) : string
    {
        // #16820 / #18419 / #18622
        if ($cmd == "" &&
            ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !ilLinkResourceList::checkListStatus($this->obj_id)) {
            $link = ilObjLinkResourceAccess::_getFirstLink($this->obj_id);

            // we could use the "internal" flag, but it would not work for "old" links
            include_once "Services/Form/classes/class.ilFormPropertyGUI.php";
            include_once "Services/Form/classes/class.ilLinkInputGUI.php";
            if (!ilLinkInputGUI::isInternalLink($link["target"])) {
                return '_blank';
            }
        }
        return "";
    }

    /**
    * Get item properties
    *
    * @return	array		array of property arrays:
    *						"alert" (boolean) => display as an alert property (usually in red)
    *						"property" (string) => property name
    *						"value" (string) => property value
    */
    public function getProperties() : array
    {
        $props = array();
        return $props;
    }


    /**
    * Get command link url.
    */
    public function getCommandLink(string $cmd) : string
    {
        if (
            (isset($_REQUEST["wsp_id"]) && $_REQUEST["wsp_id"]) ||
            (isset($_REQUEST["cmdClass"]) && $_REQUEST["cmdClass"] === "ilpersonalworkspacegui")
        ) {
            if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
                !ilLinkResourceList::checkListStatus($this->obj_id) &&
                $cmd == '') {
                $cmd = "calldirectlink";
            }
            $this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", "");
            $this->ctrl->setParameterByClass($this->gui_class_name, "wsp_id", $this->ref_id);
            return $this->ctrl->getLinkTargetByClass(array("ilpersonalworkspacegui", $this->gui_class_name), $cmd);
        } else {
            // separate method for this line
            switch ($cmd) {
                case '':
                    if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
                        !ilLinkResourceList::checkListStatus($this->obj_id)) {
                        $this->__readLink();
                        // $cmd_link = $this->link_data['target'];
                        $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=calldirectlink";
                    } else {
                        $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";
                    }
                    break;

                default:
                    $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$cmd";
            }
        }
        return $cmd_link;
    }

    /**
    * Get data of first active link resource
    *
    * @return array link data array
    */
    public function __readLink()
    {
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        include_once './Modules/WebResource/classes/class.ilParameterAppender.php';

        if (ilParameterAppender::_isEnabled()) {
            return $this->link_data = ilParameterAppender::_append(ilLinkResourceItems::_getFirstLink($this->obj_id));
        }
        return $this->link_data = ilLinkResourceItems::_getFirstLink($this->obj_id);
    }
} // END class.ilObjTestListGUI
