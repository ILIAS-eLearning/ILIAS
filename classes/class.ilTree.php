<?php
/**
* Tree class
* data representation in hierachical trees using the Nested Set Model by Joe Celco
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ilTree
{
	/**
	* ilias object
	* @var		object	ilias
	* @access	private
	*/
	var $ilias;

	/**
	* points to root node (may be a subtree)
	* @var		integer
	* @access	public
	*/
	var $root_id;

	/**
	* to use different trees in one db-table
	* @var		integer
	* @access	public
	*/
	var $tree_id;

	/**
	* table name of tree table
	* @var		string
	* @access	private
	*/
	var $table_tree;

	/**
	* table name of object_data table
	* @var		string
	* @access	private
	*/
	var $table_obj_data;

	/**
	* table name of object_reference table
	* @var		string
	* @access	private
	*/
	var $table_obj_reference;

	/**
	* column name containing primary key in reference table
	* @var		string
	* @access	private
	*/
	var $ref_pk;

	/**
	* column name containing primary key in object table
	* @var		string
	* @access	private
	*/
	var $obj_pk;

	/**
	* column name containing tree id in tree table
	* @var		string
	* @access	private
	*/
	var $tree_pk;

	/**
	* Constructor
	* @access	public
	* @param	integer	$a_tree_id		tree_id
	* @param	integer	$a_root_id		root_id (optional)
	*/
	function ilTree($a_tree_id, $a_root_id = 0)
	{
		global $ilias;

		// set ilias
		$this->ilias =& $ilias;

		if (!isset($a_tree_id) or (func_num_args() == 0) )
		{
			$this->ilias->raiseError(get_class($this)."::Constructor(): No tree_id given!",$this->ilias->error_obj->WARNING);
		}

		if (func_num_args() > 2)
		{
			$this->ilias->raiseError(get_class($this)."::Constructor(): Wrong parameter count!",$this->ilias->error_obj->WARNING);
		}

		//init variables
		if (empty($a_root_id))
		{
			$a_root_id = ROOT_FOLDER_ID;
		}

		$this->tree_id		  = $a_tree_id;
		$this->root_id		  = $a_root_id;
		$this->table_tree     = 'tree';
		$this->table_obj_data = 'object_data';
		$this->table_obj_reference = 'object_reference';
		$this->ref_pk = 'ref_id';
		$this->obj_pk = 'obj_id';
		$this->tree_pk = 'tree';
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
	*/
	function setTableNames($a_table_tree,$a_table_obj_data,$a_table_obj_reference = "")
	{
		if (!isset($a_table_tree) or !isset($a_table_obj_data))
		{
			$this->ilias->raiseError(get_class($this)."::setTableNames(): Missing parameter! ".
								"tree table: ".$a_table_tree." object data table: ".$a_table_obj_data,$this->ilias->error_obj->WARNING);
		}

		$this->table_tree = $a_table_tree;
		$this->table_obj_data = $a_table_obj_data;
		$this->table_obj_reference = $a_table_obj_reference;

		return true;
	}

	/**
	* set column containing primary key in reference table
	* @access	public
	* @param	string	column name
	* @return	boolean	true, when successfully set
	*/
	function setReferenceTablePK($a_column_name)
	{
		if (!isset($a_column_name))
		{
			$this->ilias->raiseError(get_class($this)."::setReferenceTablePK(): No column name given!",$this->ilias->error_obj->WARNING);
		}

		$this->ref_pk = $a_column_name;
		return true;
	}

	/**
	* set column containing primary key in object table
	* @access	public
	* @param	string	column name
	* @return	boolean	true, when successfully set
	*/
	function setObjectTablePK($a_column_name)
	{
		if (!isset($a_column_name))
		{
			$this->ilias->raiseError(get_class($this)."::setObjectTablePK(): No column name given!",$this->ilias->error_obj->WARNING);
		}

		$this->obj_pk = $a_column_name;
		return true;
	}

	/**
	* set column containing primary key in tree table
	* @access	public
	* @param	string	column name
	* @return	boolean	true, when successfully set
	*/
	function setTreeTablePK($a_column_name)
	{
		if (!isset($a_column_name))
		{
			$this->ilias->raiseError(get_class($this)."::setTreeTablePK(): No column name given!",$this->ilias->error_obj->WARNING);
		}

		$this->tree_pk = $a_column_name;
		return true;
	}

	/**
	* build join depending on table settings
	* @access	private
	* @return	string
	*/
	function buildJoin()
	{
		if ($this->table_obj_reference)
		{
			return "LEFT JOIN ".$this->table_obj_reference." ON ".$this->table_tree.".child=".$this->table_obj_reference.".".$this->ref_pk." ".
				   "LEFT JOIN ".$this->table_obj_data." ON ".$this->table_obj_reference.".".$this->obj_pk."=".$this->table_obj_data.".".$this->obj_pk." ";
		}
		else
		{
			return "LEFT JOIN ".$this->table_obj_data." ON ".$this->table_tree.".child=".$this->table_obj_data.".".$this->obj_pk." ";
		}
	}

	/**
	* get leaf(=end) nodes of tree
	* //TODO: Method not used yet
	* @access	public
	* @return	array	node data of all leaf nodes
	*/
	function getLeafs()
	{
		$q = "SELECT * FROM ".$this->table_tree." ".
			 $this->buildJoin().
			 "WHERE lft = (rgt -1) ".
			 "AND ".$this->tree_pk." = '".$this->tree->id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$leafs[] = $this->fetchNodeData($row);
		}

		return $leafs;
	}

	/**
	* get child nodes of given node
	* @access	public
	* @param	integer		node_id
	* @param	string		sort order of returned childs, optional (possible values: 'title','desc','last_update' or 'type')
	* @param	string		sort direction, optional (possible values: 'DESC' or 'ASC'; defalut is 'ASC')
	* @return	array		with node data of all childs or empty array
	*/
	function getChilds($a_node_id, $a_order = "", $a_direction = "ASC")
	{
		global $log;

		if (!isset($a_node_id))
		{
			$message = get_class($this)."::getChilds(): No node_id given!";
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		// init childs
		$childs = array();

		// number of childs
		$count = 0;

		// init order_clause
		$order_clause = "";

		// set order_clause if sort order parameter is given
		if (!empty($a_order))
		{
			$order_clause = "ORDER BY ".$a_order." ".$a_direction;
		}

		$q = "SELECT * FROM ".$this->table_tree." ".
			 $this->buildJoin().
			 "WHERE parent = '".$a_node_id."' ".
			 "AND ".$this->tree_pk." = '".$this->tree_id."' ".
			 $order_clause;
		$r = $this->ilias->db->query($q);

		$count = $r->numRows();

		if ($count > 0)
		{
			while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$childs[] = $this->fetchNodeData($row);
			}

			// mark the last child node (important for display)
			$childs[$count - 1]["last"] = true;

			return $childs;
		}
		else
		{
			return $childs;
		}
	}

	/**
	* get child nodes of given node by object type
	* @access	public
	* @param	integer		node_id
	* @param	string		object type
	* @return	array		with node data of all childs or empty array
	*/
	function getChildsByType($a_node_id,$a_type)
	{
		global $log;

		if (!isset($a_node_id) or !isset($a_type))
		{
			$message = get_class($this)."::getChildsByType(): Missing parameter! node_id:".$a_node_id." type:".$a_type;
			$log->writeWarning($message);
			$this->ilias->raiseError($message,$this->ilias->error_obj->WARNING);
		}

		// init childs
		$childs = array();

		$q = "SELECT * FROM ".$this->table_tree." ".
			 $this->buildJoin().
			 "WHERE parent = '".$a_node_id."' ".
			 "AND ".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND ".$this->table_obj_data.".type='".$a_type."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$childs[] = $this->fetchNodeData($row);
		}

		return $childs;
	}

	/**
	* insert new node with node_id under parent node with parent_id
	* @access	public
	* @param	integer		node_id
	* @param	integer		parent_id
	*/
	function insertNode($a_node_id,$a_parent_id)
	{
		if (!isset($a_node_id) or !isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::insertNode(): Missing parameter! ".
								"node_id: ".$a_node_id." parent_id: ".$a_parent_id,$this->ilias->error_obj->WARNING);
		}

		// get left value
		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE child = '".$a_parent_id."' ".
			 "AND ".$this->tree_pk." = '".$this->tree_id."'";
		$r = $this->ilias->db->getRow($q);

		$left = $r->lft;
		$lft = $left + 1;
		$rgt = $left + 2;

		// spread tree
		$q = "UPDATE ".$this->table_tree." SET ".
			 "lft = CASE ".
			 "WHEN lft > ".$left." ".
			 "THEN lft + 2 ".
			 "ELSE lft ".
			 "END, ".
			 "rgt = CASE ".
			 "WHEN rgt > ".$left." ".
			 "THEN rgt + 2 ".
			 "ELSE rgt ".
			 "END ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."'";
		$this->ilias->db->query($q);
		
		// get depth
		$depth = $this->getDepth($a_parent_id) + 1;

		// insert node
		$q = "INSERT INTO ".$this->table_tree." (".$this->tree_pk.",child,parent,lft,rgt,depth) ".
			 "VALUES ".
			 "('".$this->tree_id."','".$a_node_id."','".$a_parent_id."','".$lft."','".$rgt."','".$depth."')";
		$this->ilias->db->query($q);
	}

	/**
	* get all nodes in the subtree under specified node
	* 
	* @access	public
	* @param	array		node_data
	* @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
	*/
	function getSubTree($a_node)
	{
		if (!is_array($a_node))
		{
			$this->ilias->raiseError(get_class($this)."::getSubTree(): Wrong datatype for node_data! ",$this->ilias->error_obj->WARNING);
		}

	    $subtree = array();

		$q = "SELECT * FROM ".$this->table_tree." ".
			 $this->buildJoin().
			 "WHERE ".$this->table_tree.".lft BETWEEN '".$a_node["lft"]."' AND '".$a_node["rgt"]."' ".
			 "AND ".$this->table_tree.".".$this->tree_pk." = '".$this->tree_id."' ".
			 "ORDER BY ".$this->table_tree.".lft";

		$r = $this->ilias->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$subtree[] = $this->fetchNodeData($row);
		}
			
		return $subtree;
	}
	
	/**
	* delete node and the whole subtree under this node
	* @access	public
	* @param	array		node_data of a node
	*/
	function deleteTree($a_node)
	{
		if (!is_array($a_node))
		{
			$this->ilias->raiseError(get_class($this)."::deleteTree(): Wrong datatype for node_data! ",$this->ilias->error_obj->WARNING);
		}

		$diff = $a_node["rgt"] - $a_node["lft"] + 1;

		// delete subtree
		$q = "DELETE FROM ".$this->table_tree." ".
			 "WHERE lft BETWEEN '".$a_node["lft"]."' AND '".$a_node["rgt"]." '".
			 "AND ".$this->tree_pk." = '".$a_node["tree"]."'";
		$this->ilias->db->query($q);

		// close gaps
		$q = "UPDATE ".$this->table_tree." SET ".
			 "lft = CASE ".
			 "WHEN lft > '".$a_node["lft"]." '".
			 "THEN lft - '".$diff." '".
			 "ELSE lft ".
			 "END, ".
			 "rgt = CASE ".
			 "WHEN rgt > '".$a_node["lft"]." '".
			 "THEN rgt - '".$diff." '".
			 "ELSE rgt ".
			 "END ".
			 "WHERE ".$this->tree_pk." = '".$a_node["tree"]."'";
		$this->ilias->db->query($q);

		// TODO: DO WE NEED THIS INFORMATION????
		//$this->parent_id = $a_node["parent"];
	}

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	private
	* @param	integer		node_id of endnode 
	* @param	integer		node_id of startnode
	* @return	object		query result
	*/
	function fetchPath ($a_endnode_id, $a_startnode_id)
	{
		if ($this->table_obj_reference)
		{
			$leftjoin = "LEFT JOIN ".$this->table_obj_reference." ON T2.child=".$this->table_obj_reference.".".$this->ref_pk." ".
						"LEFT JOIN ".$this->table_obj_data." ON ".$this->table_obj_reference.".".$this->obj_pk."=".$this->table_obj_data.".".$this->obj_pk." ";
		}
		else
		{
			$leftjoin = "LEFT JOIN ".$this->table_obj_data." ON T2.child=".$this->table_obj_data.".".$this->obj_pk." ";
		}

		$q = "SELECT ".$this->table_obj_data.".title,".$this->table_obj_data.".type,T2.child,(T2.rgt - T2.lft) AS sort_col ".
			 "FROM ".$this->table_tree." AS T1, ".$this->table_tree." AS T2, ".$this->table_tree." AS T3 ".
			 $leftjoin.
			 "WHERE T1.child = '".$a_startnode_id."' ".
			 "AND T3.child = '".$a_endnode_id."' ".
			 "AND T2.lft BETWEEN T1.lft AND T1.rgt ".
			 "AND T3.lft BETWEEN T2.lft AND T2.rgt ".
			 "AND T1.".$this->tree_pk." = '".$this->tree_id." '".
			 "AND T2.".$this->tree_pk." = '".$this->tree_id." '".
			 "AND T3.".$this->tree_pk." = '".$this->tree_id." '".
			 "ORDER BY sort_col DESC";

		$r = $this->ilias->db->query($q);

		if ($r->numRows() > 0)
		{
			return $r;
		}
		else
		{

			$this->ilias->raiseError(get_class($this)."::fetchPath: No path found! startnode_id:".$a_startnode_id.", endnode_id:".$a_endnode_id,$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer	node_id of endnode (optional)
	* @param	integer	node_id of startnode (optional)
	* @return	array	ordered path info (id,title,parent) from start to end
	*/
	function getPathFull ($a_endnode_id, $a_startnode_id = 0)
	{
		if (!isset($a_endnode_id))
		{
			$this->ilias->raiseError(get_class($this)."::getPathFull(): No endnode_id given! ",$this->ilias->error_obj->WARNING);
		}

		if (empty($a_startnode_id))
		{
			$a_startnode_id = $this->root_id;
		}

		$r = $this->fetchPath($a_endnode_id, $a_startnode_id);
				
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$path[] = $this->fetchNodeData($row);
		}

		return $path;
	}	

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer		node_id of endnode
	* @param	integer		node_id of startnode (optional)
	* @return	array		all path ids from startnode to endnode
	*/
	function getPathId ($a_endnode_id, $a_startnode_id = 0)
	{
		if (!isset($a_endnode_id))
		{
			$this->ilias->raiseError(get_class($this)."::getPathId(): No endnode_id given! ",$this->ilias->error_obj->WARNING);
		}

		//if (!isset($a_startnode_id))
		if ($a_startnode_id == 0)
		{
			$a_startnode_id = $this->root_id;
		}

		$r = $this->fetchPath($a_endnode_id, $a_startnode_id);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = $row->child;
		}
		
		return $arr;
	}
	
	/**
	* check consistence of tree
	* @access	public
	* @return	boolean		true if tree is ok; otherwise throws error object
	*/
	function checkTree()
	{
		$q = "SELECT lft,rgt FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."'";
				 
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$lft[] = $row->lft;
			$rgt[] = $row->rgt;
		}
			
		$all = array_merge($lft,$rgt);
		$uni = array_unique($all);
			
		if (count($all) != count($uni))
		{
			$this->ilias->raiseError(get_class($this)."::checkTree(): Tree is corrupted!",$this->ilias->error_obj->WARNING);
		}

		return true;
	}

	/**
	* Return the maximum depth in tree
	* @access	public
	* @return	integer	max depth level of tree
	*/
	function getMaximumDepth()
	{
		$q = "SELECT MAX(depth) FROM ".$this->table_tree;
		$r = $this->ilias->db->query($q);
		
		$row = $r->fetchRow();
		
		return $row[0];
	}
	
	/**
	* return depth of a node in tree
	* @access	private
	* @param	integer		node_id of parent's node_id
	* @return	integer		depth of node in tree
	*/
	function getDepth($a_node_id)
	{
		if ($a_node_id)
		{
			$q = "SELECT depth FROM ".$this->table_tree." ".
				 "WHERE child = '".$a_node_id."' ".
				 "AND ".$this->tree_pk." = '".$this->tree_id."'";
			$r = $this->ilias->db->getRow($q);
	
			return $r->depth;
		}
		else
		{
			return 1;
		}
	}

	/**
	* Calculates additional information for each node in tree-structure:
	* no. of successors:	How many successors does the node have?
	* 						Every node under the concerned node in the tree counts as a successor.
	* depth			   :	The depth-level in tree the concerned node has. (the root node has a depth of 1!)
	* brother		   :	The no. of node which are on the same depth-level with the concerned node
	*
	* @access	public
	* @return	array		array of new tree information (to be specified.... :-)
	*/
	function calculateFlatTree()
	{
		if ($this->table_obj_reference)
		{
			$leftjoin = "LEFT JOIN ".$this->table_obj_reference." ON s.child=".$this->table_obj_reference.".".$this->ref_pk." ".
						"LEFT JOIN ".$this->table_obj_data." ON ".$this->table_obj_reference.".".$this->obj_pk."=".$this->table_obj_data.".".$this->obj_pk." ";
		}
		else
		{
			$leftjoin = "LEFT JOIN ".$this->table_obj_data." ON s.child=".$this->table_obj_data.".".$this->obj_pk." ";

		}

		$q = "SELECT s.child,s.lft,s.rgt,title,s.depth,".
			 "(s.rgt-s.lft-1)/2 AS successor,".
			 "((min(v.rgt)-s.rgt-(s.lft>1))/2) > 0 AS brother ".
			 "FROM ".$this->table_tree." v, ".$this->table_tree." s ".
			 $leftjoin.
			 "WHERE s.lft BETWEEN v.lft AND v.rgt ".
			 "AND (v.child != s.child OR s.lft = '1') ".
			 "AND s.".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND v.".$this->tree_pk." = '".$this->tree_id."' ".
			 "GROUP BY s.child ".
			 "ORDER BY s.lft";
		$r = $this->ilias->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = array(
							"title"		=> $row->title,
							"child"		=> $row->child,
							"successor" => $row->successor,
							"depth"		=> $row->depth,
							"brother"	=> $row->brother,
							"lft"		=> $row->lft,
							"rgt"		=> $row->rgt
						   );
		}
		
		return $arr;
	}

	/**
	* get all information of a node.
	* get data of a specific node from tree and object_data
	* @access	public
	* @param	integer		node id
	* @return	object		db result object
	*/
	function getNodeData($a_node_id)
	{
		if (!isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::getNodeData(): No node_id given! ",$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT * FROM ".$this->table_tree." ".
			 $this->buildJoin().
			 "WHERE ".$this->table_tree.".child = '".$a_node_id."' ".
			 "AND ".$this->table_tree.".".$this->tree_pk." = '".$this->tree_id."'";
		$r = $this->ilias->db->query($q);

		$row = $r->fetchRow(DB_FETCHMODE_ASSOC);

		return $this->fetchNodeData($row);
	}

	/**
	* get data of parent node from tree and object_data
	* @access	private
 	* @param	object	db	db result object containing node_data
	* @return	array		2-dim (int/str) node_data
	* TODO: select description twice for compability. Please use 'desc' in future only
	*/
	function fetchNodeData($a_row)
	{
		$data = $a_row;
		$data["desc"] = $a_row["description"];
		/*
		$data = array(
					"ref_id"		=> $a_row->ref_id,
					"obj_id"		=> $a_row->obj_id,
					"type"			=> $a_row->type,
					"title"			=> $a_row->title,
					"description"	=> $a_row->description,
					"owner"			=> $a_row->owner,
					"create_date"	=> $a_row->create_date,
					"last_update"	=> $a_row->last_update,
					"tree"			=> $a_row->tree,
					"child"			=> $a_row->child,
					"parent"		=> $a_row->parent,
					"lft"			=> $a_row->lft,
					"rgt"			=> $a_row->rgt,
					"depth"			=> $a_row->depth,
					"desc"			=> $a_row->description
					);*/

		return $data ? $data : array();
	}

	/**
	* get data of parent node from tree and object_data
	* @access	public
 	* @param	integer		node id
	* @return	array
	*/
	function getParentNodeData($a_node_id)
	{
		if (!isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::getParentNodeData(): No node_id given! ",$this->ilias->error_obj->WARNING);
		}

		if ($this->table_obj_reference)
		{
			$leftjoin = "LEFT JOIN ".$this->table_obj_reference." ON v.child=".$this->table_obj_reference.".".$this->ref_pk." ".
				  		"LEFT JOIN ".$this->table_obj_data." ON ".$this->table_obj_reference.".".$this->obj_pk."=".$this->table_obj_data.".".$this->obj_pk." ";
		}
		else
		{
			$leftjoin = "LEFT JOIN ".$this->table_obj_data." ON v.child=".$this->table_obj_data.".".$this->obj_pk." ";
		}

		$q = "SELECT * FROM ".$this->table_tree." s,".$this->table_tree." v ".
			 $leftjoin.
			 "WHERE s.child = '".$a_node_id."' ".
			 "AND s.parent = v.child ".
			 "AND s.lft > v.lft ".
			 "AND s.rgt < v.rgt ".
			 "AND s.".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND v.".$this->tree_pk." = '".$this->tree_id."'";
		$r = $this->ilias->db->query($q);

		$row = $r->fetchRow(DB_FETCHMODE_ASSOC);

		return $this->fetchNodeData($row);
	}

	/**
	* checks if a node is in the path of an other node
	* @access	public
 	* @param	integer		object id of start node
	* @param    integer     object id of query node
	* @return	integer		number of entries
	*/
	function isGrandChild($a_startnode_id,$a_querynode_id)
	{
		if (!isset($a_startnode_id) or !isset($a_querynode_id))
		{
			$this->ilias->raiseError(get_class($this)."::isGrandChild(): Missing parameter! startnode: ".$a_startnode_id." querynode: ".$a_querynode_id,$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT * FROM ".$this->table_tree." s,".$this->table_tree." v ".
			 "WHERE s.child = '".$a_startnode_id."' ".
			 "AND v.child = '".$a_querynode_id."' ".
			 "AND s.".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND v.".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND v.lft BETWEEN s.lft AND s.rgt ".
			 "AND v.rgt BETWEEN s.lft AND s.rgt";
		$r = $this->ilias->db->query($q);

		return $r->numRows();
	}

	/**
	* create a new tree
	* to do: ???
	* @param	integer		a_tree_id: obj_id of object where tree belongs to
	* @param	integer		a_node_id: root node of tree (optional; default is tree_id itself)
	* @return	boolean		true on success
	* @access	public
	*/
	function addTree($a_tree_id,$a_node_id = -1)
	{
		if (!isset($a_tree_id))
		{
			$this->ilias->raiseError(get_class($this)."::addTree(): No tree_id given! ",$this->ilias->error_obj->WARNING);
		}

		if ($a_node_id <= 0)
		{
			$a_node_id = $a_tree_id;
		}
		
		$q = "INSERT INTO ".$this->table_tree." (".$this->tree_pk.", child, parent, lft, rgt, depth) ".
			 "VALUES ".
			 "('".$a_tree_id."','".$a_node_id."', 0, 1, 2, 1)";

		$this->ilias->db->query($q);

		return true;
	}

	/**
	* get nodes by type
	* // TODO: method needs revision
	* @param	integer		a_tree_id: obj_id of object where tree belongs to
	* @param	integer		a_type_id: type of object
	* @access	public
	*/
	function getNodeDataByType($a_type)
	{
		if (!isset($a_type) or (!is_string($a_type)))
		{
			$this->ilias->raiseError(get_class($this)."::getNodeDataByType(): Type not given or wrong datatype!",$this->ilias->error_obj->WARNING);
		}

		$data = array();	// node_data
		$row = "";			// fetched row
		$left = "";			// tree_left
		$right = "";		// tree_right

		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."'".
			 "AND parent = '0'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$left = $row->lft;
			$right = $row->rgt;
		}

		$q = "SELECT * FROM ".$this->table_tree." ".
			 $this->buildJoin().
			 "WHERE ".$this->table_obj_data.".type = '".$a_type."' ".
			 "AND ".$this->table_tree.".lft BETWEEN '".$left."' AND '".$right."' ".
			 "AND ".$this->table_tree.".rgt BETWEEN '".$left."' AND '".$right."' ".
			 "AND ".$this->table_tree.".".$this->tree_pk." = '".$this->tree_id."'";
		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
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
 	*/
	function removeTree($a_tree_id)
	{
		if (!$a_tree_id)
		{
			$this->ilias->raiseError(get_class($this)."::removeTree(): No tree_id given! Action aborted",$this->ilias->error_obj->MESSAGE);
		}
		
		$q = "DELETE FROM ".$this->table_tree." WHERE ".$this->tree_pk." = '".$a_tree_id."'";
		$this->ilias->db->query($q);
		
		return true;
	}

	/**
	* save subtree: copy a subtree (defined by node_id) to a new tree
	* with $this->tree_id -node_id. This is neccessary for cut/copy
	* @param	integer	node_id
	* @return	integer
	* @access	public
	*/
	function saveSubTree($a_node_id)
	{
		if (!isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::saveSubTree(): No node_id given!",$this->ilias->error_obj->WARNING);
		}

		// GET LEFT AND RIGHT VALUE
		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND child = '".$a_node_id."' ";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$lft = $row->lft;
			$rgt = $row->rgt;
		}

		// GET ALL SUBNODES
		$q = "SELECT * FROM ".$this->table_tree." ".
			 "WHERE ".$this->tree_pk." = '".$this->tree_id."' ".
			 "AND lft >= '".$lft."' ".
			 "AND rgt <= '".$rgt."'";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$subnodes[$row->child] = $this->fetchNodeData($row);
		}

		// SAVE SUBTREE
		foreach($subnodes as $node)
		{
			$q = "INSERT INTO ".$this->table_tree." ".
				 "VALUES ('".-$a_node_id."','".$node["child"]."','".$node["parent"]."','".
				 $node["lft"]."','".$node["rgt"]."','".$node["depth"]."')";
			$r = $this->ilias->db->query($q);
		}

		return true;
	}

	/**
	* save node: copy a node (defined by obj_id and parent) to a new tree
	* with tree_id -obj_id.This is neccessary for link
	* @param	integer	node_id
	* @param	integer	parent_id
	* @return	boolean
	* @access	public
	*/
	function saveNode($a_node_id,$a_parent_id)
	{
		if (!isset($a_node_id) or !isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::saveNode(): Missing parameter! ".
								"node_id: ".$a_node_id." parent_id: ".$a_parent_id,$this->ilias->error_obj->WARNING);
		}

		// SAVE NODE
		$q = "INSERT INTO ".$this->table_tree." ".
			 "VALUES ('".-$a_node_id."','".$a_node_id."','".$a_parent_id."','1','2','1')";
		$r = $this->ilias->db->query($q);

		return true;
	}

	/**
	* get data saved/deleted nodes
	* @return	array	data
	* @param	integer	id of parent object of saved object
	* @access	public
	*/
	function getSavedNodeData($a_parent_id)
	{
		if (!isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::getSavedNodeData(): No node_id given!",$this->ilias->error_obj->WARNING);
		}

		$q =	"SELECT * FROM ".$this->table_tree." ".
				$this->buildJoin().
				"WHERE ".$this->table_tree.".".$this->tree_pk." < 0 ".
				"AND ".$this->table_tree.".parent = '".$a_parent_id."' ";

		$r = $this->ilias->db->query($q);

		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$saved[] = $this->fetchNodeData($row);
		}

		return $saved;
	}

	/**
	* get parent id of given node
	* @access	public
	* @param	integer	node id
	* @return	integer	parent id
	*/
	function getParentId($a_node_id)
	{
		if (!isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::getParentId(): No node_id given! ",$this->ilias->error_obj->WARNING);
		}

		$q = "SELECT parent FROM ".$this->table_tree." ".
			 "WHERE child='".$a_node_id."' ".
			 "AND ".$this->tree_pk."='".$this->tree_id."'";
		$r = $this->ilias->db->query($q);
		
		$row = $r->fetchRow(DB_FETCHMODE_OBJECT);

		return $row->parent;
	}

	/**
	* read root id from database
	* @param root_id
	* @access public
	* @return int new root id
	*/
	function readRootId()
	{
		$query = "SELECT child FROM $this->table_tree ".
			"WHERE parent = '0'".
			"AND ".$this->tree_pk." = '".$this->tree_id."'";
		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);

		$this->root_id = $row->child;
		return $this->root_id;
	}

	/**
	* get the root id of tree
	* @access	public
	* @return	integer	root node id
	*/
	function getRootId()
	{
		return $this->root_id;
	}

	/**
	* get tree id
	* @access	public
	* @return	integer	tree id
	*/
	function getTreeId()
	{
		return $this->tree_id;
	}

	/**
	* set tree id
	* @access	public
	* @return	integer	tree id
	*/
	function setTreeId($a_tree_id)
	{
		$this->tree_id = $a_tree_id;
	}
} // END class.tree
?>
