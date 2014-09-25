<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.srModelObjectTableGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecordList.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableField.php');

/**
 * GUI-Class arIndexTableGUI
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.6
 *
 */
class arIndexTableGUI extends srModelObjectTableGUI
{


    /**
     * @var arIndexTableField|array
     */
    protected $fields = array();
    /**
     * @var arIndexTableField|array
     */
    protected $fields_for_data = null;

    /**
     * @var ActiveRecordList
     */
    protected $active_record_list = NULL;
    /**
     * @var array
     */
    protected $actions = array();
    /**
     * @var ilToolbarGUI
     */
    protected $toolbar = NULL;
    /**
     * @var arGUI|null
     */
    protected $parent_gui = NULL;
    /**
     * @var string
     */
    protected $table_title = '';


    /**
     * @param arGUI $a_parent_obj
     * @param string $a_parent_cmd
     * @param ActiveRecordList $active_record_list
     */
    public function __construct(arGUI $a_parent_obj, $a_parent_cmd, ActiveRecordList $active_record_list)
    {
        $this->active_record_list = $active_record_list;
        $this->parent_gui         = $a_parent_obj;
        $this->ar_id_field_name   = arFieldCache::getPrimaryFieldName($this->active_record_list->getAR());
        $title                    = strtolower(str_replace("Record", "", get_class($this->active_record_list->getAR()))) . "_index";
        $this->setTableTitle($this->txt($title));
        $this->generateFields();
        $this->customizeFields();
        $this->sortFields();
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->initToolbar();
        $this->addActions();

    }


    /**
     * @return bool
     */
    protected function generateFields()
    {
        $fields = $this->active_record_list->getAR()->getArFieldList()->getFields();

        foreach ($fields as $standard_field)
        {
            $field = arIndexTableField::castFromFieldToViewField($standard_field);
            $this->addField($field);
        }
        return true;
    }

    protected function customizeFields()
    {

    }

    /**
     * @return bool
     */
    protected function sortFields()
    {
        uasort($this->fields, function (arIndexTableField $field_a, arIndexTableField $field_b)
        {
            return $field_a->getPosition() > $field_b->getPosition();
        });
    }

    /**
     * @param arIndexTableField|array
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return arIndexTableField|array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return arIndexTableField
     */
    public function getField($field_name)
    {
        return $this->fields[$field_name];
    }


    /**
     * @param arIndexTableField
     */
    public function addField(arIndexTableField $field)
    {
        $this->fields[$field->getName()] = $field;
    }

    protected function addActions()
    {
        global $lng;

        $this->addAction('view', $lng->txt('view'), get_class($this->parent_obj), 'view', 'view');
        $this->addAction('edit', $lng->txt('edit'), get_class($this->parent_obj), 'edit', 'write');
        $this->addAction('delete', $lng->txt('delete'), get_class($this->parent_obj), 'delete', 'write');
    }


    /**
     * @param string $table_title
     */
    public function setTableTitle($table_title)
    {
        $this->table_title = $table_title;
    }


    /**
     * @return string
     */
    public function getTableTitle()
    {
        return $this->table_title;
    }

    /**
     * @param \ilToolbarGUI $toolbar
     */
    public function setToolbar($toolbar)
    {
        $this->toolbar = $toolbar;
    }

    /**
     * @return \ilToolbarGUI
     */
    public function getToolbar()
    {
        return $this->toolbar;
    }


    protected function initToolbar()
    {
        $toolbar = new ilToolbarGUI();
        $toolbar->addButton($this->txt("add_item"), $this->ctrl->getLinkTarget($this->parent_obj, "add"));
        $this->setToolbar($toolbar);
    }


    protected function initTableData()
    {
        $this->active_record_list->getArWhereCollection()->setStatements(null);
        $this->active_record_list->getArJoinCollection()->setStatements(null);
        $this->active_record_list->getArLimitCollection()->setStatements(null);
        $this->active_record_list->getArOrderCollection()->setStatements(null);

        $this->filterTableData();
        $this->beforeGetData();
        $this->setOrderAndSegmentation();

        $ar_data = $this->active_record_list->getArray();
        $data    = array();

        foreach ($ar_data as $key => $item)
        {
            $data[$key] = array();
            foreach ($this->getFieldsForData() as $field)
            {
                if (array_key_exists($field->getName(), $item))
                {
                    if (!$item[$field->getName()])
                    {
                        $data[$key][$field->getName()] = $this->setArFieldEmptyFieldData($field, $item);
                    } else
                    {
                        $data[$key][$field->getName()] = $this->setArFieldData($field, $item, $item[$field->getName()]);
                    }
                } else
                {
                    $data[$key][$field->getName()] = $this->setCustomFieldData($field, $item);
                }

            }
        }
        $this->setData($data);
    }

