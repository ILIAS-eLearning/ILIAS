<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclTableListGUI
 *
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_Calls ilDclTableListGUI: ilDclFieldListGUI, ilDclFieldEditGUI, ilDclTableViewGUI, ilDclTableEditGUI
 */
class ilDclTableListGUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;


    /**
     * ilDclTableListGUI constructor.
     *
     * @param ilObjDataCollectionGUI $a_parent_obj
     */
    public function __construct(ilObjDataCollectionGUI $a_parent_obj)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $ilToolbar = $DIC['ilToolbar'];

        $this->parent_obj = $a_parent_obj;
        $this->obj_id = $a_parent_obj->obj_id;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->toolbar = $ilToolbar;

        if (!$this->checkAccess()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        }
    }


    /**
     * execute command
     */
    public function executeCommand()
    {
        global $DIC;
        $cmd = $this->ctrl->getCmd('listTables');

        $next_class = $this->ctrl->getNextClass($this);

        /*
         * see https://www.ilias.de/mantis/view.php?id=22775
         */
        $tableHelper = new ilDclTableHelper((int) $this->obj_id, (int) $_GET['ref_id'], $DIC->rbac()->review(), $DIC->user(), $DIC->database());
        // send a warning if there are roles with rbac read access on the data collection but without read access on any standard view
        $role_titles = $tableHelper->getRoleTitlesWithoutReadRightOnAnyStandardView();

        if (count($role_titles) > 0) {
            ilUtil::sendInfo($DIC->language()->txt('dcl_rbac_roles_without_read_access_on_any_standard_view') . " " . implode(", ", $role_titles));
        }

        switch ($next_class) {
            case 'ildcltableeditgui':
                $this->tabs->clearTargets();
                if ($cmd != 'create') {
                    $this->setTabs('settings');
                } else {
                    $this->tabs->setBackTarget($this->lng->txt('back'), $this->ctrl->getLinkTarget($this, 'listTables'));
                }
                require_once 'Modules/DataCollection/classes/Table/class.ilDclTableEditGUI.php';
                $ilDclTableEditGUI = new ilDclTableEditGUI($this);
                $this->ctrl->forwardCommand($ilDclTableEditGUI);
                break;

            case 'ildclfieldlistgui':
                $this->tabs->clearTargets();
                $this->setTabs('fields');
                require_once 'Modules/DataCollection/classes/Fields/class.ilDclFieldListGUI.php';
                $ilDclFieldListGUI = new ilDclFieldListGUI($this);
                $this->ctrl->forwardCommand($ilDclFieldListGUI);
                break;

            case "ildclfieldeditgui":
                $this->tabs->clearTargets();
                $this->setTabs("fields");
                require_once "Modules/DataCollection/classes/Fields/class.ilDclFieldEditGUI.php";
                $ilDclFieldEditGUI = new ilDclFieldEditGUI($this);
                $this->ctrl->forwardCommand($ilDclFieldEditGUI);
                break;

            case 'ildcltableviewgui':
                $this->tabs->clearTargets();
                $this->setTabs('tableviews');
                require_once 'Modules/DataCollection/classes/TableView/class.ilDclTableViewGUI.php';
                $ilDclTableViewGUI = new ilDclTableViewGUI($this);
                $this->ctrl->forwardCommand($ilDclTableViewGUI);
                break;

            default:
                switch ($cmd) {
                    default:
                        $this->$cmd();
                        break;
                }
        }
    }


    public function listTables()
    {
        $add_new = ilLinkButton::getInstance();
        $add_new->setPrimary(true);
        $add_new->setCaption("dcl_add_new_table");
        $add_new->setUrl($this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'create'));
        $this->toolbar->addStickyItem($add_new);

        $table_gui = new ilDclTableListTableGUI($this);
        $this->tpl->setContent($table_gui->getHTML());
    }


    protected function setTabs($active)
    {
        $this->tabs->setBackTarget($this->lng->txt('dcl_tables'), $this->ctrl->getLinkTarget($this, 'listTables'));
        $this->tabs->addTab('settings', $this->lng->txt('settings'), $this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'edit'));
        $this->tabs->addTab('fields', $this->lng->txt('dcl_list_fields'), $this->ctrl->getLinkTargetByClass('ilDclFieldListGUI', 'listFields'));
        $this->tabs->addTab('tableviews', $this->lng->txt('dcl_tableviews'), $this->ctrl->getLinkTargetByClass('ilDclTableViewGUI'));
        $this->tabs->setTabActive($active);
    }


    /**
     *
     */
    protected function save()
    {
        $comments = $_POST['comments'];
        $visible = $_POST['visible'];
        $orders = $_POST['order'];
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


    /**
     * Confirm deletion of multiple fields
     */
    public function confirmDeleteTables()
    {
        //at least one table must exist
        $tables = isset($_POST['dcl_table_ids']) ? $_POST['dcl_table_ids'] : array();
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


    /**
     *
     */
    protected function deleteTables()
    {
        $tables = isset($_POST['dcl_table_ids']) ? $_POST['dcl_table_ids'] : array();
        foreach ($tables as $table_id) {
            ilDclCache::getTableCache($table_id)->doDelete();
        }
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tables_deleted'), true);
        $this->ctrl->redirect($this, 'listTables');
    }


    /**
     * redirects if there are no tableviews left after deletion of {$delete_count} tableviews
     *
     * @param $delete_count number of tableviews to delete
     */
    public function checkTablesLeft($delete_count)
    {
        if ($delete_count >= count($this->getDataCollectionObject()->getTables())) {
            ilUtil::sendFailure($this->lng->txt('dcl_msg_tables_delete_all'), true);
            $this->ctrl->redirect($this, 'listTables');
        }
    }


    /**
     * @return bool
     */
    protected function checkAccess()
    {
        $ref_id = $this->getDataCollectionObject()->getRefId();

        return ilObjDataCollectionAccess::hasWriteAccess($ref_id);
    }


    /**
     * @return ilObjDataCollection
     */
    public function getDataCollectionObject()
    {
        return $this->parent_obj->getDataCollectionObject();
    }
}
