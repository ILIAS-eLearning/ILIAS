<?php declare(strict_types=0);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjectStatisticsGUI
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id: class.ilLPListOfObjectsGUI.php 27489 2011-01-19 16:58:09Z jluetzen $
*
* @ilCtrl_Calls ilLPObjectStatisticsGUI: ilLPObjectStatisticsTableGUI
*
* @package ilias-tracking
*
*/


class ilLPObjectStatisticsGUI extends ilLearningProgressBaseGUI
{
    protected ilCronManagerInterface $cronManager;

    public function __construct(int $a_mode, int $a_ref_id = 0)
    {
        global $DIC;

        $this->cronManager = $DIC->cron()->manager();

        parent::__construct($a_mode, $a_ref_id);
    
        if (!$this->ref_id) {
            $this->ref_id = (int) $_REQUEST["ref_id"];
        }
    }

    protected function setTabs() : void
    {
        $this->tabs_gui->addSubTab(
            'trac_object_stat_access',
            $this->lng->txt('trac_object_stat_access'),
            $this->ctrl->getLinkTarget($this, 'accessFilter')
        );
        $this->tabs_gui->addSubTab(
            'trac_object_stat_daily',
            $this->lng->txt('trac_object_stat_daily'),
            $this->ctrl->getLinkTarget($this, 'dailyFilter')
        );
        $this->tabs_gui->addSubTab(
            'trac_object_stat_lp',
            $this->lng->txt('trac_object_stat_lp'),
            $this->ctrl->getLinkTarget($this, 'learningProgressFilter')
        );
        $this->tabs_gui->addSubTab(
            'trac_object_stat_types',
            $this->lng->txt('trac_object_stat_types'),
            $this->ctrl->getLinkTarget($this, 'typesFilter')
        );

        if ($this->rbacsystem->checkAccess("visible,read", $this->ref_id)) {
            $this->tabs_gui->addSubTab(
                'trac_object_stat_admin',
                $this->lng->txt('trac_object_stat_admin'),
                $this->ctrl->getLinkTarget($this, 'admin')
            );
        }
    }
    
    public function executeCommand() : void
    {
        $this->ctrl->setReturn($this, "");
        
        $this->setTabs();

        switch ($this->ctrl->getNextClass()) {
            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();
        }
    }

