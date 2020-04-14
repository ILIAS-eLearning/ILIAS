<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDBUpdate4950
 */
class ilDBUpdate4963
{
    private static $table_tree;
    private static $tree_id;
    private static $gap;

    /**
     * Wrapper for renumber. This method locks the table tree
     * (recursive)
     * @access	public
     * @param	integer	node_id where to start (usually the root node)
     * @param	integer	first left value of start node (usually 1)
     * @return	integer	current left value of recursive call
     */
    public static function renumberBookmarkTree()
    {
        global $ilDB;

        self::$table_tree = "bookmark_tree";
        self::$gap = 0;

        $query = 'SELECT tree FROM ' . self::$table_tree .
            ' GROUP BY tree';
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            self::$tree_id = $row->tree;
            self::__renumber();
        }
    }

    // PRIVATE
    /**
     * This method is private. Always call ilTree->renumber() since it locks the tree table
     * renumber left/right values and close the gaps in numbers
     * (recursive)
     * @access	private
     * @param	integer	node_id where to start (usually the root node)
     * @param	integer	first left value of start node (usually 1)
     * @return	integer	current left value of recursive call
     */
    private static function __renumber($node_id = 1, $i = 1)
    {
        global $ilDB;

        $query = 'UPDATE ' . self::$table_tree . ' SET lft = %s WHERE child = %s AND tree = %s';
        $res = $ilDB->manipulateF($query, array('integer','integer','integer'), array(
            $i,
            $node_id,
            self::$tree_id));

        // to much dependencies
        //$childs = $this->getChilds($node_id);
        $childs = self::getChildIds($node_id);

        foreach ($childs as $child) {
            $i = self::__renumber($child, $i + 1);
        }
        $i++;

        // Insert a gap at the end of node, if the node has children
        if (count($childs) > 0) {
            $i += self::$gap * 2;
        }


        $query = 'UPDATE ' . self::$table_tree . ' SET rgt = %s WHERE child = %s AND tree = %s';
        $res = $ilDB->manipulateF($query, array('integer','integer', 'integer'), array(
            $i,
            $node_id,
            self::$tree_id));
        return $i;
    }

    /**
     * Get node child ids
     * @global type $ilDB
     * @param type $a_node
     * @return type
     */
    private static function getChildIds($a_node)
    {
        global $ilDB;

        $query = 'SELECT * FROM ' . self::$table_tree .
            ' WHERE parent = ' . $ilDB->quote($a_node, 'integer') . ' ' .
            'AND tree = ' . $ilDB->quote(self::$tree_id, 'integer');
        $res = $ilDB->query($query);

        $childs = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = $row->child;
        }
        return $childs;
    }
}
