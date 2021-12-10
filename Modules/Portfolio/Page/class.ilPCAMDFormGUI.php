<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use \ILIAS\UI\Component\Input\Container\Form;

/**
 * AMD Form Page UI
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilPCAMDFormGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilPCAMDFormGUI: ilPropertyFormGUI
 */
class ilPCAMDFormGUI extends ilPageContentGUI
{
    /**
     * Constructor
     */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->request = $DIC->http()->request();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);

        $this->lng->loadLanguageModule("prtt");
        $this->lng->loadLanguageModule("prtf");
    }

    /**
     * execute command
     */
    public function executeCommand() : void
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);

        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {

            case "ilpropertyformgui":
                $form = $this->getPortfolioForm(true);
                $this->ctrl->forwardCommand($form);
                break;

            default:
                $ret = $this->$cmd();
                break;
        }

        //return $ret;
    }

    /**
     * Is template
     * @return bool
     */
    protected function isTemplate() : bool
    {
        return ($this->getPage()->getParentType() == "prtt");
    }

    /**
     * Insert courses form
     *
     * @param Form\Standard $form
     */
    public function insert(Form\Standard $form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$form) {
            $form = $this->getTemplateForm();
        }
        $tpl->setContent($this->ui->renderer()->render($form));
    }

    /**
     * Edit courses form
     */
    public function edit() : void
    {
        if ($this->isTemplate()) {
            $this->editTemplate();
            return;
        }
        $this->editPortfolio();
    }

    /**
     * Edit courses form
     *
     * @param Form\Standard $form
     */
    public function editTemplate(Form\Standard $form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$form) {
            $form = $this->getTemplateForm(true);
        }
        $tpl->setContent($this->ui->renderer()->render($form));
    }


    /**
     * Get template  form
     * @return Form\Standard
     */
    public function getTemplateForm(bool $edit = false)
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $selected = [];
        if ($edit) {
            $selected = $this->content_obj->getRecordIds();
        }
        $recs = $this->getAdvRecords();
        $fields = array();
        foreach ($recs as $r) {
            $val = (in_array($r->getRecordId(), $selected));
            $fields["rec" . $r->getRecordId()] =
                $f->input()->field()->checkbox($r->getTitle(), $r->getDescription())
                  ->withValue($val);
        }

        // section
        $section1 = $f->input()->field()->section($fields, $this->lng->txt("prtt_select_datasets"));

        if ($edit) {
            $form_action = $ctrl->getLinkTarget($this, "update");
        } else {
            $form_action = $ctrl->getLinkTarget($this, "create_amdfrm");
        }
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Create new form element
     */
    public function create() : void
    {
        $request = $this->request;
        $form = $this->getTemplateForm();
        $lng = $this->lng;
        $tpl = $this->tpl;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_null($data)) {
                $tpl->setContent($this->ui->renderer()->render($form));
                return;
            }
            if (is_array($data["sec"])) {
                $this->content_obj = new ilPCAMDForm($this->getPage());
                $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
                $this->content_obj->setRecordIds($this->getRecordIdsFromForm($form));
                $this->updated = $this->pg_obj->update();
                if (!$this->updated) {
                    $tpl->setContent($this->ui->renderer()->render($form));
                    return;
                }
                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            }
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    /**
     * Get record ids from form
     * @param Form\Standard $form
     * @return array
     */
    protected function getRecordIdsFromForm(Form\Standard $form) : array
    {
        $data = $form->getData();
        $ids = [];
        if (!is_null($data) && is_array($data["sec"])) {
            $recs = $this->getAdvRecords();
            $ids = [];
            foreach ($recs as $r) {
                if (isset($data["sec"]["rec" . $r->getRecordId()]) && $data["sec"]["rec" . $r->getRecordId()]) {
                    $ids[] = $r->getRecordId();
                }
            }
        }
        return $ids;
    }

    /**
     * Get adv records
     */
    protected function getAdvRecords() : array
    {
        if ($this->isTemplate()) {
            $id = (int) $_GET["ref_id"];
            $is_ref_id = true;
        } else {
            $id = (int) $this->getPage()->getPortfolioId();
            $is_ref_id = false;
        }

        $recs = \ilAdvancedMDRecord::_getSelectedRecordsByObject($this->getPage()->getParentType(), $id, "pfpg", $is_ref_id);
        return $recs;
    }

    /**
     * Update courses
     */
    public function update()
    {
        $request = $this->request;
        $form = $this->getTemplateForm(true);
        $lng = $this->lng;
        $tpl = $this->tpl;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_null($data)) {
                $tpl->setContent($this->ui->renderer()->render($form));
                return;
            }
            if (is_array($data["sec"])) {
                $this->content_obj->setRecordIds($this->getRecordIdsFromForm($form));
                $this->updated = $this->pg_obj->update();
                if (!$this->updated) {
                    $tpl->setContent($this->ui->renderer()->render($form));
                    return;
                }
                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            }
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    ////
    //// Editing in portfolio
    ////


    /**
     * Edit courses form
     * @param ilPropertyFormGUI|null $form
     */
    public function editPortfolio(ilPropertyFormGUI $form = null)
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$form) {
            $form = $this->getPortfolioForm(true);
        }
        $tpl->setContent($form->getHTML());
    }

    /**
     * Get template  form
     * @param bool $edit
     * @return ilPropertyFormGUI
     */
    public function getPortfolioForm(bool $edit = false) : ilPropertyFormGUI
    {
        $content_obj = $this->content_obj;
        if (is_null($content_obj)) {
            $page = new ilPortfolioPage($_GET["ppage"]);
            $page->buildDom();
            $content_obj = $page->getContentObjectForPcId($_GET["pc_id"]);
        }

        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $selected = [];
        if ($edit) {
            $selected = $content_obj->getRecordIds();
        }
        $recs = $this->getAdvRecords();
        foreach ($recs as $r) {
            $val = (in_array($r->getRecordId(), $selected));
        }

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ctrl->getFormAction($this, "updateAdvancedMetaData"));

        $form->setTitle($lng->txt("prtf_edit_data"));

        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'prtf',
            $this->getPage()->getPortfolioId(),
            'pfpg',
            $this->getPage()->getId(),
            false
        );
        $this->record_gui->setRecordFilter($selected);
        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();

        $form->addCommandButton("updateAdvancedMetaData", $lng->txt("save"));
        $form->addCommandButton("cancel", $lng->txt("cancel"));

        return $form;
    }

    public function updateAdvancedMetaData()
    {
        $lng = $this->lng;

        $form = $this->getPortfolioForm(true);

        // needed for proper advanced MD validation
        $form->checkInput();
        if (!$this->record_gui->importEditFormPostValues()) {
            $this->editPortfolio($form); // #16470
            return false;
        }

        if ($this->record_gui->writeEditForm()) {
            ilUtil::sendSuccess($lng->txt("settings_saved"), true);
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
