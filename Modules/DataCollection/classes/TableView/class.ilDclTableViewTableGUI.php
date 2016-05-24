<?php
include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
 * Class ilDclTableViewTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewTableGUI extends ilTable2GUI
{

    /**
     * @var ilCtrl
     */
    private $ctrl;

    /**
     * @var ilDclTable
     */
    private $table;

    public function __construct(ilDclTableViewGUI $a_parent_obj, $a_parent_cmd, ilDclTable $table)
    {
        global $lng, $ilCtrl;
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = $table;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;

        $this->setId('dcl_tableviews');
        $this->addColumn('', '', '1', true);
        $this->addColumn($lng->txt('dcl_order'), NULL, '30px');
        $this->addColumn($lng->txt('title'), NULL, 'auto');
        $this->addColumn($lng->txt('description'), NULL, 'auto');
        $this->addColumn($lng->txt('actions'), NULL, '30px');

        $this->addMultiCommand('confirmDeleteFields', $lng->txt('dcl_delete_views'));

        $ilCtrl->setParameterByClass('ildcltablevieweditgui', 'table_id', $table->getId());
        $ilCtrl->setParameterByClass('ildcltableviewgui', 'table_id', $table->getId());

        $this->setFormAction($ilCtrl->getFormActionByClass('ildcltableviewgui'));
        $this->addCommandButton('saveTableOrder', $lng->txt('dcl_save'));

        $add_button = ilLinkButton::getInstance();
        $add_button->setUrl($this->ctrl->getLinkTargetByClass('ilDclTableViewEditGUI'));
        $add_button->setCaption($this->lng->txt('dcl_tableview_add'));
        $this->addCommandButtonInstance($add_button);

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setFormName('tableview_list');

        //those two are important as we get our data as objects not as arrays.
        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');
        $this->setLimit(0);

        $this->setData($this->table->getTableViews());
        $this->setTitle($lng->txt("dcl_tableviews"));
        $this->setRowTemplate('tpl.tableview_list_row.html', 'Modules/DataCollection');
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }

    /**
     * @param ilDclTableView $a_set
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable("ID", $a_set->getId());
        $this->tpl->setVariable("ORDER_NAME", "order[{$a_set->getId()}]");
        $this->tpl->setVariable("ORDER_VALUE", $a_set->getOrder());
        $this->tpl->setVariable("TITLE", $a_set->getTitle());
        $this->tpl->setVariable("DESCRIPTION", $a_set->getDescription());

        include_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
        $alist = new ilAdvancedSelectionListGUI();
        $alist->setId($a_set->getId());
        $alist->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameterByClass('ildcltablevieweditgui', 'tableview_id', $a_set->getId());
        $alist->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui'));
        $alist->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'confirmDelete'));

        $this->tpl->setVariable('ACTIONS', $alist->getHTML());

    }

}