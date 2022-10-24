<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilPCConsultationHoursGUI
 * Handles user commands on consultation hour data
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCConsultationHoursGUI extends ilPageContentGUI
{
    protected ilObjUser $user;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand(): void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Insert consultation hours form
     */
    public function insert(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Edit consultation hours form
     */
    public function edit(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init consultation hours form
     */
    protected function initForm(bool $a_insert = false): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $groups = null;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_consultation_hours"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_consultation_hours"));
        }

        $mode = new ilRadioGroupInputGUI($this->lng->txt("cont_cach_mode"), "mode");
        $mode->setRequired(true);
        $form->addItem($mode);

        $opt_auto = new ilRadioOption($this->lng->txt("cont_cach_mode_automatic"), "auto");
        $opt_auto->setInfo($this->lng->txt("cont_cach_mode_automatic_info"));
        $mode->addOption($opt_auto);

        $opt_manual = new ilRadioOption($this->lng->txt("cont_cach_mode_manual"), "manual");
        $opt_manual->setInfo($this->lng->txt("cont_cach_mode_manual_info"));
        $mode->addOption($opt_manual);

        if (!$this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $grp_ids = ilConsultationHourGroups::getGroupsOfUser($ilUser->getId());
            if (count($grp_ids)) {
                $this->lng->loadLanguageModule("dateplaner");
                $groups = new ilCheckboxGroupInputGUI($this->lng->txt("cal_ch_app_grp"), "grp");
                $groups->setRequired(true);
                $opt_manual->addSubItem($groups);

                foreach ($grp_ids as $grp_obj) {
                    $groups->addOption(new ilCheckboxOption($grp_obj->getTitle(), $grp_obj->getGroupId()));
                }
            } else {
                $opt_manual->setDisabled(true);
            }
        } else {
            $opt_manual->setDisabled(true);
        }

        if ($a_insert) {
            $mode->setValue("auto");

            $form->addCommandButton("create_consultation_hours", $this->lng->txt("select"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            // set values
            $grp_ids = $this->content_obj->getGroupIds();
            if (count($grp_ids)) {
                $mode->setValue("manual");
                $groups->setValue($grp_ids);
            } else {
                $mode->setValue("auto");
            }

            $form->addCommandButton("update", $this->lng->txt("select"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    public function create(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $grp_ids = null;
            $mode = $form->getInput("mode");
            if ($mode == "manual") {
                $grp_ids = $form->getInput("grp");
            }

            $this->content_obj = new ilPCConsultationHours($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setData($mode, (array) $grp_ids);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $form->setValuesByPost();
        $this->insert($form);
    }

    public function update(): void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $grp_ids = array();
            $mode = $form->getInput("mode");
            if ($mode == "manual") {
                $grp_ids = $form->getInput("grp");
            }

            $this->content_obj->setData($mode, $grp_ids);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $form->setValuesByPost();
        $this->edit($form);
    }
}