    protected function setOrderAndSegmentation(){
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setDefaultOrderField('title');
        $this->determineLimit();
        $this->determineOffsetAndOrder();
        $this->setMaxCount($this->active_record_list->count());
        $this->active_record_list->orderBy($this->getOrderField(), $this->getOrderDirection());
        $this->active_record_list->limit($this->getOffset() , $this->getLimit());
    }

    protected function beforeGetData()
    {
    }

    /**
     * @return arIndexTableField|array
     */
    protected function getFieldsForData()
    {
        if(!$this->fields_for_data && $this->getFields())
        {
            foreach ($this->getFields() as $field)
            {
                if (($field->getVisible() || $field->getName() == $this->ar_id_field_name))
                {
                    $this->fields_for_data[] = $field;
                }
            }
        }
        return $this->fields_for_data;
    }

    protected function filterTableData()
    {
        $filters = $this->getFilterItems();
        if ($filters)
        {
            foreach ($filters as $filter)
            {
                if (!$this->addCustomFilterWhere($filter->getInputType(), $filter->getPostVar(), $filter->getValue()))
                {
                    $this->addFilterWhere($filter->getInputType(), $filter->getPostVar(), $filter->getValue());
                }
            }
        }
    }

    /**
     * @param arIndexTableField $field
     * @param $item
     * @return string
     */
    protected function setArFieldEmptyFieldData(arIndexTableField $field, $item)
    {
        return "";
    }

    protected function setArFieldData(arIndexTableField $field, $item, $value)
    {
        switch ($field->getFieldType())
        {
            case 'integer':
            case 'float':
            case 'text':
            case 'clob':
                return $value;
            case 'date':
            case 'time':
            case 'timestamp':
                return $this->setDateFieldData($field, $item, $value);
                break;
        }
    }

    /**
     * @param arIndexTableField $field
     * @param $item
     * @param $value
     * @return string
     */
    protected function setDateFieldData(arIndexTableField $field, $item, $value)
    {
        $datetime = new ilDateTime($value, IL_CAL_DATETIME);
        return ilDatePresentation::formatDate($datetime, IL_CAL_UNIX);
    }

    /**
     * @param arIndexTableField $field
     * @param $item
     * @return string
     */
    protected function setCustomFieldData(arIndexTableField $field, $item)
    {
        return "CUSTOM-OVERRIDE: setCustomFieldData";
    }

    /**
     * @param $type
     * @param $name
     * @param $value
     * @return bool
     */
    protected function addCustomFilterWhere($type, $name, $value)
    {
        return false;
    }

    /**
     * @param $type
     * @param $name
     * @param $value
     */
    protected function addFilterWhere($type, $name, $value)
    {
        switch ($type)
        {
            case 'integer':
            case 'float':
                $this->active_record_list->where($name . " = '" . $value . "'");
                break;
            case 'text':
            case 'clob':
                $this->active_record_list->where($this->active_record_list->getAR()->getConnectorContainerName() . "." . $name . " like '%" . $value . "%'");
                break;
            case 'date':
            case 'time':
            case 'timestamp':
                break;
        }
    }


    /**
     * @return bool
     * @description returns false, if no filter is needed, otherwise implement filters
     *
     */
    protected function initTableFilter()
    {
        $this->setFilterCommand("applyFilter");
        $this->setResetCommand("resetFilter");

        $fields = $this->getFields();


        foreach ($fields as $field)
        {
            if ($field->getHasFilter())
            {
                if (!$this->addCustomFilterField($field))
                {
                    $this->addFilterField($field);
                }
            }
        }
    }

    /**
     * @param arIndexTableField $field
     * @return bool
     */
    protected function addCustomFilterField(arIndexTableField $field)
    {
        return false;
    }

    /**
     * @param arIndexTableField $field
     */
    protected function addFilterField(arIndexTableField $field)
    {
        switch ($field->getFieldType())
        {
            case 'integer':
            case 'float':
                new $this->addFilterItemToForm(ilNumberInputGUI($this->txt($field->getName()), $field->getName()));
                break;
            case 'text':
            case 'clob':
                include_once("./Services/Form/classes/class.ilTextInputGUI.php");
                $this->addFilterItemToForm(new ilTextInputGUI($this->txt($field->getName()), $field->getName()));
            case 'date':
            case 'time':
            case 'timestamp':
                break;
        }
    }

