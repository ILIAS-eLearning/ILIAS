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
 * GUI class for learning history page content
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilPCLearningHistoryGUI: ilPageEditorGUI
 */
class ilPCLearningHistoryGUI extends ilPageContentGUI
{
    protected ilObjUser $user;
    protected \ILIAS\DI\UIServices $ui;
    protected ilLearningHistoryService $service;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPCLearningHistory $a_content_obj = null,
        string $a_hier_id = "",
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("lhist");
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->service = $DIC->learningHistory();
        $this->ui = $DIC->ui();
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * Insert learning history form
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
     * Init learning history edit form
     */
    protected function initForm(bool $a_insert = false): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_create_lhist"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_lhist"));
        }

        // duration
        $du = new ilDateDurationInputGUI($lng->txt("lhist_period"), "period");
        if (!$a_insert) {
            if ($this->content_obj->getFrom() != "") {
                $du->setStart(new ilDate($this->content_obj->getFrom(), IL_CAL_DATE));
            }
            if ($this->content_obj->getTo() != "") {
                $du->setEnd(new ilDate($this->content_obj->getTo(), IL_CAL_DATE));
            }
        }
        $du->setAllowOpenIntervals(true);
        $form->addItem($du);

        //
        $radg = new ilRadioGroupInputGUI($lng->txt("lhist_type_of_achievement"), "mode");
        $radg->setValue("0");
        $op1 = new ilRadioOption($lng->txt("lhist_all"), "0");
        $radg->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("lhist_selected"), "1");
        $radg->addOption($op2);
        $form->addItem($radg);


        // select type
        $options = [];
        foreach ($this->service->provider()->getAllProviders(true) as $p) {
            $options[get_class($p)] = $p->getName();
        }
        $si = new ilMultiSelectInputGUI($lng->txt(""), "class");
        $si->setHeight(130);
        if (!$a_insert) {
            $si->setValue($this->content_obj->getClasses());
            if (count($this->content_obj->getClasses()) > 0) {
                $radg->setValue(1);
            }
        }
        $si->setOptions($options);
        $op2->addSubItem($si);

        if ($a_insert) {
            $form->addCommandButton("create_lhist", $this->lng->txt("insert"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    /**
     * Create new learning history component
     */
    public function create(): void
    {
        $valid = false;

        $form = $this->initForm(true);
        if ($form->checkInput()) {
            //$data = $form->getInput("skill_id");
            $valid = true;
        }

        if ($valid) {
            $this->content_obj = new ilPCLearningHistory($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->setAttributesFromInput($form);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $form->setValuesByPost();
        $this->insert($form);
    }

    /**
     * Update learning history component
     */
    public function update(): void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->setAttributesFromInput($form);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_obj_modified"), true);
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $this->pg_obj->addHierIDs();
        $form->setValuesByPost();
        $this->edit($form);
    }

    protected function setAttributesFromInput(ilPropertyFormGUI $form): void
    {
        /** @var ilDateDurationInputGUI $item */
        $item = $form->getItemByPostVar("period");
        $from = (is_null($item->getStart()))
            ? ""
            : $item->getStart()->get(IL_CAL_DATE);
        $to = (is_null($item->getEnd()))
            ? ""
            : $item->getEnd()->get(IL_CAL_DATE);

        $this->content_obj->setFrom($from);
        $this->content_obj->setTo($to);
        $classes = ($form->getInput("mode") == "1" && is_array($form->getInput("class")))
            ? $form->getInput("class")
            : array();
        $this->content_obj->setClasses($classes);
    }

    public static function getPlaceholderPresentation(): string
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("lhist");

        // @todo we need a ks element for this
        $content = '<div style="margin:5px" class="ilBox"><h3>' . $lng->txt("lhist_lhist") . '</h3><div class="il_Description_no_margin">' .
            $lng->txt("lhist_cont_placeholder_text") . '</div></div>';

        return $content;
    }
}
