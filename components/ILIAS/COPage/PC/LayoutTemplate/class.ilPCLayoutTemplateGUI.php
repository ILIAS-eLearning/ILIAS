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

declare(strict_types=1);

/**
 * @ilCtrl_isCalledBy ilPCLayoutTemplateGUI: ilPageEditorGUI
 */
class ilPCLayoutTemplateGUI extends ilPageContentGUI
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
     * Insert content template
     */
    public function insert(): void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();
        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init creation from
     */
    public function initCreationForm(): ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        // edit form
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->lng->txt("cont_ed_insert_lay"));

        $config = $this->getPage()->getPageConfig();
        $templates = ilPageLayout::activeLayouts($config->getLayoutTemplateType());
        $first = true;
        if ($templates) {
            $use_template = new ilRadioGroupInputGUI($this->lng->txt("cont_layout"), "tmpl");
            $use_template->setRequired(true);
            $form->addItem($use_template);

            foreach ($templates as $templ) {
                $templ->readObject();
                $opt = new ilRadioOption($templ->getTitle() . $templ->getPreview(), (string) $templ->getId());
                $use_template->addOption($opt);
                if ($first) {
                    $use_template->setValue((string) $templ->getId());
                    $first = false;
                }
            }
        }

        $form->addCommandButton("create_templ", $lng->txt("insert"));
        $form->addCommandButton("cancelCreate", $lng->txt("cancel"));

        return $form;
    }

    /**
     * Insert the template
     */
    public function create(): void
    {
        $tpl = $this->tpl;

        $form = $this->initForm();
        if ($form->checkInput()) {
            $this->content_obj = new ilPCLayoutTemplate($this->getPage());
            $this->content_obj->create(
                $this->pg_obj,
                $this->hier_id,
                $this->pc_id,
                (int) $form->getInput("tmpl")
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