    public function applyFilter()
    {
        $this->writeFilterToSession();
        $this->resetOffset();
        $this->initTableData();
    }

    public function resetFilter()
    {
        parent::resetFilter();
        $this->resetOffset();
        $this->initTableData();
    }

    protected function initTableProperties()
    {
        return false;
    }


    protected function initFormActionsAndCmdButtons()
    {
        return false;
    }

    /**
     * @param $a_set
     * @return bool|void
     */
    protected function fillTableRow($a_set)
    {
        $this->setCtrlParametersForRow($a_set);
        $this->parseRow($a_set);
        $this->addActionsToRow($a_set);
    }


    /**
     * @param $a_set
     */
    protected function setCtrlParametersForRow($a_set)
    {
        $this->ctrl->setParameterByClass(get_class($this->parent_obj), 'ar_id', ($a_set[$this->ar_id_field_name]));
    }


    /**
     * @param $a_set
     */
    protected function parseRow($a_set)
    {
        $this->tpl->setVariable('ID', $a_set[$this->ar_id_field_name]);

        foreach ($a_set as $key => $value)
        {
            $field = $this->getField($key);
            if ($field->getVisible())
            {
                $this->parseEntry($field, $value);
            }
        }
    }

    /**
     * @param $field
     * @param $value
     */
    protected function parseEntry($field, $value)
    {
        $this->tpl->setCurrentBlock('entry');
        $this->tpl->setVariable('ENTRY_CONTENT', $value);
        $this->tpl->parseCurrentBlock('entry');
    }

    /**
     * @param $id
     * @param $title
     * @param $target_class
     * @param $target_cmd
     * @param null $access
     */
    public function addAction($id, $title, $target_class, $target_cmd, $access = null)
    {
        $this->actions[$id]               = new stdClass();
        $this->actions[$id]->id           = $id;
        $this->actions[$id]->title        = $title;
        $this->actions[$id]->target_class = $target_class;
        $this->actions[$id]->target_cmd   = $target_cmd;
        $this->actions[$id]->access       = $access;
    }


    /**
     * @param $a_set
     */
    protected function addActionsToRow($a_set)
    {
        if (!empty($this->actions))
        {
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($a_set[$this->ar_id_field_name]);
            $alist->setListTitle($this->txt('actions', false));

            foreach ($this->actions as $action)
            {
                $access = true;
                if ($action->access)
                {
                    $access = $this->access->checkAccess($action->access, '', $_GET['ref_id']);
                }
                if ($access)
                {
                    $alist->addItem($action->title, $action->id, $this->ctrl->getLinkTargetByClass($action->target_class, $action->target_cmd));
                }

            }

            $this->tpl->setVariable('ACTION', $alist->getHTML());
        }
    }

    /**
     * @return bool|void
     */
    protected function initTableColumns()
    {
        if ($this->getData())
        {
            foreach (array_pop($this->getData()) as $key => $item)
            {
                if ($this->getField($key)->getVisible())
                {
                    if ($this->getField($key)->getSortable())
                    {
                        $this->addColumn($this->txt($key), $key);
                    } else
                    {
                        $this->addColumn($this->txt($key));
                    }
                }
            }
            $this->addColumn($this->txt('actions', false));
        }
    }

    /**
     * @return bool
     * @description returns false if standard-table-header is needes, otherwise implement your header
     */
    protected function initTableHeader()
    {
        return false;
    }


    /**
     * @return bool
     * @description returns false, if dynamic template is needed, otherwise implement your own template by $this->setRowTemplate($a_template, $a_template_dir = '')
     */
    protected function initTableRowTemplate()
    {
        $this->setRowTemplate('tpl.record_row.html', './Customizing/global/plugins/Libraries/ActiveRecord/');
    }


    /**
     * @return bool
     * @description returns false, if global language is needed; implement your own language by setting $this->pl
     */
    protected function initLanguage()
    {
        return false;
    }


    /**
     * @return string
     */
    public function render()
    {

        $index_table_tpl = new ilTemplate("tpl.index_table.html", true, true, "./Customizing/global/plugins/Libraries/ActiveRecord/");
        if ($this->getToolbar())
        {
            $index_table_tpl->setVariable("TOOLBAR", $this->getToolbar()->getHTML());
        }

        $index_table_tpl->setVariable("TABLE", parent::render());

        return $index_table_tpl->get();
    }


    /**
     * @param $txt
     * @param bool $plugin_txt
     * @return string
     */
    protected function txt($txt, $plugin_txt = true)
    {
        return $this->parent_gui->txt($txt, $plugin_txt);
    }
}

?>