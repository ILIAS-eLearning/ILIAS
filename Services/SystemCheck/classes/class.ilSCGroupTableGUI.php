<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/SystemCheck/classes/class.ilSCTask.php';

/**
 * Table GUI for system check groups overview
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCGroupTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     * @param type $a_parent_obj
     * @param type $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        $this->setId('sc_groups');
        parent::__construct($a_parent_obj, $a_parent_cmd);
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
        $this->addColumn($this->lng->txt('sysc_completed_num'), 'completed', '10%');
        $this->addColumn($this->lng->txt('sysc_failed_num'), 'failed', '10%');
        $this->addColumn($this->lng->txt('actions'), '', '10%');

        $this->setTitle($this->lng->txt('sysc_overview'));

        $this->setRowTemplate('tpl.syscheck_groups_row.html', 'Services/SystemCheck');
        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
    }

    /**
     * Fill row
     * @param type $a_set
     */
    public function fillRow($row)
    {
        $this->tpl->setVariable('VAL_TITLE', $row['title']);

        $GLOBALS['DIC']['ilCtrl']->setParameter($this->getParentObject(), 'grp_id', $row['id']);
        $this->tpl->setVariable(
            'VAL_LINK',
            $GLOBALS['DIC']['ilCtrl']->getLinkTarget($this->getParentObject(), 'showGroup')
        );
        
        $this->tpl->setVariable('VAL_DESC', $row['description']);
        $this->tpl->setVariable('VAL_LAST_UPDATE', $row['last_update']);
        $this->tpl->setVariable('VAL_COMPLETED', $row['completed']);
        $this->tpl->setVariable('VAL_FAILED', $row['failed']);
        
        switch ($row['status']) {
            case ilSCTask::STATUS_COMPLETED:
                $this->tpl->setVariable('STATUS_CLASS', 'smallgreen');
                break;
            case ilSCTask::STATUS_FAILED:
                $this->tpl->setVariable('STATUS_CLASS', 'warning');
                break;
                
        }
        
        
        // Actions
        include_once './Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $list = new ilAdvancedSelectionListGUI();
        $list->setSelectionHeaderClass('small');
        $list->setItemLinkClass('small');
        $list->setId('sysc_' . $row['id']);
        $list->setListTitle($this->lng->txt('actions'));
        
        $GLOBALS['DIC']['ilCtrl']->setParameter($this->getParentObject(), 'grp_id', $row['id']);
        $list->addItem(
            $this->lng->txt('show'),
            '',
            $GLOBALS['DIC']['ilCtrl']->getLinkTarget($this->getParentObject(), 'showGroup')
        );
        $this->tpl->setVariable('ACTIONS', $list->getHTML());
    }


    /**
     * Parse system check groups
     */
    public function parse()
    {
        $data = array();
        include_once './Services/SystemCheck/classes/class.ilSCGroups.php';
        foreach (ilSCGroups::getInstance()->getGroups() as $group) {
            $item = array();
            $item['id'] = $group->getId();
            
            
            include_once './Services/SystemCheck/classes/class.ilSCComponentTaskFactory.php';
            $task_gui = ilSCComponentTaskFactory::getComponentTaskGUIForGroup($group->getId());
            
            
            $item['title'] = $task_gui->getGroupTitle();
            $item['description'] = $task_gui->getGroupDescription();
            $item['status'] = $group->getStatus();
            
            include_once './Services/SystemCheck/classes/class.ilSCTasks.php';
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
