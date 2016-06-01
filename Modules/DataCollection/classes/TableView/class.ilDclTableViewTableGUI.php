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
        if ($this->parent_obj instanceof ilDclTableViewGUI)
        {
            $this->addMultiCommand('confirmDeleteTableviews', $lng->txt('dcl_delete_views'));

            $ilCtrl->setParameterByClass('ildcltablevieweditgui', 'table_id', $table->getId());
            $ilCtrl->setParameterByClass('ildcltableviewgui', 'table_id', $table->getId());

            $this->setFormAction($ilCtrl->getFormActionByClass('ildcltableviewgui'));
            $this->addCommandButton('saveTableOrder', $lng->txt('dcl_save'));

            $add_button = ilLinkButton::getInstance();
            $add_button->setUrl($this->ctrl->getLinkTargetByClass('ilDclTableViewEditGUI', 'add'));
            $add_button->setCaption($this->lng->txt('dcl_tableview_add'));
            $this->addCommandButtonInstance($add_button);

            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->setFormName('tableview_list');

            $this->addColumn('', '', '1', true);
            $this->addColumn($lng->txt('dcl_order'), NULL, '30px');

            $this->setRowTemplate('tpl.tableview_list_row.html', 'Modules/DataCollection');
        }
        elseif ($this->parent_obj instanceof ilDclRecordViewGUI)
        {
            $this->setRowTemplate('tpl.detailview_list_row.html', 'Modules/DataCollection');
        }
        $this->addColumn($lng->txt('title'), NULL, 'auto');
        $this->addColumn($lng->txt('description'), NULL, 'auto');
        $this->addColumn($lng->txt('actions'), NULL, '30px');


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

        if ($this->parent_obj instanceof ilDclTableViewGUI)
        {
            $this->setData($this->table->getTableViews());
        }
        elseif ($this->parent_obj instanceof ilDclRecordViewGUI)
        {
            $this->setData($this->table->getVisibleTableViews($this->parent_obj->parent_obj->ref_id, true));
        }

        $this->setTitle($lng->txt("dcl_tableviews"));
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
        $this->tpl->setVariable("DESCRIPTION", $a_set->getDescription());
        $this->tpl->setVariable('ACTIONS', $this->buildAction($a_set->getId()));

    }
    
    protected function buildAction($id) {


        if ($this->parent_obj instanceof ilDclTableViewGUI)
        {
            include_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($id);
            $alist->setListTitle($this->lng->txt('actions'));
            $this->ctrl->setParameterByClass('ildcltablevieweditgui', 'tableview_id', $id);
            $alist->addItem($this->lng->txt('edit'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui'));
            $alist->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTargetByClass('ildcltablevieweditgui', 'confirmDelete'));
            return $alist->getHTML();
        }
        elseif ($this->parent_obj instanceof ilDclRecordViewGUI)
        {
            $button = ilLinkButton::getInstance();
            $this->ctrl->setParameterByClass('ildclrecordviewgui', 'tableview_id', $id);
            $this->ctrl->saveParameterByClass('ildclrecordviewgui', 'record_id');
            $button->setUrl($this->ctrl->getLinkTargetByClass('ildclrecordviewgui', 'renderRecord'));
            $button->setCaption($this->lng->txt('view'));
            return $button->getToolbarHTML();
        }
        

    }

}