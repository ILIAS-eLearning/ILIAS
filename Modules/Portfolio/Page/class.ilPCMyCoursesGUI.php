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
 * Handles user commands on my courses data
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCMyCoursesGUI extends ilPageContentGUI
{
    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    public function executeCommand() : void
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

    public function insert(ilPropertyFormGUI $a_form = null) : void
    {
        $tpl = $this->tpl;
        
        /* #12816 - no form needed yet
        $this->create();
        */
            
        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm(true);
        }
        $tpl->setContent($a_form->getHTML());
    }

    public function edit(ilPropertyFormGUI $a_form = null) : void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    protected function initForm($a_insert = false) : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_my_courses"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_my_courses"));
        }
        
        $sort = new ilRadioGroupInputGUI($this->lng->txt("cont_mycourses_sortorder"), "sort");
        $sort->setInfo($this->lng->txt("cont_mycourses_sortorder_info")); //#15511
        $sort->setRequired(true);
        $form->addItem($sort);
        
        $sort->addOption(new ilRadioOption($this->lng->txt("cont_mycourses_sortorder_alphabetical"), "alpha"));
        $sort->addOption(new ilRadioOption($this->lng->txt("cont_mycourses_sortorder_location"), "loc"));
        
        if ($a_insert) {
            $sort->setValue("alpha");
            
            $form->addCommandButton("create_my_courses", $this->lng->txt("save"));
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            $sort->setValue($this->content_obj->getSorting());
            
            $form->addCommandButton("update", $this->lng->txt("save"));
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    public function create() : void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $sort = $form->getInput("sort");
            
            $this->content_obj = new ilPCMyCourses($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
            $this->content_obj->setData($sort);
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
            }
        }

        $form->setValuesByPost();
        $this->insert($form);
    }

    public function update() : void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $sort = $form->getInput("sort");
            $this->content_obj->setData($sort);
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
