<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * Build table list for objects of given user
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilTrUserObjectsPropsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup ServicesTracking
 */
class ilTrUserObjectsPropsTableGUI extends ilLPTableBaseGUI
{
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id, $a_obj_id, $a_ref_id, $a_print_view = false)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];
        
        $this->setId("truop");
        $this->user_id = $a_user_id;
        $this->obj_id = $a_obj_id;
        $this->ref_id = $a_ref_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);

        $this->parseTitle($this->obj_id, "details", $this->user_id);

        if ($a_print_view) {
            $this->setPrintMode(true);
        }

        $this->addColumn($this->lng->txt("title"), "title");
        
        foreach ($this->getSelectedColumns() as $c) {
            $l = $c;
            if (in_array($l, array("last_access", "first_access", "read_count", "spent_seconds", "mark", "status", "percentage"))) {
                $l = "trac_" . $l;
            }
            if ($l == "u_comment") {
                $l = "trac_comment";
            }
            $this->addColumn($this->lng->txt($l), $c);
        }

        if (!$this->getPrintMode()) {
            $this->addColumn($this->lng->txt("actions"), "");
        }

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
        $this->setRowTemplate("tpl.user_objects_props_row.html", "Services/Tracking");
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setShowTemplates(true);

        $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

        $this->initFilter();

        $this->getItems();
    }
    
    /**
     * Get selectable columns
     *
     * @param
     * @return
     */
    public function getSelectableColumns()
    {
        global $DIC;

        $lng = $DIC['lng'];

        // default fields
        $cols = array();
        
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
        $tracking = new ilObjUserTracking();
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS)) {
            $cols["first_access"] = array(
                "txt" => $lng->txt("trac_first_access"),
                "default" => true);
            $cols["last_access"] = array(
                "txt" => $lng->txt("trac_last_access"),
                "default" => true);
        }
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_READ_COUNT)) {
            $cols["read_count"] = array(
                "txt" => $lng->txt("trac_read_count"),
                "default" => true);
        }
        if ($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS)) {
            $cols["spent_seconds"] = array(
                "txt" => $lng->txt("trac_spent_seconds"),
                "default" => true);
        }
        
        // #15334 - parent object does not matter, sub-objects may have percentage
        $cols["percentage"] = array(
            "txt" => $lng->txt("trac_percentage"),
            "default" => true);
        
        $cols["status"] = array(
            "txt" => $lng->txt("trac_status"),
            "default" => true);
        $cols["mark"] = array(
            "txt" => $lng->txt("trac_mark"),
            "default" => true);
        $cols["u_comment"] = array(
            "txt" => $lng->txt("trac_comment"),
            "default" => false);
        
        return $cols;
    }
    
    /**
    * Get user items
    */
    public function getItems()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $this->determineOffsetAndOrder();
        
        $additional_fields = $this->getSelectedColumns();

        include_once("./Services/Tracking/classes/class.ilTrQuery.php");

        $tr_data = ilTrQuery::getObjectsDataForUser(
            $this->user_id,
            $this->obj_id,
            $this->ref_id,
            ilUtil::stripSlashes($this->getOrderField()),
            ilUtil::stripSlashes($this->getOrderDirection()),
            ilUtil::stripSlashes($this->getOffset()),
            ilUtil::stripSlashes($this->getLimit()),
            $this->filter,
            $additional_fields,
            $this->filter["view_mode"]
        );
            
        if (count($tr_data["set"]) == 0 && $this->getOffset() > 0) {
            $this->resetOffset();
            $tr_data = ilTrQuery::getObjectsDataForUser(
                $this->user_id,
                $this->obj_id,
                $this->ref_id,
                ilUtil::stripSlashes($this->getOrderField()),
                ilUtil::stripSlashes($this->getOrderDirection()),
                ilUtil::stripSlashes($this->getOffset()),
                ilUtil::stripSlashes($this->getLimit()),
                $this->filter,
                $additional_fields,
                $this->filter["view_mode"]
            );
        }
        
        // #13807
        foreach ($tr_data["set"] as $idx => $row) {
            include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
            if ($row["ref_id"] &&
                !ilLearningProgressAccess::checkPermission('read_learning_progress', $row['ref_id'])) {
                foreach (array_keys($row) as $col_id) {
                    if (!in_array($col_id, array("type", "obj_id", "ref_id", "title", "sort_title"))) {
                        $tr_data["set"][$idx][$col_id] = null;
                    }
                }
                $tr_data["set"][$idx]["privacy_conflict"] = true;
            }
        }

        $this->setMaxCount($tr_data["cnt"]);
        
        if ($this->getOrderField() == "title") {
            // sort alphabetically, move parent object to 1st position
            $set = array();
            $parent = false;
            foreach ($tr_data["set"] as $idx => $row) {
                if ($row['obj_id'] == $this->obj_id) {
                    $parent = $row;
                } elseif (isset($row["sort_title"])) {
                    $set[strtolower($row["sort_title"]) . "__" . $idx] = $row;
                } else {
                    $set[strtolower($row["title"]) . "__" . $idx] = $row;
                }
            }
            unset($tr_data["set"]);
            if ($this->getOrderDirection() == "asc") {
                ksort($set);
            } else {
                krsort($set);
            }
            $set = array_values($set);
            if ($parent) {
                array_unshift($set, $parent);
            }

            $this->setData($set);
        } else {
            $this->setData($tr_data["set"]);
        }
    }

    /**
    * Init filter
    */
    public function initFilter()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        // for scorm and objectives this filter does not make sense / is not implemented
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->obj_id);
        $collection = $olp->getCollectionInstance();
        if ($collection instanceof ilLPCollectionOfRepositoryObjects) {
            include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
            include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");

            // show collection only/all
            include_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
            include_once("./Services/Form/classes/class.ilRadioOption.php");
            $ti = new ilRadioGroupInputGUI($lng->txt("trac_view_mode"), "view_mode");
            $ti->addOption(new ilRadioOption($lng->txt("trac_view_mode_all"), ""));
            $ti->addOption(new ilRadioOption($lng->txt("trac_view_mode_collection"), "coll"));
            $this->addFilterItem($ti);
            $ti->readFromSession();
            $this->filter["view_mode"] = $ti->getValue();
        }
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($data)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $rbacsystem = $DIC['rbacsystem'];

        if (!$this->isPercentageAvailable($data["obj_id"])) {
            $data["percentage"] = null;
        }

        foreach ($this->getSelectedColumns() as $c) {
            if (!$data["privacy_conflict"]) {
                $val = (trim($data[$c]) == "")
                    ? " "
                    : $data[$c];

                if ($data[$c] != "" || $c == "status") {
                    switch ($c) {
                        case "first_access":
                            $val = ilDatePresentation::formatDate(new ilDateTime($data[$c], IL_CAL_DATETIME));
                            break;

                        case "last_access":
                            $val = ilDatePresentation::formatDate(new ilDateTime($data[$c], IL_CAL_UNIX));
                            break;

                        case "status":
                            include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
                            $path = ilLearningProgressBaseGUI::_getImagePathForStatus($data[$c]);
                            $text = ilLearningProgressBaseGUI::_getStatusText($data[$c]);
                            $val = ilUtil::img($path, $text);

                            if ($data["ref_id"] &&
                                $data["type"] != "lobj" &&
                                $data["type"] != "sco" &&
                                $data["type"] != "st" &&
                                $data["type"] != "mob") {
                                $timing = $this->showTimingsWarning($data["ref_id"], $this->user_id);
                                if ($timing) {
                                    if ($timing !== true) {
                                        $timing = ": " . ilDatePresentation::formatDate(new ilDate($timing, IL_CAL_UNIX));
                                    } else {
                                        $timing = "";
                                    }
                                    $this->tpl->setCurrentBlock('warning_img');
                                    $this->tpl->setVariable('WARNING_IMG', ilUtil::getImagePath('time_warn.svg'));
                                    $this->tpl->setVariable('WARNING_ALT', $this->lng->txt('trac_time_passed') . $timing);
                                    $this->tpl->parseCurrentBlock();
                                }
                            }
                            break;

                        case "spent_seconds":
                            if (!ilObjectLP::supportsSpentSeconds($data["type"])) {
                                $val = "-";
                            } else {
                                $val = ilDatePresentation::secondsToString($data[$c], ($data[$c] < 3600 ? true : false)); // #14858
                            }
                            break;

                        case "percentage":
                            $val = $data[$c] . "%";
                            break;

                    }
                }
                if ($c == "mark" &&
                    !ilObjectLP::supportsMark($this->type)) {
                    $val = "-";
                }
                if ($c == "spent_seconds" &&
                    !ilObjectLP::supportsSpentSeconds($this->type)) {
                    $val = "-";
                }
                if ($c == "percentage" &&
                    !$this->isPercentageAvailable($data["obj_id"])) {
                    $val = "-";
                }
            } else {
                $val = "&nbsp;";
            }

            $this->tpl->setCurrentBlock("user_field");
            $this->tpl->setVariable("VAL_UF", $val);
            $this->tpl->parseCurrentBlock();
        }
                
        if ($data["privacy_conflict"]) {
            $this->tpl->setCurrentBlock("permission_bl");
            $this->tpl->setVariable("TXT_NO_PERMISSION", $lng->txt("status_no_permission"));
            $this->tpl->parseCurrentBlock();
        }

        if ($data["title"] == "") {
            $data["title"] = "--" . $lng->txt("none") . "--";
        }
        $this->tpl->setVariable("ICON", ilObject::_getIcon("", "tiny", $data["type"]));
        $this->tpl->setVariable("ICON_ALT", $lng->txt($data["type"]));
        
        if (in_array($data['type'], array('fold', 'grp')) && $data['obj_id'] != $this->obj_id) {
            if ($data['type'] == 'fold') {
                $object_gui = 'ilobjfoldergui';
            } else {
                $object_gui = 'ilobjgroupgui';
            }
            $this->tpl->setCurrentBlock('title_linked');

            // link structure gets too complicated
            if ($_GET["baseClass"] != "ilPersonalDesktopGUI" && $_GET["baseClass"] != "ilAdministrationGUI") {
                $old = $ilCtrl->getParameterArrayByClass('illplistofobjectsgui');
                $ilCtrl->setParameterByClass('illplistofobjectsgui', 'ref_id', $data["ref_id"]);
                $ilCtrl->setParameterByClass('illplistofobjectsgui', 'details_id', $data["ref_id"]);
                $ilCtrl->setParameterByClass('illplistofobjectsgui', 'user_id', $this->user_id);
                $url = $ilCtrl->getLinkTargetByClass(array('ilrepositorygui', $object_gui, 'illearningprogressgui', 'illplistofobjectsgui'), 'userdetails');
                $ilCtrl->setParameterByClass('illplistofobjectsgui', 'ref_id', $old["ref_id"]);
                $ilCtrl->setParameterByClass('illplistofobjectsgui', 'details_id', $old["details_id"]);
                $ilCtrl->setParameterByClass('illplistofobjectsgui', 'user_id', $old["user_id"]);
            } else {
                $url = "#";
            }

            $this->tpl->setVariable("URL_TITLE", $url);
            $this->tpl->setVariable("VAL_TITLE", $data["title"]);
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('title_plain');
            $this->tpl->setVariable("VAL_TITLE", $data["title"]);
            $this->tpl->parseCurrentBlock();
        }
        
        // #16453 / #17163
        if ($data['ref_id']) {
            include_once './Services/Tree/classes/class.ilPathGUI.php';
            $path = new ilPathGUI();
            $path = $path->getPath($this->ref_id, $data['ref_id']);
            if ($path) {
                $this->tpl->setVariable('COLL_PATH', $this->lng->txt('path') . ': ' . $path);
            }
        }

        // #13807 / #17069
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if ($data["ref_id"] &&
            ilLearningProgressAccess::checkPermission('edit_learning_progress', $data['ref_id'])) {
            if (!in_array($data["type"], array("sco", "lobj")) && !$this->getPrintMode()) {
                $this->tpl->setCurrentBlock("item_command");
                $ilCtrl->setParameterByClass("illplistofobjectsgui", "userdetails_id", $data["ref_id"]);
                $this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass("illplistofobjectsgui", 'edituser'));
                $this->tpl->setVariable("TXT_COMMAND", $lng->txt('edit'));
                $ilCtrl->setParameterByClass("illplistofobjectsgui", "userdetails_id", "");
                $this->tpl->parseCurrentBlock();
            }
        }
    }
    
    protected function fillHeaderExcel(ilExcel $a_excel, &$a_row)
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt("type"));
        $a_excel->setCell($a_row, 1, $this->lng->txt("title"));

        $labels = $this->getSelectableColumns();
        $cnt = 2;
        foreach ($this->getSelectedColumns() as $c) {
            $a_excel->setCell($a_row, $cnt++, $labels[$c]["txt"]);
        }
        
        $a_excel->setBold("A" . $a_row . ":" . $a_excel->getColumnCoord($cnt - 1) . $a_row);
    }

    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt($a_set["type"]));
        $a_excel->setCell($a_row, 1, $a_set["title"]);

        $cnt = 2;
        foreach ($this->getSelectedColumns() as $c) {
            if ($c != 'status') {
                $val = $this->parseValue($c, $a_set[$c], $this->type);
            } else {
                $val = ilLearningProgressBaseGUI::_getStatusText((int) $a_set[$c]);
            }
            $a_excel->setCell($a_row, $cnt++, $val);
        }
    }

    protected function fillHeaderCSV($a_csv)
    {
        $a_csv->addColumn($this->lng->txt("type"));
        $a_csv->addColumn($this->lng->txt("title"));

        $labels = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $c) {
            $a_csv->addColumn($labels[$c]["txt"]);
        }

        $a_csv->addRow();
    }

    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn($this->lng->txt($a_set["type"]));
        $a_csv->addColumn($a_set["title"]);

        foreach ($this->getSelectedColumns() as $c) {
            if ($c != 'status') {
                $val = $this->parseValue($c, $a_set[$c], $this->type);
            } else {
                $val = ilLearningProgressBaseGUI::_getStatusText((int) $a_set[$c]);
            }
            $a_csv->addColumn($val);
        }

        $a_csv->addRow();
    }
}
