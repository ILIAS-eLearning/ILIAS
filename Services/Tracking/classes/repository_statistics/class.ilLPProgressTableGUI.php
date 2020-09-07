<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");
require_once("./Services/Tracking/classes/class.ilLearningProgressGUI.php");

/**
* TableGUI class for learning progress
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilLPProgressTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPProgressTableGUI extends ilLPTableBaseGUI
{
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_user = "", $obj_ids = null, $details = false, $mode = null, $personal_only = false, $a_parent_id = null, $a_parent_ref_id = null, $lp_context = null)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        $this->tracked_user = $a_user;
        $this->obj_ids = $obj_ids;
        $this->details = $details;
        $this->mode = $mode;
        $this->parent_obj_id = $a_parent_id;
        $this->lp_context = $lp_context;
        
        if ($a_parent_id) {
            // #15042 - needed for export meta
            $this->obj_id = $this->parent_obj_id;
            $this->ref_id = $a_parent_ref_id;
        }
        
        $this->setId("lpprgtbl");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);
    
        if (!$this->details) {
            $this->has_object_subitems = true;
            
            $user = $this->tracked_user;
            if (!$user) {
                $user = $ilUser;
            }
            
            $this->addColumn("", "", "1", true);
            $this->addColumn($this->lng->txt("trac_title"), "title", "26%");
            $this->addColumn($this->lng->txt("status"), "status", "7%");
            $this->addColumn($this->lng->txt('trac_status_changed'), 'status_changed', '10%');
            $this->addColumn($this->lng->txt("trac_percentage"), "percentage", "7%");
            $this->addColumn($this->lng->txt("trac_mark"), "", "5%");
            $this->addColumn($this->lng->txt("comment"), "", "10%");
            $this->addColumn($this->lng->txt("trac_mode"), "", "20%");
            $this->addColumn($this->lng->txt("path"), "", "20%");
            $this->addColumn($this->lng->txt("actions"), "", "5%");

            $this->setTitle(sprintf($this->lng->txt("trac_learning_progress_of"), $user->getFullName()));
            $this->initBaseFilter();

            $this->setSelectAllCheckbox("item_id");
            $this->addMultiCommand("hideSelected", $lng->txt("trac_hide_selected"));
            
            $this->setShowTemplates(true);
        } else {
            include_once './Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($this->parent_obj_id);
            $collection = $olp->getCollectionInstance();
            $this->has_object_subitems = ($collection instanceof ilLPCollectionOfRepositoryObjects);
                        
            /*
            if(!$personal_only)
            {
                $this->parseTitle($a_parent_obj->details_obj_id, "trac_subitems");
            }
            else
            {
                $this->parseTitle($a_parent_obj->details_obj_id, "trac_progress");
            }
            */
            $this->setTitle($this->lng->txt("details")); // #15247

            $this->addColumn($this->lng->txt("trac_title"), "title", "31%");
            $this->addColumn($this->lng->txt("status"), "status", "7%");
            
            if ($this->mode == ilLPObjSettings::LP_MODE_SCORM) {
                $this->lng->loadLanguageModule('content');
                $this->addColumn($this->lng->txt('cont_score'), 'score', '10%');
            } elseif ($this->has_object_subitems) {
                $this->addColumn($this->lng->txt('trac_status_changed'), 'status_changed', '10%');
                $this->addColumn($this->lng->txt("trac_percentage"), "percentage", "7%");
                $this->addColumn($this->lng->txt("trac_mark"), "", "5%");
                $this->addColumn($this->lng->txt("comment"), "", "10%");
                $this->addColumn($this->lng->txt("trac_mode"), "", "20%");
                $this->addColumn($this->lng->txt("path"), "", "20%");
            }
        }
        
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
        $this->setRowTemplate("tpl.lp_progress_list_row.html", "Services/Tracking");
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(false);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        
        if ($this->has_object_subitems) {
            $this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));
        }

        // area selector gets in the way
        if ($this->tracked_user) {
            $this->getItems();
        }
    }
    
    public function numericOrdering($a_field)
    {
        return ($a_field == "percentage"); // #15041
    }

    public function getItems()
    {
        $obj_ids = $this->obj_ids;
        if (!$obj_ids && !$this->details) {
            switch ($this->lp_context) {
                case ilLearningProgressGUI::LP_CONTEXT_ORG_UNIT:
                    $obj_ids = $this->searchObjects($this->getCurrentFilter(true), null);
                    break;

                default:
                    $obj_ids = $this->searchObjects($this->getCurrentFilter(true), "read");
                                                            
                    // check for LP relevance
                    include_once "Services/Object/classes/class.ilObjectLP.php";
                    foreach (ilObjectLP::getLPMemberships($this->tracked_user->getId(), $obj_ids, null, true) as $obj_id => $status) {
                        if (!$status) {
                            unset($obj_ids[$obj_id]);
                        }
                    }
                    break;
            }
        }
        if ($obj_ids) {
            include_once("./Services/Tracking/classes/class.ilTrQuery.php");
            switch ($this->mode) {
                case ilLPObjSettings::LP_MODE_SCORM:
                    $data = ilTrQuery::getSCOsStatusForUser($this->tracked_user->getId(), $this->parent_obj_id, $obj_ids);
                    break;
                
                case ilLPObjSettings::LP_MODE_OBJECTIVES:
                    $data = ilTrQuery::getObjectivesStatusForUser($this->tracked_user->getId(), $this->parent_obj_id, $obj_ids);
                    break;
                
                case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
                case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
                case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                    $data = ilTrQuery::getSubItemsStatusForUser($this->tracked_user->getId(), $this->parent_obj_id, $obj_ids);
                    break;
                
                default:
                    $data = ilTrQuery::getObjectsStatusForUser($this->tracked_user->getId(), $obj_ids);
                    foreach ($data as $idx => $item) {
                        if (!$item["status"] && !$this->filter["status"] && !$this->details) {
                            unset($data[$idx]);
                        } else {
                            $data[$idx]["offline"] = ilLearningProgressBaseGUI::isObjectOffline($item["obj_id"], $item["type"]);
                        }
                    }
                    break;
            }
            
            // #15334
            foreach ($data as $idx => $row) {
                if (!$this->isPercentageAvailable($row["obj_id"])) {
                    // #17000 - enable proper (numeric) sorting
                    $data[$idx]["percentage"] = -1;
                }
            }
            
            $this->setData($data);
        }
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        if (!$this->details) {
            $this->tpl->setCurrentBlock("column_checkbox");
            $this->tpl->setVariable("OBJ_ID", $a_set["obj_id"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("ICON_SRC", ilObject::_getIcon("", "tiny", $a_set["type"]));
        $this->tpl->setVariable("ICON_ALT", $this->lng->txt($a_set["type"]));
        $this->tpl->setVariable("TITLE_TEXT", $a_set["title"]);

        if ($a_set["offline"]) {
            $this->tpl->setCurrentBlock("offline");
            $this->tpl->setVariable("TEXT_STATUS", $this->lng->txt("status"));
            $this->tpl->setVariable("TEXT_OFFLINE", $this->lng->txt("offline"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("STATUS_ALT", ilLearningProgressBaseGUI::_getStatusText($a_set["status"]));
        $this->tpl->setVariable("STATUS_IMG", ilLearningProgressBaseGUI::_getImagePathForStatus($a_set["status"]));

        if ($this->mode == ilLPObjSettings::LP_MODE_SCORM) {
            $this->tpl->setVariable('SCORE_VAL', $a_set["score"]);
        } elseif ($this->has_object_subitems) {
            $this->tpl->setCurrentBlock("status_details");
            
            $this->tpl->setVariable('STATUS_CHANGED_VAL', ilDatePresentation::formatDate(new ilDateTime($a_set['status_changed'], IL_CAL_DATETIME)));

            $olp = ilObjectLP::getInstance($a_set["obj_id"]);
            $this->tpl->setVariable("MODE_TEXT", $olp->getModeText($a_set["u_mode"]));
            $this->tpl->setVariable("MARK_VALUE", $a_set["mark"]);
            $this->tpl->setVariable("COMMENT_TEXT", $a_set["comment"]);
                        
            if ($a_set["percentage"] < 0) {
                $this->tpl->setVariable("PERCENTAGE_VALUE", "");
            } else {
                $this->tpl->setVariable("PERCENTAGE_VALUE", sprintf("%d%%", $a_set["percentage"]));
            }
        
            // path
            $path = $this->buildPath($a_set["ref_ids"]);
            if ($path) {
                $this->tpl->setCurrentBlock("item_path");
                foreach ($path as $path_item) {
                    $this->tpl->setVariable("PATH_ITEM", $path_item);
                    $this->tpl->parseCurrentBlock();
                }
            }
            
            $this->tpl->parseCurrentBlock();
        }

        // not for objectives/scos
        if (!$this->mode) {
            // tlt warning
            if ($a_set["status"] != ilLPStatus::LP_STATUS_COMPLETED_NUM && $a_set["ref_ids"]) {
                $ref_id = $a_set["ref_ids"];
                $ref_id = array_shift($ref_id);
                $timing = $this->showTimingsWarning($ref_id, $this->tracked_user->getId());
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

            // hide / unhide?!
            if (!$this->details) {
                $this->tpl->setCurrentBlock("item_command");
                $ilCtrl->setParameterByClass(get_class($this), 'hide', $a_set["obj_id"]);
                $this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass(get_class($this), 'hide'));
                $this->tpl->setVariable("TXT_COMMAND", $this->lng->txt('trac_hide'));
                $this->tpl->parseCurrentBlock();

                $olp = ilObjectLP::getInstance($a_set["obj_id"]);
                if ($olp->getCollectionInstance() && $a_set["ref_ids"]) {
                    $ref_id = $a_set["ref_ids"];
                    $ref_id = array_shift($ref_id);
                    $ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', $ref_id);
                    $this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), 'details'));
                    $ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', '');
                    $this->tpl->setVariable("TXT_COMMAND", $this->lng->txt('trac_subitems'));
                    $this->tpl->parseCurrentBlock();
                }

                $this->tpl->setCurrentBlock("column_action");
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    protected function fillHeaderExcel(ilExcel $a_excel, &$a_row)
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt("type"));
        $a_excel->setCell($a_row, 1, $this->lng->txt("trac_title"));
        $a_excel->setCell($a_row, 2, $this->lng->txt("status"));
        $a_excel->setCell($a_row, 3, $this->lng->txt("trac_status_changed"));
        $a_excel->setCell($a_row, 4, $this->lng->txt("trac_percentage"));
        $a_excel->setCell($a_row, 5, $this->lng->txt("trac_mark"));
        $a_excel->setCell($a_row, 6, $this->lng->txt("comment"));
        $a_excel->setCell($a_row, 7, $this->lng->txt("trac_mode"));
        // $a_excel->setCell($a_row, 7, $this->lng->txt("path"));
        
        $a_excel->setBold("A" . $a_row . ":H" . $a_row);
    }
    
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        $a_excel->setCell($a_row, 0, $this->lng->txt($a_set["type"]));
        $a_excel->setCell($a_row, 1, $a_set["title"]);
        $a_excel->setCell($a_row, 2, ilLearningProgressBaseGUI::_getStatusText($a_set["status"]));

        $a_excel->setCell($a_row, 3, new ilDateTime($a_set['status_changed'], IL_CAL_DATETIME));
        
        if (!$this->isPercentageAvailable($a_row['obj_id'])) {
            $a_excel->setCell($a_row, 4, '-');
        } else {
            $a_excel->setCell($a_row, 4, $a_set["percentage"] . "%");
        }
        $a_excel->setCell($a_row, 5, $a_set["mark"]);
        $a_excel->setCell($a_row, 6, $a_set["comment"]);
        $a_excel->setCell($a_row, 7, ilLPObjSettings::_mode2Text($a_set["u_mode"]));
    }

    protected function fillHeaderCSV($a_csv)
    {
        $a_csv->addColumn($this->lng->txt("type"));
        $a_csv->addColumn($this->lng->txt("trac_title"));
        $a_csv->addColumn($this->lng->txt("status"));
        $a_csv->addColumn($this->lng->txt("trac_status_changed"));
        $a_csv->addColumn($this->lng->txt("trac_percentage"));
        $a_csv->addColumn($this->lng->txt("trac_mark"));
        $a_csv->addColumn($this->lng->txt("comment"));
        $a_csv->addColumn($this->lng->txt("trac_mode"));
        // $a_csv->addColumn($this->lng->txt("path"));
        $a_csv->addRow();
    }

    protected function fillRowCSV($a_csv, $a_set)
    {
        $a_csv->addColumn($this->lng->txt($a_set["type"]));
        $a_csv->addColumn($a_set["title"]);
        $a_csv->addColumn(ilLearningProgressBaseGUI::_getStatusText($a_set["status"]));

        ilDatePresentation::setUseRelativeDates(false);
        $a_csv->addColumn(ilDatePresentation::formatDate(new ilDateTime($a_set['status_changed'], IL_CAL_DATETIME)));
        ilDatePresentation::resetToDefaults();

        if (!$this->isPercentageAvailable($a_set['obj_id'])) {
            $a_csv->addColumn('-');
        } else {
            $a_csv->addColumn(sprintf("%d%%", $a_set["percentage"]));
        }
        $a_csv->addColumn($a_set["mark"]);
        $a_csv->addColumn($a_set["comment"]);
        $a_csv->addColumn(ilLPObjSettings::_mode2Text($a_set["u_mode"]));

        $a_csv->addRow();
    }
}
