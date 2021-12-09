<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define("IL_LAST_NODE", -2);
define("IL_FIRST_NODE", -1);

include_once './Services/Tree/exceptions/class.ilInvalidTreeStructureException.php';

/**
 *  @defgroup ServicesTree Services/Tree
 */

/**
* Tree class
* data representation in hierachical trees using the Nested Set Model with Gaps
* by Joe Celco.
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesTree
*/
class ilTree
{
    public const TREE_TYPE_MATERIALIZED_PATH = 'mp';
    public const TREE_TYPE_NESTED_SET = 'ns';

    const POS_LAST_NODE = -2;
    const POS_FIRST_NODE = -1;
    
    
    const RELATION_CHILD = 1;		// including grand child
    const RELATION_PARENT = 2;		// including grand child
    const RELATION_SIBLING = 3;
    const RELATION_EQUALS = 4;
    const RELATION_NONE = 5;
    
    
    /**
    * ilias object
    * @var		object	ilias
    * @access	private
    */
    public $ilias;


    /**
    * Logger object
    * @var		ilLogger
    * @access	private
    */
    public $log;

    /**
    * points to root node (may be a subtree)
    * @var		integer
    * @access	public
    */
    public $root_id;

    /**
    * to use different trees in one db-table
    * @var		integer
    * @access	public
    */
    public $tree_id;

    /**
    * table name of tree table
    * @var		string
    * @access	private
    */
    public $table_tree;

    /**
    * table name of object_data table
    * @var		string
    * @access	private
    */
    public $table_obj_data;

    /**
    * table name of object_reference table
    * @var		string
    * @access	private
    */
    public $table_obj_reference;

    /**
    * column name containing primary key in reference table
    * @var		string
    * @access	private
    */
    public $ref_pk;

    /**
    * column name containing primary key in object table
    * @var		string
    * @access	private
    */
    public $obj_pk;

    /**
    * column name containing tree id in tree table
    * @var		string
    * @access	private
    */
    public $tree_pk;

    /**
    * Size of the gaps to be created in the nested sets sequence numbering of the
    * tree nodes.
    * Having gaps in the tree greatly improves performance on all operations
    * that add or remove tree nodes.
    *
    * Setting this to zero will leave no gaps in the tree.
    * Setting this to a value larger than zero will create gaps in the tree.
    * Each gap leaves room in the sequence numbering for the specified number of
    * nodes.
    * (The gap is expressed as the number of nodes. Since each node consumes
    * two sequence numbers, specifying a gap of 1 will leave space for 2
    * sequence numbers.)
    *
    * A gap is created, when a new child is added to a node, and when not
    * enough room between node.rgt and the child with the highest node.rgt value
    * of the node is available.
    * A gap is closed, when a node is removed and when (node.rgt - node.lft)
    * is bigger than gap * 2.
    *
    *
    * @var		integer
    * @access	private
    */
    public $gap;

    protected $depth_cache = array();
    protected $parent_cache = array();
    protected $in_tree_cache = array();
    
    private $tree_impl = null;


    /**
    * Constructor
    * @access	public
    * @param	integer	$a_tree_id		tree_id
    * @param	integer	$a_root_id		root_id (optional)
    * @throws InvalidArgumentException
    */
    public function __construct($a_tree_id, $a_root_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // set db
        $this->ilDB = $ilDB;

        $this->lang_code = "en";

        // CREATE LOGGER INSTANCE
        $this->log = ilLoggerFactory::getLogger('tree');

        if (!isset($a_tree_id) or (func_num_args() == 0)) {
            $this->log->error("No tree_id given!");
            $this->log->logStack(ilLogLevel::DEBUG);
            throw new InvalidArgumentException("No tree_id given!");
        }

        if (func_num_args() > 2) {
            $this->log->error("Wrong parameter count!");
            throw new InvalidArgumentException("Wrong parameter count!");
        }

        //init variables
        if (empty($a_root_id)) {
            $a_root_id = ROOT_FOLDER_ID;
        }

        $this->tree_id = $a_tree_id;
        $this->root_id = $a_root_id;
        $this->table_tree = 'tree';
        $this->table_obj_data = 'object_data';
        $this->table_obj_reference = 'object_reference';
        $this->ref_pk = 'ref_id';
        $this->obj_pk = 'obj_id';
        $this->tree_pk = 'tree';

        $this->use_cache = true;

        // If cache is activated, cache object translations to improve performance
        $this->translation_cache = array();
        $this->parent_type_cache = array();

        // By default, we create gaps in the tree sequence numbering for 50 nodes
        $this->gap = 50;
        
        
        // init tree implementation
        $this->initTreeImplementation();
    }
    
    /**
     * @param int $node_id
     * @return array
     */
    public static function lookupTreesForNode(int $node_id) : array
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'select tree from tree ' .
            'where child = ' . $db->quote($node_id, \ilDBConstants::T_INTEGER);
        $res = $db->query($query);

