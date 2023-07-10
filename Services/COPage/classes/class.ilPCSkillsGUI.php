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
 * Class ilPCSkillsGUI
 * Handles user commands on skills data
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCSkillsGUI extends ilPageContentGUI
{
    protected ilObjUser $user;
    protected \ILIAS\Skill\Service\SkillPersonalService $skill_personal_service;

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
        $this->skill_personal_service = $DIC->skills()->personal();
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

    public function insert(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        // template mode: get skills from global skill tree
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $exp = new ilPersonalSkillExplorerGUI($this, "insert", $this, "create", "skill_id");
            if (!$exp->handleCommand()) {
                $tpl->setContent($exp->getHTML());
            }
        }
        // editor mode: use personal skills
        else {
            if (!$a_form) {
                $a_form = $this->initForm(true);
            }
            $tpl->setContent($a_form->getHTML());
        }
    }

    public function edit(ilPropertyFormGUI $a_form = null): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        // template mode: get skills from global skill tree
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $exp = new ilPersonalSkillExplorerGUI($this, "edit", $this, "update", "skill_id");
            if (!$exp->handleCommand()) {
                $tpl->setContent($exp->getHTML());
            }
        }
        // editor mode: use personal skills
        else {
            if (!$a_form) {
                $a_form = $this->initForm();
            }
            $tpl->setContent($a_form->getHTML());
        }
    }

    /**
     * Init skills form
     */
    protected function initForm(
        bool $a_insert = false
    ): ilPropertyFormGUI {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_skills"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_skills"));
        }

        $options = array();

        $skills = $this->skill_personal_service->getSelectedUserSkills($ilUser->getId());
        if ($skills) {
            foreach ($skills as $skill) {
                $options[$skill->getSkillNodeId()] = $skill->getTitle();
            }
            asort($options);
        } else {
            $this->tpl->setOnScreenMessage('failure', "cont_no_skills");
        }
        $obj = new ilSelectInputGUI($this->lng->txt("cont_pc_skills"), "skill_id");
        $obj->setRequired(true);
        $obj->setOptions($options);
        $form->addItem($obj);

        if ($a_insert) {
            $form->addCommandButton("create_skill", $this->lng->txt("select"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $obj->setValue($this->content_obj->getSkillId());
            $form->addCommandButton("update", $this->lng->txt("select"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    public function create(): void
    {
        $valid = false;
        $data = null;
        $form = null;

        // template mode: get skills from global skill tree
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $data = $this->request->getInt("skill_id");
            $valid = true;
        }
        // editor mode: use personal skills
        else {
            $form = $this->initForm(true);
            if ($form->checkInput()) {
                $data = $form->getInput("skill_id");
                $valid = true;
            }
        }

        if ($valid) {
            $this->content_obj = new ilPCSkills($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setData($data);
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
        $valid = false;
        $data = null;
        $form = null;

        // template mode: get skills from global skill tree
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $data = $this->request->getInt("skill_id");
            $valid = true;
        }
        // editor mode: use personal skills
        else {
            $form = $this->initForm();
            if ($form->checkInput()) {
                $data = $form->getInput("skill_id");
                $valid = true;
            }
        }

        if ($valid) {
            $this->content_obj->setData($data);
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
