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
 * User Interface for Tabbed Content
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCTabsGUI extends ilPageContentGUI
{
    protected ilPropertyFormGUI $form;
    protected ilDBInterface $db;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;

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
        $this->db = $DIC->database();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
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

    /**
     * Insert new tabs
     */
    public function insert(
        bool $a_omit_form_init = false
    ) : void {
        $tpl = $this->tpl;
        
        $this->displayValidationError();

        if (!$a_omit_form_init) {
            $this->initForm("create");
        }
        $html = $this->form->getHTML();
        $tpl->setContent($html);
    }

    public function editProperties($init_form = true) : void
    {
        $tpl = $this->tpl;
        
        $this->displayValidationError();
        $this->setTabs();

        if ($init_form) {
            $this->initForm();
            $this->getFormValues();
        }
        $html = $this->form->getHTML();
        $tpl->setContent($html);
    }

    public function initForm(
        string $a_mode = "edit"
    ) : void {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        ilAccordionGUI::addCss();

        // edit form
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this));
        if ($a_mode != "edit") {
            $this->form->setTitle($lng->txt("cont_ed_insert_tabs"));
        } else {
            $this->form->setTitle($lng->txt("cont_edit_tabs"));
        }
        

        // type selection
        $radg = new ilRadioGroupInputGUI($lng->txt("cont_type"), "type");
        $radg->setValue(ilPCTabs::ACCORDION_VER);

        // type: vertical accordion
        $op1 = new ilRadioOption($lng->txt("cont_tabs_acc_ver"), ilPCTabs::ACCORDION_VER);

        $templ = $this->getTemplateOptions("vaccordion");
        if (count($templ) > 0) {
            $vchar_prop = new ilAdvSelectInputGUI(
                $this->lng->txt("cont_characteristic"),
                "vaccord_templ"
            );
            foreach ($templ as $k => $te) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both" class="small">' . $te . "</div>";
                $vchar_prop->addOption($k, $te, $html);
                if ($t[2] == "VerticalAccordion") {
                    $vchar_prop->setValue($k);
                }
            }
            $op1->addSubItem($vchar_prop);
        } else {
            $vchar_prop = new ilHiddenInputGUI("vaccord_templ");
            $this->form->addItem($vchar_prop);
        }
        $radg->addOption($op1);



        // type: horizontal accordion
        $op2 = new ilRadioOption($lng->txt("cont_tabs_acc_hor"), ilPCTabs::ACCORDION_HOR);

        $templ = $this->getTemplateOptions("haccordion");
        if (count($templ) > 0) {
            $hchar_prop = new ilAdvSelectInputGUI(
                $this->lng->txt("cont_characteristic"),
                "haccord_templ"
            );
            foreach ($templ as $k => $te) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both" class="small">' . $te . "</div>";
                $hchar_prop->addOption($k, $te, $html);
                if ($t[2] == "HorizontalAccordion") {
                    $hchar_prop->setValue($k);
                }
            }
            $op2->addSubItem($hchar_prop);
        } else {
            $hchar_prop = new ilHiddenInputGUI("haccord_templ");
            $this->form->addItem($hchar_prop);
        }

        $radg->addOption($op2);

        // type: carousel
        $op3 = new ilRadioOption($lng->txt("cont_tabs_carousel"), ilPCTabs::CAROUSEL);
        $templ = $this->getTemplateOptions("carousel");
        if (count($templ) > 0) {
            $cchar_prop = new ilAdvSelectInputGUI(
                $this->lng->txt("cont_characteristic"),
                "carousel_templ"
            );
            foreach ($templ as $k => $te) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both" class="small">' . $te . "</div>";
                $cchar_prop->addOption($k, $te, $html);
                if ($t[2] == "Carousel") {
                    $cchar_prop->setValue($k);
                }
            }
            $op3->addSubItem($cchar_prop);
        } else {
            $cchar_prop = new ilHiddenInputGUI("carousel_templ");
            $this->form->addItem($cchar_prop);
        }

        $radg->addOption($op3);
        $this->form->addItem($radg);
        
        
        // number of initial tabs
        if ($a_mode == "create") {
            $nr_prop = new ilSelectInputGUI(
                $lng->txt("cont_number_of_tabs"),
                "nr"
            );
            $nrs = array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6,
                7 => 7, 8 => 8, 9 => 9, 10 => 10);
            $nr_prop->setOptions($nrs);
            $this->form->addItem($nr_prop);
        }
        
        $ni = new ilNumberInputGUI($this->lng->txt("cont_tab_cont_width"), "content_width");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $this->form->addItem($ni);
        
        $ni = new ilNumberInputGUI($this->lng->txt("cont_tab_cont_height"), "content_height");
        $ni->setMaxLength(4);
        $ni->setSize(4);
        $this->form->addItem($ni);

        // behaviour
        $options = array(
            "AllClosed" => $lng->txt("cont_all_closed"),
            "FirstOpen" => $lng->txt("cont_first_open"),
            "ForceAllOpen" => $lng->txt("cont_force_all_open"),
            );
        $si = new ilSelectInputGUI($this->lng->txt("cont_behavior"), "vbehavior");
        $si->setOptions($options);
        $op1->addSubItem($si);
        $si = new ilSelectInputGUI($this->lng->txt("cont_behavior"), "hbehavior");
        $si->setOptions($options);
        $op2->addSubItem($si);
        
        
        // alignment
        $align_opts = array("Left" => $lng->txt("cont_left"),
            "Right" => $lng->txt("cont_right"), "Center" => $lng->txt("cont_center"),
            "LeftFloat" => $lng->txt("cont_left_float"),
            "RightFloat" => $lng->txt("cont_right_float"));
        $align = new ilSelectInputGUI($this->lng->txt("cont_align"), "valign");
        $align->setOptions($align_opts);
        $align->setValue("Center");
        //$align->setInfo($lng->txt("cont_tabs_hor_align_info"));
        $op1->addSubItem($align);
        $align = new ilSelectInputGUI($this->lng->txt("cont_align"), "calign");
        $align->setOptions($align_opts);
        $align->setValue("Center");
        $op3->addSubItem($align);

        // carousel: time
        $ti = new ilNumberInputGUI($this->lng->txt("cont_auto_time"), "auto_time");
        $ti->setMaxLength(6);
        $ti->setSize(6);
        $ti->setSuffix("ms");
        $ti->setMinValue(100);
        $op3->addSubItem($ti);

        // carousel: random start
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_rand_start"), "rand_start");
        //$cb->setOptionTitle($this->lng->txt(""));
        //$cb->setInfo($this->lng->txt(""));
        $op3->addSubItem($cb);


        // save/cancel buttons
        if ($a_mode == "create") {
            $this->form->addCommandButton("create_section", $lng->txt("save"));
            $this->form->addCommandButton("cancelCreate", $lng->txt("cancel"));
        } else {
            $this->form->addCommandButton("update", $lng->txt("save"));
            $this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
        }
    }

    public function getFormValues() : void
    {
        $values["type"] = $this->content_obj->getTabType();
        $values["content_width"] = $this->content_obj->getContentWidth();
        $values["content_height"] = $this->content_obj->getContentHeight();
        $values["valign"] = $this->content_obj->getHorizontalAlign();
        $values["calign"] = $this->content_obj->getHorizontalAlign();
        $values["vbehavior"] = $this->content_obj->getBehavior();
        $values["hbehavior"] = $this->content_obj->getBehavior();

        $values["auto_time"] = $this->content_obj->getAutoTime();
        $values["rand_start"] = $this->content_obj->getRandomStart();

        $this->form->setValuesByArray($values);
        
        if ($values["type"] == ilPCTabs::ACCORDION_VER) {
            $va = $this->form->getItemByPostVar("vaccord_templ");
            $v = "t:" .
                ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()) . ":" .
                $this->content_obj->getTemplate();
            $va->setValue($v);
        }
        if ($values["type"] == ilPCTabs::ACCORDION_HOR) {
            $ha = $this->form->getItemByPostVar("haccord_templ");
            $v = "t:" .
                ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()) . ":" .
                $this->content_obj->getTemplate();
            $ha->setValue($v);
        }
        if ($values["type"] == ilPCTabs::CAROUSEL) {
            $ca = $this->form->getItemByPostVar("carousel_templ");
            $v = "t:" .
                ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()) . ":" .
                $this->content_obj->getTemplate();
            $ca->setValue($v);
        }
    }

    public function create() : void
    {
        $lng = $this->lng;
        
        $this->initForm("create");
        if ($this->form->checkInput()) {
            $this->content_obj = new ilPCTabs($this->getPage());
            $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);

            $this->setPropertiesByForm();

            for ($i = 0; $i < $this->request->getInt("nr"); $i++) {
                $this->content_obj->addTab($lng->txt("cont_new_tab"));
            }

            $this->updated = $this->pg_obj->update();

            if ($this->updated === true) {
                $this->afterCreation();
            } else {
                $this->insert();
            }
        } else {
            $this->form->setValuesByPost();
            $this->insert(true);
        }
    }
    
    public function afterCreation() : void
    {
        $ilCtrl = $this->ctrl;

        $this->pg_obj->stripHierIDs();
        $this->pg_obj->addHierIDs();
        $ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
        $ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
        $this->content_obj->setHierId($this->content_obj->readHierId());
        $this->setHierId($this->content_obj->readHierId());
        $this->content_obj->setPcId($this->content_obj->readPCId());
        $this->edit();
    }

    public function setPropertiesByForm() : void
    {
        $c = $this->content_obj;
        $f = $this->form;

        $c->setTabType($f->getInput("type"));

        $c->setContentWidth((string) $f->getInput("content_width"));
        $c->setContentHeight((string) $f->getInput("content_height"));
        $c->setTemplate("");
        switch ($this->request->getString("type")) {
            case ilPCTabs::ACCORDION_VER:
                $t = explode(":", $f->getInput("vaccord_templ"));
                $c->setTemplate($t[2] ?? "");
                $c->setBehavior($f->getInput("vbehavior"));
                $c->setHorizontalAlign($f->getInput("valign"));
                break;

            case ilPCTabs::ACCORDION_HOR:
                $t = explode(":", $f->getInput("haccord_templ"));
                $c->setTemplate($t[2] ?? "");
                $c->setBehavior($f->getInput("hbehavior"));
                break;

            case ilPCTabs::CAROUSEL:
                $t = explode(":", $f->getInput("carousel_templ"));
                $c->setTemplate($t[2] ?? "");
                $c->setHorizontalAlign($f->getInput("calign"));
                $c->setAutoTime($f->getInput("auto_time"));
                $c->setRandomStart($f->getInput("rand_start"));
                break;
        }
    }

    public function update() : void
    {
        $this->initForm();
        $this->updated = false;
        if ($this->form->checkInput()) {
            $this->setPropertiesByForm();
            $this->updated = $this->pg_obj->update();
        } else {
            $this->form->setValuesByPost();
            $this->editProperties(false);
            return;
        }
        if ($this->updated === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editProperties");
        } else {
            $this->pg_obj->addHierIDs();
            $this->editProperties(false);
        }
    }
    
    public function edit() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;

        $ilToolbar->addButton(
            $lng->txt("cont_add_tab"),
            $ilCtrl->getLinkTarget($this, "addTab")
        );

        $this->setTabs();
        $ilTabs->activateTab("cont_tabs");
        /** @var ilPCTabs $tabs */
        $tabs = $this->content_obj;
        $table_gui = new ilPCTabsTableGUI($this, "edit", $tabs);
        $tpl->setContent($table_gui->getHTML());
    }
    
    /**
     * Save tabs properties in db and return to page edit screen
     */
    public function saveTabs() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $captions = $this->request->getStringArray("caption");
        $positions = $this->request->getStringArray("position");
        if (count($captions) > 0) {
            $this->content_obj->saveCaptions($captions);
        }
        if (count($positions)) {
            $this->content_obj->savePositions($positions);
        }
        $this->updated = $this->pg_obj->update();
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "edit");
    }

    /**
     * Save tabs properties in db and return to page edit screen
     */
    public function addTab() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->content_obj->addTab($lng->txt("cont_new_tab"));
        $this->updated = $this->pg_obj->update();

        $this->tpl->setOnScreenMessage('success', $lng->txt("cont_added_tab"), true);
        $ilCtrl->redirect($this, "edit");
    }
    
    public function confirmTabsDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->setTabs();

        $tids = $this->request->getStringArray("tid");
        if (count($tids) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "edit");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("cont_tabs_confirm_deletion"));
            $cgui->setCancel($lng->txt("cancel"), "cancelTabDeletion");
            $cgui->setConfirm($lng->txt("delete"), "deleteTabs");
            
            foreach ($tids as $k => $i) {
                $id = explode(":", $k);
                $cgui->addItem(
                    "tid[]",
                    $k,
                    $this->content_obj->getCaption($id[0], $id[1])
                );
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    public function cancelTabDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "edit");
    }
    
    public function deleteTabs() : void
    {
        $ilCtrl = $this->ctrl;

        $tids = $this->request->getStringArray("tid");
        foreach ($tids as $tid) {
            $ids = explode(":", $tid);
            $this->content_obj->deleteTab($ids[0], $ids[1]);
        }
        $this->updated = $this->pg_obj->update();
        
        $ilCtrl->redirect($this, "edit");
    }
    
    public function setTabs() : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->setBackTarget(
            $lng->txt("pg"),
            $this->ctrl->getParentReturn($this)
        );

        $ilTabs->addTarget(
            "cont_tabs",
            $ilCtrl->getLinkTarget($this, "edit"),
            "edit",
            get_class($this)
        );

        $ilTabs->addTarget(
            "cont_edit_tabs",
            $ilCtrl->getLinkTarget($this, "editProperties"),
            "editProperties",
            get_class($this)
        );
    }
}
