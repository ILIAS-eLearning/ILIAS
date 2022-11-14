<?php

declare(strict_types=0);

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
 * Class ilObjectStatisticsGUI
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version      $Id: class.ilLPListOfObjectsGUI.php 27489 2011-01-19 16:58:09Z jluetzen $
 * @ilCtrl_Calls ilLPObjectStatisticsGUI: ilLPObjectStatisticsTableGUI, ilLPObjectStatisticsDailyTableGUI
 * @ilCtrl_Calls ilLPObjectStatisticsGUI: ilLPObjectStatisticsLPTableGUI
 * @package      ilias-tracking
 */
class ilLPObjectStatisticsGUI extends ilLearningProgressBaseGUI
{
    protected ilCronManager $cronManager;

    public function __construct(int $a_mode, int $a_ref_id = 0)
    {
        global $DIC;

        $this->cronManager = $DIC->cron()->manager();

        parent::__construct($a_mode, $a_ref_id);

        if (!$this->ref_id) {
            if ($this->http->wrapper()->query()->has('ref_id')) {
                $this->ref_id = $this->http->wrapper()->query()->retrieve(
                    'ref_id',
                    $this->refinery->kindlyTo()->int()
                );
            }
        }
    }

    protected function initItemIdFromPost(): array
    {
        if ($this->http->wrapper()->post()->has('item_id')) {
            return $this->http->wrapper()->post()->retrieve(
                'item_id',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        return [];
    }

    protected function setTabs(): void
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

    public function executeCommand(): void
    {
        $this->ctrl->setReturn($this, "");
        $this->setTabs();

        switch ($this->ctrl->getNextClass()) {
            case "illpobjectstatisticstablegui":
                $lp_table = new ilLPObjectStatisticsTableGUI(
                    $this,
                    "access",
                    null
                );
                $lp_table->init();
                $this->ctrl->forwardCommand($lp_table);
                break;

            case "illpobjectstatisticsdailytablegui":
                $lp_table = new ilLPObjectStatisticsDailyTableGUI(
                    $this,
                    "daily",
                    null
                );
                $lp_table->init();
                $this->ctrl->forwardCommand($lp_table);
                break;

            case "illpobjectstatisticslptablegui":
                $lp_table = new ilLPObjectStatisticsLPTableGUI(
                    $this,
                    "learningProgress",
                    null
                );
                $lp_table->init();
                $this->ctrl->forwardCommand($lp_table);
                break;

            default:
                $cmd = $this->__getDefaultCommand();
                $this->$cmd();
        }
    }

    public function applyAccessFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsTableGUI(
            $this,
            "access",
            null
        );
        $lp_table->init();
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->access();
    }

    public function resetAccessFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsTableGUI(
            $this,
            "access",
            null
        );
        $lp_table->init();
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->access();
    }

    public function accessFilter(): void
    {
        $this->access(false);
    }

    public function access(bool $a_load_data = true): void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_access');
        $this->showAggregationInfo();
        $lp_table = new ilLPObjectStatisticsTableGUI(
            $this,
            "access",
            null
        );
        $lp_table->init();
        if ($a_load_data) {
            $lp_table->getItems();
        } else {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showAccessGraph(): void
    {
        if (!$this->initItemIdFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("no_checkbox")
            );
            $this->access();
        }

        $this->tabs_gui->activateSubTab('trac_object_stat_access');
        $lp_table = new ilLPObjectStatisticsTableGUI(
            $this,
            "access",
            $this->initItemIdFromPost()
        );
        $lp_table->init();
        $lp_table->getItems();
        $this->tpl->setContent(
            $lp_table->getGraph(
                $this->initItemIdFromPost()
            ) . $lp_table->getHTML()
        );
    }

