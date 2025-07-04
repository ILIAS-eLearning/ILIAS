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
 * @ilCtrl_Calls ilDclTableListGUI: ilDclFieldListGUI, ilDclFieldEditGUI, ilDclTableViewGUI, ilDclTableEditGUI
 */
class ilDclTableListGUI
{
    protected \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $renderer;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilObjDataCollectionGUI $parent_obj;

    public function __construct(ilObjDataCollectionGUI $a_parent_obj)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();

        $this->tabs->setSetupMode(true);
        $DIC->help()->setScreenId('dcl_tables');

        $this->parent_obj = $a_parent_obj;

        if (!$this->checkAccess()) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilDclRecordListGUI::class, 'listRecords');
        }
    }

    public function getObjId(): int
    {
        return $this->parent_obj->getObjectId();
    }

    public function getRefId(): int
    {
        return $this->parent_obj->getRefId();
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        global $DIC;
        $cmd = $this->ctrl->getCmd('listTables');

        $next_class = $this->ctrl->getNextClass($this);

        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

        switch ($next_class) {
            case 'ildcltableeditgui':
                $this->tabs->clearTargets();
                if ($cmd != 'create') {
                    $this->setTabs('settings');
                } else {
                    $this->tabs->setBackTarget(
                        $this->lng->txt('back'),
                        $this->ctrl->getLinkTarget($this, 'listTables')
                    );
                }
                $ilDclTableEditGUI = new ilDclTableEditGUI($this);
                $this->ctrl->forwardCommand($ilDclTableEditGUI);
                break;

            case 'ildclfieldlistgui':
                $this->tabs->clearTargets();
                $this->setTabs('fields');
                $ilDclFieldListGUI = new ilDclFieldListGUI($this);
                $this->ctrl->forwardCommand($ilDclFieldListGUI);
                break;

            case "ildclfieldeditgui":
                $this->tabs->clearTargets();
                $this->setTabs("fields");
                $ilDclFieldEditGUI = new ilDclFieldEditGUI($this);
                $this->ctrl->forwardCommand($ilDclFieldEditGUI);
                break;

            case 'ildcltableviewgui':
                $this->tabs->clearTargets();
                $this->setTabs('tableviews');
                $ilDclTableViewGUI = new ilDclTableViewGUI($this);
                $this->ctrl->forwardCommand($ilDclTableViewGUI);
                break;

            default:
                $this->$cmd();
        }
    }

    public function listTables(): void
    {

        $add_new = $this->ui_factory->button()->primary(
            $this->lng->txt("dcl_add_new_table"),
            $this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'create')
        );
        $this->toolbar->addStickyItem($add_new);

        $this->tpl->setContent(
            $this->renderer->render(
                $this->ui_factory->panel()->listing()->standard(
                    $this->lng->txt('dcl_tables'),
                    [$this->ui_factory->item()->group('', $this->getItems())]
                )
            )
        );
    }

    protected function getItems(): array
    {
        $items = [];
        foreach ($this->parent_obj->getDataCollectionObject()->getTables() as $table) {

            $this->ctrl->setParameterByClass(ilObjDataCollectionGUI::class, 'table_id', $table->getId());
            $item = $this->ui_factory->item()->standard(
                $this->ui_factory->link()->standard(
                    $table->getTitle(),
                    $this->ctrl->getLinkTargetByClass(ilDclFieldListGUI::class, 'listFields')
                )
            )
                ->withProperties([
                    $this->lng->txt('visible') => $this->lng->txt($table->getIsVisible() ? 'yes' : 'no'),
                    $this->lng->txt('comments') => $this->lng->txt($table->getPublicCommentsEnabled() ? 'active' : 'inactive')
                ])
                ->withActions(
                    $this->ui_factory->dropdown()->standard(
                        $this->getActions($table)
                    )
                );

            if ($table->getOrder() === 10) {
                $item = $item->withDescription($this->lng->txt('default'));
            }
            $items[] = $item;
        }
        return $items;
    }


    /**
     * @return Shy[]
     */
    protected function getActions(ilDclTable $table): array
    {
        $this->ctrl->setParameterByClass(ilObjDataCollectionGUI::class, 'table_id', $table->getId());

        $actions = [];
        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass(ilDclTableEditGUI::class, 'edit'),
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('dcl_list_fields'),
            $this->ctrl->getLinkTargetByClass(ilDclFieldListGUI::class, 'listFields')
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('dcl_tableviews'),
            $this->ctrl->getLinkTargetByClass(ilDclTableViewGUI::class, 'show')
        );

        $actions[] = $this->ui_factory->button()->shy(
            $this->lng->txt('delete'),
            $this->ctrl->getLinkTargetByClass(ilDclTableEditGUI::class, 'confirmDelete')
        );

        if ($table->getOrder() !== 10) {
            $actions[] = $this->ui_factory->button()->shy(
                $this->lng->txt('set_as_default'),
                $this->ctrl->getLinkTargetByClass(ilDclTableEditGUI::class, 'setAsDefault')
            );
        }

        return $actions;
    }

    protected function setTabs(string $active): void
    {
        $this->tabs->setBackTarget($this->lng->txt('dcl_tables'), $this->ctrl->getLinkTarget($this, 'listTables'));
        $this->tabs->addTab(
            'settings',
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'edit')
        );
        $this->tabs->addTab(
            'fields',
            $this->lng->txt('dcl_list_fields'),
            $this->ctrl->getLinkTargetByClass('ilDclFieldListGUI', 'listFields')
        );
        $this->tabs->addTab(
            'tableviews',
            $this->lng->txt('dcl_tableviews'),
            $this->ctrl->getLinkTargetByClass('ilDclTableViewGUI')
        );
        $this->tabs->activateTab($active);
    }

    protected function save(): void
    {
        if ($this->http->wrapper()->post()->has("comments")) {
            $comments = $this->http->wrapper()->post()->retrieve(
                'comments',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            );
        }
        if ($this->http->wrapper()->post()->has("visible")) {
            $visible = $this->http->wrapper()->post()->retrieve(
                'visible',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
            );
        }
        $orders = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        asort($orders);
        $order = 10;
        foreach (array_keys($orders) as $table_id) {
            $table = ilDclCache::getTableCache($table_id);
            $table->setOrder($order);
            $table->setPublicCommentsEnabled(isset($comments[$table_id]));
            $table->setIsVisible(isset($visible[$table_id]));
            $table->doUpdate();
            $order += 10;
        }
        $this->ctrl->redirect($this);
    }

    protected function checkAccess(): bool
    {
        $ref_id = $this->parent_obj->getRefId();

        return ilObjDataCollectionAccess::hasWriteAccess($ref_id);
    }

    public function getDataCollectionObject(): ilObjDataCollection
    {
        return $this->parent_obj->getDataCollectionObject();
    }
}
