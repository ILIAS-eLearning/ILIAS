<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilObjTypeDefinitionGUI
*
* handles operation assignment to objects (ONLY FOR TESTING PURPOSES!)
*
* @author Stefan Meyer <meyer@leifos.com>
* $Id$Id: class.ilObjTypeDefinitionGUI.php,v 1.14 2005/11/21 17:12:08 shofmann Exp $
*
* @extends ilObjectGUI
*/

require_once "./Services/Object/classes/class.ilObjectGUI.php";

class ilObjTypeDefinitionGUI extends ilObjectGUI
{
    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
    * Constructor
    *
    * @access	public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->rbacadmin = $DIC->rbac()->admin();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->type = "typ";
        parent::__construct($a_data, $a_id, $a_call_by_reference);
    }

    /**
    * list operations of object type
    * @access	public
    */
    public function viewObject()
    {
        $rbacadmin = $this->rbacadmin;
        $rbacreview = $this->rbacreview;
        
        //prepare objectlist
        $this->data = array();
        $this->data["data"] = array();
        $this->data["ctrl"] = array();
        $this->data["cols"] = array("type", "operation", "description", "status");

        $ops_valid = $rbacreview->getOperationsOnType($_GET["obj_id"]);

        if ($list = ilRbacReview::_getOperationList("", $_GET["order"], $_GET["direction"])) {
            foreach ($list as $key => $val) {
                if (in_array($val["ops_id"], $ops_valid)) {
                    $ops_status = 'enabled';
                } else {
                    $ops_status = 'disabled';
                }

                //visible data part
                $this->data["data"][] = array(
                                    "type" 			=> "perm",
                                    "operation"		=> $val["operation"],
                                    "description"	=> $val["desc"],
                                    "status"		=> $ops_status,
                                    "obj_id"		=> $val["ops_id"]
                    );
            }
        } //if typedata

        $this->maxcount = count($this->data["data"]);

        // sorting array
        $this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = array(
                                            "obj_id"	=> $val["obj_id"],
                                            "type"		=> $val["type"]
                                            );

            unset($this->data["data"][$key]["obj_id"]);
        }

        $this->displayList();
    }

    /**
    * display object list
    *
    * @access	public
    */
    public function displayList()
    {
        include_once "./Services/Table/classes/class.ilTableGUI.php";

        // load template for table
        $this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
        // load template for table content data
        $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        $num = 0;

        $obj_str = ($this->call_by_reference) ? "" : "&obj_id=" . $this->obj_id;
        $this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=" . $this->ref_id . "$obj_str&cmd=gateway");

        // create table
        $tbl = new ilTableGUI();
        
        // title & header columns
        $tbl->setTitle($this->lng->txt("obj_" . $this->object->getType()) . " '" . $this->object->getTitle() . "'");

        foreach ($this->data["cols"] as $val) {
            $header_names[] = $this->lng->txt($val);
        }
        
        $tbl->setHeaderNames($header_names);

        $header_params = array("ref_id" => $this->ref_id,"obj_id" => $this->id);
        $tbl->setHeaderVars($this->data["cols"], $header_params);
        
        // control
        $tbl->setOrderColumn($_GET["sort_by"]);
        $tbl->setOrderDirection($_GET["sort_order"]);
        $tbl->setLimit(0);
        $tbl->setOffset(0);
        $tbl->setMaxCount($this->maxcount);
        
        // footer
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        //$tbl->disable("footer");
        
        // render table
        $tbl->render();

        if (is_array($this->data["data"][0])) {
            //table cell
            for ($i=0; $i < count($this->data["data"]); $i++) {
                $data = $this->data["data"][$i];
                $ctrl = $this->data["ctrl"][$i];

                // color changing
                $css_row = ilUtil::switchColor($i+1, "tblrow1", "tblrow2");

                $this->tpl->setCurrentBlock("table_cell");
                $this->tpl->setVariable("CELLSTYLE", "tblrow1");
                $this->tpl->parseCurrentBlock();

                foreach ($data as $key => $val) {
                    // color for status
                    if ($key == "status") {
                        if ($val == "enabled") {
                            $color = "green";
                        } else {
                            $color = "red";
                        }

                        $val = "<font color=\"" . $color . "\">" . $this->lng->txt($val) . "</font>";
                    }

                    $this->tpl->setCurrentBlock("text");

                    if ($key == "type") {
                        $val = ilUtil::getImageTagByType($val, $this->tpl->tplPath);
                    }

                    $this->tpl->setVariable("TEXT_CONTENT", $val);
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("table_cell");
                    $this->tpl->parseCurrentBlock();
                } //foreach

                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("CSS_ROW", $css_row);
                $this->tpl->parseCurrentBlock();
            } //for
        } //if is_array
    }

    /**
    * save (de-)activation of operations on object
    *
    * @access	public
    */
    public function saveObject()
    {
        $rbacsystem = $this->rbacsystem;
        $rbacadmin = $this->rbacadmin;
        $rbacreview = $this->rbacreview;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess('edit_permission', $_GET["ref_id"])) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->WARNING);
        }

        $ops_valid = $rbacreview->getOperationsOnType($_GET["obj_id"]);

        foreach ($_POST["id"] as $ops_id => $status) {
            if ($status == 'enabled') {
                if (!in_array($ops_id, $ops_valid)) {
                    $rbacadmin->assignOperationToObject($_GET["obj_id"], $ops_id);
                }
            }

            if ($status == 'disabled') {
                if (in_array($ops_id, $ops_valid)) {
                    $rbacadmin->deassignOperationFromObject($_GET["obj_id"], $ops_id);
                }
            }
        }

        $this->update = $this->object->update();

        ilUtil::sendSuccess($this->lng->txt("saved_successfully"), true);

        header("Location: adm_object.php?ref_id=" . $_GET["ref_id"] . "&obj_id=" . $_GET["obj_id"]);
        exit();
    }


    /**
    * display edit form
    *
    * @access	public
    */
    public function editObject()
    {
        $rbacsystem = $this->rbacsystem;
        $rbacreview = $this->rbacreview;
        $ilErr = $this->error;
        
        if (!$rbacsystem->checkAccess("edit_permission", $_GET["ref_id"])) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        //prepare objectlist
        $this->data = array();
        $this->data["data"] = array();
        $this->data["ctrl"] = array();
        $this->data["cols"] = array("type", "operation", "description", "status");

        $ops_valid = $rbacreview->getOperationsOnType($this->obj_id);

        if ($ops_arr = ilRbacReview::_getOperationList('', $a_order, $a_direction)) {
            $options = array("e" => "enabled","d" => "disabled");

            foreach ($ops_arr as $key => $ops) {
                // BEGIN ROW
                if (in_array($ops["ops_id"], $ops_valid)) {
                    $ops_status = 'e';
                } else {
                    $ops_status = 'd';
                }

                $obj = $ops["ops_id"];
                $ops_options = ilUtil::formSelect($ops_status, "id[$obj]", $options);

                //visible data part
                $this->data["data"][] = array(
                            "type"			=> "perm",
                            "operation"		=> $ops["operation"],
                            "description"	=> $ops["desc"],
                            "status"		=> $ops_status,
                            "status_html"	=> $ops_options,
                            "obj_id"		=> $val["ops_id"]
                );
            }
        } //if typedata

        $this->maxcount = count($this->data["data"]);

        // sorting array
        $this->data["data"] = ilUtil::sortArray($this->data["data"], $_GET["sort_by"], $_GET["sort_order"]);

        // now compute control information
        foreach ($this->data["data"] as $key => $val) {
            $this->data["ctrl"][$key] = array(
                                            "obj_id"	=> $val["obj_id"],
                                            "type"		=> $val["type"]
                                            );

            unset($this->data["data"][$key]["obj_id"]);
            $this->data["data"][$key]["status"] = $this->data["data"][$key]["status_html"];
            unset($this->data["data"][$key]["status_html"]);
        }

        // build table
        include_once "./Services/Table/classes/class.ilTableGUI.php";

        // load template for table
        $this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
        // load template for table content data
        $this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

        $num = 0;

        $obj_str = ($this->call_by_reference) ? "" : "&obj_id=" . $this->obj_id;
        $this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=" . $this->ref_id . "$obj_str&cmd=save");

        // create table
        $tbl = new ilTableGUI();
        
        // title & header columns
        $tbl->setTitle($this->lng->txt("edit_operations") . " " . strtolower($this->lng->txt("of")) . " '" . $this->object->getTitle() . "'");

        foreach ($this->data["cols"] as $val) {
            $header_names[] = $this->lng->txt($val);
        }
        
        $tbl->setHeaderNames($header_names);

        $header_params = array("ref_id" => $this->ref_id,"obj_id" => $this->id,"cmd" => "edit");
        $tbl->setHeaderVars($this->data["cols"], $header_params);
        
        // control
        $tbl->setOrderColumn($_GET["sort_by"]);
        $tbl->setOrderDirection($_GET["sort_order"]);
        $tbl->setLimit(0);
        $tbl->setOffset(0);
        $tbl->setMaxCount($this->maxcount);
        
        // SHOW VALID ACTIONS
        $this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
        $this->tpl->setVariable("COLUMN_COUNTS", count($this->data["cols"]));
        
        // footer
        $tbl->setFooter("tblfooter", $this->lng->txt("previous"), $this->lng->txt("next"));
        //$tbl->disable("footer");
        
        // render table
        $tbl->render();

        if (is_array($this->data["data"][0])) {
            //table cell
            for ($i=0; $i < count($this->data["data"]); $i++) {
                $data = $this->data["data"][$i];
                $ctrl = $this->data["ctrl"][$i];

                // color changing
                $css_row = ilUtil::switchColor($i+1, "tblrow1", "tblrow2");

                $this->tpl->setCurrentBlock("table_cell");
                $this->tpl->setVariable("CELLSTYLE", "tblrow1");
                $this->tpl->parseCurrentBlock();

                foreach ($data as $key => $val) {
                    $this->tpl->setCurrentBlock("text");

                    if ($key == "type") {
                        $val = ilUtil::getImageTagByType($val, $this->tpl->tplPath);
                    }

                    $this->tpl->setVariable("TEXT_CONTENT", $val);
                    $this->tpl->parseCurrentBlock();

                    $this->tpl->setCurrentBlock("table_cell");
                    $this->tpl->parseCurrentBlock();
                } //foreach

                $this->tpl->setVariable("BTN_VALUE", $this->lng->txt("save"));

                $this->tpl->setCurrentBlock("tbl_content");
                $this->tpl->setVariable("CSS_ROW", $css_row);
                $this->tpl->parseCurrentBlock();
            } //for
        } //if is_array
    }
    
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }
    
    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        $rbacsystem = $this->rbacsystem;

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "view"),
                array("view",""),
                "",
                ""
            );

            $this->tabs_gui->addTarget(
                "edit_operations",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                "",
                ""
            );
        }
    }
} // END class.ilObjTypeDefinitionGUI
