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
 ********************************************************************
 */

/**
 * Class ilDclTableListGUI
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @ilCtrl_Calls ilDclTableListGUI: ilDclFieldListGUI, ilDclFieldEditGUI, ilDclTableViewGUI, ilDclTableEditGUI
 */
class ilDclTableListGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalPageTemplate $tpl;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ILIAS\HTTP\Services $http;
    protected ILIAS\Refinery\Factory $refinery;
    protected ilObjDataCollectionGUI $parent_obj;
    protected int $obj_id;

    /**
     * ilDclTableListGUI constructor.
     * @param ilObjDataCollectionGUI $a_parent_obj
     */
    public function __construct(ilObjDataCollectionGUI $a_parent_obj)
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $ilToolbar = $DIC['ilToolbar'];

        $this->parent_obj = $a_parent_obj;
        $this->obj_id = 0;
        if ($a_parent_obj->getRefId() >= 0) {
            $this->obj_id = ilObject::_lookupObjectId($a_parent_obj->getRefId());
        }

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->toolbar = $ilToolbar;
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        if (!$this->checkAccess()) {
            $main_tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        }
    }

    public function getObjId(): int
    {
        return $this->parent_obj->getObjectId();
    }

    /**
     * execute command
     */
    public function executeCommand(): void
    {
        global $DIC;
        $cmd = $this->ctrl->getCmd('listTables');

        $next_class = $this->ctrl->getNextClass($this);

        /*
         * see https://www.ilias.de/mantis/view.php?id=22775
         */
        $ref_id = $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int());

        $tableHelper = new ilDclTableHelper(
            $this->obj_id,
            $ref_id,
            $DIC->rbac()->review(),
            $DIC->user(),
            $DIC->database()
        );
        // send a warning if there are roles with rbac read access on the data collection but without read access on any standard view
        $role_titles = $tableHelper->getRoleTitlesWithoutReadRightOnAnyStandardView();

        if (count($role_titles) > 0) {
            $this->tpl->setOnScreenMessage(
                'info',
                $DIC->language()->txt('dcl_rbac_roles_without_read_access_on_any_standard_view') . " " . implode(
                    ", ",
                    $role_titles
                )
            );
        }

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
        $add_new = ilLinkButton::getInstance();
        $add_new->setPrimary(true);
        $add_new->setCaption("dcl_add_new_table");
        $add_new->setUrl($this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'create'));
        $this->toolbar->addStickyItem($add_new);

        $table_gui = new ilDclTableListTableGUI($this);
        $this->tpl->setContent($table_gui->getHTML());
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
        $comments = $this->http->wrapper()->post()->retrieve(
            'comments',
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        $visible = $this->http->wrapper()->post()->retrieve(
            'visible',
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
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

    public function confirmDeleteTables(): void
    {
        //at least one table must exist
        $tables = [];
        $has_dcl_table_ids = $this->http->wrapper()->post()->has('dcl_table_ids');
        if ($has_dcl_table_ids) {
            $tables = $this->http->wrapper()->post()->retrieve(
                'dcl_table_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }
        $this->checkTablesLeft(count($tables));

        $this->tabs->clearSubTabs();
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_tables_confirm_delete'));

        foreach ($tables as $table_id) {
            $conf->addItem('dcl_table_ids[]', $table_id, ilDclCache::getTableCache($table_id)->getTitle());
        }
        $conf->setConfirm($this->lng->txt('delete'), 'deleteTables');
        $conf->setCancel($this->lng->txt('cancel'), 'listTables');
        $this->tpl->setContent($conf->getHTML());
    }

    protected function deleteTables(): void
    {
        $has_dcl_table_ids = $this->http->wrapper()->post()->has('dcl_table_ids');
        if ($has_dcl_table_ids) {
            $tables = $this->http->wrapper()->post()->retrieve(
                'dcl_table_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
            foreach ($tables as $table_id) {
                ilDclCache::getTableCache($table_id)->doDelete();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tables_deleted'), true);
        $this->ctrl->redirect($this, 'listTables');
    }

    /**
     * redirects if there are no tableviews left after deletion of {$delete_count} tableviews
     * @param $delete_count number of tableviews to delete
     */
    public function checkTablesLeft(int $delete_count): void
    {
        if ($delete_count >= count($this->getDataCollectionObject()->getTables())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_msg_tables_delete_all'), true);
            $this->ctrl->redirect($this, 'listTables');
        }
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
