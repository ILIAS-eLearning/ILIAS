<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.srModelObjectTableGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecordList.php');

/**
 * TableGUI ActiveRecordTableGUI
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:
 *
 */
class arIndexTableGUI extends srModelObjectTableGUI
{

    /**
     * @var array
     */
    protected $fields_to_hide = array();

    /**
     * @var ActiveRecordList
     */
    protected $active_record_list = null;

    /**
     * @var string
     */
    protected $lng_prefix = "";

    /**
     * @var array
     */
    protected $actions = array();

    /**
     * @var array $data = null;
     */

    public function __construct($a_parent_obj, $a_parent_cmd, ActiveRecordList $active_record_list, ilPlugin $plugin_object=null)
    {
        if($plugin_object)
        {
            $this->setLngPrefix($plugin_object->getPrefix());
            $plugin_object->loadLanguageModule();
        }
        $this->table_title = $this->txt($this->table_title);
        $this->active_record_list = $active_record_list;
        $this->initFieldsToHide();
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addActions();
    }

    /**
     * @param string $lng_prefix
     */
    public function setLngPrefix($lng_prefix)
    {
        $this->lng_prefix = $lng_prefix;
    }

    /**
     * @return string
     */
    public function getLngPrefix()
    {
        return $this->lng_prefix;
    }


    /**
     * @param array $fields_to_hide
     */
    public function setFieldsToHide($fields_to_hide)
    {
        $this->fields_to_hide = $fields_to_hide;
    }

    /**
     * @return array
     */
    public function getFieldsToHide()
    {
        return $this->fields_to_hide;
    }

    protected function initFieldsToHide()
    {
    }

    protected function initTableData()
    {
        $this->setData($this->active_record_list->getArray());
    }

    /**
     * @return bool
     * @description returns false, if no filter is needed, otherwise implement filters
     *
     */
    protected function initTableFilter()
    {
        return false;
    }


    /**
     * @return bool
     * @description returns false or set the following
     * @description e.g. override table id oder title: $this->table_id = 'myid', $this->table_title = 'My Title'
     */
    protected function initTableProperties()
    {
        return false;
    }


    /**
     * @return bool
     * @description return false or implements own form action and
     */
    //TODO GET ersetzen
    protected function initFormActionsAndCmdButtons()
    {
        return false;
    }


    /**
     * @description implement your fillRow
     *
     * @param $a_set
     *
     * @return bool
     */
    protected function fillTableRow($a_set)
    {
        $this->setCtrlParametersForRow($a_set);
        $this->addFieldsToRow($a_set);
        $this->addActionsToRow($a_set);
    }

    protected function setCtrlParametersForRow($a_set)
    {
    }

    protected function addFieldsToRow($a_set)
    {
        $class = $this->active_record_list->getClass();
        $record_fields = $class::returnDbFields();

        $this->tpl->setVariable('ID', $a_set['id']);

        foreach ($a_set as $key => $item)
        {
            if (!in_array($key, $this->fields_to_hide))
            {
                $field = $record_fields[$key];
                $this->tpl->setCurrentBlock('entry');
                switch ($field->db_type)
                {
                    case 'integer':
                    case 'float':
                    case 'text':
                    case 'clob':
                        $this->tpl->setVariable('ENTRY_CONTENT', $item);
                        break;
                    case 'date':
                    case 'time':
                    case 'timestamp':
                        $this->tpl->setVariable('ENTRY_CONTENT', date("Y-m-d H:i:s",$item));
                        break;

                }

                $this->tpl->parseCurrentBlock();
            }
        }
    }

    public function addAction($id, $title, $target_class, $target_cmd, $access)
    {
        $this->actions[$id] = new stdClass();
        $this->actions[$id]->id = $id;
        $this->actions[$id]->title = $title;
        $this->actions[$id]->target_class = $target_class;
        $this->actions[$id]->target_cmd = $target_cmd;
        $this->actions[$id]->access = $access;
    }

    protected function addActions()
    {

    }

    protected function addActionsToRow($a_set)
    {
        if(!empty($this->actions))
        {
            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($a_set['id']);
            $alist->setListTitle('actions');

            foreach($this->actions as $action)
            {
                if (($this->access->checkAccess($action->access, '', $_GET['ref_id'])))
                {
                    $alist->addItem($action->title, $action->id, $this->ctrl->getLinkTargetByClass($action->target_class,$action->target_cmd));
                }
            }

            $this->tpl->setVariable('ACTION', $alist->getHTML());
        }
    }



    /**
     * @return bool
     * @description returns false, if automatic columns are needed, otherwise implement your columns
     */
    protected function initTableColumns()
    {
        $this->addColumn('', '', '1', true);

        foreach (array_pop($this->getData()) as $key => $item)
        {
            if (!in_array($key, $this->fields_to_hide))
            {
                $this->addColumn($this->txt($key));
            }
        }
        $this->addColumn('actions');
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

    protected function txt($txt)
    {
        global $lng;

        if($this->getLngPrefix()!="")
        {
            return $lng->txt($this->getLngPrefix() . "_" . $txt, $this->getLngPrefix());
        }
        else
        {
            return $lng->txt($txt);
        }

    }
}
?>