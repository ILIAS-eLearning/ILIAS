<?php

declare(strict_types=1);
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
 * Table GUI for system check task overview
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTaskTableGUI extends ilTable2GUI
{
    private int $group_id = 0;

    private ilAccess $access;

    public function __construct(int $a_group_id, object $a_parent_obj, string $a_parent_cmd = '')
    {
        global $DIC;
        $this->group_id = $a_group_id;
        $this->setId('sc_groups');
        $this->access = $DIC->access();

        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function init(): void
    {
        $this->lng->loadLanguageModule('sysc');
        $this->addColumn($this->lng->txt('title'), 'title', '60%');
        $this->addColumn($this->lng->txt('last_update'), 'last_update_sort', '20%');
        $this->addColumn($this->lng->txt('status'), 'status', '10%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        $this->setTitle($this->lng->txt('sysc_task_overview'));

        $this->setRowTemplate('tpl.syscheck_tasks_row.html', 'Services/SystemCheck');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
    }

    /**
     * @param array $a_set
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_TITLE', (string) ($a_set['title'] ?? ''));
        $this->tpl->setVariable('VAL_DESC', (string) ($a_set['description'] ?? ''));

        $status = (int) ($a_set['status'] ?? 0);
        $text = ilSCUtils::taskStatus2Text($status);
        switch ($status) {
            case ilSCTask::STATUS_COMPLETED:
                $this->tpl->setVariable('VAL_STATUS_SUCCESS', $text);
                break;

            case ilSCTask::STATUS_FAILED:
                $this->tpl->setCurrentBlock('warning');
                $this->tpl->setVariable('VAL_STATUS_WARNING', $text);
                $this->tpl->parseCurrentBlock();
                break;

            default:
                $this->tpl->setVariable('VAL_STATUS_STANDARD', $text);
                break;
        }

        $this->tpl->setVariable('VAL_LAST_UPDATE', (string) ($a_set['last_update'] ?? ''));

        // Actions
        if ($this->access->checkAccess('write', '', $this->parent_obj->getObject()->getRefId())) {
            $id = (int) ($a_set['id'] ?? 0);
            $list = new ilAdvancedSelectionListGUI();
            $list->setSelectionHeaderClass('small');
            $list->setItemLinkClass('small');
            $list->setId('sysc_' . $id);
            $list->setListTitle($this->lng->txt('actions'));

            $task_handler = ilSCComponentTaskFactory::getComponentTask($id);

            $this->ctrl->setParameterByClass(get_class($task_handler), 'task_id', $id);
            foreach ($task_handler->getActions() as $actions) {
                $list->addItem(
                    (string) ($actions['txt'] ?? ''),
                    '',
                    $this->ctrl->getLinkTargetByClass(
                        get_class($task_handler),
                        (string) ($actions['command'] ?? '')
                    )
                );
            }

            $this->tpl->setVariable('ACTIONS', $list->getHTML());
        }
    }

    public function parse(): void
    {
        $data = array();

        foreach (ilSCTasks::getInstanceByGroupId($this->getGroupId())->getTasks() as $task) {
            $task_handler = ilSCComponentTaskFactory::getComponentTask($task->getId());

            if (!$task->isActive()) {
                continue;
            }

            $item = array();
            $item['id'] = $task->getId();
            $item['title'] = $task_handler->getTitle();
            $item['description'] = $task_handler->getDescription();
            $item['last_update'] = ilDatePresentation::formatDate($task->getLastUpdate());
            $item['last_update_sort'] = $task->getLastUpdate()->get(IL_CAL_UNIX);
            $item['status'] = $task->getStatus();

            $data[] = $item;
        }

        $this->setData($data);
    }
}
