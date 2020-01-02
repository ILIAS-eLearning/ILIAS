<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjOrgUnitListGUI
 *
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilObjOrgUnitListGUI extends ilObjectListGUI
{

    /**
     * @var ilTemplate
     */
    protected $tpl;


    public function __construct()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        parent::__construct();
        $this->tpl = $tpl;
        //$this->enableComments(false, false);
    }


    /**
     * initialisation
     */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->info_screen_enabled = true;
        $this->copy_enabled = false;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;

        $this->type = "orgu";
        $this->gui_class_name = "ilobjorgunitgui";

        // general commands array
        $this->commands = ilObjOrgUnitAccess::_getCommands();
    }


    /**
     * no timing commands needed in orgunits.
     */
    public function insertTimingsCommand()
    {
        return;
    }


    /**
     * no social commands needed in orgunits.
     */
    public function insertCommonSocialCommands($a_header_actions = false)
    {
        return;
    }


    /**
     * insert info screen command
     */
    public function insertInfoScreenCommand()
    {
        if ($this->std_cmd_only) {
            return;
        }
        $cmd_link = $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
        $cmd_frame = $this->getCommandFrame("infoScreen");

        $this->insertCommand($cmd_link, $this->lng->txt("info_short"), $cmd_frame, ilUtil::getImagePath("icon_info.svg"));
    }


    /**
     * @param string $a_cmd
     *
     * @return string
     */
    public function getCommandLink($a_cmd)
    {
        $this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $this->ref_id);

        return $this->ctrl->getLinkTargetByClass("ilobjorgunitgui", $a_cmd);
    }


    public function insertIconsAndCheckboxes()
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilias = $DIC['ilias'];
        // FSX removed $this->getCheckboxStatus() in if-Statement: 0014726
        if (!$ilias->getSetting('custom_icons')) {
            parent::insertIconsAndCheckboxes();

            return;
        }
        $icons_cache = ilObjOrgUnit::getIconsCache();
        if (isset($icons_cache[$this->obj_id])) {
            $icon_file = $icons_cache[$this->obj_id];
            // icon link
            if (!$this->default_command or (!$this->getCommandsStatus() and !$this->restrict_to_goto)) {
            } else {
                $this->tpl->setCurrentBlock("icon_link_s");

                if ($this->default_command["frame"] != "") {
                    $this->tpl->setVariable("ICON_TAR", "target='" . $this->default_command["frame"] . "'");
                }

                $this->tpl->setVariable("ICON_HREF", $this->default_command["link"]);
                $this->tpl->parseCurrentBlock();
                $this->tpl->touchBlock("icon_link_e");
            }
            $this->enableIcon(false);
            if ($this->getContainerObject()->isActiveAdministrationPanel() && !$_SESSION['clipboard']) {
                $this->tpl->touchBlock("i_1");    // indent main div  }
                $this->tpl->touchBlock("d_2");    // indent main div  } #0014913
            } else {
                $this->tpl->touchBlock("d_1");
            }

            parent::insertIconsAndCheckboxes();
            $this->tpl->setCurrentBlock("icon");
            $this->tpl->setVariable("ALT_ICON", $lng->txt("icon") . " " . $lng->txt("obj_" . $this->getIconImageType()));
            $this->tpl->setVariable("SRC_ICON", $icon_file);
            $this->tpl->parseCurrentBlock();
            $this->enableIcon(true);
        } else {
            parent::insertIconsAndCheckboxes();
        }
    }
}
