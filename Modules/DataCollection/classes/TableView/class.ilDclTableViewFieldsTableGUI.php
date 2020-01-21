<?php

/**
 * Class ilDclTableViewEditFieldsTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableViewEditFieldsTableGUI extends ilTable2GUI
{
    public function __construct(ilDclTableViewEditGUI $a_parent_obj)
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        parent::__construct($a_parent_obj);

        $this->setId('dcl_tableviews');
        $this->setTitle($lng->txt('dcl_tableview_fieldsettings'));
        $this->addColumn($lng->txt('dcl_fieldtitle'), null, 'auto');
        $this->addColumn($lng->txt('dcl_field_visible'), null, 'auto');
        $this->addColumn($lng->txt('dcl_filter'), null, 'auto');
        $this->addColumn($lng->txt('dcl_std_filter'), null, 'auto');
        $this->addColumn($lng->txt('dcl_filter_changeable'), null, 'auto');

        $ilCtrl->saveParameter($this, 'tableview_id');
        $this->setFormAction($ilCtrl->getFormActionByClass('ildcltablevieweditgui'));
        $this->addCommandButton('saveTable', $lng->txt('dcl_save'));

        $this->setExternalSegmentation(true);
        $this->setExternalSorting(true);

        $this->setRowTemplate('tpl.tableview_fields_row.html', 'Modules/DataCollection');
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setShowRowsSelector(false);
        $this->setShowTemplates(false);
        $this->setEnableHeader(true);
        $this->setEnableTitle(true);
        $this->setDefaultOrderDirection('asc');

        $this->parseData($a_parent_obj->tableview->getFieldSettings());
    }


    public function parseData($data)
    {
        //enable/disable comments
        if (!$this->parent_obj->table->getPublicCommentsEnabled()) {
            foreach ($data as $key => $rec) {
                if ($rec->getField() == 'comments') {
                    unset($data[$key]);
                }
            }
        }
        $this->setData($data);
    }


    /**
     * @param ilDclTableViewFieldSetting $a_set
     */
    public function fillRow($a_set)
    {
        $field = $a_set->getFieldObject();
        if ($field->getId() == 'comments' && !$this->parent_obj->table->getPublicCommentsEnabled()) {
            return;
        }

        $this->tpl->setVariable('FIELD_TITLE', $field->getTitle());
        $this->tpl->setVariable('ID', $a_set->getId());
        $this->tpl->setVariable('FIELD_ID', $a_set->getField());
        $this->tpl->setVariable('VISIBLE', $a_set->isVisible() ? 'checked' : '');
        if ($field->allowFilterInListView()) {
            $this->tpl->setVariable('IN_FILTER', $a_set->isInFilter() ? 'checked' : '');
            $this->tpl->setVariable('FILTER_VALUE', $this->getStandardFilterHTML($field, $a_set->getFilterValue()));
            $this->tpl->setVariable('FILTER_CHANGEABLE', $a_set->isFilterChangeable() ? 'checked' : '');
        } else {
            $this->tpl->setVariable('NO_FILTER', '');
        }
    }


    /**
     * @param ilDclBaseFieldModel $field
     * @param                     $value
     *
     * @return mixed
     * @throws ilDclException
     */
    protected function getStandardFilterHTML(ilDclBaseFieldModel $field, $value)
    {
        $field_representation = ilDclFieldFactory::getFieldRepresentationInstance($field);
        $field_representation->addFilterInputFieldToTable($this);
        $filter = end($this->filters);
        $this->filters = array();
        $filter->setValueByArray($value);

        return $filter->render();
    }
}
