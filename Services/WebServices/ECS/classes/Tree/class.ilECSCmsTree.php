<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Tree/classes/class.ilTree.php';

/**
 *
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilECSCmsTree extends ilTree
{
    public function __construct($a_tree_id)
    {
        parent::__construct($a_tree_id, self::lookupRootId($a_tree_id));

        $this->setObjectTablePK('obj_id');
        $this->setTableNames('ecs_cms_tree', 'ecs_cms_data');
        $this->useCache(false);
    }

    public function insertRootNode($tree, $a_child)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'INSERT INTO ecs_cms_tree ' .
            '(tree,child,parent,lft,rgt,depth) ' .
            'VALUES ( ' .
            $ilDB->quote($tree, 'integer') . ', ' .
            $ilDB->quote($a_child, 'integer') . ', ' .
            $ilDB->quote(0, 'integer') . ', ' .
            $ilDB->quote(1, 'integer') . ', ' .
            $ilDB->quote(100, 'integer') . ', ' .
            $ilDB->quote(1, 'integer') . ' )';

        $ilDB->manipulate($query);
        
        
        return true;
    }
    
    /**
     * Delete tree by tree_id
     */
    public static function deleteByTreeId($a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $GLOBALS['DIC']->logger()->wsrv()->debug('Deleting cms tree: ' . $a_tree_id);
        $query = 'DELETE FROM ecs_cms_tree ' .
                'WHERE tree = ' . $ilDB->quote($a_tree_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Check if tree exists
     * @param int $a_tree_id
     */
    public function treeExists($a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT COUNT(*) num FROM ecs_cms_tree WHERE tree = ' . $ilDB->quote($a_tree_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->num > 0 ? true : false;
        }
        return false;
    }


    /**
     * lookup root id
     */
    public static function lookupRootId($a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT child FROM ecs_cms_tree WHERE tree = ' . $ilDB->quote($a_tree_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->child;
        }
        return 0;
    }
}
