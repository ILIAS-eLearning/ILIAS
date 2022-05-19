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
 * Class ilPCContentTemplateGUI
 *
 * User Interface for inserting content templates
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilPCContentTemplateGUI: ilPageEditorGUI
 */
class ilPCContentTemplateGUI extends ilPageContentGUI
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
        $this->lng = $DIC->language();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
     * Execute command
     */
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

    /**
     * Insert content template
     */
    public function insert() : void
    {
        $tpl = $this->tpl;
        
        $this->displayValidationError();
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init creation from
     */
    public function initForm() : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        // edit form
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
    public function create() : void
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
