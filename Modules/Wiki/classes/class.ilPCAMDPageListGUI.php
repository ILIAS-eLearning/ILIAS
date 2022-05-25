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
 * Handles user commands on advanced md page list
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPCAMDPageListGUI extends ilPageContentGUI
{
    protected ?ilAdvancedMDRecordGUI $record_gui = null;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }

    /**
     * @return mixed
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

    public function insert(ilPropertyFormGUI $a_form = null) : void
    {
        $tpl = $this->tpl;
        
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

    protected function initForm(bool $a_insert = false) : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_insert) {
            $form->setTitle($this->lng->txt("cont_insert_amd_page_list"));
        } else {
            $form->setTitle($this->lng->txt("cont_update_amd_page_list"));
        }
        $form->setDescription($this->lng->txt("wiki_page_list_form_info"));
                
        $mode = new ilSelectInputGUI($this->lng->txt("wiki_page_list_mode"), "mode");
        $mode->setOptions(array(
            0 => $this->lng->txt("wiki_page_list_mode_unordered"),
            1 => $this->lng->txt("wiki_page_list_mode_ordered")
        ));
        $mode->setRequired(true);
        $form->addItem($mode);

        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_SEARCH,
            'wiki',
            $this->getPage()->getWikiId(),
            'wpg',
            $this->getPage()->getId()
        );
        $this->record_gui->setPropertyForm($form);
        
        if (!$a_insert) {
            $mode->setValue($this->content_obj->getMode());
            $this->record_gui->setSearchFormValues($this->content_obj->getFieldValues());
        }
        
        $this->record_gui->parse();

        $no_fields = (count($form->getItems()) === 1);
        if ($no_fields) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("wiki_pg_list_no_search_fields"));
        }

        if ($a_insert) {
            if (!$no_fields) {
                $form->addCommandButton("create_amd_page_list", $this->lng->txt("select"));
            }
            $form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
        } else {
            if (!$no_fields) {
                $form->addCommandButton("update", $this->lng->txt("select"));
            }
            $form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
        }

        return $form;
    }

    public function create() : void
    {
        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $elements = $this->record_gui->importSearchForm();
            if (is_array($elements)) {
                $this->content_obj = new ilPCAMDPageList($this->getPage());
                $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
                $this->content_obj->setData($elements, $form->getInput("mode"));
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }
            }
        }

        $this->insert($form);
    }

    public function update() : void
    {
        $form = $this->initForm();
        if ($form->checkInput()) {
            $elements = $this->record_gui->importSearchForm();
            if (is_array($elements)) {
                $this->content_obj->setData($elements, $form->getInput("mode"));
                $this->updated = $this->pg_obj->update();
                if ($this->updated === true) {
                    $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
                }
            }
        }

        $this->pg_obj->addHierIDs();
        $this->edit($form);
    }
}
