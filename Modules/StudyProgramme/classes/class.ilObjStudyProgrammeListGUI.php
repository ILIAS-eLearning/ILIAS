<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilObjStudyProgrammeListGUI extends ilObjectListGUI
{
    public function __construct()
    {
        global $DIC;

        parent::__construct();
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule("prg");
    }

    public function init() : void
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
        $this->commands = ilObjStudyProgrammeAccess::_getCommands();
    }

    /**
     * no timing commands needed for program.
     */
    public function insertTimingsCommand() : void
    {
    }

    /**
     * no social commands needed in program.
     */
    public function insertCommonSocialCommands($header_actions = false) : void
    {
    }

    /**
     * @inheritdoc
     */
    public function getCommandLink(string $cmd) : string
    {
        $this->ctrl->setParameterByClass("ilobjstudyprogrammegui", "ref_id", $this->ref_id);

        return $this->ctrl->getLinkTargetByClass("ilobjstudyprogrammegui", $cmd);
    }

    /**
    * @inheritdoc
    */
    public function getListItemHTML(
        int $ref_id,
        int $obj_id,
        string $title,
        string $description,
        bool $use_async = false,
        bool $get_async_commands = false,
        string $async_url = "",
        int $context = self::CONTEXT_REPOSITORY
    ) : string {
        $prg = new ilObjStudyProgramme($ref_id);
        $assignments = $prg->getAssignments();
        if ($this->getCheckboxStatus() && count($assignments) > 0) {
            $this->setAdditionalInformation($this->lng->txt("prg_can_not_manage_in_repo"));
            $this->enableCheckbox(false);
        } else {
            $this->setAdditionalInformation(null);
        }

        return parent::getListItemHTML(
            $ref_id,
            $obj_id,
            $title,
            $description,
            $use_async,
            $get_async_commands,
            $async_url
        );
    }
}
