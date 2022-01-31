<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

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
        $query = 'INSERT INTO ecs_cms_tree ' .
            '(tree,child,parent,lft,rgt,depth) ' .
            'VALUES ( ' .
            $this->db->quote($tree, 'integer') . ', ' .
            $this->db->quote($a_child, 'integer') . ', ' .
            $this->db->quote(0, 'integer') . ', ' .
            $this->db->quote(1, 'integer') . ', ' .
            $this->db->quote(100, 'integer') . ', ' .
            $this->db->quote(1, 'integer') . ' )';

        $this->db->manipulate($query);

        return true;
    }
    
    /**
     * Delete tree by tree_id
     */
    public static function deleteByTreeId($a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $DIC->logger()->wsrv()->debug('Deleting cms tree: ' . $a_tree_id);
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
        $query = 'SELECT COUNT(*) num FROM ecs_cms_tree WHERE tree = ' . $this->db->quote($a_tree_id, 'integer');
        $res = $this->db->query($query);
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
