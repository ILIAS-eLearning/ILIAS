<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

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
        ilPageContent $a_content_obj,
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
    * Edit data of table. (classic version)
    */
    public function editDataCl() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();

        $this->displayValidationError();
        
        $this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.tabledata.html", "Services/COPage");
        $dtpl = $this->tpl;
        //$dtpl = new ilTemplate("tpl.tabledata.html", true, true, "Services/COPage");
        $dtpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "tableAction"));
        $dtpl->setVariable("BB_MENU", $this->getBBMenu("cell_0_0"));

        ilYuiUtil::initDragDrop();
        ilYuiUtil::initConnection();
        $this->tpl->addJavaScript("./Services/COPage/phpBB/3_0_5/editor.js");
        $this->tpl->addJavaScript("./Services/COPage/js/paragraph_editing.js");

        // get all rows
        $xpc = xpath_new_context($this->dom);
        $path = "//PageContent[@HierId='" . $this->getHierId() . "']" .
            "/Table/TableRow";
        $res = xpath_eval($xpc, $path);

        for ($i = 0; $i < count($res->nodeset); $i++) {
            $xpc2 = xpath_new_context($this->dom);
            $path2 = "//PageContent[@HierId='" . $this->getHierId() . "']" .
                "/Table/TableRow[$i+1]/TableData";
            $res2 = xpath_eval($xpc2, $path2);
            
            // if this is the first row -> col icons
            if ($i == 0) {
                for ($j = 0; $j < count($res2->nodeset); $j++) {
                    if ($j == 0) {
                        $dtpl->touchBlock("empty_td");
                    }

                    if ($j == 0) {
                        if (count($res2->nodeset) == 1) {
                            $move_type = "none";
                        } else {
                            $move_type = "forward";
                        }
                    } elseif ($j == (count($res2->nodeset) - 1)) {
                        $move_type = "backward";
                    } else {
                        $move_type = "both";
                    }
                    $dtpl->setCurrentBlock("col_icon");
                    $dtpl->setVariable("COL_ICON_ALT", $lng->txt("content_column"));
                    $dtpl->setVariable("COL_ICON", ilUtil::getImagePath("col.svg"));
                    $dtpl->setVariable("COL_ONCLICK", "COL_" . $move_type);
                    $dtpl->setVariable("NR", $j);
                    $dtpl->parseCurrentBlock();
                }
                $dtpl->setCurrentBlock("row");
                $dtpl->parseCurrentBlock();
            }


            for ($j = 0; $j < count($res2->nodeset); $j++) {
                // first col: row icons
                if ($j == 0) {
                    if ($i == 0) {
                        if (count($res->nodeset) == 1) {
                            $move_type = "none";
                        } else {
                            $move_type = "forward";
                        }
                    } elseif ($i == (count($res->nodeset) - 1)) {
                        $move_type = "backward";
                    } else {
                        $move_type = "both";
                    }
                    $dtpl->setCurrentBlock("row_icon");
                    $dtpl->setVariable("ROW_ICON_ALT", $lng->txt("content_row"));
                    $dtpl->setVariable("ROW_ICON", ilUtil::getImagePath("row.svg"));
                    $dtpl->setVariable("ROW_ONCLICK", "ROW_" . $move_type);
                    $dtpl->setVariable("NR", $i);
                    $dtpl->parseCurrentBlock();
                }
                
                // cell
                if ($res2->nodeset[$j]->get_attribute("Hidden") != "Y") {
                    $dtpl->setCurrentBlock("cell");

                    $cmd = $ilCtrl->getCmd();
                    if ($cmd == "update") {
                        $s_text = ilUtil::stripSlashes("cell_" . $i . "_" . $j, false);
                    } else {
                        $s_text = ilPCParagraph::xml2output($this->content_obj->getCellText($i, $j));
                    }
    
                    $dtpl->setVariable("PAR_TA_NAME", "cell[" . $i . "][" . $j . "]");
                    $dtpl->setVariable("PAR_TA_ID", "cell_" . $i . "_" . $j);
                    $dtpl->setVariable("PAR_TA_CONTENT", $s_text);
                    
                    $cs = $res2->nodeset[$j]->get_attribute("ColSpan");
                    $rs = $res2->nodeset[$j]->get_attribute("RowSpan");
                    //					$dtpl->setVariable("WIDTH", "140");
                    //					$dtpl->setVariable("HEIGHT", "80");
                    if ($cs > 1) {
                        $dtpl->setVariable("COLSPAN", 'colspan="' . $cs . '"');
                        $dtpl->setVariable("WIDTH", (140 + ($cs - 1) * 146));
                    }
                    if ($rs > 1) {
                        $dtpl->setVariable("ROWSPAN", 'rowspan="' . $rs . '"');
                        $dtpl->setVariable("HEIGHT", (80 + ($rs - 1) * 86));
                    }
                    $dtpl->parseCurrentBlock();
                }
            }
            $dtpl->setCurrentBlock("row");
            $dtpl->parseCurrentBlock();
        }
        
        // init menues
        $types = array("row", "col");
        $moves = array("none", "backward", "both", "forward");
        $commands = array(
            "row" => array(	"newRowAfter" => "cont_ed_new_row_after",
                            "newRowBefore" => "cont_ed_new_row_before",
                            "moveRowUp" => "cont_ed_row_up",
                            "moveRowDown" => "cont_ed_row_down",
                            "deleteRow" => "cont_ed_delete_row"),
            "col" => array(	"newColAfter" => "cont_ed_new_col_after",
                            "newColBefore" => "cont_ed_new_col_before",
                            "moveColLeft" => "cont_ed_col_left",
                            "moveColRight" => "cont_ed_col_right",
                            "deleteCol" => "cont_ed_delete_col")
        );
        foreach ($types as $type) {
            foreach ($moves as $move) {
                foreach ($commands[$type] as $command => $lang_var) {
                    if ($move == "none" && (substr($command, 0, 4) == "move" || substr($command, 0, 6) == "delete")) {
                        continue;
                    }
                    if (($move == "backward" && (in_array($command, array("movedown", "moveright")))) ||
                        ($move == "forward" && (in_array($command, array("moveup", "moveleft"))))) {
                        continue;
                    }
                    $this->tpl->setCurrentBlock("menu_item");
                    $this->tpl->setVariable("MENU_ITEM_TITLE", $lng->txt($lang_var));
                    $this->tpl->setVariable("CMD", $command);
                    $this->tpl->setVariable("TYPE", $type);
                    $this->tpl->parseCurrentBlock();
                }
                $this->tpl->setCurrentBlock("menu");
                $this->tpl->setVariable("TYPE", $type);
                $this->tpl->setVariable("MOVE", $move);
                $this->tpl->parseCurrentBlock();
            }
        }
        
        // update/cancel
        $this->tpl->setCurrentBlock("commands");
        $this->tpl->setVariable("BTN_NAME", "update");
        $this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
        $this->tpl->parseCurrentBlock();
        
        $this->tpl->setVariable(
            "FORMACTION2",
            $ilCtrl->getFormAction($this, "tableAction")
        );
        $this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_table"));
    }
    
    /**
     * Update table data in dom and update page in db
     */
    public function update(bool $a_redirect = true) : void
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
    public function updateJS() : void
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
    public function getNewTableObject() : ilPCDataTable
    {
        return new ilPCDataTable($this->getPage());
    }
    
    /**
     * After creation processing
     */
    public function afterCreation() : void
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
    public function tableAction() : void
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
    public function setTabs(string $data_tab_txt_key = "") : void
    {
        parent::setTabs("cont_ed_edit_data");
    }
}
