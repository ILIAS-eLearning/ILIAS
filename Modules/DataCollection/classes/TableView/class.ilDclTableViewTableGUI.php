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

    public function __construct(ilDclTableViewGUI $a_parent_obj, $a_parent_cmd, $table_id)
    {
        global $lng, $ilCtrl;
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->parent_obj = $a_parent_obj;
        $this->table = ilDclCache::getTableCache($table_id);

        $this->setId('dcl_tableview_list');
        $this->addColumn('', '', '1', true);
        $this->addColumn($lng->txt('dcl_order'), NULL, '30px');
        $this->addColumn($lng->txt('dcl_title'), NULL, 'auto');
        $this->addColumn($lng->txt('actions'), NULL, '30px');

        $this->addMultiCommand('confirmDeleteFields', $lng->txt('dcl_delete_views'));

        $ilCtrl->setParameterByClass('ildcltablevieweditgui', 'table_id', $this->parent_obj->table_id);
        $ilCtrl->setParameterByClass('ildcltableviewgui', 'table_id', $this->parent_obj->table_id);

        $this->setFormAction($ilCtrl->getFormActionByClass('ildcltableviewgui'));
        $this->addCommandButton('save', $lng->txt('dcl_save'));

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

        $this->setData($this->table->getFields());
        require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclDatatype.php'); //ist dies benötigt?
        $this->setTitle($lng->txt('dcl_table_list_fields'));
        $this->setRowTemplate('tpl.tableview_list_row.html', 'Modules/DataCollection');
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }

}