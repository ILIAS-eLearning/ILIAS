<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @noinspection PhpIncludeInspection */
require_once './Services/Table/classes/class.ilTable2GUI.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/Form/classes/class.ilTextInputGUI.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/Form/classes/class.ilCheckboxInputGUI.php';
/** @noinspection PhpIncludeInspection */
require_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

/**
 * Class ilWorkflowEngineDefinitionsTableGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 *
 * @version $Id$
 *
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineDefinitionsTableGUI extends ilTable2GUI
{
    /** @var ilCtrl $ilCtrl */
    protected $ilCtrl;

    /** @var ilLanguage $lng */
    protected $lng;

    /**
     * ilWorkflowEngineDefinitionsTableGUI constructor.
     *
     * @param        $parent_obj
     * @param string $parent_cmd
     * @param string $template_context
     */
    public function __construct($parent_obj, $parent_cmd, $template_context ="")
    {
        $this->setId('wfedef');
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        global $DIC;
        $this->ilCtrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];

        $this->initColumns();
        $this->setEnableHeader(true);

        $this->setFormAction($this->ilCtrl->getFormAction($parent_obj));

        $this->initFilter();

        $this->setRowTemplate("tpl.wfe_def_row.html", "Services/WorkflowEngine");
        $this->populateTable();

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");
        $this->setTitle($this->lng->txt("definitions"));
    }

    /**
     * @return void
     */
    public function initFilter()
    {
        $title_filter_input = new ilTextInputGUI($this->lng->txt("title"), "title");
        $title_filter_input->setMaxLength(64);
        $title_filter_input->setSize(20);
        $this->addFilterItem($title_filter_input);
        $title_filter_input->readFromSession();
        $this->filter["title"] = $title_filter_input->getValue();

        $instances_filter_input = new ilCheckboxInputGUI($this->lng->txt('instances'), 'instances');
        $this->addFilterItem($instances_filter_input);
        $instances_filter_input->readFromSession();
        $this->filter['instances'] = $instances_filter_input->getChecked();
    }

    /**
     * @return void
     */
    public function initColumns()
    {
        $this->addColumn($this->lng->txt("title"), "title", "20%");

        $selected_columns = $this->getSelectedColumns();

        if (in_array('file', $selected_columns)) {
            $this->addColumn($this->lng->txt("file"), "file", "30%");
        }

        if (in_array('version', $selected_columns)) {
            $this->addColumn($this->lng->txt("version"), "version", "10%");
        }

        if (in_array('status', $selected_columns)) {
            $this->addColumn($this->lng->txt("status"), "status", "10%");
        }

        if (in_array('instances', $selected_columns)) {
            $this->addColumn($this->lng->txt("instances"), "instances", "15%");
        }

        $this->addColumn($this->lng->txt("actions"), "", "10%");
    }

    /**
     * @return array
     */
    public function getSelectableColumns()
    {
        $cols["file"] = array(
            "txt" => $this->lng->txt("file"),
            "default" => true);
        $cols["version"] = array(
            "txt" => $this->lng->txt("version"),
            "default" => true);
        $cols["status"] = array(
            "txt" => $this->lng->txt("status"),
            "default" => true);
        $cols["instances"] = array(
            "txt" => $this->lng->txt("instances"),
            "default" => true);
        return $cols;
    }

    /**
     * @return void
     */
    private function populateTable()
    {
        global $DIC;

        require_once 'Services/WorkflowEngine/classes/administration/class.ilWorkflowDefinitionRepository.php';
        $repository = new ilWorkflowDefinitionRepository(
            $DIC['ilDB'],
            $DIC['filesystem'],
            ilObjWorkflowEngine::getRepositoryDir(true)
        );

        $baseList = $repository->getAll();

        $that = $this;

        array_walk($baseList, function (array &$definition) use ($that) {
            $status = $that->lng->txt('missing_parsed_class');
            if ($definition['status']) {
                $status = 'OK';
            }

            $definition['status'] = $status;
        });

        $filteredBaseList = array_filter($baseList, function ($item) use ($that) {
            return !$this->isFiltered($item);
        });

        $this->setData($filteredBaseList);
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    public function isFiltered($row)
    {
        // Title filter
        $title_filter = $this->getFilterItemByPostVar('title');
        if ($title_filter->getValue() != null) {
            if (strpos(strtolower($row['title']), strtolower($title_filter->getValue())) === false) {
                return true;
            }
        }

        // Instances filter
        $instances_filter = $this->getFilterItemByPostVar('instances');
        if ($instances_filter->getChecked() && $row['instances']['active'] == 0) {
            return true;
        }

        return false;
    }

    /**
     * @param array $set
     */
    protected function fillRow($set)
    {
        $this->tpl->setVariable('VAL_TITLE', $set['title']);

        $selected_columns = $this->getSelectedColumns();

        if (in_array('file', $selected_columns)) {
            $this->tpl->setVariable('VAL_FILE', $set['file']);
        }

        if (in_array('version', $selected_columns)) {
            $this->tpl->setVariable('VAL_VERSION', $set['version']);
        }

        if (in_array('status', $selected_columns)) {
            if ($set['status'] != 'OK') {
                $this->tpl->setVariable('VAL_STATUS', $set['status']);
            } else {
                $this->tpl->setVariable('VAL_STATUS', $this->lng->txt('ok'));
            }
        }

        if (in_array('instances', $selected_columns)) {
            $this->tpl->setVariable('TXT_INSTANCES_TOTAL', $this->lng->txt('total'));
            $this->tpl->setVariable('VAL_INSTANCES_TOTAL', 0+$set['instances']['total']);
            $this->tpl->setVariable('TXT_INSTANCES_ACTIVE', $this->lng->txt('active'));
            $this->tpl->setVariable('VAL_INSTANCES_ACTIVE', 0+$set['instances']['active']);
        }

        $action = new ilAdvancedSelectionListGUI();
        $action->setId('asl_' . $set['id']);
        $action->setListTitle($this->lng->txt('actions'));
        $this->ilCtrl->setParameter($this->parent_obj, 'process_id', $set['id']);
        $action->addItem(
            $this->lng->txt('start_process'),
            'start',
            $this->ilCtrl->getLinkTarget($this->parent_obj, 'definitions.start')
        );

        if (0+$set['instances']['active'] == 0) {
            $action->addItem(
                $this->lng->txt('delete_definition'),
                'delete',
                $this->ilCtrl->getLinkTarget($this->parent_obj, 'definitions.confirmdelete')
            );
        }

        require_once ilObjWorkflowEngine::getRepositoryDir() . '/' . $set['id'] . '.php';
        $class = substr($set['id'], 4);
        if ($class::$startEventRequired == true) {
            $action->addItem(
                $this->lng->txt('start_listening'),
                'startlistening',
                $this->ilCtrl->getLinkTarget($this->parent_obj, 'definitions.startlistening')
            );

            $action->addItem(
                $this->lng->txt('stop_listening'),
                'stoplistening',
                $this->ilCtrl->getLinkTarget($this->parent_obj, 'definitions.stoplistening')
            );
        }

        $this->tpl->setVariable('HTML_ASL', $action->getHTML());
    }
}
