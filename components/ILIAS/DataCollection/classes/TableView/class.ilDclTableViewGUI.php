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

declare(strict_types=1);

/**
 * @ilCtrl_Calls ilDclTableViewGUI: ilDclTableViewEditGUI
 */
class ilDclTableViewGUI
{
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilDclTable $table;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilDclTableListGUI $parent_obj;

    /**
     * Constructor
     * @param ilDclTableListGUI $a_parent_obj
     * @param int               $table_id
     */
    public function __construct(ilDclTableListGUI $a_parent_obj, int $table_id = 0)
    {
        global $DIC;

        $locator = $DIC['ilLocator'];
        $this->parent_obj = $a_parent_obj;
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        if ($table_id == 0) {
            $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }

        $this->table = ilDclCache::getTableCache($table_id);

        $this->ctrl->saveParameterByClass('ilDclTableEditGUI', 'table_id');
        $locator->addItem($this->table->getTitle(), $this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'edit'));
        $this->tpl->setLocator();

        if (!$this->checkAccess()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilDclRecordListGUI::class, 'listRecords');
        }
    }

    public function executeCommand(): void
    {
        $this->ctrl->saveParameter($this, 'table_id');
        $cmd = $this->ctrl->getCmd("show");
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case strtolower(ilDclTableViewEditGUI::class):
                if ($this->http->wrapper()->query()->has('tableview_id')) {
                    $tableview_id = $this->http->wrapper()->query()->retrieve(
                        'tableview_id',
                        $this->refinery->kindlyTo()->int()
                    );
                } else {
                    $tableview_id = 0;
                }

                $edit_gui = new ilDclTableViewEditGUI(
                    $this,
                    $this->table,
                    ilDclTableView::findOrGetInstance($tableview_id)
                );
                $this->ctrl->saveParameter($edit_gui, 'tableview_id');
                $this->ctrl->forwardCommand($edit_gui);
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    public function getParentObj(): ilDclTableListGUI
    {
        return $this->parent_obj;
    }

    protected function checkAccess(): bool
    {
        return ilObjDataCollectionAccess::hasAccessToEditTable(
            $this->parent_obj->getDataCollectionObject()->getRefId(),
            $this->table->getId()
        );
    }

    public function show(): void
    {
        $add_new = $this->ui_factory->button()->primary(
            $this->lng->txt("dcl_add_new_view"),
            $this->ctrl->getLinkTargetByClass(ilDclTableViewEditGUI::class, 'add')
        );
        $this->toolbar->addStickyItem($add_new);

        $this->toolbar->addSeparator();

        $switcher = new ilDclSwitcher($this->toolbar, $this->ui_factory, $this->ctrl, $this->lng);
        $switcher->addTableSwitcherToToolbar(
            $this->parent_obj->getDataCollectionObject()->getTables(),
            self::class,
            'show'
        );

        $table_gui = new ilDclTableViewTableGUI($this, 'show', $this->table, $this->getParentObj()->getRefId());
        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
     * Confirm deletion of multiple fields
     */
    public function confirmDeleteTableviews(): void
    {
        //at least one view must exist
        $has_dcl_tableview_ids = $this->http->wrapper()->post()->has('dcl_tableview_ids');
        if (!$has_dcl_tableview_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_delete_views_no_selection'), true);
            $this->ctrl->redirect($this, 'show');
        }

        $tableviews = $this->http->wrapper()->post()->retrieve(
            'dcl_tableview_ids',
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );
        $this->checkViewsLeft(count($tableviews));

        $this->tabs->clearSubTabs();
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_tableviews_confirm_delete'));

        foreach ($tableviews as $tableview_id) {
            $conf->addItem('dcl_tableview_ids[]', (string)$tableview_id, ilDclTableView::find($tableview_id)->getTitle());
        }
        $conf->setConfirm($this->lng->txt('delete'), 'deleteTableviews');
        $conf->setCancel($this->lng->txt('cancel'), 'show');
        $this->tpl->setContent($conf->getHTML());
    }

    protected function deleteTableviews(): void
    {
        $has_dcl_tableview_ids = $this->http->wrapper()->post()->has('dcl_tableview_ids');
        if ($has_dcl_tableview_ids) {
            $tableviews = $this->http->wrapper()->post()->retrieve(
                'dcl_tableview_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            foreach ($tableviews as $tableview_id) {
                ilDclTableView::find($tableview_id)->delete();
            }
        }

        $this->table->sortTableViews();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableviews_deleted'), true);
        $this->ctrl->redirect($this, 'show');
    }

    /**
     * redirects if there are no tableviews left after deletion of {$delete_count} tableviews
     * @param $delete_count number of tableviews to delete
     */
    public function checkViewsLeft(int $delete_count): void
    {
        if ($delete_count >= count($this->table->getTableViews())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_msg_tableviews_delete_all'), true);
            $this->ctrl->redirect($this, 'show');
        }
    }

    /**
     * invoked by ilDclTableViewTableGUI
     */
    public function saveTableViewOrder(): void
    {
        $orders = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        asort($orders);
        $tableviews = [];
        foreach (array_keys($orders) as $tableview_id) {
            $tableviews[] = ilDclTableView::find($tableview_id);
        }
        $this->table->sortTableViews($tableviews);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableviews_order_updated'));
        $this->ctrl->redirect($this);
    }
}
