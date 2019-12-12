<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/SystemCheck/classes/class.ilSCComponentTaskGUI.php';

/**
 * Handles tree tasks
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_isCalledBy ilSCTreeTasksGUI: ilObjSystemCheckGUI
 *
 */
class ilSCTreeTasksGUI extends ilSCComponentTaskGUI
{
    const TYPE_DUPLICATES = 'duplicates';
    const TYPE_DUMP = 'dump';
    const TYPE_MISSING = 'missing_reference';
    const TYPE_MISSING_TREE = 'missing_tree';
    const TYPE_STRUCTURE = 'structure';
    
    
    public function getGroupTitle()
    {
        return $this->getLang()->txt('sysc_grp_tree');
    }
    
    public function getGroupDescription()
    {
        return $this->getLang()->txt('sysc_grp_tree_desc');
    }
    
    /**
     * Get title of task
     */
    public function getTitle()
    {
        switch ($this->getTask()->getIdentifier()) {
            case self::TYPE_DUMP:
                return $this->getLang()->txt('sysc_task_tree_dump');
                
            case self::TYPE_DUPLICATES:
                return $this->getLang()->txt('sysc_task_tree_duplicates');
                
            case self::TYPE_MISSING:
                return $this->getLang()->txt('sysc_task_tree_missing_reference');
                
            case self::TYPE_MISSING_TREE:
                return $this->getLang()->txt('sysc_task_tree_missing_tree');

            case self::TYPE_STRUCTURE:
                return $this->getLang()->txt('sysc_task_structure');
        }
    }
    
    /**
     * Get title of task
     */
    public function getDescription()
    {
        switch ($this->getTask()->getIdentifier()) {
            case self::TYPE_DUMP:
                return $this->getLang()->txt('sysc_task_tree_dump_desc');
                
            case self::TYPE_DUPLICATES:
                return $this->getLang()->txt('sysc_task_tree_duplicates_desc');

            case self::TYPE_MISSING:
                return $this->getLang()->txt('sysc_task_tree_missing_reference_desc');

            case self::TYPE_MISSING_TREE:
                return $this->getLang()->txt('sysc_task_tree_missing_tree_desc');

            case self::TYPE_STRUCTURE:
                return $this->getLang()->txt('sysc_task_structure_desc');
        }
    }

    /**
     * get actions for table gui
     */
    public function getActions()
    {
        $repair = false;
        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            $repair = true;
        }
        