    public function applyTypesFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsTypesTableGUI(
            $this,
            "types",
            null,
            false
        );
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->types();
    }

    public function resetTypesFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsTypesTableGUI(
            $this,
            "types",
            null,
            false
        );
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->types();
    }

    public function typesFilter(): void
    {
        $this->types(false);
    }

    public function types(bool $a_load_data = true): void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_types');
        $this->showCronJobInfo();

        $lp_table = new ilLPObjectStatisticsTypesTableGUI(
            $this,
            "types",
            null,
            $a_load_data
        );

        if (!$a_load_data) {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }

        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showTypesGraph(): void
    {
        if (!$this->initItemIdFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("no_checkbox")
            );
            $this->types();
            return;
        }

        $this->tabs_gui->activateSubTab('trac_object_stat_types');

        $lp_table = new ilLPObjectStatisticsTypesTableGUI(
            $this,
            "types",
            $this->initItemIdFromPost()
        );

        $this->tpl->setContent(
            $lp_table->getGraph(
                $this->initItemIdFromPost()
            ) . $lp_table->getHTML()
        );
    }

    public function applyDailyFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsDailyTableGUI(
            $this,
            "daily",
            null
        );
        $lp_table->init();
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->daily();
    }

    public function resetDailyFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsDailyTableGUI(
            $this,
            "daily",
            null
        );
        $lp_table->init();
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->daily();
    }

    public function dailyFilter(): void
    {
        $this->daily(false);
    }

    public function daily(bool $a_load_data = true): void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_daily');

        $this->showAggregationInfo();

        $lp_table = new ilLPObjectStatisticsDailyTableGUI(
            $this,
            "daily",
            null
        );
        $lp_table->init();

        if ($a_load_data) {
            $lp_table->getItems();
        } else {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showDailyGraph(): void
    {
        if (!$this->initItemIdFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("no_checkbox")
            );
            $this->daily();
            return;
        }

        $this->tabs_gui->activateSubTab('trac_object_stat_daily');

        $lp_table = new ilLPObjectStatisticsDailyTableGUI(
            $this,
            "daily",
            $this->initItemIdFromPost()
        );
        $lp_table->init();
        $lp_table->getItems();
        $this->tpl->setContent(
            $lp_table->getGraph(
                $this->initItemIdFromPost()
            ) . $lp_table->getHTML()
        );
    }

    public function admin(): void
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
            $lp_table = new ilLPObjectStatisticsAdminTableGUI($this, "admin");
            $this->tpl->setContent($lp_table->getHTML());
        }
    }

    public function adminSync(): void
    {
        ilChangeEvent::_syncObjectStats(time(), 1);

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt(
                "trac_sync_obj_stats_success"
            ),
            true
        );
        $this->ctrl->redirect($this, "admin");
    }

    public function confirmDeleteData(): void
    {
        if (!$this->initItemIdFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("no_checkbox")
            );
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
        foreach ($this->initItemIdFromPost() as $i) {
            $caption = $this->lng->txt(
                "month_" . str_pad(
                    substr($i, 5),
                    2,
                    "0",
                    STR_PAD_LEFT
                ) . "_long"
            ) .
                " " . substr($i, 0, 4);

            $cgui->addItem("item_id[]", $i, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function deleteData(): void
    {
        if (!$this->initItemIdFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("no_checkbox")
            );
            $this->admin();
            return;
        }

        ilTrQuery::deleteObjectStatistics($this->initItemIdFromPost());
        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt("trac_data_deleted")
        );
        $this->admin();
    }

    public function applyLearningProgressFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsLPTableGUI(
            $this,
            "learningProgress",
            null
        );
        $lp_table->init();
        $lp_table->resetOffset();
        $lp_table->writeFilterToSession();
        $this->learningProgress();
    }

    public function resetLearningProgressFilter(): void
    {
        $lp_table = new ilLPObjectStatisticsLPTableGUI(
            $this,
            "learningProgress",
            null
        );
        $lp_table->init();
        $lp_table->resetOffset();
        $lp_table->resetFilter();
        $this->learningProgress();
    }

    public function learningProgressFilter(): void
    {
        $this->learningProgress(false);
    }

    public function learningProgress(bool $a_load_data = true): void
    {
        $this->tabs_gui->activateSubTab('trac_object_stat_lp');

        $this->showCronJobInfo();

        $lp_table = new ilLPObjectStatisticsLPTableGUI(
            $this,
            "learningProgress",
            null
        );
        $lp_table->init();
        if ($a_load_data) {
            $lp_table->loadItems();
        } else {
            $lp_table->disable("content");
            $lp_table->disable("header");
        }
        $this->tpl->setContent($lp_table->getHTML());
    }

    public function showLearningProgressGraph()
    {
        if (!$this->initItemIdFromPost()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("no_checkbox")
            );
            $this->learningProgress();
            return;
        }

        $this->tabs_gui->activateSubTab('trac_object_stat_lp');
        $lp_table = new ilLPObjectStatisticsLPTableGUI(
            $this,
            "learningProgress",
            $this->initItemIdFromPost(),
            true
        );
        $lp_table->init();
        $lp_table->loadItems();
        $this->tpl->setContent(
            $lp_table->getGraph(
                $this->initItemIdFromPost()
            ) . $lp_table->getHTML()
        );
    }

    public function showLearningProgressDetails(): void
    {
        $item_id = 0;
        if ($this->http->wrapper()->query()->has('item_id')) {
            $item_id = $this->http->wrapper()->query()->retrieve(
                'item_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $lp_table = new ilLPObjectStatisticsLPTableGUI(
            $this,
            "showLearningProgressDetails",
            array($item_id),
            false,
            true
        );
        $lp_table->init();
        $lp_table->loadItems();
        $a_tpl = new ilTemplate(
            "tpl.lp_object_statistics_lp_details.html",
            true,
            true,
            "Services/Tracking"
        );
        $a_tpl->setVariable("CONTENT", $lp_table->getHTML());
        $a_tpl->setVariable('CLOSE_IMG_TXT', $this->lng->txt('close'));
        echo $a_tpl->get();
        exit();
    }

    protected function showAggregationInfo(bool $a_show_link = true): void
    {
        $info = ilTrQuery::getObjectStatisticsLogInfo();
        $info_date = ilDatePresentation::formatDate(
            new ilDateTime($info["tstamp"], IL_CAL_UNIX)
        );

        $link = "";
        if ($a_show_link && $this->access->checkAccess(
            "write",
            "",
            $this->ref_id
        )) {
            $link = " <a href=\"" . $this->ctrl->getLinkTarget(
                $this,
                "admin"
            ) . "\">&raquo;" .
                $this->lng->txt("trac_log_info_link") . "</a>";
        }

        $this->tpl->setOnScreenMessage(
            'info',
            sprintf(
                $this->lng->txt(
                    "trac_log_info"
                ),
                $info_date,
                $info["counter"]
            ) . $link
        );
    }

    protected function showCronJobInfo(): void
    {
        if (!$this->cronManager->isJobActive("lp_object_statistics")) {
            $this->tpl->setOnScreenMessage(
                'info',
                $this->lng->txt("trac_cron_info")
            );
        }
    }
}
