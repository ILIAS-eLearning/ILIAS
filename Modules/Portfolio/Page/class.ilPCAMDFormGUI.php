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

use ILIAS\UI\Component\Input\Container\Form;
use ILIAS\Portfolio\StandardGUIRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * AMD Form Page UI
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilPCAMDFormGUI: ilPageEditorGUI
 * @ilCtrl_Calls ilPCAMDFormGUI: ilPropertyFormGUI
 */
class ilPCAMDFormGUI extends ilPageContentGUI
{
    protected ilAdvancedMDRecordGUI $record_gui;
    protected \ILIAS\DI\UIServices $ui;
    protected StandardGUIRequest $port_request;
    protected ServerRequestInterface $http_request;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->http_request = $DIC->http()->request();
        $this->port_request = $DIC->portfolio()
            ->internal()
            ->gui()
            ->standardRequest();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);

        $this->lng->loadLanguageModule("prtt");
        $this->lng->loadLanguageModule("prtf");
    }

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
    }

    protected function isTemplate() : bool
    {
        return ($this->getPage()->getParentType() === "prtt");
    }

    public function insert(Form\Standard $form = null) : void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$form) {
            $form = $this->getTemplateForm();
        }
        $tpl->setContent($this->ui->renderer()->render($form));
    }

    public function edit() : void
    {
        if ($this->isTemplate()) {
            $this->editTemplate();
            return;
        }
        $this->editPortfolio();
    }

    public function editTemplate(Form\Standard $form = null) : void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$form) {
            $form = $this->getTemplateForm(true);
        }
        $tpl->setContent($this->ui->renderer()->render($form));
    }

    public function getTemplateForm(bool $edit = false) : Form\Standard
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;

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

    public function create() : void
    {
        $request = $this->http_request;
        $form = $this->getTemplateForm();
        $lng = $this->lng;
        $tpl = $this->tpl;

        if ($request->getMethod() === "POST") {
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
                $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            }
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    protected function getRecordIdsFromForm(Form\Standard $form) : array
    {
        $data = $form->getData();
        $ids = [];
        if (!is_null($data) && is_array($data["sec"])) {
            $recs = $this->getAdvRecords();
            $ids = [];
            foreach ($recs as $r) {
                $rec_id = $data["sec"]["rec" . $r->getRecordId()];
                if (isset($rec_id) && $rec_id) {
                    $ids[] = $r->getRecordId();
                }
            }
        }
        return $ids;
    }

    protected function getAdvRecords() : array
    {
        if ($this->isTemplate()) {
            $id = $this->requested_ref_id;
            $is_ref_id = true;
        } else {
            $id = $this->getPage()->getPortfolioId();
            $is_ref_id = false;
        }

        $recs = \ilAdvancedMDRecord::_getSelectedRecordsByObject($this->getPage()->getParentType(), $id, "pfpg", $is_ref_id);
        return $recs;
    }

    public function update() : void
    {
        $request = $this->http_request;
        $form = $this->getTemplateForm(true);
        $lng = $this->lng;
        $tpl = $this->tpl;

        if ($request->getMethod() === "POST") {
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
                $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            }
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }

    ////
    //// Editing in portfolio
    ////


    /**
     * Edit courses form
     */
    public function editPortfolio(?ilPropertyFormGUI $form = null) : void
    {
        $tpl = $this->tpl;

        $this->displayValidationError();

        if (!$form) {
            $form = $this->getPortfolioForm(true);
        }
        $tpl->setContent($form->getHTML());
    }

    public function getPortfolioForm(bool $edit = false) : ilPropertyFormGUI
    {
        $content_obj = $this->content_obj;
        if (is_null($content_obj)) {
            $page = new ilPortfolioPage($this->port_request->getPortfolioPageId());
            $page->buildDom();
            $content_obj = $page->getContentObjectForPcId($this->request->getPCId());
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

    public function updateAdvancedMetaData() : void
    {
        $lng = $this->lng;

        $form = $this->getPortfolioForm(true);

        // needed for proper advanced MD validation
        $form->checkInput();
        if (!$this->record_gui->importEditFormPostValues()) {
            $this->editPortfolio($form); // #16470
            return;
        }

        if ($this->record_gui->writeEditForm()) {
            $this->tpl->setOnScreenMessage('success', $lng->txt("settings_saved"), true);
        }
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
}
