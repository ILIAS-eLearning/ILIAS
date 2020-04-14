<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
* TableGUI class for learning progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilLPObjectStatisticsAdminTableGUI: ilFormPropertyDispatchGUI
* @ingroup ServicesTracking
*/
class ilLPObjectStatisticsAdminTableGUI extends ilLPTableBaseGUI
{
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->setId("lpobjstattbl");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->addColumn("", "", "1", true);
        $this->addColumn($lng->txt("month"), "month");
        $this->addColumn($lng->txt("count"), "count");

        $this->setTitle($this->lng->txt("trac_object_stat_admin"));

        // $this->setSelectAllCheckbox("item_id");
        $this->addMultiCommand("confirmDeleteData", $lng->txt("trac_delete_data"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.lp_object_statistics_admin_row.html", "Services/Tracking");
        $this->setEnableHeader(true);
        $this->setEnableNumInfo(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderField("month");
        $this->setDefaultOrderDirection("desc");
        
        $this->getItems();
    }

    public function getItems()
    {
        include_once "Services/Tracking/classes/class.ilTrQuery.php";
        $data = ilTrQuery::getObjectStatisticsMonthlySummary();
        
        // #11855
        foreach ($data as $idx => $item) {
            $data[$idx]["id"] = $item["month"];
            
            $data[$idx]["month"] = substr($item["month"], 0, 4) .
                "-" . str_pad(substr($item["month"], 5), 2, "0", STR_PAD_LEFT);
        }

        $this->setData($data);
    }
    
    /**
    * Fill table row
    */
    protected function fillRow($a_set)
    {
        global $DIC;

        $lng = $DIC['lng'];

        $caption = $lng->txt("month_" . substr($a_set["month"], 5, 2) . "_long") .
            " " . substr($a_set["month"], 0, 4);

        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("MONTH", $caption);
        $this->tpl->setVariable("COUNT", $a_set["count"]);
    }
}
