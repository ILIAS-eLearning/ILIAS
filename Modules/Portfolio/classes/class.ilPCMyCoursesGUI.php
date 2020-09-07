<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Portfolio/classes/class.ilPCMyCourses.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCMyCoursesGUI
*
* Handles user commands on my courses data
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ModulesPortfolio
*/
class ilPCMyCoursesGUI extends ilPageContentGUI
{
    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $ret = &$this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert courses form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function insert(ilPropertyFormGUI $a_form = null)
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

    /**
     * Edit courses form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$a_form) {
            $a_form = $this->initForm();
        }
        $tpl->setContent($a_form->getHTML());
    }

    /**
     * Init courses form
     *
     * @param bool $a_insert
     * @return ilPropertyFormGUI
     */
    protected function initForm($a_insert = false)
    {
        $ilCtrl = $this->ctrl;

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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

    /**
    * Create new courses
    */
    public function create()
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
        return $this->insert($form);
    }

    /**
    * Update courses
    */
    public function update()
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
        return $this->edit($form);
    }
}
