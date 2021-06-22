<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */



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
    public function getTitle()
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
    public function getDescription()
    {
    
        if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !ilLinkResourceList::checkListStatus($this->obj_id)) {
            $this->__readLink();
            
            $desc = $this->link_data['description'];
            
            // #10682
            if ($this->settings->get("rep_shorten_description")) {
                $desc = ilUtil::shortenText(
                    $desc,
                    $this->settings->get("rep_shorten_description_length"),
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
    public function init()
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
    *
    * @param	string		$a_cmd			command
    *
    * @return	string		command target frame
    */
    public function getCommandFrame($a_cmd)
    {
        // #16820 / #18419 / #18622
        if ($a_cmd == "" &&
            ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
            !ilLinkResourceList::checkListStatus($this->obj_id)) {
            $link = ilObjLinkResourceAccess::_getFirstLink($this->obj_id);
            
            // we could use the "internal" flag, but it would not work for "old" links
            if (!ilLinkInputGUI::isInternalLink($link["target"])) {
                return '_blank';
            }
        }
        return '';
    }
            
    public function getProperties()
    {
        $props = array();

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
        global $DIC;
        $request = $DIC->http()->request();

        if(isset($request->getQueryParams()['wsp_id'])) {
            $wsp_id = $request->getQueryParams()['wsp_id'];
        } else if(isset($request->getParsedBody()['wsp_id'])) {
            $wsp_id = $request->getParsedBody()['wsp_id'];
        }

        if(isset($request->getQueryParams()['cmdClass'])) {
            $cmd_class = $request->getQueryParams()['cmdClass'];
        } else if(isset($request->getParsedBody()['cmdClass'])) {
            $cmd_class = $request->getParsedBody()['cmdClass'];
        }

        if (
            (isset($wsp_id) && $wsp_id) ||
            (isset($cmd_class) && $cmd_class === "ilpersonalworkspacegui")
        ) {
            if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
                !ilLinkResourceList::checkListStatus($this->obj_id) &&
                $a_cmd == '') {
                $a_cmd = "calldirectlink";
            }
            $this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", "");
            $this->ctrl->setParameterByClass($this->gui_class_name, "wsp_id", $this->ref_id);
            return $this->ctrl->getLinkTargetByClass(array("ilpersonalworkspacegui", $this->gui_class_name), $a_cmd);
        } else {
            // separate method for this line
            switch ($a_cmd) {
                case '':
                    if (ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) &&
                        !ilLinkResourceList::checkListStatus($this->obj_id)) {
                        $this->__readLink();
                        // $cmd_link = $this->link_data['target'];
                        $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=calldirectlink";
                    } else {
                        $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$a_cmd";
                    }
                    break;

                default:
                    $cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $this->ref_id . "&cmd=$a_cmd";
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

        if (ilParameterAppender::_isEnabled()) {
            return $this->link_data = ilParameterAppender::_append($tmp = ilLinkResourceItems::_getFirstLink($this->obj_id));
        }
        return $this->link_data = ilLinkResourceItems::_getFirstLink($this->obj_id);
    }
} // END class.ilObjTestListGUI
