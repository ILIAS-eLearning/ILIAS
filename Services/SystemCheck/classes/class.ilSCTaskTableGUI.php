<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table GUI for system check task overview
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTaskTableGUI extends ilTable2GUI
{
    private $group_id = 0;
    private $component_task_handler = null;
    
    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     */
    public function __construct($a_group_id, $a_parent_obj, $a_parent_cmd = "")
    {
        $this->group_id = $a_group_id;
        $this->setId('sc_groups');
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }
    
    /**
     * Get group id
     * @return type
     */
    public function getGroupId()
    {
        return $this->group_id;
    }
    
    
    /**
     * init table
     */
    public function init()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule('sysc');
        $this->addColumn($this->lng->txt('title'), 'title', '60%');
        $this->addColumn($this->lng->txt('last_update'), 'last_update_sort', '20%');
        $this->addColumn($this->lng->txt('status'), 'status', '10%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        $this->setTitle($this->lng->txt('sysc_task_overview'));

        $this->setRowTemplate('tpl.syscheck_tasks_row.html', 'Services/SystemCheck');
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
    }

    /**
     * Fill row
     * @param type $a_set
     */
    public function fillRow($row)
    {
        $this->tpl->setVariable('VAL_TITLE', $row['title']);
        $this->tpl->setVariable('VAL_DESC', $row['description']);

        include_once './Services/SystemCheck/classes/class.ilSCUtils.php';
        $text = ilSCUtils::taskStatus2Text($row['status']);
        switch ($row['status']) {
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

        $this->tpl->setVariable('VAL_LAST_UPDATE', $row['last_update']);
        
        // Actions
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('sysc_' . $row['id']);
        $list->setListTitle($this->lng->txt('actions'));
        
        include_once './Services/SystemCheck/classes/class.ilSCComponentTaskFactory.php';
        $task_handler = ilSCComponentTaskFactory::getComponentTask($row['id']);
        
        $GLOBALS['DIC']['ilCtrl']->setParameterByClass(get_class($task_handler), 'task_id', $row['id']);
        foreach ((array) $task_handler->getActions() as $actions) {
            $list->addItem(
                $actions['txt'],
                '',
                $GLOBALS['DIC']['ilCtrl']->getLinkTargetByClass(
                    get_class($task_handler),
                    $actions['command']
                )
            );
        }
        
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }


    /**
     * Parse system check groups
     */
    public function parse()
    {
        $data = array();
        include_once './Services/SystemCheck/classes/class.ilSCTasks.php';
        foreach (ilSCTasks::getInstanceByGroupId($this->getGroupId())->getTasks() as $task) {
            include_once './Services/SystemCheck/classes/class.ilSCComponentTaskFactory.php';
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
