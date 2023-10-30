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
 * Class ilPCTableGUI
 * User Interface for Data Table Editing
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCDataTableGUI extends ilPCTableGUI
{
    protected \ILIAS\HTTP\Services $http;
    protected ilGlobalTemplateInterface $main_tpl;

    public function __construct(
        ilPageObject $a_pg_obj,
        ?ilPageContent $a_content_obj,
        string $a_hier_id,
        string $a_pc_id = ""
    ) {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
        $this->setCharacteristics(array("StandardTable" => $this->lng->txt("cont_StandardTable")));
        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->http = $DIC->http();
    }

    protected function getFormTitle(string $a_mode = "edit"): string
    {
        if ($a_mode === "create") {
            return $this->lng->txt("cont_ed_insert_dtab");
        }
        return $this->lng->txt("cont_table_properties");
    }

    /**
     * execute command
     * @return mixed
     */
    public function executeCommand()
    {
        $this->getCharacteristicsOfCurrentStyle(["table"]);	// scorm-2004

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


    ////
    //// Classic editing
    ////

    /**
     * Update table data in dom and update page in db
     */
    public function update(bool $a_redirect = true): void
    {
        $lng = $this->lng;

        // handle input data
        $data = array();
        $cell = $this->request->getArrayArray("cell");
        if (is_array($cell)) {
            foreach ($cell as $i => $row) {
                if (is_array($row)) {
                    foreach ($row as $j => $cell) {
                        $data[$i][$j] =
                            ilPCParagraph::_input2xml(
                                $cell,
                                $this->content_obj->getLanguage()
                            );
                    }
                }
            }
        }

        $this->updated = $this->content_obj->setData($data);

        if ($this->updated !== true) {
            $this->editData();
            return;
        }

        $this->updated = $this->pg_obj->update();

        if ($a_redirect) {
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editData");
        }
    }

    /**
     * Update via JavaScript
     */
    public function updateJS(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->request->getString("cancel_update") != "") {
            //			$this->ctrl->redirect($this, "editData");
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        }

        // handle input data
        $data = array();
        $post = $this->http->request()->getParsedBody();
        foreach ($post as $k => $content) {
            if (substr($k, 0, 5) != "cell_") {
                continue;
            }

            // determine cell content
            $div = ilUtil::stripSlashes($content, false);
            $p1 = strpos($div, '>');
            $div = substr($div, $p1 + 1);
            $div = "<div class='ilc_text_block_TableContent'>" . $div;
            $text = ilPCParagraph::handleAjaxContent($div);
            if ($text === false) {
                $ilCtrl->returnToParent($this, "jump" . $this->hier_id);
            }
            $text = $text["text"];

            $text = ilPCParagraph::_input2xml(
                $text,
                $this->content_obj->getLanguage(),
                true,
                false
            );
            $text = ilPCParagraph::handleAjaxContentPost($text);

            // set content in data array
            $id = explode("_", $k);
            $data[(int) $id[1]][(int) $id[2]] = $text;
        }

        // update data
        $this->updated = $this->content_obj->setData($data);

        if ($this->updated !== true) {
            $this->editData();
            return;
        }

        $this->updated = $this->pg_obj->update();

        $tab_cmd_id = $this->request->getInt("tab_cmd_id");
        $tab_cmd_type = $this->request->getString("tab_cmd_type");
        $tab_cmd = $this->request->getString("tab_cmd");

        // perform table action? (move...?)
        //$this->update(false);
        $this->pg_obj->addHierIDs();
        $failed = false;
        if ($tab_cmd != "") {
            $cell_hier_id = ($tab_cmd_type == "col")
                ? $this->hier_id . "_1_" . ($tab_cmd_id + 1)
                : $this->hier_id . "_" . ($tab_cmd_id + 1) . "_1";
            $cell_obj = $this->pg_obj->getContentObject($cell_hier_id);
            if (is_object($cell_obj)) {
                $cell_obj->$tab_cmd();
                $ret = $this->pg_obj->update();
                if ($ret !== true) {
                    $this->main_tpl->setOnScreenMessage('failure', $ret[0][1], true);
                    $failed = true;
                }
            }
        }

        if (!$failed) {
            $this->main_tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        if ($this->request->getString("save_return") != "") {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->ctrl->redirect($this, "editData");
        }
    }


    /**
     * Get new table object
     */
    public function getNewTableObject(): ilPCDataTable
    {
        return new ilPCDataTable($this->getPage());
    }

    /**
     * After creation processing
     */
    public function afterCreation(): void
    {
        $ilCtrl = $this->ctrl;

        $this->pg_obj->stripHierIDs();
        $this->pg_obj->addHierIDs();
        $ilCtrl->setParameter($this, "hier_id", $this->content_obj->readHierId());
        $ilCtrl->setParameter($this, "pc_id", $this->content_obj->readPCId());
        $ilCtrl->redirect($this, "editData");
    }

    /**
     * Perform operation on table (adding, moving, deleting rows/cols)
     */
    public function tableAction(): void
    {
        $ilCtrl = $this->ctrl;

        $this->update(false);
        $this->pg_obj->addHierIDs();

        $type = $this->request->getString("type");
        $action = $this->request->getString("action");
        $id = $this->request->getInt("id");

        $cell_hier_id = ($type == "col")
            ? $this->hier_id . "_1_" . ($id + 1)
            : $this->hier_id . "_" . ($id + 1) . "_1";
        $cell_obj = $this->pg_obj->getContentObject($cell_hier_id);
        if (is_object($cell_obj)) {
            $cell_obj->$action();
            $this->edit_repo->setPageError($this->pg_obj->update());
        }
        $ilCtrl->redirect($this, "editData");
    }

    /**
     * Set tabs
     */
    public function setTabs(string $data_tab_txt_key = ""): void
    {
        parent::setTabs("cont_ed_edit_data");
    }

    protected function getCellContent(int $i, int $j): string
    {
        $cmd = $this->ctrl->getCmd();
        if ($cmd == "update") {
            $s_text = ilUtil::stripSlashes("cell_" . $i . "_" . $j, false);
        } else {
            $s_text = ilPCParagraph::xml2output(
                $this->content_obj->getCellText($i, $j),
                true,
                false
            );
            include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
            $s_text = ilPCParagraphGUI::xml2outputJS(
                $s_text,
                "TableContent",
                $this->content_obj->readPCId() . "_" . $i . "_" . $j
            );
        }

        // #20628
        $s_text = str_replace("{", "&#123;", $s_text);
        $s_text = str_replace("}", "&#125;", $s_text);
        return $s_text;
    }

    public function initCreationForm(
    ): ilPropertyFormGUI {

        $a_seleted_value = "";
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setShowTopButtons(false);
        $form->setTitle($this->getFormTitle("create"));

        $nr = array();
        for ($i = 1; $i <= 20; $i++) {
            $nr[$i] = $i;
        }

        // cols
        $cols = new ilSelectInputGUI($this->lng->txt("cont_nr_cols"), "nr_cols");
        $cols->setOptions($nr);
        $cols->setValue(2);
        $form->addItem($cols);

        // rows
        $rows = new ilSelectInputGUI($this->lng->txt("cont_nr_rows"), "nr_rows");
        $rows->setOptions($nr);
        $rows->setValue(2);
        $form->addItem($rows);

        // table templates and table classes
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_table_style"),
            "characteristic"
        );
        $chars = $this->getCharacteristics();
        $templates = $this->getTemplateOptions();
        $chars = array_merge($templates, $chars);
        if (is_object($this->content_obj)) {
            if (($chars[$a_seleted_value] ?? "") == "" && ($this->content_obj->getClass() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getClass() => $this->content_obj->getClass()),
                    $chars
                );
            }
        }
        foreach ($chars as $k => $char) {
            if (strpos($k, ":") > 0) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both;" class="small">' . $char . "</div>";
            } else {
                $html = '<table class="ilc_table_' . $k . '"><tr><td class="small">' .
                    $char . '</td></tr></table>';
            }
            $char_prop->addOption($k, $char, $html);
        }
        if (count($chars) > 1) {
            $char_prop->setValue("StandardTable");
            $form->addItem($char_prop);
        }

        // row header
        $cb = new ilCheckboxInputGUI($lng->txt("cont_has_row_header"), "has_row_header");
        $form->addItem($cb);

        $form->addCommandButton("create_tab", $lng->txt("save"));
        $form->addCommandButton("cancelCreate", $lng->txt("cancel"));

        return $form;
    }

    public function initEditingForm(
    ): ilPropertyFormGUI {

        $a_seleted_value = "";
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setShowTopButtons(false);
        $form->setTitle($this->getFormTitle("edit"));

        // table templates and table classes
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_table_style"),
            "characteristic"
        );
        $chars = $this->getCharacteristics();
        $templates = $this->getTemplateOptions();
        $chars = array_merge($templates, $chars);
        if (is_object($this->content_obj)) {
            if (($chars[$a_seleted_value] ?? "") == "" && ($this->content_obj->getClass() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getClass() => $this->content_obj->getClass()),
                    $chars
                );
            }
        }
        foreach ($chars as $k => $char) {
            if (strpos($k, ":") > 0) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both;" class="small">' . $char . "</div>";
            } else {
                $html = '<table class="ilc_table_' . $k . '"><tr><td class="small">' .
                    $char . '</td></tr></table>';
            }
            $char_prop->addOption($k, $char, $html);
        }
        if (count($chars) > 1) {
            if ($this->content_obj->getTemplate() !== "") {
                $val = "t:" .
                    ilObjStyleSheet::_lookupTemplateIdByName($this->getStyleId(), $this->content_obj->getTemplate()) . ":" .
                    $this->content_obj->getTemplate();
            } else {
                $val = $this->content_obj->getClass();
            }
            $char_prop->setValue($val);
            $form->addItem($char_prop);
        }

        // row header
        $cb = new ilCheckboxInputGUI($lng->txt("cont_has_row_header"), "has_row_header");
        if ($this->content_obj->getHeaderRows() > 0) {
            $cb->setChecked(true);
        }
        $form->addItem($cb);

        return $form;
    }

    public function initImportForm(
    ): ilPropertyFormGUI {

        $a_seleted_value = "";
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setShowTopButtons(false);
        $form->setTitle($this->getFormTitle("create"));

        $hi = new ilHiddenInputGUI("import");
        $hi->setValue("1");
        $form->addItem($hi);

        $import_data = new ilTextAreaInputGUI("", "import_table");
        $import_data->setInfo($this->lng->txt("cont_table_import_info"));
        $import_data->setRows(8);
        $import_data->setCols(50);
        $form->addItem($import_data);

        // table templates and table classes
        $char_prop = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_table_style"),
            "import_characteristic"
        );
        $chars = $this->getCharacteristics();
        $templates = $this->getTemplateOptions();
        $chars = array_merge($templates, $chars);
        if (is_object($this->content_obj)) {
            if (($chars[$a_seleted_value] ?? "") == "" && ($this->content_obj->getClass() != "")) {
                $chars = array_merge(
                    array($this->content_obj->getClass() => $this->content_obj->getClass()),
                    $chars
                );
            }
        }
        foreach ($chars as $k => $char) {
            if (strpos($k, ":") > 0) {
                $t = explode(":", $k);
                $html = $this->style->lookupTemplatePreview($t[1]) . '<div style="clear:both;" class="small">' . $char . "</div>";
            } else {
                $html = '<table class="ilc_table_' . $k . '"><tr><td class="small">' .
                    $char . '</td></tr></table>';
            }
            $char_prop->addOption($k, $char, $html);
        }
        if (count($chars) > 1) {
            $char_prop->setValue("StandardTable");
            $form->addItem($char_prop);
        }

        // row header
        $cb = new ilCheckboxInputGUI($lng->txt("cont_has_row_header"), "has_row_header");
        $form->addItem($cb);


        $form->addCommandButton("create_tab", $lng->txt("save"));
        $form->addCommandButton("cancelCreate", $lng->txt("cancel"));

        return $form;
    }

    public function initCellPropertiesForm(
    ): ilPropertyFormGUI {

        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setShowTopButtons(false);
        $form->setTitle($this->lng->txt("cont_cell_properties"));

        $style_cb = new ilCheckboxInputGUI($lng->txt("cont_change_style"), "style_cb");

        $style = new ilAdvSelectInputGUI(
            $this->lng->txt("cont_style"),
            "style"
        );
        $this->setBasicTableCellStyles();
        $this->getCharacteristicsOfCurrentStyle(["table_cell"]);	// scorm-2004
        $chars = $this->getCharacteristics();	// scorm-2004
        $options = array_merge(array("" => $this->lng->txt("none")), $chars);	// scorm-2004
        foreach ($options as $k => $option) {
            $html = '<table border="0" cellspacing="0" cellpadding="0"><tr><td class="ilc_table_cell_' . $k . '">' .
                $option . '</td></tr></table>';
            $style->addOption($k, $option, $html);
        }

        if (count($options) > 1) {
            $style_cb->addSubItem($style);
            $form->addItem($style_cb);
        }

        $width_cb = new ilCheckboxInputGUI($lng->txt("cont_change_width"), "width_cb");
        $ti = new ilTextInputGUI($lng->txt("cont_width"), "width");
        $ti->setMaxLength(20);
        $ti->setSize(7);
        $width_cb->addSubItem($ti);
        $form->addItem($width_cb);

        // alignment
        $al_cb = new ilCheckboxInputGUI($lng->txt("cont_change_alignment"), "al_cb");
        $options = array(
            "" => $lng->txt("default"),
            "Left" => $lng->txt("cont_left"),
            "Center" => $lng->txt("cont_center"),
            "Right" => $lng->txt("cont_right")
        );
        $si = new ilSelectInputGUI($lng->txt("cont_alignment"), "alignment");
        $si->setOptions($options);
        $al_cb->addSubItem($si);
        $form->addItem($al_cb);

        return $form;
    }

}
