<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCQuestionOverview.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Class ilPCQuestionOverviewGUI
 *
 * User Interface for question overview editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCOPage
 */
class ilPCQuestionOverviewGUI extends ilPageContentGUI
{

    /**
     * Constructor
     */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }


    /**
     * Execute command
     */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert new question overview
     */
    public function insert()
    {
        $this->edit(true);
    }

    /**
     * Edit question overview form.
     */
    public function edit($a_insert = false)
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        
        $this->displayValidationError();
        
        // edit form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
        return $ret;
    }

    /**
     * Create new question overview
     */
    public function create()
    {
        $this->content_obj = new ilPCQuestionOverview($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setShortMessage(ilUtil::stripSlashes($_POST["short"]));
        $this->content_obj->setListWrongQuestions(ilUtil::stripSlashes($_POST["wrong_questions"]));
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
    public function update()
    {
        $this->content_obj->setShortMessage(ilUtil::stripSlashes($_POST["short"]));
        $this->content_obj->setListWrongQuestions(ilUtil::stripSlashes($_POST["wrong_questions"]));
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }
}