        $actions = array();
        switch ($this->getTask()->getIdentifier()) {
            case self::TYPE_DUPLICATES:
                
                $actions[] = array(
                    'txt' => $this->getLang()->txt('sysc_action_validate'),
                    'command' => 'validateDuplicates'
                );
                
                if ($repair) {
                    $actions[] = array(
                        'txt' => $this->getLang()->txt('sysc_action_repair'),
                        'command' => 'repairDuplicates'
                    );
                }
                break;
                
            case self::TYPE_DUMP:
                
                include_once './Services/Repository/classes/class.ilValidator.php';
                $validator = new ilValidator();
                if ($validator->hasScanLog()) {
                    $actions[] = array(
                        'txt' => $this->getLang()->txt('sysc_action_show_tree'),
                        'command' => 'showTree'
                    );
                }
                
                $actions[] = array(
                    'txt' => $this->getLang()->txt('sysc_action_list_tree'),
                    'command' => 'listTree'
                );
                break;
                
            case self::TYPE_MISSING:

                $actions[] = array(
                    'txt' => $this->getLang()->txt('sysc_action_validate'),
                    'command' => 'findMissing'
                );
                
                if ($repair) {
                    $actions[] = array(
                        'txt' => $this->getLang()->txt('sysc_action_repair'),
                        'command' => 'confirmRepairMissing'
                    );
                }
                break;
                
            case self::TYPE_MISSING_TREE:

                $actions[] = array(
                    'txt' => $this->getLang()->txt('sysc_action_validate'),
                    'command' => 'findMissingTreeEntries'
                );
                
                if ($repair) {
                    $actions[] = array(
                        'txt' => $this->getLang()->txt('sysc_action_repair'),
                        'command' => 'confirmRepairMissingTreeEntries'
                    );
                }
                break;
                
            case self::TYPE_STRUCTURE:

                $actions[] = array(
                    'txt' => $this->getLang()->txt('sysc_action_validate'),
                    'command' => 'analyzeStructure'
                );
                
                if ($repair) {
                    $actions[] = array(
                        'txt' => $this->getLang()->txt('sysc_action_repair'),
                        'command' => 'confirmRepairStructure'
                    );
                }
                break;
                
        }
        return $actions;
    }
    
    /**
     * Analyze tree structure
     */
    public function analyzeStructure()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->validateStructure();
        
        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            ilUtil::sendFailure($this->getLang()->txt('sysc_tree_structure_failures') . ' ' . $num_failures, true);
        } else {
            ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }
    
    
    /**
     * Show repair missing confirmation
     * @return type
     */
    protected function confirmRepairStructure()
    {
        return $this->showSimpleConfirmation(
            $this->getLang()->txt('sysc_message_tree_structure_confirm'),
            $this->getLang()->txt('sysc_btn_tree_structure'),
            'repairStructure'
        );
    }
    
    /**
     * Repair structure
     */
    protected function repairStructure()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        
        
        if ($GLOBALS['DIC']['tree']->getTreeImplementation() instanceof ilMaterializedPathTree) {
            ilMaterializedPathTree::createFromParentReleation();
        } elseif ($GLOBALS['DIC']['tree']->getTreeImplementation() instanceof ilNestedSetTree) {
            $GLOBALS['DIC']['tree']->renumber(ROOT_FOLDER_ID);
        }
        
        $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        
        ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        $this->getCtrl()->returnToParent($this);
    }
    
    
    
    

    /**
     * List tree
     */
    public function listTree()
    {
        include_once './Services/Repository/classes/class.ilValidator.php';
        $validator = new ilValidator(true);
        $errors_count = $validator->dumpTree();
        
        
        $GLOBALS['DIC']['ilLog']->write(print_r($this->getTask(), true));
        
        if ($errors_count) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
            ilUtil::sendFailure($this->getLang()->txt('sysc_tree_list_failures') . ' ' . $errors_count, true);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
            ilUtil::sendFailure($this->getLang()->txt('sysc_message_success'), true);
        }
        
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        
        $this->getCtrl()->returnToParent($this);
    }

    /**
     * Show already scanned tree
     */
    public function showTree()
    {
        include_once "./Services/Repository/classes/class.ilValidator.php";
        $validator = new ilValidator();
        $scan_log = $validator->readScanLog();

        if (is_array($scan_log)) {
            $scan_log = '<pre>' . implode("", $scan_log) . '</pre>';
            $GLOBALS['DIC']['tpl']->setContent($scan_log);
        }
    }
    
    
    /**
     * start task
     * @param type $a_task_identifier
     */
    public function validateDuplicates()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->validateDuplicates();
        
        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            ilUtil::sendFailure($this->getLang()->txt('sysc_tree_duplicate_failures') . ' ' . $num_failures, true);
        } else {
            ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }
    
    
    
    /**
     * repair
     * @param type $a_task_identifier
     */
    protected function repairDuplicates()
    {
        // repair duplicates
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $deepest_duplicate = ilSCTreeTasks::findDeepestDuplicate();
        
        include_once './Services/Tree/classes/class.ilSCTreeDuplicatesTableGUI.php';
        $table = new ilSCTreeDuplicatesTableGUI($this, 'repairTask');
        $table->init();
        $table->parse($deepest_duplicate);
        
        $GLOBALS['DIC']['tpl']->setContent($table->getHTML());
    }
    
    protected function deleteDuplicatesFromRepository()
    {
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Removing from repository: ' . $_REQUEST['duplicate_id']);
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        ilSCTreeTasks::deleteDuplicateFromTree((int) $_REQUEST['duplicate_id'], false);

        $tasks = new ilSCTreeTasks($this->getTask());
        if ($tasks->checkDuplicates()) {
            ilSCTreeTasks::repairPK();
        }
        
        ilUtil::sendSuccess($this->getLang()->txt('sysc_deleted_duplicate'), true);
        $this->getCtrl()->returnToParent($this);
    }
    
    protected function deleteDuplicatesFromTrash()
    {
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Removing from repository: ' . $_REQUEST['duplicate_id']);
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        ilSCTreeTasks::deleteDuplicateFromTree((int) $_REQUEST['duplicate_id'], true);
        

        $tasks = new ilSCTreeTasks($this->getTask());
        if ($tasks->checkDuplicates()) {
            ilSCTreeTasks::repairPK();
        }

        ilUtil::sendSuccess($this->getLang()->txt('sysc_deleted_duplicate'), true);
        $this->getCtrl()->returnToParent($this);
    }

    /**
     * find missing objects
     */
    protected function findMissing()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->findMissing();
        
        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            ilUtil::sendFailure($this->getLang()->txt('sysc_tree_missing_failures') . ' ' . $num_failures, true);
        } else {
            ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }
    
    
    
    /**
     * Show repair missing confirmation
     * @return type
     */
    protected function confirmRepairMissing()
    {
        return $this->showSimpleConfirmation(
            $this->getLang()->txt('sysc_message_tree_missing_confirm'),
            $this->getLang()->txt('sysc_btn_tree_missing'),
            'repairMissing'
        );
    }

    /**
     * Repair missing objects
     */
    protected function repairMissing()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        $tasks->repairMissing();
        
        
        $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        
        ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        $this->getCtrl()->returnToParent($this);
    }
    
    /**
     * find missing objects
     */
    protected function findMissingTreeEntries()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->findMissingTreeEntries();
        
        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            ilUtil::sendFailure($this->getLang()->txt('sysc_tree_missing_tree_failures') . ' ' . $num_failures, true);
        } else {
            ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }
    
    /**
     * Show repair missing confirmation
     * @return type
     */
    protected function confirmRepairMissingTreeEntries()
    {
        return $this->showSimpleConfirmation(
            $this->getLang()->txt('sysc_message_tree_missing_confirm'),
            $this->getLang()->txt('sysc_btn_tree_missing'),
            'repairMissingTreeEntries'
        );
    }
    
    /**
     * Repair missing objects
     */
    protected function repairMissingTreeEntries()
    {
        include_once './Services/Tree/classes/class.ilSCTreeTasks.php';
        $tasks = new ilSCTreeTasks($this->getTask());
        $tasks->repairMissingTreeEntries();
        
        $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        
        ilUtil::sendSuccess($this->getLang()->txt('sysc_message_success'), true);
        $this->getCtrl()->returnToParent($this);
    }
}
