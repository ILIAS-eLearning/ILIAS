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
 * Class ilPCProfileGUI
 * Handles user commands on personal data
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCProfileGUI extends ilPageContentGUI
{
    protected \ILIAS\HTTP\Services $http;
    protected ilToolbarGUI $toolbar;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->http = $DIC->http();
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
     * Insert new personal data form.
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
     * Edit personal data form.
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
     * Init profile form
     */
    protected function initForm(bool $a_insert = false): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;

        $is_template = ($this->getPageConfig()->getEnablePCType("PlaceHolder"));

        if (!$is_template) {
            $ilToolbar->addButton(
                $this->lng->txt("cont_edit_personal_data"),
                $ilCtrl->getLinkTargetByClass("ildashboardgui", "jumptoprofile"),
                "profile"
            );

            $lng_suffix = "";
        } else {
            $lng_suffix = "_template";
        }

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_profile"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_profile"));
        }

        $mode = new ilRadioGroupInputGUI($this->lng->txt("cont_profile_mode"), "mode");
        $form->addItem($mode);

        $mode_inherit = new ilRadioOption($this->lng->txt("cont_profile_mode_inherit"), "inherit");
        $mode_inherit->setInfo($this->lng->txt("cont_profile_mode" . $lng_suffix . "_inherit_info"));
        $mode->addOption($mode_inherit);

        $mode_manual = new ilRadioOption($this->lng->txt("cont_profile_mode_manual"), "manual");
        $mode_manual->setInfo($this->lng->txt("cont_profile_mode_manual_info"));
        $mode->addOption($mode_manual);

        $prefs = array();
        if ($a_insert) {
            $mode->setValue("inherit");
        } else {
            $mode_value = $this->content_obj->getMode();
            $mode->setValue($mode_value);

            $prefs = array();
            if ($mode_value == "manual") {
                foreach ($this->content_obj->getFields() as $name) {
                    $prefs["public_" . $name] = "y";
                }
            }
        }

        $profile = new ilPersonalProfileGUI();
        $profile->showPublicProfileFields($form, $prefs, $mode_manual, $is_template);

        if ($a_insert) {
            $form->addCommandButton("create_profile", $this->lng->txt("save"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    /**
     * Gather field values
     */
    protected function getFieldsValues(): array
    {
        $fields = array();
        foreach ($this->http->request()->getParsedBody() as $name => $value) {
            if (substr($name, 0, 4) == "chk_") {
                if ($value) {
                    $fields[] = substr($name, 4);
                }
            }
        }
        return $fields;
    }

    /**
     * Create new personal data.
     */
    public function create(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $this->content_obj = new ilPCProfile($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setFields(
                $form->getInput("mode"),
                $this->getFieldsValues()
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->insert($form);
    }

    /**
     * Update personal data.
     */
    public function update(): void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $this->content_obj->setFields(
                $form->getInput("mode"),
                $this->getFieldsValues()
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $this->edit($form);
    }
}
