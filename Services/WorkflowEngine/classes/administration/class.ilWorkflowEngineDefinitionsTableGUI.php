<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilWorkflowEngineDefinitionsTableGUI
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @ingroup Services/WorkflowEngine
 */
class ilWorkflowEngineDefinitionsTableGUI extends ilTable2GUI
{
    protected ilCtrl $ilCtrl;
    protected array $filter;
    protected ilRbacSystem $rbac;
    protected ILIAS\WorkflowEngine\Service $service;

    public function __construct(
        ilObjWorkflowEngineGUI $parent_obj,
        string $parent_cmd,
        string $template_context = ""
    ) {
        $this->setId('wfedef');
        parent::__construct($parent_obj, $parent_cmd, $template_context);

        global $DIC;
        $this->ilCtrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->rbac = $DIC->rbac()->system();
        $this->service = $DIC->workflowEngine();

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

    public function initFilter(): void
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

    public function initColumns(): void
    {
        $this->addColumn($this->lng->txt("title"), "title", "20%");

        $selected_columns = $this->getSelectedColumns();

        if (in_array('file', $selected_columns, true)) {
            $this->addColumn($this->lng->txt("file"), "file", "30%");
        }

        if (in_array('version', $selected_columns, true)) {
            $this->addColumn($this->lng->txt("version"), "version", "10%");
        }

        if (in_array('status', $selected_columns, true)) {
            $this->addColumn($this->lng->txt("status"), "status", "10%");
        }

        if (in_array('instances', $selected_columns, true)) {
            $this->addColumn($this->lng->txt("instances"), "instances", "15%");
        }
        if ($this->rbac->checkAccess(
            'edit',
            $this->service->internal()->request()->getRefId()
        )) {
            $this->addColumn($this->lng->txt("actions"), "", "10%");
        }
    }

    /**
     * @return array
     */
    public function getSelectableColumns(): array
    {
        $cols["file"] = [
            "txt" => $this->lng->txt("file"),
            "default" => true
        ];
        $cols["version"] = [
            "txt" => $this->lng->txt("version"),
            "default" => true
        ];
        $cols["status"] = [
            "txt" => $this->lng->txt("status"),
            "default" => true
        ];
        $cols["instances"] = [
            "txt" => $this->lng->txt("instances"),
            "default" => true
        ];
        return $cols;
    }

    private function populateTable(): void
    {
        global $DIC;

        $repository = new ilWorkflowDefinitionRepository(
            $DIC['ilDB'],
            $DIC['filesystem'],
            ilObjWorkflowEngine::getRepositoryDir(true)
        );

        $baseList = $repository->getAll();

        $that = $this;

        array_walk($baseList, static function (array &$definition) use ($that): void {
            $status = $that->lng->txt('missing_parsed_class');
            if ($definition['status']) {
                $status = 'OK';
            }

            $definition['status'] = $status;
        });

        $filteredBaseList = array_filter($baseList, function ($item): bool {
            return !$this->isFiltered($item);
        });

        $this->setData($filteredBaseList);
    }

    /**
     * @param array $row
     * @return bool
     */
    public function isFiltered(array $row): bool
    {
        $title_filter = $this->getFilterItemByPostVar('title');
        if ($title_filter->getValue() != null && stripos($row['title'], $title_filter->getValue()) === false) {
            return true;
        }

        $instances_filter = $this->getFilterItemByPostVar('instances');
        if ($row['instances']['active'] == 0 && $instances_filter->getChecked()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $a_set
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);

        $selected_columns = $this->getSelectedColumns();

        if (in_array('file', $selected_columns, true)) {
            $this->tpl->setVariable('VAL_FILE', $a_set['file']);
        }

        if (in_array('version', $selected_columns, true)) {
            $this->tpl->setVariable('VAL_VERSION', $a_set['version']);
        }

        if (in_array('status', $selected_columns, true)) {
            if ($a_set['status'] !== 'OK') {
                $this->tpl->setVariable('VAL_STATUS', $a_set['status']);
            } else {
                $this->tpl->setVariable('VAL_STATUS', $this->lng->txt('ok'));
            }
        }

        if (in_array('instances', $selected_columns, true)) {
            $this->tpl->setVariable('TXT_INSTANCES_TOTAL', $this->lng->txt('total'));
            $this->tpl->setVariable('VAL_INSTANCES_TOTAL', 0 + $a_set['instances']['total']);
            $this->tpl->setVariable('TXT_INSTANCES_ACTIVE', $this->lng->txt('active'));
            $this->tpl->setVariable('VAL_INSTANCES_ACTIVE', 0 + $a_set['instances']['active']);
        }

        if ($this->rbac->checkAccess(
            'edit',
            $this->service->internal()->request()->getRefId()
        )) {
            $action = new ilAdvancedSelectionListGUI();
            $action->setId('asl_' . $a_set['id']);
            $action->setListTitle($this->lng->txt('actions'));
            $this->ilCtrl->setParameter($this->parent_obj, 'process_id', $a_set['id']);
            $action->addItem(
                $this->lng->txt('start_process'),
                'start',
                $this->ilCtrl->getLinkTarget($this->parent_obj, 'definitions.start')
            );

            if (0 + $a_set['instances']['active'] === 0) {
                $action->addItem(
                    $this->lng->txt('delete_definition'),
                    'delete',
                    $this->ilCtrl->getLinkTarget($this->parent_obj, 'definitions.confirmdelete')
                );
            }

            require_once ilObjWorkflowEngine::getRepositoryDir() . '/' . $a_set['id'] . '.php';
            /** @var class-string $class */
            $class = substr($a_set['id'], 4);
            /** @noinspection PhpUndefinedFieldInspection (defined in ilWorkflowScaffold.php / generated code */
            if ($class::$startEventRequired) {
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
}