        $trees = [];
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            $trees[] = $row->tree;
        }
        return $trees;
    }
    
    /**
     * Init tree implementation
     */
    public function initTreeImplementation()
    {
        global $DIC;

        if (!$DIC->isDependencyAvailable('settings') || $DIC->settings()->getModule() != 'common') {
            include_once './Services/Administration/classes/class.ilSetting.php';
            $setting = new ilSetting('common');
        } else {
            $setting = $DIC->settings();
        }
        
        if ($this->__isMainTree()) {
            if ($setting->get('main_tree_impl', 'ns') == 'ns') {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Using nested set.');
                include_once './Services/Tree/classes/class.ilNestedSetTree.php';
                $this->tree_impl = new ilNestedSetTree($this);
            } else {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Using materialized path.');
                include_once './Services/Tree/classes/class.ilMaterializedPathTree.php';
                $this->tree_impl = new ilMaterializedPathTree($this);
            }
        } else {
            #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Using netsted set for non main tree.');
            include_once './Services/Tree/classes/class.ilNestedSetTree.php';
            $this->tree_impl = new ilNestedSetTree($this);
        }
    }
    
    /**
     * Get tree implementation
     * @return ilTreeImplementation $impl
     */
    public function getTreeImplementation()
    {
        return $this->tree_impl;
    }
    
    /**
    * Use Cache (usually activated)
    */
    public function useCache($a_use = true)
    {
        $this->use_cache = $a_use;
    }
    
    /**
     * Check if cache is active
     * @return bool
     */
    public function isCacheUsed()
    {
        return $this->__isMainTree() and $this->use_cache;
    }
    
    /**
     * Get depth cache
     * @return type
     */
    public function getDepthCache()
    {
        return (array) $this->depth_cache;
    }
    
    /**
     * Get parent cache
     * @return type
     */
    public function getParentCache()
    {
        return (array) $this->parent_cache;
    }
    
    /**
    * Store user language. This function is used by the "main"
    * tree only (during initialisation).
    */
    public function initLangCode()
    {
        global $DIC;

        // lang_code is only required in $this->fetchnodedata
        try {
            $ilUser = $DIC['ilUser'];
            $this->lang_code = $ilUser->getCurrentLanguage();
        } catch (\InvalidArgumentException $e) {
            $this->lang_code = "en";
        }
    }
    
    /**
     * Get tree table name
     * @return string tree table name
     */
    public function getTreeTable()
    {
        return $this->table_tree;
    }
    
    /**
     * Get object data table
     * @return type
     */
    public function getObjectDataTable()
    {
        return $this->table_obj_data;
    }
    
    /**
     * Get tree primary key
     * @return string column of pk
     */
    public function getTreePk()
    {
        return $this->tree_pk;
    }
    
    /**
     * Get reference table if available
     */
    public function getTableReference()
    {
        return $this->table_obj_reference;
    }
    
    /**
     * Get default gap	 * @return int
     */
    public function getGap()
    {
        return $this->gap;
    }
    
    /***
     * reset in tree cache
     */
    public function resetInTreeCache()
    {
        $this->in_tree_cache = array();
    }


    /**
    * set table names
    * The primary key of the table containing your object_data must be 'obj_id'
    * You may use a reference table.
    * If no reference table is specified the given tree table is directly joined
    * with the given object_data table.
    * The primary key in object_data table and its foreign key in reference table must have the same name!
    *
    * @param	string	table name of tree table
    * @param	string	table name of object_data table
    * @param	string	table name of object_reference table (optional)
    * @access	public
    * @return	boolean
     *
     * @throws InvalidArgumentException
    */
    public function setTableNames($a_table_tree, $a_table_obj_data, $a_table_obj_reference = "")
    {
        if (!isset($a_table_tree) or !isset($a_table_obj_data)) {
            $message = "Missing parameter! " .
                                "tree table: " . $a_table_tree . " object data table: " . $a_table_obj_data;
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        $this->table_tree = $a_table_tree;
        $this->table_obj_data = $a_table_obj_data;
        $this->table_obj_reference = $a_table_obj_reference;
        
        $this->initTreeImplementation();

        return true;
    }

    /**
    * set column containing primary key in reference table
    * @access	public
    * @param	string	column name
    * @return	boolean	true, when successfully set
    * @throws InvalidArgumentException
    */
    public function setReferenceTablePK($a_column_name)
    {
        if (!isset($a_column_name)) {
            $message = "No column name given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        $this->ref_pk = $a_column_name;
        return true;
    }

    /**
    * set column containing primary key in object table
    * @access	public
    * @param	string	column name
    * @return	boolean	true, when successfully set
    * @throws InvalidArgumentException
    */
    public function setObjectTablePK($a_column_name)
    {
        if (!isset($a_column_name)) {
            $message = "No column name given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        $this->obj_pk = $a_column_name;
        return true;
    }

    /**
    * set column containing primary key in tree table
    * @access	public
    * @param	string	column name
    * @return	boolean	true, when successfully set
    * @throws InvalidArgumentException
    */
    public function setTreeTablePK($a_column_name)
    {
        if (!isset($a_column_name)) {
            $message = "No column name given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        $this->tree_pk = $a_column_name;
        return true;
    }

    /**
    * build join depending on table settings
    * @access	private
    * @return	string
    */
    public function buildJoin()
    {
        if ($this->table_obj_reference) {
            // Use inner join instead of left join to improve performance
            return "JOIN " . $this->table_obj_reference . " ON " . $this->table_tree . ".child=" . $this->table_obj_reference . "." . $this->ref_pk . " " .
                   "JOIN " . $this->table_obj_data . " ON " . $this->table_obj_reference . "." . $this->obj_pk . "=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        } else {
            // Use inner join instead of left join to improve performance
            return "JOIN " . $this->table_obj_data . " ON " . $this->table_tree . ".child=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        }
    }
    
    /**
     * Get relation of two nodes
     * @param int $a_node_a
     * @param int $a_node_b
     */
    public function getRelation($a_node_a, $a_node_b)
    {
        return $this->getRelationOfNodes(
            $this->getNodeTreeData($a_node_a),
            $this->getNodeTreeData($a_node_b)
        );
    }
    
    /**
     * get relation of two nodes by node data
     * @param array $a_node_a_arr
     * @param array $a_node_b_arr
     *
     */
    public function getRelationOfNodes($a_node_a_arr, $a_node_b_arr)
    {
        return $this->getTreeImplementation()->getRelation($a_node_a_arr, $a_node_b_arr);
    }
    
    /**
     * Get node child ids
     * @global type $ilDB
     * @param type $a_node
     * @return type
     */
    public function getChildIds($a_node)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                'WHERE parent = ' . $ilDB->quote($a_node, 'integer') . ' ' .
                'AND tree = ' . $ilDB->quote($this->tree_id, 'integer' . ' ' .
                'ORDER BY lft');
        $res = $ilDB->query($query);
        
        $childs = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = $row->child;
        }
        return $childs;
    }

    /**
    * get child nodes of given node
    * @access	public
    * @param	integer		node_id
    * @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
    * @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChilds($a_node_id, $a_order = "", $a_direction = "ASC")
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilUser = $DIC['ilUser'];
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        // init childs
        $childs = array();

        // number of childs
        $count = 0;

        // init order_clause
        $order_clause = "";

        // set order_clause if sort order parameter is given
        if (!empty($a_order)) {
            $order_clause = "ORDER BY " . $a_order . " " . $a_direction;
        } else {
            $order_clause = "ORDER BY " . $this->table_tree . ".lft";
        }

             
        $query = sprintf(
            'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                "WHERE parent = %s " .
                "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
                $order_clause,
            $ilDB->quote($a_node_id, 'integer'),
            $ilDB->quote($this->tree_id, 'integer')
        );

        $res = $ilDB->query($query);
        
        if (!$count = $res->numRows()) {
            return array();
        }

        // get rows and object ids
        $rows = array();
        while ($r = $ilDB->fetchAssoc($res)) {
            $rows[] = $r;
            $obj_ids[] = $r["obj_id"];
        }

        // preload object translation information
        if ($this->__isMainTree() && $this->isCacheUsed() && is_object($ilObjDataCache) &&
            is_object($ilUser) && $this->lang_code == $ilUser->getLanguage() && !$this->oc_preloaded[$a_node_id]) {
            //			$ilObjDataCache->preloadTranslations($obj_ids, $this->lang_code);
            $ilObjDataCache->preloadObjectCache($obj_ids, $this->lang_code);
            $this->fetchTranslationFromObjectDataCache($obj_ids);
            $this->oc_preloaded[$a_node_id] = true;
        }

        foreach ($rows as $row) {
            $childs[] = $this->fetchNodeData($row);

            // Update cache of main tree
            if ($this->__isMainTree()) {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Storing in tree cache '.$row['child'].' = true');
                $this->in_tree_cache[$row['child']] = $row['tree'] == 1;
            }
        }
        $childs[$count - 1]["last"] = true;
        return $childs;
    }

    /**
    * get child nodes of given node (exclude filtered obj_types)
    * @access	public
    * @param	array		objects to filter (e.g array('rolf'))
    * @param	integer		node_id
    * @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
    * @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
    * @return	array		with node data of all childs or empty array
    */
    public function getFilteredChilds($a_filter, $a_node, $a_order = "", $a_direction = "ASC")
    {
        $childs = $this->getChilds($a_node, $a_order, $a_direction);

        foreach ($childs as $child) {
            if (!in_array($child["type"], $a_filter)) {
                $filtered[] = $child;
            }
        }
        return $filtered ? $filtered : array();
    }


    /**
    * get child nodes of given node by object type
    * @access	public
    * @param	integer		node_id
    * @param	string		object type
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChildsByType($a_node_id, $a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id) or !isset($a_type)) {
            $message = "Missing parameter! node_id:" . $a_node_id . " type:" . $a_type;
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        if ($a_type == 'rolf' && $this->table_obj_reference) {
            // Performance optimization: A node can only have exactly one
            // role folder as its child. Therefore we don't need to sort the
            // results, and we can let the database know about the expected limit.
            $ilDB->setLimit(1, 0);
            $query = sprintf(
                "SELECT * FROM " . $this->table_tree . " " .
                $this->buildJoin() .
                "WHERE parent = %s " .
                "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
                "AND " . $this->table_obj_data . ".type = %s ",
                $ilDB->quote($a_node_id, 'integer'),
                $ilDB->quote($this->tree_id, 'integer'),
                $ilDB->quote($a_type, 'text')
            );
        } else {
            $query = sprintf(
                "SELECT * FROM " . $this->table_tree . " " .
                $this->buildJoin() .
                "WHERE parent = %s " .
                "AND " . $this->table_tree . "." . $this->tree_pk . " = %s " .
                "AND " . $this->table_obj_data . ".type = %s " .
                "ORDER BY " . $this->table_tree . ".lft",
                $ilDB->quote($a_node_id, 'integer'),
                $ilDB->quote($this->tree_id, 'integer'),
                $ilDB->quote($a_type, 'text')
            );
        }
        $res = $ilDB->query($query);
        
        // init childs
        $childs = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $childs[] = $this->fetchNodeData($row);
        }
        
        return $childs ? $childs : array();
    }


    /**
    * get child nodes of given node by object type
    * @access	public
    * @param	integer		node_id
    * @param	array		array of object type
    * @return	array		with node data of all childs or empty array
    * @throws InvalidArgumentException
    */
    public function getChildsByTypeFilter($a_node_id, $a_types, $a_order = "", $a_direction = "ASC")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id) or !$a_types) {
            $message = "Missing parameter! node_id:" . $a_node_id . " type:" . $a_types;
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }
    
        $filter = ' ';
        if ($a_types) {
            $filter = 'AND ' . $this->table_obj_data . '.type IN(' . implode(',', ilUtil::quoteArray($a_types)) . ') ';
        }

        // set order_clause if sort order parameter is given
        if (!empty($a_order)) {
            $order_clause = "ORDER BY " . $a_order . " " . $a_direction;
        } else {
            $order_clause = "ORDER BY " . $this->table_tree . ".lft";
        }
        
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE parent = ' . $ilDB->quote($a_node_id, 'integer') . ' ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = ' . $ilDB->quote($this->tree_id, 'integer') . ' ' .
            $filter .
            $order_clause;
        
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $childs[] = $this->fetchNodeData($row);
        }
        
        return $childs ? $childs : array();
    }
    
    /**
     * Insert node from trash deletes trash entry.
     * If we have database query exceptions we could wrap insertNode in try/catch
     * and rollback if the insert failed.
     *
     * @param type $a_source_id
     * @param type $a_target_id
     * @param type $a_tree_id
     *
     * @throws InvalidArgumentException
     */
    public function insertNodeFromTrash($a_source_id, $a_target_id, $a_tree_id, $a_pos = IL_LAST_NODE, $a_reset_deleted_date = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->__isMainTree()) {
            if ($a_source_id <= 1 or $a_target_id <= 0) {
                ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::INFO);
                throw new InvalidArgumentException('Invalid parameter given for ilTree::insertNodeFromTrash');
            }
        }
        if (!isset($a_source_id) or !isset($a_target_id)) {
            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::INFO);
            throw new InvalidArgumentException('Missing parameter for ilTree::insertNodeFromTrash');
        }
        if ($this->isInTree($a_source_id)) {
            ilLoggerFactory::getLogger('tree')->error('Node already in tree');
            ilLoggerFactory::getLogger('tree')->logStack(ilLogLevel::INFO);
            throw new InvalidArgumentException('Node already in tree.');
        }
        
        $query = 'DELETE from tree ' .
                'WHERE tree = ' . $ilDB->quote($a_tree_id, 'integer') . ' ' .
                'AND child = ' . $ilDB->quote($a_source_id, 'integer');
        $ilDB->manipulate($query);
        
        $this->insertNode($a_source_id, $a_target_id, IL_LAST_NODE, $a_reset_deleted_date);
    }
    
    
    /**
    * insert new node with node_id under parent node with parent_id
    * @access	public
    * @param	integer		node_id
    * @param	integer		parent_id
    * @param	integer		IL_LAST_NODE | IL_FIRST_NODE | node id of preceding child
    * @throws InvalidArgumentException
    */
    public function insertNode($a_node_id, $a_parent_id, $a_pos = IL_LAST_NODE, $a_reset_deletion_date = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        //echo "+$a_node_id+$a_parent_id+";
        // CHECK node_id and parent_id > 0 if in main tree
        if ($this->__isMainTree()) {
            if ($a_node_id <= 1 or $a_parent_id <= 0) {
                $message = sprintf(
                    'Invalid parameters! $a_node_id: %s $a_parent_id: %s',
                    $a_node_id,
                    $a_parent_id
                );
                $this->log->logStack(ilLogLevel::ERROR, $message);
                throw new InvalidArgumentException($message);
            }
        }


        if (!isset($a_node_id) or !isset($a_parent_id)) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException("Missing parameter! " .
                "node_id: " . $a_node_id . " parent_id: " . $a_parent_id);
        }
        if ($this->isInTree($a_node_id)) {
            throw new InvalidArgumentException("Node " . $a_node_id . " already in tree " .
                                     $this->table_tree . "!");
        }

        $this->getTreeImplementation()->insertNode($a_node_id, $a_parent_id, $a_pos);
        
        $this->in_tree_cache[$a_node_id] = true;

        // reset deletion date
        if ($a_reset_deletion_date) {
            ilObject::_resetDeletedDate($a_node_id);
        }
        
        if (isset($GLOBALS['DIC']["ilAppEventHandler"]) && $this->__isMainTree()) {
            $GLOBALS['DIC']['ilAppEventHandler']->raise(
                "Services/Tree",
                "insertNode",
                array(
                        'tree' => $this->table_tree,
                        'node_id' => $a_node_id,
                        'parent_id' => $a_parent_id)
            );
        }
    }
    
    /**
     * get filtered subtree
     *
     * get all subtree nodes beginning at a specific node
     * excluding specific object types and their child nodes.
     *
     * E.g getFilteredSubTreeNodes()
     *
     * @access public
     * @param
     * @return
     */
    public function getFilteredSubTree($a_node_id, $a_filter = array())
    {
        $node = $this->getNodeData($a_node_id);
        
        $first = true;
        $depth = 0;
        foreach ($this->getSubTree($node) as $subnode) {
            if ($depth and $subnode['depth'] > $depth) {
                continue;
            }
            if (!$first and in_array($subnode['type'], $a_filter)) {
                $depth = $subnode['depth'];
                $first = false;
                continue;
            }
            $depth = 0;
            $first = false;
            $filtered[] = $subnode;
        }
        return $filtered ? $filtered : array();
    }
    
    /**
     * Get all ids of subnodes
     * @return
     * @param object $a_ref_id
     */
    public function getSubTreeIds($a_ref_id)
    {
        return $this->getTreeImplementation()->getSubTreeIds($a_ref_id);
    }
    

    /**
    * get all nodes in the subtree under specified node
    *
    * @access	public
    * @param	array		node_data
    * @param    boolean     with data: default is true otherwise this function return only a ref_id array
    * @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
    * @throws InvalidArgumentException
    */
    public function getSubTree($a_node, $a_with_data = true, $a_type = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!is_array($a_node)) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException(__METHOD__ . ': wrong datatype for node data given');
        }

        /*
        if($a_node['lft'] < 1 or $a_node['rgt'] < 2)
        {
            $GLOBALS['DIC']['ilLog']->logStack();
            $message = sprintf('%s: Invalid node given! $a_node["lft"]: %s $a_node["rgt"]: %s',
                                   __METHOD__,
                                   $a_node['lft'],
                                   $a_node['rgt']);

            throw new InvalidArgumentException($message);
        }
        */
        
        $query = $this->getTreeImplementation()->getSubTreeQuery($a_node, $a_type);
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($a_with_data) {
                $subtree[] = $this->fetchNodeData($row);
            } else {
                $subtree[] = $row['child'];
            }
            // the lm_data "hack" should be removed in the trunk during an alpha
            if ($this->__isMainTree() || $this->table_tree == "lm_tree") {
                $this->in_tree_cache[$row['child']] = true;
            }
        }
        return $subtree ? $subtree : array();
    }

    /**
    * get types of nodes in the subtree under specified node
    *
    * @access	public
    * @param	array		node_id
    * @param	array		object types to filter e.g array('rolf')
    * @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
    */
    public function getSubTreeTypes($a_node, $a_filter = 0)
    {
        $a_filter = $a_filter ? $a_filter : array();

        foreach ($this->getSubtree($this->getNodeData($a_node)) as $node) {
            if (in_array($node["type"], $a_filter)) {
                continue;
            }
            $types["$node[type]"] = $node["type"];
        }
        return $types ? $types : array();
    }

    /**
     * delete node and the whole subtree under this node
     * @access	public
     * @param	array		node_data of a node
     * @throws InvalidArgumentException
     * @throws ilInvalidTreeStructureException
     */
    public function deleteTree($a_node)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->log->debug('Delete tree with node ' . $a_node);
        
        if (!is_array($a_node)) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException(__METHOD__ . ': Wrong datatype for node data!');
        }
        
        $this->log->debug($this->tree_pk);
        
        if ($this->__isMainTree()) {
            // @todo normally this part is not executed, since the subtree is first
            // moved to trash and then deleted.
            if (!$this->__checkDelete($a_node)) {
                $this->log->logStack(ilLogLevel::ERROR);
                throw new ilInvalidTreeStructureException('Deletion canceled due to invalid tree structure.' . print_r($a_node, true));
            }
        }

        $this->getTreeImplementation()->deleteTree($a_node['child']);
        
        $this->resetInTreeCache();
    }
    
    /**
     * Validate parent relations of tree
     * @return int[] array of failure nodes
     */
    public function validateParentRelations()
    {
        return $this->getTreeImplementation()->validateParentRelations();
    }

    /**
    * get path from a given startnode to a given endnode
    * if startnode is not given the rootnode is startnode.
    * This function chooses the algorithm to be used.
    *
    * @access	public
    * @param	integer	node_id of endnode
    * @param	integer	node_id of startnode (optional)
    * @return	array	ordered path info (id,title,parent) from start to end
    */
    public function getPathFull($a_endnode_id, $a_startnode_id = 0)
    {
        $pathIds = $this->getPathId($a_endnode_id, $a_startnode_id);

        // We retrieve the full path in a single query to improve performance
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Abort if no path ids were found
        if (count($pathIds) == 0) {
            return null;
        }

        $inClause = 'child IN (';
        for ($i = 0; $i < count($pathIds); $i++) {
            if ($i > 0) {
                $inClause .= ',';
            }
            $inClause .= $ilDB->quote($pathIds[$i], 'integer');
        }
        $inClause .= ')';

        $q = 'SELECT * ' .
            'FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() . ' ' .
            'WHERE ' . $inClause . ' ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = ' . $this->ilDB->quote($this->tree_id, 'integer') . ' ' .
            'ORDER BY depth';
        $r = $ilDB->query($q);

        $pathFull = array();
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $pathFull[] = $this->fetchNodeData($row);

            // Update cache
            if ($this->__isMainTree()) {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Storing in tree cache '.$row['child']);
                $this->in_tree_cache[$row['child']] = $row['tree'] == 1;
            }
        }
        return $pathFull;
    }
    

    /**
     * Preload depth/parent
     *
     * @param
     * @return
     */
    public function preloadDepthParent($a_node_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!$this->__isMainTree() || !is_array($a_node_ids) || !$this->isCacheUsed()) {
            return;
        }

        $res = $ilDB->query('SELECT t.depth, t.parent, t.child ' .
            'FROM ' . $this->table_tree . ' t ' .
            'WHERE ' . $ilDB->in("child", $a_node_ids, false, "integer") .
            'AND ' . $this->tree_pk . ' = ' . $ilDB->quote($this->tree_id, "integer"));
        while ($row = $ilDB->fetchAssoc($res)) {
            $this->depth_cache[$row["child"]] = $row["depth"];
            $this->parent_cache[$row["child"]] = $row["parent"];
        }
    }

    /**
    * get path from a given startnode to a given endnode
    * if startnode is not given the rootnode is startnode
    * @access	public
    * @param	integer		node_id of endnode
    * @param	integer		node_id of startnode (optional)
    * @return	array		all path ids from startnode to endnode
    * @throws InvalidArgumentException
    */
    public function getPathId($a_endnode_id, $a_startnode_id = 0)
    {
        if (!$a_endnode_id) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException(__METHOD__ . ': No endnode given!');
        }
        
        // path id cache
        if ($this->isCacheUsed() && isset($this->path_id_cache[$a_endnode_id][$a_startnode_id])) {
            //echo "<br>getPathIdhit";
            return $this->path_id_cache[$a_endnode_id][$a_startnode_id];
        }
        //echo "<br>miss";

        $pathIds = $this->getTreeImplementation()->getPathIds($a_endnode_id, $a_startnode_id);
        
        if ($this->__isMainTree()) {
            $this->path_id_cache[$a_endnode_id][$a_startnode_id] = $pathIds;
        }
        return $pathIds;
    }

    // BEGIN WebDAV: getNodePathForTitlePath function added
    /**
    * Converts a path consisting of object titles into a path consisting of tree
    * nodes. The comparison is non-case sensitive.
    *
    * Note: this function returns the same result as getNodePath,
    * but takes a title path as parameter.
    *
    * @access	public
    * @param	Array	Path array with object titles.
    *                       e.g. array('ILIAS','English','Course A')
    * @param	ref_id	Startnode of the relative path.
    *                       Specify null, if the title path is an absolute path.
    *                       Specify a ref id, if the title path is a relative
    *                       path starting at this ref id.
    * @return	array	ordered path info (depth,parent,child,obj_id,type,title)
    *               or null, if the title path can not be converted into a node path.
    */
    public function getNodePathForTitlePath($titlePath, $a_startnode_id = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $log = $DIC['log'];
        //$log->write('getNodePathForTitlePath('.implode('/',$titlePath));
        
        // handle empty title path
        if ($titlePath == null || count($titlePath) == 0) {
            if ($a_startnode_id == 0) {
                return null;
            } else {
                return $this->getNodePath($a_startnode_id);
            }
        }

        // fetch the node path up to the startnode
        if ($a_startnode_id != null && $a_startnode_id != 0) {
            // Start using the node path to the root of the relative path
            $nodePath = $this->getNodePath($a_startnode_id);
            $parent = $a_startnode_id;
        } else {
            // Start using the root of the tree
            $nodePath = array();
            $parent = 0;
        }

        
        // Convert title path into Unicode Normal Form C
        // This is needed to ensure that we can compare title path strings with
        // strings from the database.
        require_once('include/Unicode/UtfNormal.php');
        include_once './Services/Utilities/classes/class.ilStr.php';
        $inClause = 'd.title IN (';
        for ($i = 0; $i < count($titlePath); $i++) {
            $titlePath[$i] = ilStr::strToLower(UtfNormal::toNFC($titlePath[$i]));
            if ($i > 0) {
                $inClause .= ',';
            }
            $inClause .= $ilDB->quote($titlePath[$i], 'text');
        }
        $inClause .= ')';

        // Fetch all rows that are potential path elements
        if ($this->table_obj_reference) {
            $joinClause = 'JOIN ' . $this->table_obj_reference . '  r ON t.child = r.' . $this->ref_pk . ' ' .
                'JOIN ' . $this->table_obj_data . ' d ON r.' . $this->obj_pk . ' = d.' . $this->obj_pk;
        } else {
            $joinClause = 'JOIN ' . $this->table_obj_data . '  d ON t.child = d.' . $this->obj_pk;
        }
        // The ORDER BY clause in the following SQL statement ensures that,
        // in case of a multiple objects with the same title, always the Object
        // with the oldest ref_id is chosen.
        // This ensure, that, if a new object with the same title is added,
        // WebDAV clients can still work with the older object.
        $q = 'SELECT t.depth, t.parent, t.child, d.' . $this->obj_pk . ' obj_id, d.type, d.title ' .
            'FROM ' . $this->table_tree . '  t ' .
            $joinClause . ' ' .
            'WHERE ' . $inClause . ' ' .
            'AND t.depth <= ' . (count($titlePath) + count($nodePath)) . ' ' .
            'AND t.tree = 1 ' .
            'ORDER BY t.depth, t.child ASC';
        $r = $ilDB->query($q);
        
        $rows = array();
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $row['title'] = UtfNormal::toNFC($row['title']);
            $row['ref_id'] = $row['child'];
            $rows[] = $row;
        }

        // Extract the path elements from the fetched rows
        for ($i = 0; $i < count($titlePath); $i++) {
            $pathElementFound = false;
            foreach ($rows as $row) {
                if ($row['parent'] == $parent &&
                ilStr::strToLower($row['title']) == $titlePath[$i]) {
                    // FIXME - We should test here, if the user has
                    // 'visible' permission for the object.
                    $nodePath[] = $row;
                    $parent = $row['child'];
                    $pathElementFound = true;
                    break;
                }
            }
            // Abort if we haven't found a path element for the current depth
            if (!$pathElementFound) {
                //$log->write('ilTree.getNodePathForTitlePath('.var_export($titlePath,true).','.$a_startnode_id.'):null');
                return null;
            }
        }
        // Return the node path
        //$log->write('ilTree.getNodePathForTitlePath('.var_export($titlePath,true).','.$a_startnode_id.'):'.var_export($nodePath,true));
        return $nodePath;
    }
    // END WebDAV: getNodePathForTitlePath function added
    // END WebDAV: getNodePath function added
    /**
    * Returns the node path for the specified object reference.
    *
    * Note: this function returns the same result as getNodePathForTitlePath,
    * but takes ref-id's as parameters.
    *
    * This function differs from getPathFull, in the following aspects:
    * - The title of an object is not translated into the language of the user
    * - This function is significantly faster than getPathFull.
    *
    * @access	public
    * @param	integer	node_id of endnode
    * @param	integer	node_id of startnode (optional)
    * @return	array	ordered path info (depth,parent,child,obj_id,type,title)
    *               or null, if the node_id can not be converted into a node path.
    */
    public function getNodePath($a_endnode_id, $a_startnode_id = 0)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $pathIds = $this->getPathId($a_endnode_id, $a_startnode_id);

        // Abort if no path ids were found
        if (count($pathIds) == 0) {
            return null;
        }

        
        $types = array();
        $data = array();
        for ($i = 0; $i < count($pathIds); $i++) {
            $types[] = 'integer';
            $data[] = $pathIds[$i];
        }

        $query = 'SELECT t.depth,t.parent,t.child,d.obj_id,d.type,d.title ' .
            'FROM ' . $this->table_tree . ' t ' .
            'JOIN ' . $this->table_obj_reference . ' r ON r.ref_id = t.child ' .
            'JOIN ' . $this->table_obj_data . ' d ON d.obj_id = r.obj_id ' .
            'WHERE ' . $ilDB->in('t.child', $data, false, 'integer') . ' ' .
            'ORDER BY t.depth ';
            
        $res = $ilDB->queryF($query, $types, $data);

        $titlePath = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $titlePath[] = $row;
        }
        return $titlePath;
    }
    // END WebDAV: getNodePath function added

    /**
    * check consistence of tree
    * all left & right values are checked if they are exists only once
    * @access	public
    * @return	boolean		true if tree is ok; otherwise throws error object
    * @throws ilInvalidTreeStructureException
    */
    public function checkTree()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $types = array('integer');
        $query = 'SELECT lft,rgt FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->tree_pk . ' = %s ';
        
        $res = $ilDB->queryF($query, $types, array($this->tree_id));
        while ($row = $ilDB->fetchObject($res)) {
            $lft[] = $row->lft;
            $rgt[] = $row->rgt;
        }

        $all = array_merge($lft, $rgt);
        $uni = array_unique($all);

        if (count($all) != count($uni)) {
            $message = 'Tree is corrupted!';

            $this->log->error($message);
            throw new ilInvalidTreeStructureException($message);
        }

        return true;
    }

    /**
     * check, if all childs of tree nodes exist in object table
     *
     * @param bool $a_no_zero_child
     * @return bool
     * @throws ilInvalidTreeStructureException
    */
    public function checkTreeChilds($a_no_zero_child = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                'WHERE ' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft';
        $r1 = $ilDB->queryF($query, array('integer'), array($this->tree_id));
        
        while ($row = $ilDB->fetchAssoc($r1)) {
            //echo "tree:".$row[$this->tree_pk].":lft:".$row["lft"].":rgt:".$row["rgt"].":child:".$row["child"].":<br>";
            if (($row["child"] == 0) && $a_no_zero_child) {
                $message = "Tree contains child with ID 0!";
                $this->log->error($message);
                throw new ilInvalidTreeStructureException($message);
            }

            if ($this->table_obj_reference) {
                // get object reference data
                $query = 'SELECT * FROM ' . $this->table_obj_reference . ' WHERE ' . $this->ref_pk . ' = %s ';
                $r2 = $ilDB->queryF($query, array('integer'), array($row['child']));
                
                //echo "num_childs:".$r2->numRows().":<br>";
                if ($r2->numRows() == 0) {
                    $message = "No Object-to-Reference entry found for ID " . $row["child"] . "!";
                    $this->log->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
                if ($r2->numRows() > 1) {
                    $message = "More Object-to-Reference entries found for ID " . $row["child"] . "!";
                    $this->log->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }

                // get object data
                $obj_ref = $ilDB->fetchAssoc($r2);

                $query = 'SELECT * FROM ' . $this->table_obj_data . ' WHERE ' . $this->obj_pk . ' = %s';
                $r3 = $ilDB->queryF($query, array('integer'), array($obj_ref[$this->obj_pk]));
                if ($r3->numRows() == 0) {
                    $message = " No child found for ID " . $obj_ref[$this->obj_pk] . "!";
                    $this->log->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
                if ($r3->numRows() > 1) {
                    $message = "More childs found for ID " . $obj_ref[$this->obj_pk] . "!";
                    $this->log->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
            } else {
                // get only object data
                $query = 'SELECT * FROM ' . $this->table_obj_data . ' WHERE ' . $this->obj_pk . ' = %s';
                $r2 = $ilDB->queryF($query, array('integer'), array($row['child']));
                //echo "num_childs:".$r2->numRows().":<br>";
                if ($r2->numRows() == 0) {
                    $message = "No child found for ID " . $row["child"] . "!";
                    $this->log->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
                if ($r2->numRows() > 1) {
                    $message = "More childs found for ID " . $row["child"] . "!";
                    $this->log->error($message);
                    throw new ilInvalidTreeStructureException($message);
                }
            }
        }

        return true;
    }

    /**
     * Return the current maximum depth in the tree
     * @access	public
     * @return	integer	max depth level of tree
     */
    public function getMaximumDepth()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT MAX(depth) depth FROM ' . $this->table_tree;
        $res = $ilDB->query($query);
        
        $row = $ilDB->fetchAssoc($res);
        return $row['depth'];
    }

    /**
    * return depth of a node in tree
    * @access	private
    * @param	integer		node_id of parent's node_id
    * @return	integer		depth of node in tree
    */
    public function getDepth($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($a_node_id) {
            if ($this->__isMainTree()) {
                $query = 'SELECT depth FROM ' . $this->table_tree . ' ' .
                    'WHERE child = %s ';
                $res = $ilDB->queryF($query, array('integer'), array($a_node_id));
                $row = $ilDB->fetchObject($res);
            } else {
                $query = 'SELECT depth FROM ' . $this->table_tree . ' ' .
                'WHERE child = %s ' .
                'AND ' . $this->tree_pk . ' = %s ';
                $res = $ilDB->queryF($query, array('integer','integer'), array($a_node_id,$this->tree_id));
                $row = $ilDB->fetchObject($res);
            }

            return $row->depth;
        } else {
            return 1;
        }
    }
    
    /**
     * return all columns of tabel tree
     * @param type $a_node_id
     * @return array of table column => values
     *
     * @throws InvalidArgumentException
     */
    public function getNodeTreeData($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$a_node_id) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Missing or empty parameter $a_node_id: ' . $a_node_id);
        }
        
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                'WHERE child = ' . $ilDB->quote($a_node_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            return $row;
        }
        return array();
    }


    /**
    * get all information of a node.
    * get data of a specific node from tree and object_data
    * @access	public
    * @param	integer		node id
    * @return	array		2-dim (int/str) node_data
    * @throws InvalidArgumentException
    */
    // BEGIN WebDAV: Pass tree id to this method
    //function getNodeData($a_node_id)
    public function getNodeData($a_node_id, $a_tree_pk = null)
    // END PATCH WebDAV: Pass tree id to this method
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id)) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException("No node_id given!");
        }
        if ($this->__isMainTree()) {
            if ($a_node_id < 1) {
                $message = 'No valid parameter given! $a_node_id: %s' . $a_node_id;

                $this->log->error($message);
                throw new InvalidArgumentException($message);
            }
        }

        // BEGIN WebDAV: Pass tree id to this method
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $a_tree_pk === null ? $this->tree_id : $a_tree_pk));
        // END WebDAV: Pass tree id to this method
        $row = $ilDB->fetchAssoc($res);
        $row[$this->tree_pk] = $this->tree_id;

        return $this->fetchNodeData($row);
    }
    
    /**
    * get data of parent node from tree and object_data
    * @access	private
    * @param	object	db	db result object containing node_data
    * @return	array		2-dim (int/str) node_data
    * TODO: select description twice for compability. Please use 'desc' in future only
    */
    public function fetchNodeData($a_row)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $lng = $DIC['lng'];
        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];

        //$ilBench->start("Tree", "fetchNodeData_getRow");
        $data = $a_row;
        $data["desc"] = $a_row["description"];  // for compability
        //$ilBench->stop("Tree", "fetchNodeData_getRow");

        // multilingual support systemobjects (sys) & categories (db)
        //$ilBench->start("Tree", "fetchNodeData_readDefinition");
        if (is_object($objDefinition)) {
            $translation_type = $objDefinition->getTranslationType($data["type"]);
        }
        //$ilBench->stop("Tree", "fetchNodeData_readDefinition");

        if ($translation_type == "sys") {
            //$ilBench->start("Tree", "fetchNodeData_getLangData");
            if ($data["type"] == "rolf" and $data["obj_id"] != ROLE_FOLDER_ID) {
                $data["description"] = $lng->txt("obj_" . $data["type"] . "_local_desc") . $data["title"] . $data["desc"];
                $data["desc"] = $lng->txt("obj_" . $data["type"] . "_local_desc") . $data["title"] . $data["desc"];
                $data["title"] = $lng->txt("obj_" . $data["type"] . "_local");
            } else {
                $data["title"] = $lng->txt("obj_" . $data["type"]);
                $data["description"] = $lng->txt("obj_" . $data["type"] . "_desc");
                $data["desc"] = $lng->txt("obj_" . $data["type"] . "_desc");
            }
            //$ilBench->stop("Tree", "fetchNodeData_getLangData");
        } elseif ($translation_type == "db") {

            // Try to retrieve object translation from cache
            if ($this->isCacheUsed() &&
                array_key_exists($data["obj_id"] . '.' . $lang_code, $this->translation_cache)) {
                $key = $data["obj_id"] . '.' . $lang_code;
                $data["title"] = $this->translation_cache[$key]['title'];
                $data["description"] = $this->translation_cache[$key]['description'];
                $data["desc"] = $this->translation_cache[$key]['desc'];
            } else {
                // Object translation is not in cache, read it from database
                //$ilBench->start("Tree", "fetchNodeData_getTranslation");
                $query = 'SELECT title,description FROM object_translation ' .
                    'WHERE obj_id = %s ' .
                    'AND lang_code = %s ' .
                    'AND NOT lang_default = %s';

                $res = $ilDB->queryF($query, array('integer','text','integer'), array(
                    $data['obj_id'],
                    $this->lang_code,
                    1));
                $row = $ilDB->fetchObject($res);

                if ($row) {
                    $data["title"] = $row->title;
                    $data["description"] = ilUtil::shortenText($row->description, ilObject::DESC_LENGTH, true);
                    $data["desc"] = $row->description;
                }
                //$ilBench->stop("Tree", "fetchNodeData_getTranslation");

                // Store up to 1000 object translations in cache
                if ($this->isCacheUsed() && count($this->translation_cache) < 1000) {
                    $key = $data["obj_id"] . '.' . $lang_code;
                    $this->translation_cache[$key] = array();
                    $this->translation_cache[$key]['title'] = $data["title"] ;
                    $this->translation_cache[$key]['description'] = $data["description"];
                    $this->translation_cache[$key]['desc'] = $data["desc"];
                }
            }
        }

        // TODO: Handle this switch by module.xml definitions
        if ($data['type'] == 'crsr' or $data['type'] == 'catr' or $data['type'] == 'grpr' or $data['type'] === 'prgr') {
            include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
            $data['title'] = ilContainerReference::_lookupTitle($data['obj_id']);
        }

        return $data ? $data : array();
    }

    /**
     * Get translation data from object cache (trigger in object cache on preload)
     *
     * @param	array	$a_obj_ids		object ids
     */
    protected function fetchTranslationFromObjectDataCache($a_obj_ids)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        if ($this->isCacheUsed() && is_array($a_obj_ids) && is_object($ilObjDataCache)) {
            foreach ($a_obj_ids as $id) {
                $this->translation_cache[$id . '.']['title'] = $ilObjDataCache->lookupTitle($id);
                $this->translation_cache[$id . '.']['description'] = $ilObjDataCache->lookupDescription($id);
                ;
                $this->translation_cache[$id . '.']['desc'] =
                    $this->translation_cache[$id . '.']['description'];
            }
        }
    }


    /**
    * get all information of a node.
    * get data of a specific node from tree and object_data
    * @access	public
    * @param	integer		node id
    * @return	boolean		true, if node id is in tree
    */
    public function isInTree($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!isset($a_node_id)) {
            return false;
            #$this->ilErr->raiseError(get_class($this)."::getNodeData(): No node_id given! ",$this->ilErr->WARNING);
        }
        // is in tree cache
        if ($this->isCacheUsed() && isset($this->in_tree_cache[$a_node_id])) {
            #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Using in tree cache '.$a_node_id);
            //echo "<br>in_tree_hit";
            return $this->in_tree_cache[$a_node_id];
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s';
            
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $this->tree_id));

        if ($res->numRows() > 0) {
            if ($this->__isMainTree()) {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Storing in tree cache '.$a_node_id.' = true');
                $this->in_tree_cache[$a_node_id] = true;
            }
            return true;
        } else {
            if ($this->__isMainTree()) {
                #$GLOBALS['DIC']['ilLog']->write(__METHOD__.': Storing in tree cache '.$a_node_id.' = false');
                $this->in_tree_cache[$a_node_id] = false;
            }
            return false;
        }
    }

    /**
    * get data of parent node from tree and object_data
    * @access	public
    * @param	integer		node id
    * @return	array
    * @throws InvalidArgumentException
    */
    public function getParentNodeData($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        if (!isset($a_node_id)) {
            $ilLog->logStack();
            throw new InvalidArgumentException(__METHOD__ . ': No node_id given!');
        }

        if ($this->table_obj_reference) {
            // Use inner join instead of left join to improve performance
            $innerjoin = "JOIN " . $this->table_obj_reference . " ON v.child=" . $this->table_obj_reference . "." . $this->ref_pk . " " .
                        "JOIN " . $this->table_obj_data . " ON " . $this->table_obj_reference . "." . $this->obj_pk . "=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        } else {
            // Use inner join instead of left join to improve performance
            $innerjoin = "JOIN " . $this->table_obj_data . " ON v.child=" . $this->table_obj_data . "." . $this->obj_pk . " ";
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' s, ' . $this->table_tree . ' v ' .
            $innerjoin .
            'WHERE s.child = %s ' .
            'AND s.parent = v.child ' .
            'AND s.' . $this->tree_pk . ' = %s ' .
            'AND v.' . $this->tree_pk . ' = %s';
        $res = $ilDB->queryF($query, array('integer','integer','integer'), array(
            $a_node_id,
            $this->tree_id,
            $this->tree_id));
        $row = $ilDB->fetchAssoc($res);
        return $this->fetchNodeData($row);
    }

    /**
    * checks if a node is in the path of an other node
    * @access	public
    * @param	integer		object id of start node
    * @param    integer     object id of query node
    * @return	integer		number of entries
    */
    public function isGrandChild($a_startnode_id, $a_querynode_id)
    {
        return $this->getRelation($a_startnode_id, $a_querynode_id) == self::RELATION_PARENT;
    }

    /**
    * create a new tree
    * to do: ???
    * @param	integer		a_tree_id: obj_id of object where tree belongs to
    * @param	integer		a_node_id: root node of tree (optional; default is tree_id itself)
    * @return	boolean		true on success
    * @throws InvalidArgumentException
    * @access	public
    */
    public function addTree($a_tree_id, $a_node_id = -1)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // FOR SECURITY addTree() IS NOT ALLOWED ON MAIN TREE
        if ($this->__isMainTree()) {
            $message = sprintf(
                'Operation not allowed on main tree! $a_tree_if: %s $a_node_id: %s',
                $a_tree_id,
                $a_node_id
            );
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        if (!isset($a_tree_id)) {
            $message = "No tree_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        if ($a_node_id <= 0) {
            $a_node_id = $a_tree_id;
        }

        $query = 'INSERT INTO ' . $this->table_tree . ' (' .
            $this->tree_pk . ', child,parent,lft,rgt,depth) ' .
            'VALUES ' .
            '(%s,%s,%s,%s,%s,%s)';
        $res = $ilDB->manipulateF($query, array('integer','integer','integer','integer','integer','integer'), array(
            $a_tree_id,
            $a_node_id,
            0,
            1,
            2,
            1));

        return true;
    }

    /**
     * get nodes by type
     * @param	integer		a_tree_id: obj_id of object where tree belongs to
     * @param	integer		a_type_id: type of object
     * @access	public
     * @throws InvalidArgumentException
     * @return array
     * @deprecated since 4.4.0
     */
    public function getNodeDataByType($a_type)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_type) or (!is_string($a_type))) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Type not given or wrong datatype');
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_obj_data . '.type = ' . $this->ilDB->quote($a_type, 'text') .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = ' . $this->ilDB->quote($this->tree_id, 'integer');

        $res = $ilDB->query($query);
        $data = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $data[] = $this->fetchNodeData($row);
        }

        return $data;
    }

    /**
    * remove an existing tree
    *
    * @param	integer		a_tree_id: tree to be removed
    * @return	boolean		true on success
    * @access	public
    * @throws InvalidArgumentException
    */
    public function removeTree($a_tree_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // OPERATION NOT ALLOWED ON MAIN TREE
        if ($this->__isMainTree()) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Operation not allowed on main tree');
        }
        if (!$a_tree_id) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('Missing parameter tree id');
        }

        $query = 'DELETE FROM ' . $this->table_tree .
            ' WHERE ' . $this->tree_pk . ' = %s ';
        $ilDB->manipulateF($query, array('integer'), array($a_tree_id));
        return true;
    }
    
    /**
     * Move node to trash bin
     * @param int $a_node_id
     * @param bool $a_set_deleted
     * @param int deleted_by user_id
     * @return bool
     * @throws InvalidArgumentException
     */
    public function moveToTrash($a_node_id, $a_set_deleted = false, $a_deleted_by = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $user = $DIC->user();
        if(!$a_deleted_by) {
            $a_deleted_by = $user->getId();
        }

        if (!$a_node_id) {
            $this->log->logStack(ilLogLevel::ERROR);
            throw new InvalidArgumentException('No valid parameter given! $a_node_id: ' . $a_node_id);
        }


        $query = $this->getTreeImplementation()->getSubTreeQuery($this->getNodeTreeData($a_node_id), '', false);
        $res = $ilDB->query($query);

        $subnodes = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $subnodes[] = $row['child'];
        }

        if (!count($subnodes)) {
            // Possibly already deleted
            return false;
        }

        if ($a_set_deleted) {
            ilObject::setDeletedDates($subnodes, $a_deleted_by);
        }

        // netsted set <=> mp
        $this->getTreeImplementation()->moveToTrash($a_node_id);

        return true;
    }

    /**
     * This is a wrapper for isSaved() with a more useful name
     * @param int $a_node_id
     */
    public function isDeleted($a_node_id)
    {
        return $this->isSaved($a_node_id);
    }

    /**
     * Use method isDeleted
     * check if node is saved
     * @deprecated since 4.4.0
     */
    public function isSaved($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // is saved cache
        if ($this->isCacheUsed() && isset($this->is_saved_cache[$a_node_id])) {
            //echo "<br>issavedhit";
            return $this->is_saved_cache[$a_node_id];
        }

        $query = 'SELECT ' . $this->tree_pk . ' FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ';
        $res = $ilDB->queryF($query, array('integer'), array($a_node_id));
        $row = $ilDB->fetchAssoc($res);

        if ($row[$this->tree_pk] < 0) {
            if ($this->__isMainTree()) {
                $this->is_saved_cache[$a_node_id] = true;
            }
            return true;
        } else {
            if ($this->__isMainTree()) {
                $this->is_saved_cache[$a_node_id] = false;
            }
            return false;
        }
    }

    /**
     * Preload deleted information
     *
     * @param array nodfe ids
     * @return bool
     */
    public function preloadDeleted($a_node_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_node_ids) || !$this->isCacheUsed()) {
            return;
        }

        $query = 'SELECT ' . $this->tree_pk . ', child FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $ilDB->in("child", $a_node_ids, false, "integer");

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if ($row[$this->tree_pk] < 0) {
                if ($this->__isMainTree()) {
                    $this->is_saved_cache[$row["child"]] = true;
                }
            } else {
                if ($this->__isMainTree()) {
                    $this->is_saved_cache[$row["child"]] = false;
                }
            }
        }
    }


    /**
    * get data saved/deleted nodes
    * @return	array	data
    * @param	integer	id of parent object of saved object
    * @access	public
    * @throws InvalidArgumentException
    */
    public function getSavedNodeData($a_parent_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_parent_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.' . $this->tree_pk . ' < %s ' .
            'AND ' . $this->table_tree . '.parent = %s';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            0,
            $a_parent_id));

        while ($row = $ilDB->fetchAssoc($res)) {
            $saved[] = $this->fetchNodeData($row);
        }

        return $saved ? $saved : array();
    }
    
    /**
    * get object id of saved/deleted nodes
    * @return	array	data
    * @param	array	object ids to check
    * @access	public
    */
    public function getSavedNodeObjIds(array $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT ' . $this->table_obj_data . '.obj_id FROM ' . $this->table_tree . ' ' .
            $this->buildJoin() .
            'WHERE ' . $this->table_tree . '.' . $this->tree_pk . ' < ' . $ilDB->quote(0, 'integer') . ' ' .
            'AND ' . $ilDB->in($this->table_obj_data . '.obj_id', $a_obj_ids, '', 'integer');
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $saved[] = $row['obj_id'];
        }

        return $saved ? $saved : array();
    }

    /**
    * get parent id of given node
    * @access	public
    * @param	integer	node id
    * @return	integer	parent id
    * @throws InvalidArgumentException
    */
    public function getParentId($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        if ($this->__isMainTree()) {
            $query = 'SELECT parent FROM ' . $this->table_tree . ' ' .
                'WHERE child = %s ';
            $res = $ilDB->queryF(
                $query,
                ['integer'],
                [$a_node_id]
            );
        } else {
            $query = 'SELECT parent FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ' .
            'AND ' . $this->tree_pk . ' = %s ';
            $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $this->tree_id));
        }

        $row = $ilDB->fetchObject($res);
        return $row->parent;
    }

    /**
    * get left value of given node
    * @access	public
    * @param	integer	node id
    * @return	integer	left value
    * @throws InvalidArgumentException
    */
    public function getLeftValue($a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        $query = 'SELECT lft FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ' .
            'AND ' . $this->tree_pk . ' = %s ';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $this->tree_id));
        $row = $ilDB->fetchObject($res);
        return $row->lft;
    }

    /**
    * get sequence number of node in sibling sequence
    * @access	public
    * @param	array		node
    * @return	integer		sequence number
    * @throws InvalidArgumentException
    */
    public function getChildSequenceNumber($a_node, $type = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }
        
        if ($type) {
            $query = 'SELECT count(*) cnt FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft <= %s ' .
                'AND type = %s ' .
                'AND parent = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';

            $res = $ilDB->queryF($query, array('integer','text','integer','integer'), array(
                $a_node['lft'],
                $type,
                $a_node['parent'],
                $this->tree_id));
        } else {
            $query = 'SELECT count(*) cnt FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft <= %s ' .
                'AND parent = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';

            $res = $ilDB->queryF($query, array('integer','integer','integer'), array(
                $a_node['lft'],
                $a_node['parent'],
                $this->tree_id));
        }
        $row = $ilDB->fetchAssoc($res);
        return $row["cnt"];
    }

    /**
    * read root id from database
    * @param root_id
    * @access public
    * @return int new root id
    */
    public function readRootId()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT child FROM ' . $this->table_tree . ' ' .
            'WHERE parent = %s ' .
            'AND ' . $this->tree_pk . ' = %s ';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            0,
            $this->tree_id));
        $row = $ilDB->fetchObject($res);
        $this->root_id = $row->child;
        return $this->root_id;
    }

    /**
    * get the root id of tree
    * @access	public
    * @return	integer	root node id
    */
    public function getRootId()
    {
        return $this->root_id;
    }
    public function setRootId($a_root_id)
    {
        $this->root_id = $a_root_id;
    }

    /**
    * get tree id
    * @access	public
    * @return	integer	tree id
    */
    public function getTreeId()
    {
        return $this->tree_id;
    }

    /**
    * set tree id
    * @access	public
    * @return	integer	tree id
    */
    public function setTreeId($a_tree_id)
    {
        $this->tree_id = $a_tree_id;
    }

    /**
    * get node data of successor node
    *
    * @access	public
    * @param	integer		node id
    * @return	array		node data array
    * @throws InvalidArgumentException
    */
    public function fetchSuccessorNode($a_node_id, $a_type = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        // get lft value for current node
        $query = 'SELECT lft FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $this->tree_id));
        $curr_node = $ilDB->fetchAssoc($res);
        
        if ($a_type) {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft > %s ' .
                'AND ' . $this->table_obj_data . '.type = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft ';
            $ilDB->setLimit(1);
            $res = $ilDB->queryF($query, array('integer','text','integer'), array(
                $curr_node['lft'],
                $a_type,
                $this->tree_id));
        } else {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft > %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft ';
            $ilDB->setLimit(1);
            $res = $ilDB->queryF($query, array('integer','integer'), array(
                $curr_node['lft'],
                $this->tree_id));
        }

        if ($res->numRows() < 1) {
            return false;
        } else {
            $row = $ilDB->fetchAssoc($res);
            return $this->fetchNodeData($row);
        }
    }

    /**
    * get node data of predecessor node
    *
    * @access	public
    * @param	integer		node id
    * @return	array		node data array
    * @throws InvalidArgumentException
    */
    public function fetchPredecessorNode($a_node_id, $a_type = "")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!isset($a_node_id)) {
            $message = "No node_id given!";
            $this->log->error($message);
            throw new InvalidArgumentException($message);
        }

        // get lft value for current node
        $query = 'SELECT lft FROM ' . $this->table_tree . ' ' .
            'WHERE ' . $this->table_tree . '.child = %s ' .
            'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $this->tree_id));

        $curr_node = $ilDB->fetchAssoc($res);
        
        if ($a_type) {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft < %s ' .
                'AND ' . $this->table_obj_data . '.type = %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft DESC';
            $ilDB->setLimit(1);
            $res = $ilDB->queryF($query, array('integer','text','integer'), array(
                $curr_node['lft'],
                $a_type,
                $this->tree_id));
        } else {
            $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
                $this->buildJoin() .
                'WHERE lft < %s ' .
                'AND ' . $this->table_tree . '.' . $this->tree_pk . ' = %s ' .
                'ORDER BY lft DESC';
            $ilDB->setLimit(1);
            $res = $ilDB->queryF($query, array('integer','integer'), array(
                $curr_node['lft'],
                $this->tree_id));
        }
        
        if ($res->numRows() < 1) {
            return false;
        } else {
            $row = $ilDB->fetchAssoc($res);
            return $this->fetchNodeData($row);
        }
    }

    /**
    * Wrapper for renumber. This method locks the table tree
    * (recursive)
    * @access	public
    * @param	integer	node_id where to start (usually the root node)
    * @param	integer	first left value of start node (usually 1)
    * @return	integer	current left value of recursive call
    */
    public function renumber($node_id = 1, $i = 1)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $renumber_callable = function (ilDBInterface $ilDB) use ($node_id,$i,&$return) {
            $return = $this->__renumber($node_id, $i);
        };

        // LOCKED ###################################
        if ($this->__isMainTree()) {
            $ilAtomQuery = $ilDB->buildAtomQuery();
            $ilAtomQuery->addTableLock($this->table_tree);

            $ilAtomQuery->addQueryCallable($renumber_callable);
            $ilAtomQuery->run();
        } else {
            $renumber_callable($ilDB);
        }
        return $return;
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
    public function __renumber($node_id = 1, $i = 1)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if ($this->isRepositoryTree()) {
            $query = 'UPDATE ' . $this->table_tree . ' SET lft = %s WHERE child = %s';
            $ilDB->manipulateF(
                $query,
                array('integer','integer'),
                array(
                $i,
                $node_id)
            );
        } else {
            $query = 'UPDATE ' . $this->table_tree . ' SET lft = %s WHERE child = %s AND tree = %s';
            $ilDB->manipulateF(
                $query,
                array('integer','integer','integer'),
                array(
            $i,
            $node_id,
                $this->tree_id)
            );
        }

        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE parent = ' . $ilDB->quote($node_id, 'integer') . ' ' .
            'ORDER BY lft';
        $res = $ilDB->query($query);

        $childs = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $childs[] = $row->child;
        }

        foreach ($childs as $child) {
            $i = $this->__renumber($child, $i + 1);
        }
        $i++;
        
        // Insert a gap at the end of node, if the node has children
        if (count($childs) > 0) {
            $i += $this->gap * 2;
        }
        
        
        if ($this->isRepositoryTree()) {
            $query = 'UPDATE ' . $this->table_tree . ' SET rgt = %s WHERE child = %s';
            $res = $ilDB->manipulateF(
                $query,
                array('integer','integer'),
                array(
                $i,
                $node_id)
            );
        } else {
            $query = 'UPDATE ' . $this->table_tree . ' SET rgt = %s WHERE child = %s AND tree = %s';
            $res = $ilDB->manipulateF($query, array('integer','integer', 'integer'), array(
            $i,
            $node_id,
            $this->tree_id));
        }
        return $i;
    }


    /**
    * Check for parent type
    * e.g check if a folder (ref_id 3) is in a parent course obj => checkForParentType(3,'crs');
    *
    * @access	public
    * @param	integer	ref_id
    * @param	string type
    * @return	mixed false if item is not in tree,
    * 				  int (object ref_id) > 0 if path container course, int 0 if pathc does not contain the object type
    */
    public function checkForParentType($a_ref_id, $a_type, $a_exclude_source_check = false)
    {
        // #12577
        $cache_key = $a_ref_id . '.' . $a_type . '.' . ((int) $a_exclude_source_check);
        
        // Try to return a cached result
        if ($this->isCacheUsed() &&
            array_key_exists($cache_key, $this->parent_type_cache)) {
            return $this->parent_type_cache[$cache_key];
        }
        
        // Store up to 1000 results in cache
        $do_cache = ($this->__isMainTree() && count($this->parent_type_cache) < 1000);

        // ref_id is not in tree
        if (!$this->isInTree($a_ref_id)) {
            if ($do_cache) {
                $this->parent_type_cache[$cache_key] = false;
            }
            return false;
        }
        
        $path = array_reverse($this->getPathFull($a_ref_id));

        // remove first path entry as it is requested node
        if ($a_exclude_source_check) {
            array_shift($path);
        }

        foreach ($path as $node) {
            // found matching parent
            if ($node["type"] == $a_type) {
                if ($do_cache) {
                    $this->parent_type_cache[$cache_key] = $node["child"];
                }
                return $node["child"];
            }
        }
        
        if ($do_cache) {
            $this->parent_type_cache[$cache_key] = false;
        }
        return 0;
    }

    /**
    * STATIC METHOD
    * Removes a single entry from a tree. The tree structure is NOT updated!
    *
    * @access	public
    * @param	integer	tree id
    * @param	integer	child id
    * @param	string	db_table name. default is 'tree' (optional)
    * @throws InvalidArgumentException
    */
    public static function _removeEntry($a_tree, $a_child, $a_db_table = "tree")
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if ($a_db_table === 'tree') {
            if ($a_tree == 1 and $a_child == ROOT_FOLDER_ID) {
                $message = sprintf(
                    'Tried to delete root node! $a_tree: %s $a_child: %s',
                    $a_tree,
                    $a_child
                );
                ilLoggerFactory::getLogger('tree')->error($message);
                throw new InvalidArgumentException($message);
            }
        }
        
        $query = 'DELETE FROM ' . $a_db_table . ' ' .
            'WHERE tree = %s ' .
            'AND child = %s ';
        $res = $ilDB->manipulateF($query, array('integer','integer'), array(
            $a_tree,
            $a_child));
    }
    
    /**
    * Check if operations are done on main tree
    *
    * @access	private
    * @return boolean
    */
    public function __isMainTree()
    {
        return $this->table_tree === 'tree';
    }

    /**
     * Check for deleteTree()
     * compares a subtree of a given node by checking lft, rgt against parent relation
     *
     * @access	private
     * @param array node data from ilTree::getNodeData()
     * @return boolean
     *
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
    */
    public function __checkDelete($a_node)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        
        $query = $this->getTreeImplementation()->getSubTreeQuery($a_node, array(), false);
        $this->log->debug($query);
        $res = $ilDB->query($query);
        
        $counter = (int) $lft_childs = array();
        while ($row = $ilDB->fetchObject($res)) {
            $lft_childs[$row->child] = $row->parent;
            ++$counter;
        }

        // CHECK FOR DUPLICATE CHILD IDS
        if ($counter != count($lft_childs)) {
            $message = 'Duplicate entries for "child" in maintree! $a_node_id: ' . $a_node['child'];

            $this->log->error($message);
            throw new ilInvalidTreeStructureException($message);
        }

        // GET SUBTREE BY PARENT RELATION
        $parent_childs = array();
        $this->__getSubTreeByParentRelation($a_node['child'], $parent_childs);
        $this->__validateSubtrees($lft_childs, $parent_childs);

        return true;
    }

    /**
     *
     * @global type $ilDB
     * @param type $a_node_id
     * @param type $parent_childs
     * @return boolean
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __getSubTreeByParentRelation($a_node_id, &$parent_childs)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // GET PARENT ID
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE child = %s ' .
            'AND tree = %s ';
        $res = $ilDB->queryF($query, array('integer','integer'), array(
            $a_node_id,
            $this->tree_id));

        $counter = 0;
        while ($row = $ilDB->fetchObject($res)) {
            $parent_childs[$a_node_id] = $row->parent;
            ++$counter;
        }
        // MULTIPLE ENTRIES
        if ($counter > 1) {
            $message = 'Multiple entries in maintree! $a_node_id: ' . $a_node_id;

            $this->log->error($message);
            throw new ilInvalidTreeStructureException($message);
        }

        // GET ALL CHILDS
        $query = 'SELECT * FROM ' . $this->table_tree . ' ' .
            'WHERE parent = %s ';
        $res = $ilDB->queryF($query, array('integer'), array($a_node_id));

        while ($row = $ilDB->fetchObject($res)) {
            // RECURSION
            $this->__getSubTreeByParentRelation($row->child, $parent_childs);
        }
        return true;
    }

    /**
     * @param $lft_childs
     * @param $parent_childs
     * @return bool
     * @throws ilInvalidTreeStructureException
     * @deprecated since 4.4.0
     */
    public function __validateSubtrees(&$lft_childs, $parent_childs)
    {
        // SORT BY KEY
        ksort($lft_childs);
        ksort($parent_childs);

        $this->log->debug('left childs ' . print_r($lft_childs, true));
        $this->log->debug('parent childs ' . print_r($parent_childs, true));

        if (count($lft_childs) != count($parent_childs)) {
            $message = '(COUNT) Tree is corrupted! Left/Right subtree does not comply with parent relation';
            $this->log->error($message);
            throw new ilInvalidTreeStructureException($message);
        }
        

        foreach ($lft_childs as $key => $value) {
            if ($parent_childs[$key] != $value) {
                $message = '(COMPARE) Tree is corrupted! Left/Right subtree does not comply with parent relation';
                $this->log->error($message);
                throw new ilInvalidTreeStructureException($message);
            }
            if ($key == ROOT_FOLDER_ID) {
                $message = '(ROOT_FOLDER) Tree is corrupted! Tried to delete root folder';
                $this->log->error($message);
                throw new ilInvalidTreeStructureException($message);
            }
        }
        return true;
    }
    
    /**
     * Move Tree Implementation
     *
     * @access	public
     * @param int source ref_id
     * @param int target ref_id
     * @param int location IL_LAST_NODE or IL_FIRST_NODE (IL_FIRST_NODE not implemented yet)
     * @return bool
     */
    public function moveTree($a_source_id, $a_target_id, $a_location = self::POS_LAST_NODE)
    {
        $old_parent_id = $this->getParentId($a_source_id);
        $this->getTreeImplementation()->moveTree($a_source_id, $a_target_id, $a_location);
        if (isset($GLOBALS['DIC']["ilAppEventHandler"]) && $this->__isMainTree()) {
            $GLOBALS['DIC']['ilAppEventHandler']->raise(
                "Services/Tree",
                "moveTree",
                array(
                        'tree' => $this->table_tree,
                        'source_id' => $a_source_id,
                        'target_id' => $a_target_id,
                        'old_parent_id' => $old_parent_id
                        )
            );
        }
        return true;
    }
    
    
    
    
    /**
     * This method is used for change existing objects
     * and returns all necessary information for this action.
     * The former use of ilTree::getSubtree needs to much memory.
     * @param ref_id ref_id of source node
     * @return
     */
    public function getRbacSubtreeInfo($a_endnode_id)
    {
        return $this->getTreeImplementation()->getSubtreeInfo($a_endnode_id);
    }
    

    /**
     * Get tree subtree query
     * @param type $a_node_id
     * @param type $a_types
     * @param type $a_force_join_reference
     * @return type
     */
    public function getSubTreeQuery($a_node_id, $a_fields = array(), $a_types = '', $a_force_join_reference = false)
    {
        return $this->getTreeImplementation()->getSubTreeQuery(
            $this->getNodeTreeData($a_node_id),
            $a_types,
            $a_force_join_reference,
            $a_fields
        );
    }
    
    /**
     * @inheritdoc
     */
    public function getTrashSubTreeQuery($a_node_id, $a_fields = [], $a_types = '', $a_force_join_reference = false)
    {
        return $this->getTreeImplementation()->getTrashSubTreeQuery(
            $this->getNodeTreeData($a_node_id),
            $a_types,
            $a_force_join_reference,
            $a_fields
        );
    }
    
    
    /**
     * get all node ids in the subtree under specified node id, filter by object ids
     *
     * @param int $a_node_id
     * @param array $a_obj_ids
     * @param array $a_fields
     * @return	array
     */
    public function getSubTreeFilteredByObjIds($a_node_id, array $a_obj_ids, array $a_fields = array())
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $node = $this->getNodeData($a_node_id);
        if (!sizeof($node)) {
            return;
        }
        
        $res = array();
        
        $query = $this->getTreeImplementation()->getSubTreeQuery($node, '', true, array($this->ref_pk));
        
        $fields = '*';
        if (count($a_fields)) {
            $fields = implode(',', $a_fields);
        }
        
        $query = "SELECT " . $fields .
            " FROM " . $this->getTreeTable() .
            " " . $this->buildJoin() .
            " WHERE " . $this->getTableReference() . "." . $this->ref_pk . " IN (" . $query . ")" .
            " AND " . $ilDB->in($this->getObjectDataTable() . "." . $this->obj_pk, $a_obj_ids, "", "integer");
        $set = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }
        
        return $res;
    }
    
    public function deleteNode($a_tree_id, $a_node_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        
        $query = 'DELETE FROM tree where ' .
                'child = ' . $ilDB->quote($a_node_id, 'integer') . ' ' .
                'AND tree = ' . $ilDB->quote($a_tree_id, 'integer');
        $ilDB->manipulate($query);

        $ilAppEventHandler->raise(
            "Services/Tree",
            "deleteNode",
            array('tree' => $this->table_tree,
                          'node_id' => $a_node_id,
                          'tree_id' => $a_tree_id
                    )
                );
    }

    /**
     * Lookup object types in trash
     * @global type $ilDB
     * @return type
     */
    public function lookupTrashedObjectTypes()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT DISTINCT(o.type) ' . $ilDB->quoteIdentifier('type') . ' FROM tree t JOIN object_reference r ON child = r.ref_id ' .
                'JOIN object_data o on r.obj_id = o.obj_id ' .
                'WHERE tree < ' . $ilDB->quote(0, 'integer') . ' ' .
                'AND child = -tree ' .
                'GROUP BY o.type';
        $res = $ilDB->query($query);
        
        $types_deleted = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $types_deleted[] = $row->type;
        }
        return $types_deleted;
    }
    
    /**
     * check if current tree instance operates on repository tree table
     */
    public function isRepositoryTree()
    {
        if ($this->table_tree == 'tree') {
            return true;
        }
        return false;
    }
} // END class.tree