    public function applyAccessFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTableGUI($this, "access", null, false);
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->access();
    }

    public function resetAccessFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTableGUI($this, "access", null, false);
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->access();
    }

    public function accessFilter() : void
    {
        $this->access(false);
    }

    public function access(bool $a_load_data = true) : void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_access');
        $this->showAggregationInfo();
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTableGUI($this, "access", null, $a_load_data);
        
        if (!$a_load_data) {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showAccessGraph() : void
    {
        if (!$_POST["item_id"]) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->access();
        }
        
        $this->tabs_gui->activateSubTab('trac_object_stat_access');
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTableGUI($this, "access", $_POST["item_id"]);

        $this->tpl->setContent($lp_table->getGraph($_POST["item_id"]) . $lp_table->getHTML());
    }

    public function applyTypesFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", null, false);
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->types();
    }

    public function resetTypesFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", null, false);
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->types();
    }

    public function typesFilter() : void
    {
        $this->types(false);
    }

    public function types(bool $a_load_data = true) : void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_types');
        $this->showCronJobInfo();

        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", null, $a_load_data);

        if (!$a_load_data) {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showTypesGraph() : void
    {
        if (!$_POST["item_id"]) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->types();
            return;
        }
        
        $this->tabs_gui->activateSubTab('trac_object_stat_types');

        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsTypesTableGUI.php");
        $lp_table = new ilLPObjectStatisticsTypesTableGUI($this, "types", $_POST["item_id"]);

        $this->tpl->setContent($lp_table->getGraph($_POST["item_id"]) . $lp_table->getHTML());
    }

    public function applyDailyFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
        $lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", null, false);
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->daily();
    }

    public function resetDailyFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
        $lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", null, false);
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->daily();
    }

    public function dailyFilter() : void
    {
        $this->daily(false);
    }

    public function daily(bool $a_load_data = true) : void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_daily');
        
        $this->showAggregationInfo();

        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
        $lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", null, $a_load_data);

        if (!$a_load_data) {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showDailyGraph() : void
    {
        if (!$_POST["item_id"]) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->daily();
            return;
        }
        
        $this->tabs_gui->activateSubTab('trac_object_stat_daily');

        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsDailyTableGUI.php");
        $lp_table = new ilLPObjectStatisticsDailyTableGUI($this, "daily", $_POST["item_id"]);

        $this->tpl->setContent($lp_table->getGraph($_POST["item_id"]) . $lp_table->getHTML());
    }

    public function admin() : void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_admin');
        
        $this->showAggregationInfo(false);

        if ($this->rbacsystem->checkAccess('write', $this->ref_id)) {
            $this->toolbar->addButton(
                $this->lng->txt("trac_sync_obj_stats"),
                $this->ctrl->getLinkTarget($this, "adminSync")
            );
        }

        if ($this->access->checkAccess("delete", "", $this->ref_id)) {
            include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsAdminTableGUI.php");
            $lp_table = new ilLPObjectStatisticsAdminTableGUI($this, "admin");
            $this->tpl->setContent($lp_table->getHTML());
        }
    }

    public function adminSync() : void
    {
        include_once "Services/Tracking/classes/class.ilChangeEvent.php";
        ilChangeEvent::_syncObjectStats(time(), 1);

        ilUtil::sendSuccess($this->lng->txt("trac_sync_obj_stats_success"), true);
        $this->ctrl->redirect($this, "admin");
    }

    public function confirmDeleteData() : void
    {
        if (!$_POST["item_id"]) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->admin();
            return;
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "admin")
        );

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("trac_sure_delete_data"));
        $cgui->setCancel($this->lng->txt("cancel"), "admin");
        $cgui->setConfirm($this->lng->txt("delete"), "deleteData");

        // list objects that should be deleted
        foreach ($_POST["item_id"] as $i) {
            $caption = $this->lng->txt("month_" . str_pad(substr($i, 5), 2, "0", STR_PAD_LEFT) . "_long") .
            " " . substr($i, 0, 4);
            
            $cgui->addItem("item_id[]", $i, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteData() : void
    {
        if (!$_POST["item_id"]) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->admin();
            return;
        }

        include_once "Services/Tracking/classes/class.ilTrQuery.php";
        ilTrQuery::deleteObjectStatistics($_POST["item_id"]);
        ilUtil::sendSuccess($this->lng->txt("trac_data_deleted"));
        $this->admin();
    }
    
    public function applyLearningProgressFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
        $lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", null, false);
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->learningProgress();
    }

    public function resetLearningProgressFilter() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
        $lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", null, false);
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->learningProgress();
    }
    
    public function learningProgressFilter() : void
    {
        $this->learningProgress(false);
    }

    public function learningProgress(bool $a_load_data = true) : void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_lp');
        
        $this->showCronJobInfo();

        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
        $lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", null, $a_load_data);
        
        if (!$a_load_data) {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showLearningProgressGraph()
    {
        if (!$_POST["item_id"]) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"));
            $this->learningProgress();
            return;
        }
        
        $this->tabs_gui->activateSubTab('trac_object_stat_lp');

        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
        $lp_table = new ilLPObjectStatisticsLPTableGUI($this, "learningProgress", $_POST["item_id"], true, true);
                
        $this->tpl->setContent($lp_table->getGraph($_POST["item_id"]) . $lp_table->getHTML());
    }

    public function showLearningProgressDetails() : void
    {
        include_once("./Services/Tracking/classes/object_statistics/class.ilLPObjectStatisticsLPTableGUI.php");
        $lp_table = new ilLPObjectStatisticsLPTableGUI($this, "showLearningProgressDetails", array($_GET["item_id"]), true, false, true);
        
        $a_tpl = new ilTemplate("tpl.lp_object_statistics_lp_details.html", true, true, "Services/Tracking");
        $a_tpl->setVariable("CONTENT", $lp_table->getHTML());
        $a_tpl->setVariable('CLOSE_IMG_TXT', $this->lng->txt('close'));
        echo $a_tpl->get();
        exit();
    }
    
    protected function showAggregationInfo(bool $a_show_link = true) : void
    {
        include_once "Services/Tracking/classes/class.ilTrQuery.php";
        $info = ilTrQuery::getObjectStatisticsLogInfo();
        $info_date = ilDatePresentation::formatDate(new ilDateTime($info["tstamp"], IL_CAL_UNIX));
                    
        $link = "";
        if ($a_show_link && $this->access->checkAccess("write", "", $this->ref_id)) {
            $link = " <a href=\"" . $this->ctrl->getLinkTarget($this, "admin") . "\">&raquo;" .
                $this->lng->txt("trac_log_info_link") . "</a>";
        }
        
        ilUtil::sendInfo(sprintf($this->lng->txt("trac_log_info"), $info_date, $info["counter"]) . $link);
    }
    
    protected function showCronJobInfo() : void
    {
        include_once "Services/Cron/classes/class.ilCronManager.php";
        if (!$this->cronManager->isJobActive("lp_object_statistics")) {
            ilUtil::sendInfo($this->lng->txt("trac_cron_info"));
        }
    }
}
