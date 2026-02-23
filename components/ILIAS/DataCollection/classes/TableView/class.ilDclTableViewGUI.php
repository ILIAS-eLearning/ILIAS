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

use ILIAS\UI\Component\Button\Shy;

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

        $DIC->help()->setScreenId('dcl_views');

        if ($table_id == 0) {
            $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }

        $this->table = ilDclCache::getTableCache($table_id);
        $this->table->getCollectionObject()->setRefId(
            $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int())
        );

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
            'show',
            $this->table->getId()
        );

        $this->tpl->setContent(
            $this->renderer->render(
                $this->ui_factory->panel()->listing()->standard(
                    sprintf($this->lng->txt('dcl_tableviews_of_X'), $this->table->getTitle()),
                    [$this->ui_factory->item()->group('', $this->getItems())]
                )
            )
        );
    }

    protected function getItems(): array
    {
        $items = [];
        foreach ($this->table->getTableViews() as $tableview) {

            $this->ctrl->setParameterByClass(ilDclTableViewEditGUI::class, 'tableview_id', $tableview->getId());
            $item = $this->ui_factory->item()->standard(
                $this->ui_factory->link()->standard(
                    $tableview->getTitle(),
                    $this->ctrl->getLinkTargetByClass(ilDclTableViewEditGUI::class, 'show')
                )
            )
                ->withDescription($tableview->getDescription())
                ->withActions($this->ui_factory->dropdown()->standard($this->getActions($tableview)));

            $items[] = $item;
        }
        return $items;
    }

    /**
     * @return Shy[]
     */
    protected function getActions(ilDclTableView $tableview): array
    {
        $this->ctrl->setParameterByClass(ilDclTableViewEditGUI::class, 'tableview_id', $tableview->getId());

        $actions = [];
        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('edit'),
            $this->ctrl->getLinkTargetByClass(ilDclTableViewEditGUI::class, 'editGeneralSettings')
        );
        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('copy'),
            $this->ctrl->getLinkTargetByClass(ilDclTableViewEditGUI::class, 'copy')
        );
        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('delete'),
            $this->ctrl->getLinkTargetByClass(ilDclTableViewEditGUI::class, 'confirmDelete')
        );
        return $actions;
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
}
