<?php

/**
 * Class ilDclTableViewGUI
 *
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup      ModulesDataCollection
 *
 * @ilCtrl_Calls ilDclTableViewGUI: ilDclTableViewEditGUI
 */
class ilDclTableViewGUI
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
     * @var ilToolbarGUI
     */
    protected $toolbar;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilTabsGUI
     */
    protected $tabs;
    /**
     * @var ilDclTable
     */
    protected $table;


    /**
     * Constructor
     *
     * @param ilDclTableListGUI $a_parent_obj
     * @param    int            $table_id
     */
    public function __construct(ilDclTableListGUI $a_parent_obj, $table_id = 0)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilToolbar = $DIC['ilToolbar'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $locator = $DIC['ilLocator'];

        if ($table_id == 0) {
            $table_id = $_GET['table_id'];
        }

        $this->parent_obj = $a_parent_obj;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tabs = $ilTabs;
        $this->toolbar = $ilToolbar;
        $this->table = ilDclCache::getTableCache($table_id);

        $this->ctrl->saveParameterByClass('ilDclTableEditGUI', 'table_id');
        $locator->addItem($this->table->getTitle(), $this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'edit'));
        $this->tpl->setLocator();

        if (!$this->checkAccess()) {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        }
    }


    /**
     *
     */
    public function executeCommand()
    {
        $this->ctrl->saveParameter($this, 'table_id');
        $cmd = $this->ctrl->getCmd("show");
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ildcltablevieweditgui':
                $edit_gui = new ilDclTableViewEditGUI($this, $this->table, ilDclTableView::findOrGetInstance($_GET['tableview_id']));
                $this->ctrl->saveParameter($edit_gui, 'tableview_id');
                $this->ctrl->forwardCommand($edit_gui);
                break;
            default:
                switch ($cmd) {
                    default:
                        $this->$cmd();
                        break;
                }
                break;
        }
    }


    /**
     * @return bool
     */
    protected function checkAccess()
    {
        return ilObjDataCollectionAccess::hasAccessToEditTable($this->parent_obj->getDataCollectionObject()->getRefId(), $this->table->getId());
    }


    /**
     *
     */
    public function show()
    {
        $add_new = ilLinkButton::getInstance();
        $add_new->setPrimary(true);
        $add_new->setCaption("dcl_add_new_view");
        $add_new->setUrl($this->ctrl->getLinkTargetByClass('ilDclTableViewEditGUI', 'add'));
        $this->toolbar->addStickyItem($add_new);

        $this->toolbar->addSeparator();

        // Show tables
        $tables = $this->parent_obj->getDataCollectionObject()->getTables();

        foreach ($tables as $table) {
            $options[$table->getId()] = $table->getTitle(); //TODO order tables
        }
        include_once './Services/Form/classes/class.ilSelectInputGUI.php';
        $table_selection = new ilSelectInputGUI('', 'table_id');
        $table_selection->setOptions($options);
        $table_selection->setValue($this->table->getId());

        $this->toolbar->setFormAction($this->ctrl->getFormActionByClass("ilDclTableViewGUI", "doTableSwitch"));
        $this->toolbar->addText($this->lng->txt("dcl_select"));
        $this->toolbar->addInputItem($table_selection);
        $button = ilSubmitButton::getInstance();
        $button->setCommand("doTableSwitch");
        $button->setCaption('change');
        $this->toolbar->addButtonInstance($button);

        $table_gui = new ilDclTableViewTableGUI($this, 'show', $this->table);
        $this->tpl->setContent($table_gui->getHTML());
    }


    /**
     *
     */
    public function doTableSwitch()
    {
        $this->ctrl->setParameterByClass("ilDclTableViewGUI", "table_id", $_POST['table_id']);
        $this->ctrl->redirectByClass("ilDclTableViewGUI", "show");
    }


    /**
     * Confirm deletion of multiple fields
     */
    public function confirmDeleteTableviews()
    {
        //at least one view must exist
        $tableviews = isset($_POST['dcl_tableview_ids']) ? $_POST['dcl_tableview_ids'] : array();
        $this->checkViewsLeft(count($tableviews));

        $this->tabs->clearSubTabs();
        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->lng->txt('dcl_tableviews_confirm_delete'));

        foreach ($tableviews as $tableview_id) {
            $conf->addItem('dcl_tableview_ids[]', $tableview_id, ilDclTableView::find($tableview_id)->getTitle());
        }
        $conf->setConfirm($this->lng->txt('delete'), 'deleteTableviews');
        $conf->setCancel($this->lng->txt('cancel'), 'show');
        $this->tpl->setContent($conf->getHTML());
    }


    /**
     *
     */
    protected function deleteTableviews()
    {
        $tableviews = isset($_POST['dcl_tableview_ids']) ? $_POST['dcl_tableview_ids'] : array();
        foreach ($tableviews as $tableview_id) {
            ilDclTableView::find($tableview_id)->delete();
        }
        $this->table->sortTableViews();
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableviews_deleted'), true);
        $this->ctrl->redirect($this, 'show');
    }


    /**
     * redirects if there are no tableviews left after deletion of {$delete_count} tableviews
     *
     * @param $delete_count number of tableviews to delete
     */
    public function checkViewsLeft($delete_count)
    {
        if ($delete_count >= count($this->table->getTableViews())) {
            ilUtil::sendFailure($this->lng->txt('dcl_msg_tableviews_delete_all'), true);
            $this->ctrl->redirect($this, 'show');
        }
    }


    /**
     * invoked by ilDclTableViewTableGUI
     */
    public function saveTableViewOrder()
    {
        $orders = $_POST['order'];
        asort($orders);
        $tableviews = array();
        foreach (array_keys($orders) as $tableview_id) {
            $tableviews[] = ilDclTableView::find($tableview_id);
        }
        $this->table->sortTableViews($tableviews);
        ilUtil::sendSuccess($this->lng->txt('dcl_msg_tableviews_order_updated'));
        $this->ctrl->redirect($this);
    }
}
