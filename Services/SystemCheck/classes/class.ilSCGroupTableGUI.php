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
 * Table GUI for system check groups overview
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroupTableGUI extends ilTable2GUI
{
    public function __construct(object $a_parent_obj, string $a_parent_cmd = '')
    {
        $this->setId('sc_groups');
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    public function init(): void
    {
        $this->lng->loadLanguageModule('sysc');
        $this->addColumn($this->lng->txt('title'), 'title', '60%');
        $this->addColumn($this->lng->txt('last_update'), 'last_update_sort', '20%');
        $this->addColumn($this->lng->txt('sysc_completed_num'), 'completed', '10%');
        $this->addColumn($this->lng->txt('sysc_failed_num'), 'failed', '10%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        $this->setTitle($this->lng->txt('sysc_overview'));

        $this->setRowTemplate('tpl.syscheck_groups_row.html', 'Services/SystemCheck');
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_TITLE', (string) ($a_set['title'] ?? ''));

        $id = (int) ($a_set['id'] ?? 0);
        $this->ctrl->setParameter($this->getParentObject(), 'grp_id', $id);
        $this->tpl->setVariable(
            'VAL_LINK',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'showGroup')
        );

        $this->tpl->setVariable('VAL_DESC', (string) ($a_set['description'] ?? ''));
        $this->tpl->setVariable('VAL_LAST_UPDATE', (string) ($a_set['last_update'] ?? ''));
        $this->tpl->setVariable('VAL_COMPLETED', (int) ($a_set['completed'] ?? 0));
        $this->tpl->setVariable('VAL_FAILED', (int) ($a_set['failed'] ?? 0));

        switch ($a_set['status']) {
            case ilSCTask::STATUS_COMPLETED:
                $this->tpl->setVariable('STATUS_CLASS', 'smallgreen');
                break;
            case ilSCTask::STATUS_FAILED:
                $this->tpl->setVariable('STATUS_CLASS', 'warning');
                break;

        }

        // Actions

        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('sysc_' . $id);
        $list->setListTitle($this->lng->txt('actions'));

        $this->ctrl->setParameter($this->getParentObject(), 'grp_id', $id);
        $list->addItem(
            $this->lng->txt('show'),
            '',
            $this->ctrl->getLinkTarget($this->getParentObject(), 'showGroup')
        );
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }

    public function parse(): void
    {
        $data = array();

        foreach (ilSCGroups::getInstance()->getGroups() as $group) {
            $item = array();
            $item['id'] = $group->getId();

            $task_gui = ilSCComponentTaskFactory::getComponentTaskGUIForGroup($group->getId());

            $item['title'] = $task_gui->getGroupTitle();
            $item['description'] = $task_gui->getGroupDescription();
            $item['status'] = $group->getStatus();

            $item['completed'] = ilSCTasks::lookupCompleted($group->getId());
            $item['failed'] = ilSCTasks::lookupFailed($group->getId());

            $last_update = ilSCTasks::lookupLastUpdate($group->getId());
            $item['last_update'] = ilDatePresentation::formatDate($last_update);
            $item['last_update_sort'] = $last_update->get(IL_CAL_UNIX);
            $data[] = $item;
        }

        $this->setData($data);
    }
}
