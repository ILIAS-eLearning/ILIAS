<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Defines a system check task
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilSCTreeTasks
{
    private $db = null;
    private $task = null;
    
    public function __construct(ilSCTask $task)
    {
        $this->db = $GLOBALS['DIC']['ilDB'];
        $this->task = $task;
    }
    
    
    /**
     * find duplicates
     * @global type $ilDB
     * @return int
     */
    public static function findDeepestDuplicate()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT child FROM tree first  ' .
                'WHERE EXISTS ( ' .
                'SELECT child FROM tree second WHERE first.child = second.child ' .
                'GROUP BY child HAVING COUNT(child)  >  1 ) ' .
                'ORDER BY depth DESC';
        
        $GLOBALS['DIC']['ilLog']->write($query);
        
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->child;
        }
        return 0;
    }
    
    public static function repairPK()
    {
        #$GLOBALS['DIC']['ilDB']->dropPrimaryKey('tree');
        $GLOBALS['DIC']['ilDB']->addPrimaryKey('tree', array('child'));
    }
    
    public static function getNodeInfo($a_tree_id, $a_child)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM tree WHERE child = ' . $ilDB->quote($a_child, 'integer') . ' AND tree = ' . $ilDB->quote($a_tree_id, 'integer');
        $res = $ilDB->query($query);

        $node = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $node['child'] = $row->child;
            $node['tree'] = $row->tree;
            $node['depth'] = $row->depth;
            
            // read obj_id
            $query = 'SELECT obj_id FROM object_reference WHERE ref_id = ' . $ilDB->quote($a_child, 'integer');
            $ref_res = $ilDB->query($query);
            while ($ref_row = $ref_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $node['obj_id'] = $ref_row->obj_id;
                
                // read object info
                $query = 'SELECT title, description, type FROM object_data ' .
                        'WHERE obj_id = ' . $ilDB->quote($ref_row->obj_id);
                $obj_res = $ilDB->query($query);
                while ($obj_row = $obj_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    $node['title'] = $obj_row->title;
                    $node['description'] = $obj_row->description;
                    $node['type'] = $obj_row->type;
                }
            }
        }
        return $node;
    }
    
    public static function getChilds($a_tree_id, $a_childs)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM tree WHERE tree = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' . 'AND child = ' . $ilDB->quote($a_childs, 'integer');
        $res = $ilDB->query($query);
        
        $childs = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = $row->child;
        }
        return $childs;
    }
    
    /**
     * find duplicates
     * @global type $ilDB
     * @return type
     */
    public static function findDuplicates($a_duplicate_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM tree first  ' .
                'WHERE EXISTS ( ' .
                'SELECT child FROM tree second WHERE first.child = second.child ' .
                'GROUP BY child HAVING COUNT(child)  >  1 ) ' .
                'AND child = ' . $ilDB->quote($a_duplicate_id, 'integer') . ' ' .
                'ORDER BY depth DESC';
        $res = $ilDB->query($query);
        
        $GLOBALS['DIC']['ilLog']->write($query);
        
        $nodes = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $node = array();
            $node['tree'] = $row->tree;
            $node['child'] = $row->child;
            $node['depth'] = $row->depth;
            
            $nodes[] = $node;
        }
        
        return $nodes;
    }
    
    public static function hasDuplicate($a_child)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        return count(self::findDuplicates($a_child));
    }
    
    public static function deleteDuplicateFromTree($a_duplicate_id, $a_delete_trash)
    {
        $dups = self::findDuplicates($a_duplicate_id);
        foreach ($dups as $dup) {
            if ($a_delete_trash and $dup['tree'] < 1) {
                self::deleteDuplicate($dup['tree'], $dup['child']);
            }
            if (!$a_delete_trash and $dup['tree'] == 1) {
                self::deleteDuplicate($dup['tree'], $dup['child']);
            }
        }
        return true;
    }
    
    protected static function deleteDuplicate($tree_id, $dup_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT child FROM tree ' .
                'WHERE parent = ' . $ilDB->quote($dup_id, 'integer') . ' ' .
                'AND tree = ' . $ilDB->quote($tree_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // start recursion
            self::deleteDuplicate($tree_id, $row->child);
        }
        // now delete node
        if (self::hasDuplicate($dup_id)) {
            $query = 'DELETE FROM tree ' .
                    'WHERE child = ' . $ilDB->quote($dup_id, 'integer') . ' ' .
                    'AND tree = ' . $ilDB->quote($tree_id, 'integer');
            $ilDB->manipulate($query);
        }
    }


    /**
     * @return ilDB
     */
    public function getDB()
    {
        return $this->db;
    }
    
    /**
     *
     * @return ilSCTask
     */
    public function getTask()
    {
        return $this->task;
    }
    
    
    
    /**
     * validate tree structure base on parent relation
     * @return type
     */
    public function validateStructure()
    {
        $failures = $this->checkStructure();
        
        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }
    
    
    public function checkStructure()
    {
        return $GLOBALS['DIC']['tree']->validateParentRelations();
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'select child from tree child where not exists ' .
                '( ' .
                'select child from tree parent where child.parent = parent.child and (parent.lft < child.lft) and (parent.rgt > child.rgt) ' .
                ')' .
                'and tree = 1 and child <> 1';
        $res = $ilDB->query($query);
        
        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = $row->child;
        }
        return $failures;
    }
    
    
    
    /**
     *
     */
    public function validateDuplicates()
    {
        $failures = $this->checkDuplicates();
        
        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }
    
    /**
     * Check for duplicates
     */
    public function checkDuplicates()
    {
        $query = 'SELECT child, count(child) num FROM tree ' .
                'GROUP BY child ' .
                'HAVING count(child) > 1';
        $res = $this->getDB()->query($query);
        
        $GLOBALS['DIC']['ilLog']->write($query);
        
        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = $row->child;
        }
        return $failures;
    }
    
    public function findMissingTreeEntries()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $failures = $this->readMissingTreeEntries();
        
        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }
        
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }
    
    
    /**
     * Find missing objects
     */
    public function findMissing()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $failures = $this->readMissing();
        
        if (count($failures)) {
            $this->getTask()->setStatus(ilSCTask::STATUS_FAILED);
        } else {
            $this->getTask()->setStatus(ilSCTask::STATUS_COMPLETED);
        }
        
        $this->getTask()->setLastUpdate(new ilDateTime(time(), IL_CAL_UNIX));
        $this->getTask()->update();
        return count($failures);
    }
    
    /**
     * Repair missing objects
     */
    public function repairMissing()
    {
        $failures = $this->readMissing();
        $recf_ref_id = $this->createRecoveryContainer();
        foreach ($failures as $ref_id) {
            $this->repairMissingObject($recf_ref_id, $ref_id);
        }
    }
    
    /**
     * Repair missing object
     * @param type $a_parent_ref
     */
    protected function repairMissingObject($a_parent_ref, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // check if object entry exist
        $query = 'SELECT obj_id FROM object_reference ' .
                'WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
        
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $query = 'SELECT type, title FROM object_data ' .
                    'WHERE obj_id = ' . $ilDB->quote($row->obj_id, 'integer');
            $ores = $ilDB->query($query);
            
            $done = false;
            while ($orow = $ores->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Moving to recovery folder: ' . $orow->type . ': ' . $orow->title);
                $done = true;
                
                include_once './Services/Object/classes/class.ilObjectFactory.php';
                $factory = new ilObjectFactory();
                $ref_obj = $factory->getInstanceByRefId($a_ref_id, false);
                
                if ($ref_obj instanceof ilObjRoleFolder) {
                    $ref_obj->delete();
                } elseif ($ref_obj instanceof ilObject) {
                    $ref_obj->putInTree($a_parent_ref);
                    $ref_obj->setPermissions($a_parent_ref);
                    $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Moving finished');
                    break;
                }
            }
            if (!$done) {
                // delete reference value
                $query = 'DELETE FROM object_reference WHERE ref_id = ' . $ilDB->quote($a_ref_id, 'integer');
                $ilDB->manipulate($query);
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Delete reference for "object" without tree and object_data entry: ref_id= ' . $a_ref_id);
            }
        }
    }

    /**
     * Read missing objects in tree
     * Entry in oject_reference but no entry in tree
     * @global type $ilDB
     * @return type
     */
    protected function readMissing()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT ref_id FROM object_reference ' .
                'LEFT JOIN tree ON ref_id = child ' .
                'WHERE child IS NULL';
        $res = $ilDB->query($query);
        
        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = $row->ref_id;
        }
        return $failures;
    }
    
    /**
     * repair missing tree entries
     * @global type $ilDB
     */
    public function repairMissingTreeEntries()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $missing = $this->readMissingTreeEntries();
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($missing, true));
        
        foreach ($missing as $ref_id) {
            // check for duplicates
            $query = 'SELECT tree, child FROM tree ' .
                    'WHERE child = ' . $ilDB->quote($ref_id);
            $res = $ilDB->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . $row->tree . ': ' . $ref_id);
                
                $this->deleteMissingTreeEntry($row->tree, $ref_id);
            }
        }
    }
    
    /**
     * Delete missing tree entries from tree table
     */
    protected function deleteMissingTreeEntry($a_tree_id, $a_ref_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT child FROM tree ' .
                'WHERE parent = ' . $ilDB->quote($a_ref_id, 'integer') . ' ' .
                'AND tree = ' . $ilDB->quote($a_tree_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // check for duplicates
            $query = 'SELECT tree, child FROM tree ' .
                    'WHERE child = ' . $ilDB->quote($row->child);
            $resd = $ilDB->query($query);
            while ($rowd = $resd->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->deleteMissingTreeEntry($rowd->tree, $rowd->child);
            }
        }
        
        // finally delete
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        $factory = new ilObjectFactory();
        $ref_obj = $factory->getInstanceByRefId($a_ref_id, false);
                
        if (($ref_obj instanceof ilObject) and $ref_obj->getType()) {
            $ref_obj->delete();
        }
        
        $query = 'DELETE from tree ' .
                'WHERE tree = ' . $ilDB->quote($a_tree_id) . ' ' .
                'AND child = ' . $ilDB->quote($a_ref_id);
        $ilDB->manipulate($query);
    }
    
    
    /**
     * Read missing tree entries for referenced objects
     * Entry in tree but no entry in object reference
     * @global type $ilDB
     * @return type
     */
    protected function readMissingTreeEntries()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT child FROM tree ' .
                'LEFT JOIN object_reference ON child = ref_id ' .
                'WHERE ref_id IS NULL';
        
        $res = $ilDB->query($query);
        
        $failures = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $failures[] = $row->child;
        }
        return $failures;
    }
    
    
    /**
     * Create a reccovery folder
     */
    protected function createRecoveryContainer()
    {
        $now = new ilDateTime(time(), IL_CAL_UNIX);
        
        include_once './Modules/Folder/classes/class.ilObjFolder.php';
        $folder = new ilObjFolder();
        $folder->setTitle('__System check recovery: ' . $now->get(IL_CAL_DATETIME));
        $folder->create();
        $folder->createReference();
        $folder->putInTree(RECOVERY_FOLDER_ID);
        
        return $folder->getRefId();
    }
}
