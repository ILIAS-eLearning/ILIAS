<?php

/**
 * Class ilDclTableViewGUI
 * @author       Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup      ModulesDataCollection
 * @ilCtrl_Calls ilDclTableViewGUI: ilDclTableViewEditGUI
 */
class ilDclTableViewGUI
{

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

        if ($table_id == 0) {
            $table_id = $this->http->wrapper()->query()->retrieve('table_id', $this->refinery->kindlyTo()->int());
        }

        $this->table = ilDclCache::getTableCache($table_id);

        $this->ctrl->saveParameterByClass('ilDclTableEditGUI', 'table_id');
        $locator->addItem($this->table->getTitle(), $this->ctrl->getLinkTargetByClass('ilDclTableEditGUI', 'edit'));
        $this->tpl->setLocator();

        if (!$this->checkAccess()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass('ildclrecordlistgui', 'listRecords');
        }
    }

    public function executeCommand() : void
    {
        $this->ctrl->saveParameter($this, 'table_id');
        $cmd = $this->ctrl->getCmd("show");
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ildcltablevieweditgui':
                if ($this->http->wrapper()->query()->has('tableview_id')) {
                    $tableview_id = $this->http->wrapper()->query()->retrieve('tableview_id',
                        $this->refinery->kindlyTo()->int());
                } else {
                    $tableview_id = 0;
                }

                $edit_gui = new ilDclTableViewEditGUI($this, $this->table,
                    ilDclTableView::findOrGetInstance($tableview_id));
                $this->ctrl->saveParameter($edit_gui, 'tableview_id');
                $this->ctrl->forwardCommand($edit_gui);
                break;
            default:
                $this->$cmd();
                break;
        }
    }

    public function getParentObj() : ilDclTableListGUI
    {
        return $this->parent_obj;
    }

    protected function checkAccess() : bool
    {
        return ilObjDataCollectionAccess::hasAccessToEditTable($this->parent_obj->getDataCollectionObject()->getRefId(),
            $this->table->getId());
    }

    public function show() : void
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
            $options[$table->getId()] = $table->getTitle();
        }
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

    public function doTableSwitch() : void
    {
        $this->ctrl->setParameterByClass("ilDclTableViewGUI", "table_id",
            $this->http->wrapper()->post()->retrieve('table_id', $this->refinery->kindlyTo()->int()));
        $this->ctrl->redirectByClass("ilDclTableViewGUI", "show");
    }

    /**
     * Confirm deletion of multiple fields
     */
    public function confirmDeleteTableviews() : void
    {
        //at least one view must exist

        $tableviews = [];
        $has_dcl_tableview_ids = $this->http->wrapper()->post()->has('dcl_tableview_ids');
        if ($has_dcl_tableview_ids) {
            $tableviews = $this->http->wrapper()->post()->retrieve('dcl_tableview_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()));
        }
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

    protected function deleteTableviews() : void
    {
        $tableviews = [];
        $has_dcl_tableview_ids = $this->http->wrapper()->post()->has('dcl_tableview_ids');
        if ($has_dcl_tableview_ids) {
            $tableviews = $this->http->wrapper()->post()->retrieve('dcl_tableview_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()));
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
    public function checkViewsLeft(int $delete_count) : void
    {
        if ($delete_count >= count($this->table->getTableViews())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('dcl_msg_tableviews_delete_all'), true);
            $this->ctrl->redirect($this, 'show');
        }
    }

    /**
     * invoked by ilDclTableViewTableGUI
     */
    public function saveTableViewOrder() : void
    {
        $orders = $this->http->wrapper()->post()->retrieve(
            'order',
            $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->string())
        );
        asort($orders);
        $tableviews = array();
        foreach (array_keys($orders) as $tableview_id) {
            $tableviews[] = ilDclTableView::find($tableview_id);
        }
        $this->table->sortTableViews($tableviews);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('dcl_msg_tableviews_order_updated'));
        $this->ctrl->redirect($this);
    }
}
