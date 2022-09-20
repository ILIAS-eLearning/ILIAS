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
 * Class ilPCQuestionOverviewGUI
 * User Interface for question overview editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCQuestionOverviewGUI extends ilPageContentGUI
{
    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
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

    public function insert(): void
    {
        $this->edit(true);
    }

    public function edit(
        bool $a_insert = false
    ): void {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->displayValidationError();

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_ed_insert_qover"));
        } else {
            $form->setTitle($this->lng->txt("cont_edit_qover"));
        }

        // short message
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_qover_short_message"), "short");
        $cb->setInfo($this->lng->txt("cont_qover_short_message_info"));
        if (!$a_insert) {
            $cb->setChecked($this->content_obj->getShortMessage());
        } else {
            $cb->setChecked(true);
        }
        $form->addItem($cb);

        // list wrong questions
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_qover_list_wrong_q"), "wrong_questions");
        $cb->setInfo($this->lng->txt("cont_qover_list_wrong_q_info"));
        if (!$a_insert) {
            $cb->setChecked($this->content_obj->getListWrongQuestions());
        }
        $form->addItem($cb);

        // save/cancel buttons
        if ($a_insert) {
            $form->addCommandButton("create_qover", $lng->txt("save"));
            $form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("update", $lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
        $html = $form->getHTML();
        $tpl->setContent($html);
    }

    /**
     * Create new question overview
     */
    public function create(): void
    {
        $this->content_obj = new ilPCQuestionOverview($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setShortMessage(
            $this->request->getString("short")
        );
        $this->content_obj->setListWrongQuestions(
            $this->request->getString("wrong_questions")
        );
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }

    /**
     * Update question overview
     */
    public function update(): void
    {
        $this->content_obj->setShortMessage(
            $this->request->getString("short")
        );
        $this->content_obj->setListWrongQuestions(
            $this->request->getString("wrong_questions")
        );
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }
}
