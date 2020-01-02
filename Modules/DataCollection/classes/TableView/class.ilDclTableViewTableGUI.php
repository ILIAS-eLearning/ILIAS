<?php

/**
 * Class ilDclTableViewTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 *
 *
 */
class ilDclTableViewTableGUI extends ilTable2GUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilDclTable
     */
    protected $table;
    /**
     * @var ilDclTableViewGUI
     */
    protected $parent_obj;


    /**
     * ilDclTableViewTableGUI constructor.
     *
     * @param ilDclTableViewGUI $a_parent_obj
     * @param string            $a_parent_cmd
     * @param ilDclTable        $table
     */
    public function __construct(ilDclTableViewGUI $a_parent_obj, $a_parent_cmd, ilDclTable $table)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = $table;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        if ($this->parent_obj instanceof ilDclTableViewGUI) {
            $ilCtrl->setParameterByClass('ildcltableviewgui', 'table_id', $table->getId());
            $this->setFormAction($ilCtrl->getFormActionByClass('ildcltableviewgui'));
            $this->addMultiCommand('confirmDeleteTableviews', $lng->txt('dcl_delete_views'));
            $this->addCommandButton('saveTableViewOrder', $lng->txt('dcl_save_order'));

            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->setFormName('tableview_list');

            $this->addColumn('', '', '1', true);
            $this->addColumn($lng->txt('dcl_order'), null, '30px');

            $this->setRowTemplate('tpl.tableview_list_row.html', 'Modules/DataCollection');
            $this->setData($this->table->getTableViews());
        } elseif ($this->parent_obj instanceof ilDclDetailedViewGUI) {
            $this->setRowTemplate('tpl.detailview_list_row.html', 'Modules/DataCollection');
            $this->setData($this->table->getVisibleTableViews($this->parent_obj->parent_obj->ref_id, true));
        }

        $this->addColumn($lng->txt('title'), null, 'auto');
        $this->addColumn($lng->txt('description'), null, 'auto');
        $this->addColumn($lng->txt('actions'), null, '30px');

        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');
        $this->setLimit(0);

        $this->setId('dcl_tableviews');
        $this->setTitle($lng->txt("dcl_tableviews_table"));
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }


    /**
     * @param ilDclTableView $a_set
     */
    public function fillRow($a_set)
    {
        if ($this->parent_obj instanceof ilDclTableViewGUI) {
            $this->tpl->setVariable("ID", $a_set->getId());
            $this->tpl->setVariable("ORDER_NAME", "order[{$a_set->getId()}]");
            $this->tpl->setVariable("ORDER_VALUE", $a_set->getOrder());
        }
        $this->tpl->setVariable("TITLE", $a_set->getTitle());
        $this->ctrl->setParameterByClass('ilDclTableViewEditGUI', 'tableview_id', $a_set->getId());
        $this->tpl->setVariable("TITLE_LINK", $this->ctrl->getLinkTargetByClass('ilDclTableViewEditGUI'));
        $this->tpl->setVariable("DESCRIPTION", $a_set->getDescription());
        $this->tpl->setVariable('ACTIONS', $this->buildAction($a_set->getId()));
    }


    /**
     * build either actions menu or view button
     *
     * @param $id
     *
     * @return string
     */
    protected function buildAction($id)
    {
        if ($this->parent_obj instanceof ilDclTableViewGUI) {
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($id);
            $alist->setListTitle($this->lng->txt('actions'));
            $this->ctrl->setParameterByClass('ildcltableviewgui', 'tableview_id', $id);
            $this->ctrl->setParameterByClass('ilDclDetailedViewDefinitionGUI', 'tableview_id', $id);
            $alist->addItem($this->lng->txt('settings'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'editGeneralSettings'));
            $alist->addItem($this->lng->txt('dcl_list_visibility_and_filter'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'editFieldSettings'));
            $alist->addItem($this->lng->txt('dcl_detailed_view'), '', $this->ctrl->getLinkTargetByClass(array('ildcltablevieweditgui', 'ilDclDetailedViewDefinitionGUI'), 'edit'));
            $alist->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'confirmDelete'));

            return $alist->getHTML();
        } elseif ($this->parent_obj instanceof ilDclDetailedViewGUI) {
            $button = ilDclLinkButton::getInstance();
            $this->ctrl->setParameterByClass('ilDclDetailedViewGUI', 'tableview_id', $id);
            $this->ctrl->saveParameterByClass('ilDclDetailedViewGUI', 'record_id');
            $button->setUrl($this->ctrl->getLinkTargetByClass('ilDclDetailedViewGUI', 'renderRecord'));
            $button->setCaption('view');

            return $button->getToolbarHTML();
        }
    }
}
