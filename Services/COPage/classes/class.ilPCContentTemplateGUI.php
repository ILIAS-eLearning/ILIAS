<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCContentTemplate.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
 * Class ilPCContentTemplateGUI
 *
 * User Interface for inserting content templates
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilPCContentTemplateGUI: ilPageEditorGUI
 *
 * @ingroup ServicesCOPage
 */
class ilPCContentTemplateGUI extends ilPageContentGUI
{

    /**
     * Constructor
     */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
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
     * Insert content template
     */
    public function insert()
    {
        $tpl = $this->tpl;
        
        $this->displayValidationError();
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init creation from
     */
    public function initForm()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        // edit form
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_ed_insert_templ"));

        $radg = new ilRadioGroupInputGUI($lng->txt("cont_template"), "page_templ");
        $radg->setRequired(true);

        $ts = $this->getPage()->getContentTemplates();
        foreach ($ts as $t) {
            $op = new ilRadioOption($t["title"], $t["id"] . ":" . $t["parent_type"]);
            $radg->addOption($op);
        }

        $form->addItem($radg);


        $form->addCommandButton("create_templ", $lng->txt("insert"));
        $form->addCommandButton("cancelCreate", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Insert the template
     */
    public function create()
    {
        $tpl = $this->tpl;
        
        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->content_obj = new ilPCContentTemplate($this->getPage());
            $this->content_obj->create(
                $this->pg_obj,
                $this->hier_id,
                $this->pc_id,
                $form->getInput("page_templ")
            );
            $this->updated = $this->pg_obj->update();
            if ($this->updated === true) {
                $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                return;
            }
        }
        $this->displayValidationError();
        $form->setValuesByPost();
        $tpl->setContent($form->getHTML());
    }
}
