<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Handles tree tasks
 * @author            Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_isCalledBy ilSCTreeTasksGUI: ilObjSystemCheckGUI
 */
class ilSCTreeTasksGUI extends ilSCComponentTaskGUI
{
    protected const TYPE_DUPLICATES = 'duplicates';
    public const TYPE_DUMP = 'dump';
    protected const TYPE_MISSING = 'missing_reference';
    protected const TYPE_MISSING_TREE = 'missing_tree';
    protected const TYPE_STRUCTURE = 'structure';

    protected ilTree $tree;
    protected GlobalHttpState $http;
    protected Factory $refinery;

    public function __construct(ilSCTask $task = null)
    {
        global $DIC;
        parent::__construct($task);

        $this->tree = $DIC->repositoryTree();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    protected function getDuplicateIdFromRequest() : int
    {
        if ($this->http->wrapper()->query()->has('duplicate_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'duplicate_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    public function getGroupTitle() : string
    {
        return $this->getLang()->txt('sysc_grp_tree');
    }

    public function getGroupDescription() : string
    {
        return $this->getLang()->txt('sysc_grp_tree_desc');
    }

    public function getTitle() : string
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
        return '';
    }

    public function getDescription() : string
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
        return '';
    }

    public function getActions() : array
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

    public function analyzeStructure() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->validateStructure();

        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            $this->tpl->setOnScreenMessage('failure', $this->getLang()->txt('sysc_tree_structure_failures') . ' ' . $num_failures, true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }

    protected function confirmRepairStructure() : void
    {
        $this->showSimpleConfirmation(
            $this->getLang()->txt('sysc_message_tree_structure_confirm'),
            $this->getLang()->txt('sysc_btn_tree_structure'),
            'repairStructure'
        );
    }

    protected function repairStructure() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());

        if ($this->tree->getTreeImplementation() instanceof ilMaterializedPathTree) {
            ilMaterializedPathTree::createFromParentReleation();
        } elseif ($this->tree->getTreeImplementation() instanceof ilNestedSetTree) {
            $this->tree->renumber(ROOT_FOLDER_ID);
        }

        $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();

        $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        $this->getCtrl()->returnToParent($this);
    }

    public function listTree() : void
    {
        $validator = new ilValidator(true);
        $errors_count = $validator->dumpTree();

        if ($errors_count) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
            $this->tpl->setOnScreenMessage('failure', $this->getLang()->txt('sysc_tree_list_failures') . ' ' . $errors_count, true);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
            $this->tpl->setOnScreenMessage('failure', $this->getLang()->txt('sysc_message_success'), true);
        }

        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();

        $this->getCtrl()->returnToParent($this);
    }

    public function showTree() : void
    {
        $validator = new ilValidator();
        $scan_log = $validator->readScanLog();

        if (is_array($scan_log)) {
            $scan_log = '<pre>' . implode("", $scan_log) . '</pre>';
            $this->tpl->setContent($scan_log);
        }
    }

    public function validateDuplicates() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->validateDuplicates();

        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            $this->tpl->setOnScreenMessage('failure', $this->getLang()->txt('sysc_tree_duplicate_failures') . ' ' . $num_failures, true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }

    protected function repairDuplicates() : void
    {
        $deepest_duplicate = ilSCTreeTasks::findDeepestDuplicate();

        $table = new ilSCTreeDuplicatesTableGUI($this, 'repairTask');
        $table->init();
        $table->parse($deepest_duplicate);

        $this->tpl->setContent($table->getHTML());
    }

    protected function deleteDuplicatesFromRepository() : void
    {
        ilSCTreeTasks::deleteDuplicateFromTree($this->getDuplicateIdFromRequest(), false);

        $tasks = new ilSCTreeTasks($this->getTask());
        if ($tasks->checkDuplicates()) {
            ilSCTreeTasks::repairPK();
        }

        $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_deleted_duplicate'), true);
        $this->getCtrl()->returnToParent($this);
    }

    protected function deleteDuplicatesFromTrash() : void
    {
        ilSCTreeTasks::deleteDuplicateFromTree($this->getDuplicateIdFromRequest(), true);

        $tasks = new ilSCTreeTasks($this->getTask());
        if ($tasks->checkDuplicates()) {
            ilSCTreeTasks::repairPK();
        }

        $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_deleted_duplicate'), true);
        $this->getCtrl()->returnToParent($this);
    }

    protected function findMissing() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->findMissing();

        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            $this->tpl->setOnScreenMessage('failure', $this->getLang()->txt('sysc_tree_missing_failures') . ' ' . $num_failures, true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }

    protected function confirmRepairMissing() : void
    {
        $this->showSimpleConfirmation(
            $this->getLang()->txt('sysc_message_tree_missing_confirm'),
            $this->getLang()->txt('sysc_btn_tree_missing'),
            'repairMissing'
        );
    }

    protected function repairMissing() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());
        $tasks->repairMissing();

        $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();

        $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        $this->getCtrl()->returnToParent($this);
    }

    protected function findMissingTreeEntries() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());
        $num_failures = $tasks->findMissingTreeEntries();

        if ($this->getTask()->getStatus() == ilSCTask::STATUS_FAILED) {
            // error message
            $this->tpl->setOnScreenMessage('failure', $this->getLang()->txt('sysc_tree_missing_tree_failures') . ' ' . $num_failures, true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        }
        $this->getCtrl()->returnToParent($this);
    }

    protected function confirmRepairMissingTreeEntries() : void
    {
        $this->showSimpleConfirmation(
            $this->getLang()->txt('sysc_message_tree_missing_confirm'),
            $this->getLang()->txt('sysc_btn_tree_missing'),
            'repairMissingTreeEntries'
        );
    }

    protected function repairMissingTreeEntries() : void
    {
        $tasks = new ilSCTreeTasks($this->getTask());
        $tasks->repairMissingTreeEntries();

        $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();

        $this->tpl->setOnScreenMessage('success', $this->getLang()->txt('sysc_message_success'), true);
        $this->getCtrl()->returnToParent($this);
    }
}
