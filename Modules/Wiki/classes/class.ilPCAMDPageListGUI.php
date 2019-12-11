<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Wiki/classes/class.ilPCAMDPageList.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCAMDPageListGUI
*
* Handles user commands on advanced md page list
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ModulesWiki
*/
class ilPCAMDPageListGUI extends ilPageContentGUI
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
                $ret = $this->$cmd();
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
                
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_SEARCH, 'wiki', $this->getPage()->getWikiId(), 'wpg', $this->getPage()->getId());
        $this->record_gui->setPropertyForm($form);
        
        if (!$a_insert) {
            $mode->setValue($this->content_obj->getMode());
            $this->record_gui->setSearchFormValues($this->content_obj->getFieldValues());
        }
        
        $this->record_gui->parse();

        $no_fields = (count($form->getItems()) == 1);
        if ($no_fields) {
            ilUtil::sendFailure($this->lng->txt("wiki_pg_list_no_search_fields"));
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

    /**
    * Create new courses
    */
    public function create()
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

        // $form->setValuesByPost();
        return $this->insert($form);
    }

    /**
    * Update courses
    */
    public function update()
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
        // $form->setValuesByPost();
        return $this->edit($form);
    }
}
