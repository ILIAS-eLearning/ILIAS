<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectListGUI.php");
include_once('./Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php');

/**
 * Class ilObjStudyProgrammeListGUI
 *
 * @author: Richard Klees <richard.klees@concepts-and-training.de>
 *
 */

class ilObjStudyProgrammeListGUI extends ilObjectListGUI
{

    /**
     * @var ilTemplate
     */
    protected $tpl;


    public function __construct()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        parent::__construct();
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->lng->loadLanguageModule("prg");
        //$this->enableComments(false, false);
    }


    /**
     * initialisation
     */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = false;
        $this->info_screen_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = false;

        $this->type = "prg";
        $this->gui_class_name = "ilobjstudyprogrammegui";

        // general commands array
        include_once('./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeAccess.php');
        $this->commands = ilObjStudyProgrammeAccess::_getCommands();
    }


    /**
     * no timing commands needed for program.
     */
    public function insertTimingsCommand()
    {
        return;
    }


    /**
     * no social commands needed in program.
     */
    public function insertCommonSocialCommands($a_header_actions = false)
    {
        return;
    }


    /**
     * insert info screen program
     */
    /*function insertInfoScreenCommand() {

        if ($this->std_cmd_only) {
            return;
        }
        $cmd_link = $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary");
        $cmd_frame = $this->getCommandFrame("infoScreen");

        $this->insertCommand($cmd_link, $this->lng->txt("info_short"), $cmd_frame, ilUtil::getImagePath("icon_info.svg"));
    }*/


    /**
     * @param string $a_cmd
     *
     * @return string
     */
    public function getCommandLink($a_cmd)
    {
        $this->ctrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $this->ref_id);

        return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammegui", $a_cmd);
    }

    /**
    * Get all item information (title, commands, description) in HTML
    *
    * @access	public
    * @param	int			$a_ref_id		item reference id
    * @param	int			$a_obj_id		item object id
    * @param	int			$a_title		item title
    * @param	int			$a_description	item description
    * @param	bool		$a_use_asynch
    * @param	bool		$a_get_asynch_commands
    * @param	string		$a_asynch_url
    * @param	bool		$a_context	    workspace/tree context
    * @return	string		html code
    */
    public function getListItemHTML(
        $a_ref_id,
        $a_obj_id,
        $a_title,
        $a_description,
        $a_use_asynch = false,
        $a_get_asynch_commands = false,
        $a_asynch_url = "",
        $a_context = self::CONTEXT_REPOSITORY
    ) {
        $prg = new ilObjStudyProgramme($a_ref_id);
        $assignments = $prg->getAssignments();
        if ($this->getCheckboxStatus() && count($assignments) > 0) {
            $this->setAdditionalInformation($this->lng->txt("prg_can_not_manage_in_repo"));
            $this->enableCheckbox(false);
        } else {
            $this->setAdditionalInformation(null);
        }

        return parent::getListItemHTML($a_ref_id, $a_obj_id, $a_title, $a_description, $a_use_asynch, $a_get_asynch_commands, $a_asynch_url, $a_context);
    }
}
