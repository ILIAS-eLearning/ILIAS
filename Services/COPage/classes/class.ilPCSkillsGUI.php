<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Class ilPCSkillsGUI
 *
 * Handles user commands on skills data
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCSkillsGUI extends ilPageContentGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;


    /**
    * Constructor
    * @access	public
    */
    public function __construct(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
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
                $ret = $this->$cmd();
                break;
        }

        return $ret;
    }

    /**
     * Insert skills form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function insert(ilPropertyFormGUI $a_form = null)
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

    /**
     * Edit skills form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function edit(ilPropertyFormGUI $a_form = null)
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
     *
     * @param bool $a_insert
     * @return ilPropertyFormGUI
     */
    protected function initForm($a_insert = false)
    {
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

        $skills = ilPersonalSkill::getSelectedUserSkills($ilUser->getId());
        if ($skills) {
            foreach ($skills as $skill) {
                $options[$skill["skill_node_id"]] = $skill["title"];
            }
            asort($options);
        } else {
            ilUtil::sendFailure("cont_no_skills");
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

    /**
    * Create new skill
    */
    public function create()
    {
        $valid = false;
        
        // template mode: get skills from global skill tree
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $data = (int) $_GET["skill_id"];
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
        return $this->insert($form);
    }

    /**
    * Update blog
    */
    public function update()
    {
        // template mode: get skills from global skill tree
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $data = (int) $_GET["skill_id"];
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
        return $this->edit($form);
    }
}
