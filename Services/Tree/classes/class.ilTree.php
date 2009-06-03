<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

define("IL_LAST_NODE", -2);
define("IL_FIRST_NODE", -1);

/** @defgroup ServicesTree Services/Tree
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
	/**
	* ilias object
	* @var		object	ilias
	* @access	private
	*/
	var $ilias;


	/**
	* Logger object
	* @var		object	ilias
	* @access	private
	*/
	var $log;

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
	var $gap;

	/**
	* Constructor
	* @access	public
	* @param	integer	$a_tree_id		tree_id
	* @param	integer	$a_root_id		root_id (optional)
	*/
	function ilTree($a_tree_id, $a_root_id = 0)
	{
		global $ilDB,$ilErr,$ilias,$ilLog;

		// set db & error handler
		$this->ilDB = $ilDB;

		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr = $ilErr;
		}

		$this->lang_code = "en";
		
		if (!isset($a_tree_id) or (func_num_args() == 0) )
		{
			$this->ilErr->raiseError(get_class($this)."::Constructor(): No tree_id given!",$this->ilErr->WARNING);
		}

		if (func_num_args() > 2)
		{
			$this->ilErr->raiseError(get_class($this)."::Constructor(): Wrong parameter count!",$this->ilErr->WARNING);
		}

		// CREATE LOGGER INSTANCE
		$this->log = $ilLog;

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
		$this->use_cache = false;

		// If cache is activated, cache object translations to improve performance
		$this->translation_cache = array();
		$this->parent_type_cache = array();

		// By default, we create gaps in the tree sequence numbering for 50 nodes
		$this->gap = 50;
	}
	
	/**
	* Use Cache (usually not activated)
	*/
	function useCache($a_use = true)
	{
		$this->use_cache = $a_use;
	}
	
	/**
	* Store user language. This function is used by the "main"
	* tree only (during initialisation).
	*/
	function initLangCode()
	{
		global $ilUser;
		
		// lang_code is only required in $this->fetchnodedata
		if (!is_object($ilUser))
		{
			$this->lang_code = "en";
		}
		else
		{
			$this->lang_code = $ilUser->getCurrentLanguage();
		}
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
			$this->ilErr->raiseError(get_class($this)."::setTableNames(): Missing parameter! ".
								"tree table: ".$a_table_tree." object data table: ".$a_table_obj_data,$this->ilErr->WARNING);
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
			$this->ilErr->raiseError(get_class($this)."::setReferenceTablePK(): No column name given!",$this->ilErr->WARNING);
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
			$this->ilErr->raiseError(get_class($this)."::setObjectTablePK(): No column name given!",$this->ilErr->WARNING);
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
			$this->ilErr->raiseError(get_class($this)."::setTreeTablePK(): No column name given!",$this->ilErr->WARNING);
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
			// Use inner join instead of left join to improve performance
			return "JOIN ".$this->table_obj_reference." ON ".$this->table_tree.".child=".$this->table_obj_reference.".".$this->ref_pk." ".
				   "JOIN ".$this->table_obj_data." ON ".$this->table_obj_reference.".".$this->obj_pk."=".$this->table_obj_data.".".$this->obj_pk." ";
		}
		else
		{
			// Use inner join instead of left join to improve performance
			return "JOIN ".$this->table_obj_data." ON ".$this->table_tree.".child=".$this->table_obj_data.".".$this->obj_pk." ";
		}
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
		global $ilBench,$ilDB;
		
		if (!isset($a_node_id))
		{
			$message = get_class($this)."::getChilds(): No node_id given!";
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
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
		else
		{
			$order_clause = "ORDER BY ".$this->table_tree.".lft";
		}

			 
		$query = sprintf('SELECT * FROM '.$this->table_tree.' '.
				$this->buildJoin().
				"WHERE parent = %s " .
				"AND ".$this->table_tree.".".$this->tree_pk." = %s ".
				$order_clause,
				$ilDB->quote($a_node_id,'integer'),
				$ilDB->quote($this->tree_id,'integer'));

		$res = $ilDB->query($query);
		
		if(!$count = $res->numRows())
		{
			return array();
		}
		
		while($row = $ilDB->fetchAssoc($res))
		{
			$childs[] = $this->fetchNodeData($row);
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
	function getFilteredChilds($a_filter,$a_node,$a_order = "",$a_direction = "ASC")
	{
		$childs = $this->getChilds($a_node,$a_order,$a_direction);

		foreach($childs as $child)
		{
			if(!in_array($child["type"],$a_filter))
			{
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
	*/
	function getChildsByType($a_node_id,$a_type)
	{
		global $ilDB;
		
		if (!isset($a_node_id) or !isset($a_type))
		{
			$message = get_class($this)."::getChildsByType(): Missing parameter! node_id:".$a_node_id." type:".$a_type;
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

        if ($a_type=='rolf' && $this->table_obj_reference) {
            // Performance optimization: A node can only have exactly one
            // role folder as its child. Therefore we don't need to sort the
            // results, and we can let the database know about the expected limit.
            $ilDB->setLimit(1,0);
            $query = sprintf("SELECT * FROM ".$this->table_tree." ".
                $this->buildJoin().
                "WHERE parent = %s ".
                "AND ".$this->table_tree.".".$this->tree_pk." = %s ".
                "AND ".$this->table_obj_data.".type = %s ",
                $ilDB->quote($a_node_id,'integer'),
                $ilDB->quote($this->tree_id,'integer'),
                $ilDB->quote($a_type,'text'));
        } else {
            $query = sprintf("SELECT * FROM ".$this->table_tree." ".
                $this->buildJoin().
                "WHERE parent = %s ".
                "AND ".$this->table_tree.".".$this->tree_pk." = %s ".
                "AND ".$this->table_obj_data.".type = %s ".
                "ORDER BY ".$this->table_tree.".lft",
                $ilDB->quote($a_node_id,'integer'),
                $ilDB->quote($this->tree_id,'integer'),
                $ilDB->quote($a_type,'text'));
        }
		$res = $ilDB->query($query);
		
		// init childs
		$childs = array();
		while($row = $ilDB->fetchAssoc($res))
		{
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
	*/
	public function getChildsByTypeFilter($a_node_id,$a_types)
	{
		global $ilDB;
		
		if (!isset($a_node_id) or !$a_types)
		{
			$message = get_class($this)."::getChildsByType(): Missing parameter! node_id:".$a_node_id." type:".$a_types;
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
	
		$filter = ' ';
		if($a_types)
		{
			$filter = 'AND '.$this->table_obj_data.'.type IN('.implode(',',ilUtil::quoteArray($a_types)).') ';
		}

		$query = 'SELECT * FROM '.$this->table_tree.' '.
			$this->buildJoin().
			'WHERE parent = '.$ilDB->quote($a_node_id,'integer').' '.
			'AND '.$this->table_tree.'.'.$this->tree_pk.' = '.$ilDB->quote($this->tree_id,'integer').' '.
			$filter.
			'ORDER BY '.$this->table_tree.'.lft';
		
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$childs[] = $this->fetchNodeData($row);
		}	
		
		return $childs ? $childs : array();
	}
	
	/**
	* insert new node with node_id under parent node with parent_id
	* @access	public
	* @param	integer		node_id
	* @param	integer		parent_id
	* @param	integer		IL_LAST_NODE | IL_FIRST_NODE | node id of preceding child
	*/
	function insertNode($a_node_id, $a_parent_id, $a_pos = IL_LAST_NODE, $a_reset_deletion_date = false)
	{
		global $ilDB;
		
//echo "+$a_node_id+$a_parent_id+";
		// CHECK node_id and parent_id > 0 if in main tree
		if($this->__isMainTree())
		{
			if($a_node_id <= 1 or $a_parent_id <= 0)
			{
				$message = sprintf('%s::insertNode(): Invalid parameters! $a_node_id: %s $a_parent_id: %s',
								   get_class($this),
								   $a_node_id,
								   $a_parent_id);
				$this->log->write($message,$this->log->FATAL);
				$this->ilErr->raiseError($message,$this->ilErr->WARNING);
			}
		}


		if (!isset($a_node_id) or !isset($a_parent_id))
		{
			$this->ilErr->raiseError(get_class($this)."::insertNode(): Missing parameter! ".
				"node_id: ".$a_node_id." parent_id: ".$a_parent_id,$this->ilErr->WARNING);
		}
		if ($this->isInTree($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::insertNode(): Node ".$a_node_id." already in tree ".
									 $this->table_tree."!",$this->ilErr->WARNING);
		}

		//
		switch ($a_pos)
		{
			case IL_FIRST_NODE:

				if($this->__isMainTree())
				{
					ilDB::_lockTables(array('tree' => 'WRITE'));
				}

				// get left value of parent
				$query = sprintf('SELECT * FROM '.$this->table_tree.' '.
					'WHERE child = %s '.
					'AND '.$this->tree_pk.' = %s ',
					$ilDB->quote($a_parent_id,'integer'),
					$ilDB->quote($this->tree_id,'integer'));
				
				$res = $ilDB->query($query);
				$r = $ilDB->fetchObject($res);

				if ($r->parent == NULL)
				{
					if($this->__isMainTree())
					{
						ilDB::_unlockTables();
					}
					$this->ilErr->raiseError(get_class($this)."::insertNode(): Parent with ID ".$a_parent_id." not found in ".
											 $this->table_tree."!",$this->ilErr->WARNING);
				}

				$left = $r->lft;
				$lft = $left + 1;
				$rgt = $left + 2;

				// spread tree
				$query = sprintf('UPDATE '.$this->table_tree.' SET '.
					'lft = CASE WHEN lft > %s THEN lft + 2 ELSE lft END, '.
					'rgt = CASE WHEN rgt > %s THEN rgt + 2 ELSE rgt END '.
					'WHERE '.$this->tree_pk.' = %s ',
					$ilDB->quote($left,'integer'),
					$ilDB->quote($left,'integer'),
					$ilDB->quote($this->tree_id,'integer'));
				$res = $ilDB->manipulate($query);
				break;

			case IL_LAST_NODE:
				// Special treatment for trees with gaps
				if ($this->gap > 0)
				{
					if($this->__isMainTree())
					{
						ilDB::_lockTables(array('tree' => 'WRITE'));
					}

					// get lft and rgt value of parent
					$query = sprintf('SELECT rgt,lft,parent FROM '.$this->table_tree.' '.
						'WHERE child = %s '.
						'AND '.$this->tree_pk.' =  %s',
						$ilDB->quote($a_parent_id,'integer'),
						$ilDB->quote($this->tree_id,'integer'));
					$res = $ilDB->query($query);
					$r = $ilDB->fetchAssoc($res);

					if ($r['parent'] == null)
					{
						if($this->__isMainTree())
						{
							ilDB::_unlockTables();
						}
						$this->ilErr->raiseError(get_class($this)."::insertNode(): Parent with ID ".
												$a_parent_id." not found in ".$this->table_tree."!",$this->ilErr->WARNING);
					}
					$parentRgt = $r['rgt'];
					$parentLft = $r['lft'];
					
					// Get the available space, without taking children into account yet
					$availableSpace = $parentRgt - $parentLft;
					if ($availableSpace < 2)
					{
						// If there is not enough space between parent lft and rgt, we don't need
						// to look any further, because we must spread the tree.
						$lft = $parentRgt;
					}
					else
					{
						// If there is space between parent lft and rgt, we need to check
						// whether there is space left between the rightmost child of the
						// parent and parent rgt.
						$query = sprintf('SELECT MAX(rgt) max_rgt FROM '.$this->table_tree.' '.
							'WHERE parent = %s '.
							'AND '.$this->tree_pk.' = %s',
							$ilDB->quote($a_parent_id,'integer'),
							$ilDB->quote($this->tree_id,'integer'));
						$res = $ilDB->query($query);
						$r = $ilDB->fetchAssoc($res);

						if (isset($r['max_rgt']))
						{
							// If the parent has children, we compute the available space
							// between rgt of the rightmost child and parent rgt.
							$availableSpace = $parentRgt - $r['max_rgt'];
							$lft = $r['max_rgt'] + 1;
						}
						else
						{
							// If the parent has no children, we know now, that we can
							// add the new node at parent lft + 1 without having to spread
							// the tree.
							$lft = $parentLft + 1;
						}
					}
					$rgt = $lft + 1;
					

					// spread tree if there is not enough space to insert the new node
					if ($availableSpace < 2)
					{
						//$this->log->write('ilTree.insertNode('.$a_node_id.','.$a_parent_id.') creating gap at '.$a_parent_id.' '.$parentLft.'..'.$parentRgt.'+'.(2 + $this->gap * 2));
						$query = sprintf('UPDATE '.$this->table_tree.' SET '.
							'lft = CASE WHEN lft  > %s THEN lft + %s ELSE lft END, '.
							'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END '.
							'WHERE '.$this->tree_pk.' = %s ',
							$ilDB->quote($parentRgt,'integer'),
							$ilDB->quote((2 + $this->gap * 2),'integer'),
							$ilDB->quote($parentRgt,'integer'),
							$ilDB->quote((2 + $this->gap * 2),'integer'),
							$ilDB->quote($this->tree_id,'integer'));
						$res = $ilDB->manipulate($query);
					}
					else
					{
						//$this->log->write('ilTree.insertNode('.$a_node_id.','.$a_parent_id.') reusing gap at '.$a_parent_id.' '.$parentLft.'..'.$parentRgt.' for node '.$a_node_id.' '.$lft.'..'.$rgt);
					}				
				}
				// Treatment for trees without gaps
				else 
				{
					if($this->__isMainTree())
					{
						ilDB::_lockTables(array('tree' => 'WRITE'));
					}

					// get right value of parent
					$query = sprintf('SELECT * FROM '.$this->table_tree.' '.
						'WHERE child = %s '.
						'AND '.$this->tree_pk.' = %s ',
						$ilDB->quote($a_parent_id,'integer'),
						$ilDB->quote($this->tree_id,'integer'));
					$res = $ilDB->query($query);
					$r = $ilDB->fetchObject($res);

					if ($r->parent == null)
					{
						if($this->__isMainTree())
						{
							ilDB::_unlockTables();
						}
						$this->ilErr->raiseError(get_class($this)."::insertNode(): Parent with ID ".
												 $a_parent_id." not found in ".$this->table_tree."!",$this->ilErr->WARNING);
					}

					$right = $r->rgt;
					$lft = $right;
					$rgt = $right + 1;

					// spread tree
					$query = sprintf('UPDATE '.$this->table_tree.' SET '.
						'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, '.
						'rgt = CASE WHEN rgt >= %s THEN rgt + 2 ELSE rgt END '.
						'WHERE '.$this->tree_pk.' = %s',
						$ilDB->quote($right,'integer'),
						$ilDB->quote($right,'integer'),
						$ilDB->quote($this->tree_id,'integer'));
					$res = $ilDB->manipulate($query);
				}

				break;

			default:

				// this code shouldn't be executed
				if($this->__isMainTree())
				{
					ilDB::_lockTables(array('tree' => 'WRITE'));
				}

				// get right value of preceeding child
				$query = sprintf('SELECT * FROM '.$this->table_tree.' '.
					'WHERE child = %s '.
					'AND '.$this->tree_pk.' = %s ',
					$ilDB->quote($a_pos,'integer'),
					$ilDB->quote($this->tree_id,'integer'));
				$res = $ilDB->query($query);
				$r = $ilDB->fetchObject($res);

				// crosscheck parents of sibling and new node (must be identical)
				if ($r->parent != $a_parent_id)
				{
					if($this->__isMainTree())
					{
						ilDB::_unlockTables();
					}
					$this->ilErr->raiseError(get_class($this)."::insertNode(): Parents mismatch! ".
						"new node parent: ".$a_parent_id." sibling parent: ".$r->parent,$this->ilErr->WARNING);
				}

				$right = $r->rgt;
				$lft = $right + 1;
				$rgt = $right + 2;

				// update lft/rgt values
				$query = sprintf('UPDATE '.$this->table_tree.' SET '.
					'lft = CASE WHEN lft >  %s THEN lft + 2 ELSE lft END, '.
					'rgt = CASE WHEN rgt >  %s THEN rgt + 2 ELSE rgt END '.
					'WHERE '.$this->tree_pk.' = %s',
					$ilDB->quote($right,'integer'),
					$ilDB->quote($right,'integer'),
					$ilDB->quote($this->tree_id,'integer'));
				$res = $ilDB->manipulate($query);
				break;

		}

		// get depth
		$depth = $this->getDepth($a_parent_id) + 1;

		// insert node
		//$this->log->write('ilTree.insertNode('.$a_node_id.','.$a_parent_id.') inserting node:'.$a_node_id.' parent:'.$a_parent_id." ".$lft."..".$rgt." depth:".$depth);
		$query = sprintf('INSERT INTO '.$this->table_tree.' ('.$this->tree_pk.',child,parent,lft,rgt,depth) '.
			'VALUES (%s,%s,%s,%s,%s,%s)',
			$ilDB->quote($this->tree_id,'integer'),
			$ilDB->quote($a_node_id,'integer'),
			$ilDB->quote($a_parent_id,'integer'),
			$ilDB->quote($lft,'integer'),
			$ilDB->quote($rgt,'integer'),
			$ilDB->quote($depth,'integer'));
		$res = $ilDB->manipulate($query);

		// Finally unlock tables
		if($this->__isMainTree())
		{
			ilDB::_unlockTables();
		}
		
		// reset deletion date
		if ($a_reset_deletion_date)
		{
			ilObject::_resetDeletedDate($a_node_id);
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
	public function getFilteredSubTree($a_node_id,$a_filter = array())
	{
		$node = $this->getNodeData($a_node_id);
		
		$first = true;
		$depth = 0;
		foreach($this->getSubTree($node) as $subnode)
		{
			if($depth and $subnode['depth'] > $depth)
			{
				continue;
			}
			if(!$first and in_array($subnode['type'],$a_filter))
			{
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
	* get all nodes in the subtree under specified node
	*
	* @access	public
	* @param	array		node_data
	* @param    boolean     with data: default is true otherwise this function return only a ref_id array
	* @return	array		2-dim (int/array) key, node_data of each subtree node including the specified node
	*/
	function getSubTree($a_node,$a_with_data = true, $a_type = "")
	{
		global $ilDB;
		
		if (!is_array($a_node))
		{
			$this->ilErr->raiseError(get_class($this)."::getSubTree(): Wrong datatype for node_data! ",$this->ilErr->WARNING);
		}

		if($a_node['lft'] < 1 or $a_node['rgt'] < 2)
		{
			$message = sprintf('%s::getSubTree(): Invalid node given! $a_node["lft"]: %s $a_node["rgt"]: %s',
								   get_class($this),
								   $a_node['lft'],
								   $a_node['rgt']);

			$this->log->write($message,$this->log->FATAL);

			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		
		
		$fields = array('integer','integer','integer');
		$data = array($a_node['lft'],$a_node['rgt'],$this->tree_id);
		$type_str = '';
		
		if(strlen($a_type))
		{
			$fields[] = 'text';
			$data[] = $a_type;
			$type_str = "AND ".$this->table_obj_data.".type= %s ";
		}
		
		$query = "SELECT * FROM ".$this->table_tree." ".
			$this->buildJoin().
			"WHERE ".$this->table_tree.".lft BETWEEN %s AND %s ".
			"AND ".$this->table_tree.".".$this->tree_pk." = %s ".
			$type_str.
			"ORDER BY ".$this->table_tree.".lft";
		$res = $ilDB->queryF($query,$fields,$data);
		while($row = $ilDB->fetchAssoc($res))
		{
			if($a_with_data)
			{
				$subtree[] = $this->fetchNodeData($row);
			}
			else
			{
				$subtree[] = $row['child'];
			}
			$this->in_tree_cache[$row['child']] = true;
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
	function getSubTreeTypes($a_node,$a_filter = 0)
	{
		$a_filter = $a_filter ? $a_filter : array();

		foreach($this->getSubtree($this->getNodeData($a_node)) as $node)
		{
			if(in_array($node["type"],$a_filter))
			{
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
	*/
	function deleteTree($a_node)
	{
		global $ilDB;
		
		if (!is_array($a_node))
		{
			$this->ilErr->raiseError(get_class($this)."::deleteTree(): Wrong datatype for node_data! ",$this->ilErr->WARNING);
		}
		if($this->__isMainTree() and $a_node[$this->tree_pk] === 1)
		{
			if($a_node['lft'] <= 1 or $a_node['rgt'] <= 2)
			{
				$message = sprintf('%s::deleteTree(): Invalid parameters given: $a_node["lft"]: %s, $a_node["rgt"] %s',
								   get_class($this),
								   $a_node['lft'],
								   $a_node['rgt']);

				$this->log->write($message,$this->log->FATAL);
				$this->ilErr->raiseError($message,$this->ilErr->WARNING);
			}
			else if(!$this->__checkDelete($a_node))
			{
				$message = sprintf('%s::deleteTree(): Check delete failed: $a_node["lft"]: %s, $a_node["rgt"] %s',
								   get_class($this),
								   $a_node['lft'],
								   $a_node['rgt']);
				$this->log->write($message,$this->log->FATAL);
				$this->ilErr->raiseError($message,$this->ilErr->WARNING);
			}

		}
		$diff = $a_node["rgt"] - $a_node["lft"] + 1;


		// LOCKED ###########################################################
		// get lft and rgt values. Don't trust parameter lft/rgt values of $a_node
		if($this->__isMainTree())
		{
			ilDB::_lockTables(array('tree' => 'WRITE'));
		}

		$query = sprintf('SELECT * FROM '.$this->table_tree.' '.
			'WHERE child = %s '.
			'AND '.$this->tree_pk.' = %s ',
			$ilDB->quote($a_node['child'],'integer'),
			$ilDB->quote($a_node[$this->tree_pk],'integer'));
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchObject($res))
		{
			$a_node['lft'] = $row->lft;
			$a_node['rgt'] = $row->rgt;
			$diff = $a_node["rgt"] - $a_node["lft"] + 1;
		}

		// delete subtree
		$query = sprintf('DELETE FROM '.$this->table_tree.' '.
			'WHERE lft BETWEEN %s AND %s '.
			'AND rgt BETWEEN %s AND %s '.
			'AND '.$this->tree_pk.' = %s',
			$ilDB->quote($a_node['lft'],'integer'),
			$ilDB->quote($a_node['rgt'],'integer'),
			$ilDB->quote($a_node['lft'],'integer'),
			$ilDB->quote($a_node['rgt'],'integer'),
			$ilDB->quote($a_node[$this->tree_pk],'integer'));
		$res = $ilDB->manipulate($query);
			
        // Performance improvement: We only close the gap, if the node 
        // is not in a trash tree, and if the resulting gap will be 
        // larger than twice the gap value 
		if ($a_node[$this->tree_pk] >= 0 && $a_node['rgt'] - $a_node['lft'] >= $this->gap * 2)
		{
			//$this->log->write('ilTree.deleteTree('.$a_node['child'].') closing gap at '.$a_node['lft'].'...'.$a_node['rgt']);
			// close gaps
			$query = sprintf('UPDATE '.$this->table_tree.' SET '.
				'lft = CASE WHEN lft > %s THEN lft - %s ELSE lft END, '.
				'rgt = CASE WHEN rgt > %s THEN rgt - %s ELSE rgt END '.
				'WHERE '.$this->tree_pk.' = %s ',
				$ilDB->quote($a_node['lft'],'integer'),
				$ilDB->quote($diff,'integer'),
				$ilDB->quote($a_node['lft'],'integer'),
				$ilDB->quote($diff,'integer'),
				$ilDB->quote($a_node[$this->tree_pk],'integer'));
				
			$res = $ilDB->manipulate($query);
		}
		else
		{
			//$this->log->write('ilTree.deleteTree('.$a_node['child'].') leaving gap open '.$a_node['lft'].'...'.$a_node['rgt']);
		}

		if($this->__isMainTree())
		{
			ilDB::_unlockTables();
		}
		// LOCKED ###########################################################
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
	function getPathFull($a_endnode_id, $a_startnode_id = 0)
	{
		$pathIds =& $this->getPathId($a_endnode_id, $a_startnode_id);

		// We retrieve the full path in a single query to improve performance
        global $ilDB;

		// Abort if no path ids were found
		if (count($pathIds) == 0)
		{
			return null;
		}

		$inClause = 'child IN (';
		for ($i=0; $i < count($pathIds); $i++)
		{
			if ($i > 0) $inClause .= ',';
			$inClause .= $ilDB->quote($pathIds[$i]);
		}
		$inClause .= ')';

		$q = 'SELECT * '.
			'FROM '.$this->table_tree.' '.
            $this->buildJoin().' '.
			'WHERE '.$inClause.' '.
            'AND '.$this->table_tree.'.'.$this->tree_pk.' = '.$this->ilDB->quote($this->tree_id).' '.
			'ORDER BY depth';
		$r = $ilDB->query($q);

		$pathFull = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$pathFull[] = $this->fetchNodeData($row);

			// is in tree cache
			if ($this->use_cache && $this->__isMainTree())
			{
				$this->in_tree_cache[$row['child']] = $row['tree'] == 1;
			}
		}
		return $pathFull;
	}
	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer		node_id of endnode
	* @param	integer		node_id of startnode (optional)
	* @return	array		all path ids from startnode to endnode
	*/
	function getPathIdsUsingNestedSets($a_endnode_id, $a_startnode_id = 0)
	{
		global $ilDB;
		
		// The nested sets algorithm is very easy to implement.
		// Unfortunately it always does a full table space scan to retrieve the path
		// regardless whether indices on lft and rgt are set or not.
		// (At least, this is what happens on MySQL 4.1).
		// This algorithms performs well for small trees which are deeply nested.
		
		if (!isset($a_endnode_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getPathId(): No endnode_id given! ",$this->ilErr->WARNING);
		}
		
		$fields = array('integer','integer','integer');
		$data = array($a_endnode_id,$this->tree_id,$this->tree_id);
		
		$query = "SELECT T2.child ".
			"FROM ".$this->table_tree." T1, ".$this->table_tree." T2 ".
			"WHERE T1.child = %s ".
			"AND T1.lft BETWEEN T2.lft AND T2.rgt ".
			"AND T1.".$this->tree_pk." = %s ".
			"AND T2.".$this->tree_pk." = %s ".
			"ORDER BY T2.depth";
		$res = $ilDB->queryF($query,$fields,$data);
		
		$takeId = $a_startnode_id == 0;
		while($row = $ilDB->fetchAssoc($res))
		{
			if ($takeId || $row['child'] == $a_startnode_id)
			{
				$takeId = true;
				$pathIds[] = $row['child'];
			}
		}
		return $pathIds ? $pathIds : array();
	}
	
	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer		node_id of endnode
	* @param	integer		node_id of startnode (optional)
	* @return	array		all path ids from startnode to endnode
	*/
	function getPathIdsUsingAdjacencyMap($a_endnode_id, $a_startnode_id = 0)
	{
		// The adjacency map algorithm is harder to implement than the nested sets algorithm.
		// This algorithms performs an index search for each of the path element.
		// This algorithms performs well for large trees which are not deeply nested.

		// The $takeId variable is used, to determine if a given id shall be included in the path
		$takeId = $a_startnode_id == 0;
		
		if (!isset($a_endnode_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getPathId(): No endnode_id given! ",$this->ilErr->WARNING);
		}
		
		global $log, $ilDB;
		
		$types = array('integer','integer');
		$data = array($a_endnode_id,$this->tree_id);
		
		$query = 'SELECT t.depth, t.parent '.
			'FROM '.$this->table_tree.' t '.
			'WHERE child = %s '.
			'AND '.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,$types,$data);
		
		if($res->numRows() == 0)
		{
			return array();
		}
		
		$row = $ilDB->fetchAssoc($res);
		$nodeDepth = $row['depth'];
		$parentId = $row['parent'];
			//$this->writelog('getIdsUsingAdjacencyMap depth='.$nodeDepth);

		// Fetch the node ids. For shallow depths we can fill in the id's directly.	
		$pathIds = array();
		if ($nodeDepth == 1)
		{
				$takeId = $takeId || $a_endnode_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else if ($nodeDepth == 2)
		{
				$takeId = $takeId || $parentId == $a_startnode_id;
				if ($takeId) $pathIds[] = $parentId;
				$takeId = $takeId || $a_endnode_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else if ($nodeDepth == 3)
		{
				$takeId = $takeId || $this->root_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $this->root_id;
				$takeId = $takeId || $parentId == $a_startnode_id;
				if ($takeId) $pathIds[] = $parentId;
				$takeId = $takeId || $a_endnode_id == $a_startnode_id;
				if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else if ($nodeDepth < 32)
		{
			// Adjacency Map Tree performs better than
			// Nested Sets Tree even for very deep trees.
			// The following code construct nested self-joins
			// Since we already know the root-id of the tree and
			// we also know the id and parent id of the current node,
			// we only need to perform $nodeDepth - 3 self-joins. 
			// We can further reduce the number of self-joins by 1
			// by taking into account, that each row in table tree
			// contains the id of itself and of its parent.
			$qSelect = 't1.child c0';
			$qJoin = '';
			for ($i = 1; $i < $nodeDepth - 2; $i++)
			{
				$qSelect .= ', t'.$i.'.parent c'.$i;
				$qJoin .= ' JOIN '.$this->table_tree.' t'.$i.' ON '.
							't'.$i.'.child=t'.($i - 1).'.parent AND '.
							't'.$i.'.'.$this->tree_pk.' = '.(int) $this->tree_id;
			}
			
			$types = array('integer','integer');
			$data = array($this->tree_id,$parentId);
			$query = 'SELECT '.$qSelect.' '.
				'FROM '.$this->table_tree.' t0 '.$qJoin.' '.
				'WHERE t0.'.$this->tree_pk.' = %s '.
				'AND t0.child = %s ';
				
			$ilDB->setLimit(1);
			$res = $ilDB->queryF($query,$types,$data);

			if ($res->numRows() == 0)
			{
				return array();
			}
			$row = $ilDB->fetchAssoc($res);
			
			$takeId = $takeId || $this->root_id == $a_startnode_id;			
			if ($takeId) $pathIds[] = $this->root_id;
			for ($i = $nodeDepth - 4; $i >=0; $i--)
			{
				$takeId = $takeId || $row['c'.$i] == $a_startnode_id;
				if ($takeId) $pathIds[] = $row['c'.$i];
			}
			$takeId = $takeId || $parentId == $a_startnode_id;
			if ($takeId) $pathIds[] = $parentId;
			$takeId = $takeId || $a_endnode_id == $a_startnode_id;
			if ($takeId) $pathIds[] = $a_endnode_id;
		}
		else
		{
			// Fall back to nested sets tree for extremely deep tree structures
			return $this->getPathIdsUsingNestedSets($a_endnode_id, $a_startnode_id);
		}
		
		return $pathIds;
	}

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* @access	public
	* @param	integer		node_id of endnode
	* @param	integer		node_id of startnode (optional)
	* @return	array		all path ids from startnode to endnode
	*/
	function getPathId($a_endnode_id, $a_startnode_id = 0)
	{
		// path id cache
		if ($this->use_cache && isset($this->path_id_cache[$a_endnode_id][$a_startnode_id]))
		{
//echo "<br>getPathIdhit";
			return $this->path_id_cache[$a_endnode_id][$a_startnode_id];
		}
//echo "<br>miss";

		$pathIds =& $this->getPathIdsUsingAdjacencyMap($a_endnode_id, $a_startnode_id);
		
		$this->path_id_cache[$a_endnode_id][$a_startnode_id] = $pathIds;
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
	function getNodePathForTitlePath($titlePath, $a_startnode_id = null)
	{
		global $ilDB, $log;
		//$log->write('getNodePathForTitlePath('.implode('/',$titlePath));
		
		// handle empty title path
		if ($titlePath == null || count($titlePath) == 0)
		{
			if ($a_startnode_id == 0)
			{
				return null;
			}
			else
			{
				return $this->getNodePath($a_startnode_id);
			}
		}

		// fetch the node path up to the startnode
		if ($a_startnode_id != null && $a_startnode_id != 0)
		{
			// Start using the node path to the root of the relative path
			$nodePath = $this->getNodePath($a_startnode_id);
			$parent = $a_startnode_id;
		}
		else
		{
			// Start using the root of the tree
			$nodePath = array();
			$parent = 0;
		}

		
		// Convert title path into Unicode Normal Form C
		// This is needed to ensure that we can compare title path strings with
		// strings from the database.
		require_once('include/Unicode/UtfNormal.php');
		$inClause = 'd.title IN (';
		for ($i=0; $i < count($titlePath); $i++)
		{
			$titlePath[$i] = strtolower(UtfNormal::toNFC($titlePath[$i]));
			if ($i > 0) $inClause .= ',';
			$inClause .= $ilDB->quote($titlePath[$i]);
		}
		$inClause .= ')';

		// Fetch all rows that are potential path elements
		if ($this->table_obj_reference)
		{
			$joinClause = 'JOIN '.$this->table_obj_reference.'  r ON t.child = r.'.$this->ref_pk.' '.
				'JOIN '.$this->table_obj_data.' d ON r.'.$this->obj_pk.' = d.'.$this->obj_pk;
		}
		else
		{
			$joinClause = 'JOIN '.$this->table_obj_data.'  d ON t.child = d.'.$this->obj_pk;
		}
		// The ORDER BY clause in the following SQL statement ensures that,
		// in case of a multiple objects with the same title, always the Object
		// with the oldest ref_id is chosen.
		// This ensure, that, if a new object with the same title is added,
		// WebDAV clients can still work with the older object.
		$q = 'SELECT t.depth, t.parent, t.child, d.'.$this->obj_pk.' obj_id, d.type, d.title '.
			'FROM '.$this->table_tree.'  t '.
			$joinClause.' '.
			'WHERE '.$inClause.' '.
			'AND t.depth <= '.(count($titlePath)+count($nodePath)).' '.
			'AND t.tree = 1 '.
			'ORDER BY t.depth, t.child ASC';
		$r = $ilDB->query($q);
		
		$rows = array();
		while ($row = $r->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$row['title'] = UtfNormal::toNFC($row['title']);
			$row['ref_id'] = $row['child'];
			$rows[] = $row;
		}

		// Extract the path elements from the fetched rows
		for ($i = 0; $i < count($titlePath); $i++) {
			$pathElementFound = false; 
			foreach ($rows as $row) {
				if ($row['parent'] == $parent && 
				strtolower($row['title']) == $titlePath[$i])
				{
					// FIXME - We should test here, if the user has 
					// 'visible' permission for the object.
					$nodePath[] = $row;
					$parent = $row['child'];
					$pathElementFound = true;
					break;
				}
			}
			// Abort if we haven't found a path element for the current depth
			if (! $pathElementFound)
			{
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
	function getNodePath($a_endnode_id, $a_startnode_id = 0)
	{
		global $ilDB;

		$pathIds = $this->getPathId($a_endnode_id, $a_startnode_id);

		// Abort if no path ids were found
		if (count($pathIds) == 0)
		{
			return null;
		}

		
		$types = array();
		$data = array();
		for ($i = 0; $i < count($pathIds); $i++)
		{
			$types[] = 'integer';
			$data[] = $pathIds[$i];
		}

		$query = 'SELECT t.depth,t.parent,t.child,d.obj_id,d.type,d.title '.
			'FROM '.$this->table_tree.' t '.
			'JOIN '.$this->table_obj_reference.' r ON r.ref_id = t.child '.
			'JOIN '.$this->table_obj_data.' d ON d.obj_id = r.obj_id '.
			'WHERE '.$ilDB->in('t.child',$data,false,'integer').' '.
			'ORDER BY t.depth ';
			
		$res = $ilDB->queryF($query,$types,$data);

		$titlePath = array();
		while ($row = $ilDB->fetchAssoc($res))
		{
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
	*/
	function checkTree()
	{
		global $ilDB;
		
		$types = array('integer');
		$query = 'SELECT lft,rgt FROM '.$this->table_tree.' '.
			'WHERE '.$this->tree_pk.' = %s ';
		
		$res = $ilDB->queryF($query,$types,array($this->tree_id));
		while ($row = $ilDB->fetchObject($res))
		{
			$lft[] = $row->lft;
			$rgt[] = $row->rgt;
		}

		$all = array_merge($lft,$rgt);
		$uni = array_unique($all);

		if (count($all) != count($uni))
		{
			$message = sprintf('%s::checkTree(): Tree is corrupted!',
							   get_class($this));

			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		return true;
	}

	/**
	* check, if all childs of tree nodes exist in object table
	*/
	function checkTreeChilds($a_no_zero_child = true)
	{
		global $ilDB;
		
		$query = 'SELECT * FROM '.$this->table_tree.' '.
				'WHERE '.$this->tree_pk.' = %s '.
				'ORDER BY lft';
		$r1 = $ilDB->queryF($query,array('integer'),array($this->tree_id));
		
		while ($row = $ilDB->fetchAssoc($r1))
		{
//echo "tree:".$row[$this->tree_pk].":lft:".$row["lft"].":rgt:".$row["rgt"].":child:".$row["child"].":<br>";
			if (($row["child"] == 0) && $a_no_zero_child)
			{
				$this->ilErr->raiseError(get_class($this)."::checkTreeChilds(): Tree contains child with ID 0!",$this->ilErr->WARNING);
			}

			if ($this->table_obj_reference)
			{
				// get object reference data
				$query = 'SELECT * FROM '.$this->table_obj_reference.' WHERE '.$this->ref_pk.' = %s ';
				$r2 = $ilDB->queryF($query,array('integer'),array($row['child']));
				
//echo "num_childs:".$r2->numRows().":<br>";
				if ($r2->numRows() == 0)
				{
					$this->ilErr->raiseError(get_class($this)."::checkTree(): No Object-to-Reference entry found for ID ".
						$row["child"]."!",$this->ilErr->WARNING);
				}
				if ($r2->numRows() > 1)
				{
					$this->ilErr->raiseError(get_class($this)."::checkTree(): More Object-to-Reference entries found for ID ".
						$row["child"]."!",$this->ilErr->WARNING);
				}

				// get object data
				$obj_ref = $ilDB->fetchAssoc($r2);

				$query = 'SELECT * FROM '.$this->table_obj_data.' WHERE '.$this->obj_pk.' = %s';
				$r3 = $ilDB->queryF($query,array('integer'),array($obj_ref[$this->obj_pk]));
				if ($r3->numRows() == 0)
				{
					$this->ilErr->raiseError(get_class($this)."::checkTree(): No child found for ID ".
						$obj_ref[$this->obj_pk]."!",$this->ilErr->WARNING);
				}
				if ($r3->numRows() > 1)
				{
					$this->ilErr->raiseError(get_class($this)."::checkTree(): More childs found for ID ".
						$obj_ref[$this->obj_pk]."!",$this->ilErr->WARNING);
				}

			}
			else
			{
				// get only object data
				$query = 'SELECT * FROM '.$this->table_obj_data.' WHERE '.$this->obj_pk.' = %s';
				$r2 = $ilDB->queryF($query,array('integer'),array($row['child']));
//echo "num_childs:".$r2->numRows().":<br>";
				if ($r2->numRows() == 0)
				{
					$this->ilErr->raiseError(get_class($this)."::checkTree(): No child found for ID ".
						$row["child"]."!",$this->ilErr->WARNING);
				}
				if ($r2->numRows() > 1)
				{
					$this->ilErr->raiseError(get_class($this)."::checkTree(): More childs found for ID ".
						$row["child"]."!",$this->ilErr->WARNING);
				}
			}
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
		global $ilDB;
		
		$query = 'SELECT MAX(depth) depth FROM '.$this->table_tree;
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
	function getDepth($a_node_id)
	{
		global $ilDB;
		
		if ($a_node_id)
		{
			$query = 'SELECT depth FROM '.$this->table_tree.' '.
				'WHERE child = %s '.
				'AND '.$this->tree_pk.' = %s ';
			$res = $ilDB->queryF($query,array('integer','integer'),array($a_node_id,$this->tree_id));
			$row = $ilDB->fetchObject($res);

			return $row->depth;
		}
		else
		{
			return 1;
		}
	}


	/**
	* get all information of a node.
	* get data of a specific node from tree and object_data
	* @access	public
	* @param	integer		node id
	* @return	array		2-dim (int/str) node_data
	*/
	// BEGIN WebDAV: Pass tree id to this method
	//function getNodeData($a_node_id)
	function getNodeData($a_node_id, $a_tree_pk = null)
	// END PATCH WebDAV: Pass tree id to this method
	{
		global $ilDB;
		
		if (!isset($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getNodeData(): No node_id given! ",$this->ilErr->WARNING);
		}
		if($this->__isMainTree())
		{
			if($a_node_id < 1)
			{
				$message = sprintf('%s::getNodeData(): No valid parameter given! $a_node_id: %s',
								   get_class($this),
								   $a_node_id);

				$this->log->write($message,$this->log->FATAL);
				$this->ilErr->raiseError($message,$this->ilErr->WARNING);
			}
		}

		// BEGIN WebDAV: Pass tree id to this method
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			$this->buildJoin().
			'WHERE '.$this->table_tree.'.child = %s '.
			'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
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
	function fetchNodeData($a_row)
	{
		global $objDefinition, $lng, $ilBench,$ilDB;

		//$ilBench->start("Tree", "fetchNodeData_getRow");
		$data = $a_row;
		$data["desc"] = $a_row["description"];  // for compability
		//$ilBench->stop("Tree", "fetchNodeData_getRow");

		// multilingual support systemobjects (sys) & categories (db)
		//$ilBench->start("Tree", "fetchNodeData_readDefinition");
		if (is_object($objDefinition))
		{
			$translation_type = $objDefinition->getTranslationType($data["type"]);
		}
		//$ilBench->stop("Tree", "fetchNodeData_readDefinition");

		if ($translation_type == "sys")
		{
			//$ilBench->start("Tree", "fetchNodeData_getLangData");
			if ($data["type"] == "rolf" and $data["obj_id"] != ROLE_FOLDER_ID)
			{
				$data["description"] = $lng->txt("obj_".$data["type"]."_local_desc").$data["title"].$data["desc"];
				$data["desc"] = $lng->txt("obj_".$data["type"]."_local_desc").$data["title"].$data["desc"];
				$data["title"] = $lng->txt("obj_".$data["type"]."_local");
			}
			else
			{
				$data["title"] = $lng->txt("obj_".$data["type"]);
				$data["description"] = $lng->txt("obj_".$data["type"]."_desc");
				$data["desc"] = $lng->txt("obj_".$data["type"]."_desc");
			}
			//$ilBench->stop("Tree", "fetchNodeData_getLangData");
		}
		elseif ($translation_type == "db")
		{

			// Try to retrieve object translation from cache
			if ($this->use_cache &&
				array_key_exists($data["obj_id"].'.'.$lang_code, $this->translation_cache)) {

				$key = $data["obj_id"].'.'.$lang_code;
				$data["title"] = $this->translation_cache[$key]['title'];
				$data["description"] = $this->translation_cache[$key]['description'];
				$data["desc"] = $this->translation_cache[$key]['desc'];
			} else {
				// Object translation is not in cache, read it from database

				//$ilBench->start("Tree", "fetchNodeData_getTranslation");
				$query = 'SELECT title,description FROM object_translation '.
					'WHERE obj_id = %s '.
					'AND lang_code = %s '.
					'AND NOT lang_default = %s';

				$res = $ilDB->queryF($query,array('integer','text','integer'),array(
					$data['obj_id'],
					$this->lang_code,
					1));
				$row = $ilDB->fetchObject($res);

				if ($row)
				{
					$data["title"] = $row->title;
					$data["description"] = ilUtil::shortenText($row->description,MAXLENGTH_OBJ_DESC,true);
					$data["desc"] = $row->description;
				}
				//$ilBench->stop("Tree", "fetchNodeData_getTranslation");

				// Store up to 1000 object translations in cache
				if ($this->use_cache && count($this->translation_cache) < 1000)
				{
					$key = $data["obj_id"].'.'.$lang_code;
					$this->translation_cache[$key] = array();
					$this->translation_cache[$key]['title'] = $data["title"] ;
					$this->translation_cache[$key]['description'] = $data["description"];
					$this->translation_cache[$key]['desc'] = $data["desc"];
				}
			}
		}
		
		// TODO: Handle this switch by module.xml definitions
		if($data['type'] == 'crsr' or $data['type'] == 'catr')
		{
			include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
			$data['title'] = ilContainerReference::_lookupTargetTitle($data['obj_id']);
		}

		return $data ? $data : array();
	}


	/**
	* get all information of a node.
	* get data of a specific node from tree and object_data
	* @access	public
	* @param	integer		node id
	* @return	boolean		true, if node id is in tree
	*/
	function isInTree($a_node_id)
	{
		global $ilDB;

		if (!isset($a_node_id))
		{
			return false;
			#$this->ilErr->raiseError(get_class($this)."::getNodeData(): No node_id given! ",$this->ilErr->WARNING);
		}
		
		// is in tree cache
		if ($this->use_cache && isset($this->in_tree_cache[$a_node_id]))
		{
//echo "<br>in_tree_hit";
			return $this->in_tree_cache[$a_node_id];
		}
		
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE '.$this->table_tree.'.child = %s '.
			'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s';
			
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$a_node_id,
			$this->tree_id));

		if ($res->numRows() > 0)
		{
			$this->in_tree_cache[$a_node_id] = true;
			return true;
		}
		else
		{
			$this->in_tree_cache[$a_node_id] = false;
			return false;
		}
	}

	/**
	* get data of parent node from tree and object_data
	* @access	public
 	* @param	integer		node id
	* @return	array
	*/
	function getParentNodeData($a_node_id)
	{
		global $ilDB;
		global $ilLog;
		
		if (!isset($a_node_id))
		{
			$ilLog->logStack();
			$this->ilErr->raiseError(get_class($this)."::getParentNodeData(): No node_id given! ",$this->ilErr->WARNING);
		}

		if ($this->table_obj_reference)
		{
			// Use inner join instead of left join to improve performance
			$innerjoin = "JOIN ".$this->table_obj_reference." ON v.child=".$this->table_obj_reference.".".$this->ref_pk." ".
				  		"JOIN ".$this->table_obj_data." ON ".$this->table_obj_reference.".".$this->obj_pk."=".$this->table_obj_data.".".$this->obj_pk." ";
		}
		else
		{
			// Use inner join instead of left join to improve performance
			$innerjoin = "JOIN ".$this->table_obj_data." ON v.child=".$this->table_obj_data.".".$this->obj_pk." ";
		}

		$query = 'SELECT * FROM '.$this->table_tree.' s, '.$this->table_tree.' v '.
			$innerjoin.
			'WHERE s.child = %s '.
			'AND s.parent = v.child '.
			'AND s.lft > v.lft '.
			'AND s.rgt < v.rgt '.
			'AND s.'.$this->tree_pk.' = %s '.
			'AND v.'.$this->tree_pk.' = %s';
		$res = $ilDB->queryF($query,array('integer','integer','integer'),array(
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
	function isGrandChild($a_startnode_id,$a_querynode_id)
	{
		global $ilDB;
		
		if (!isset($a_startnode_id) or !isset($a_querynode_id))
		{
			return false;
		}

		$query = 'SELECT * FROM '.$this->table_tree.' s, '.$this->table_tree.' v '.
			'WHERE s.child = %s '.
			'AND v.child = %s '.
			'AND s.'.$this->tree_pk.' = %s '.
			'AND v.'.$this->tree_pk.' = %s '.
			'AND v.lft BETWEEN s.lft AND s.rgt '.
			'AND v.rgt BETWEEN s.lft AND s.rgt';
		$res = $ilDB->queryF(
			$query,
			array('integer','integer','integer','integer'),
			array(
				$a_startnode_id,
				$a_querynode_id,
				$this->tree_id,
				$this->tree_id));
		
		return $res->numRows();
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
		global $ilDB;

		// FOR SECURITY addTree() IS NOT ALLOWED ON MAIN TREE
		if($this->__isMainTree())
		{
			$message = sprintf('%s::addTree(): Operation not allowed on main tree! $a_tree_if: %s $a_node_id: %s',
							   get_class($this),
							   $a_tree_id,
							   $a_node_id);
			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		if (!isset($a_tree_id))
		{
			$this->ilErr->raiseError(get_class($this)."::addTree(): No tree_id given! ",$this->ilErr->WARNING);
		}

		if ($a_node_id <= 0)
		{
			$a_node_id = $a_tree_id;
		}

		$query = 'INSERT INTO '.$this->table_tree.' ('.
			$this->tree_pk.', child,parent,lft,rgt,depth) '.
			'VALUES '.
			'(%s,%s,%s,%s,%s,%s)';
		$res = $ilDB->manipulateF($query,array('integer','integer','integer','integer','integer','integer'),array(
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
	* // TODO: method needs revision
	* @param	integer		a_tree_id: obj_id of object where tree belongs to
	* @param	integer		a_type_id: type of object
	* @access	public
	*/
	function getNodeDataByType($a_type)
	{
		global $ilDB;
		
		if (!isset($a_type) or (!is_string($a_type)))
		{
			$this->ilErr->raiseError(get_class($this)."::getNodeDataByType(): Type not given or wrong datatype!",$this->ilErr->WARNING);
		}

		$data = array();	// node_data
		$row = "";			// fetched row
		$left = "";			// tree_left
		$right = "";		// tree_right

		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE '.$this->tree_pk.' = %s '.
			'AND parent = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$this->tree_id,
			0));
		
		while ($row = $ilDB->fetchObject($res))
		{
			$left = $row->lft;
			$right = $row->rgt;
		}

		$query = 'SELECT * FROM '.$this->table_tree.' '.
			$this->buildJoin().
			'WHERE '.$this->table_obj_data.'.type = %s '.
			'AND '.$this->table_tree.'.lft BETWEEN %s AND %s '.
			'AND '.$this->table_tree.'.rgt BETWEEN %s AND %s '.
			'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('text','integer','integer','integer','integer','integer'),array(
			$a_type,
			$left,
			$right,
			$left,
			$right,
			$this->tree_id));

		while($row = $ilDB->fetchAssoc($res))
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
		global $ilDB;
		
		// OPERATION NOT ALLOWED ON MAIN TREE
		if($this->__isMainTree())
		{
			$message = sprintf('%s::removeTree(): Operation not allowed on main tree! $a_tree_if: %s',
							   get_class($this),
							   $a_tree_id);
			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}
		if (!$a_tree_id)
		{
			$this->ilErr->raiseError(get_class($this)."::removeTree(): No tree_id given! Action aborted",$this->ilErr->MESSAGE);
		}

		$query = 'DELETE FROM '.$this->table_tree.
			' WHERE '.$this->tree_pk.' = %s ';
		$res = $ilDB->manipulateF($query,array('integer'),array($a_tree_id));
		return true;
	}

	/**
	* save subtree: delete a subtree (defined by node_id) to a new tree
	* with $this->tree_id -node_id. This is neccessary for undelete functionality
	* @param	integer	node_id
	* @return	integer
	* @access	public
	*/
	function saveSubTree($a_node_id, $a_set_deleted = false)
	{
		global $ilDB;
		
		if (!$a_node_id)
		{
			$message = sprintf('%s::saveSubTree(): No valid parameter given! $a_node_id: %s',
							   get_class($this),
							   $a_node_id);
			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		// LOCKED ###############################################
		if($this->__isMainTree())
		{
			ilDB::_lockTables(array('tree' => 'WRITE',
				'object_reference' => 'WRITE'));
		}

		// GET LEFT AND RIGHT VALUE
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE '.$this->tree_pk.' = %s '.
			'AND child = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$this->tree_id,
			$a_node_id));

		while($row = $ilDB->fetchObject($res))
		{
			$lft = $row->lft;
			$rgt = $row->rgt;
		}

		// GET ALL SUBNODES
		$query = 'SELECT child FROM '.$this->table_tree.' '.
			'WHERE '.$this->tree_pk.' = %s '.
			'AND lft BETWEEN %s AND %s ';
		$res = $ilDB->queryF($query,array('integer','integer','integer'),array(
			$this->tree_id,
			$lft,
			$rgt));

		$subnodes = array();
		while($row = $ilDB->fetchAssoc($res))
		{
			$subnodes[] = $row['child'];
		}
		
		if(!count($subnodes))
		{
			// possibly already deleted

			// Unlock locked tables before returning
			if($this->__isMainTree())
			{
				ilDB::_unlockTables();
			}

			return false;
		}

		// SAVE SUBTREE
		foreach($subnodes as $child)
		{
			// set node as deleted
			if ($a_set_deleted)
			{
				// TODO: new method that expects an array of ids
				ilObject::_setDeletedDate($child);
			}
		}
		
		// Set the nodes deleted (negative tree id)
		$query = 'UPDATE '.$this->table_tree.' '.
			'SET tree = %s '.
			'WHERE '.$this->tree_pk.' = %s '.
			'AND lft BETWEEN %s AND %s ';
		$res = $ilDB->manipulateF($query,array('integer','integer','integer','integer'),array(
			-$a_node_id,
			$this->tree_id,
			$lft,
			$rgt));
		
		if($this->__isMainTree())
		{
			ilDB::_unlockTables();
		}

		// LOCKED ###############################################
		return true;
	}

	/**
	* This is a wrapper for isSaved() with a more useful name
	*/
	function isDeleted($a_node_id)
	{
		return $this->isSaved($a_node_id);
	}

	/**
	* check if node is saved
	*/
	function isSaved($a_node_id)
	{
		global $ilDB;
		
		// is saved cache
		if ($this->use_cache && isset($this->is_saved_cache[$a_node_id]))
		{
//echo "<br>issavedhit";
			return $this->is_saved_cache[$a_node_id];
		}
		
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE child = %s ';
		$res = $ilDB->queryF($query,array('integer'),array($a_node_id));
		$row = $ilDB->fetchAssoc($res);

		if ($row[$this->tree_pk] < 0)
		{
			$this->is_saved_cache[$a_node_id] = true;
			return true;
		}
		else
		{
			$this->is_saved_cache[$a_node_id] = false;
			return false;
		}
	}



	/**
	* get data saved/deleted nodes
	* @return	array	data
	* @param	integer	id of parent object of saved object
	* @access	public
	*/
	function getSavedNodeData($a_parent_id)
	{
		global $ilDB;
		
		if (!isset($a_parent_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getSavedNodeData(): No node_id given!",$this->ilErr->WARNING);
		}

		$query = 'SELECT * FROM '.$this->table_tree.' '.
			$this->buildJoin().
			'WHERE '.$this->table_tree.'.'.$this->tree_pk.' < %s '.
			'AND '.$this->table_tree.'.parent = %s';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			0,
			$a_parent_id));

		while($row = $ilDB->fetchAssoc($res))
		{
			$saved[] = $this->fetchNodeData($row);
		}

		return $saved ? $saved : array();
	}

	/**
	* get parent id of given node
	* @access	public
	* @param	integer	node id
	* @return	integer	parent id
	*/
	function getParentId($a_node_id)
	{
		global $ilDB;
		
		if (!isset($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getParentId(): No node_id given! ",$this->ilErr->WARNING);
		}

		$query = 'SELECT parent FROM '.$this->table_tree.' '.
			'WHERE child = %s '.
			'AND '.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$a_node_id,
			$this->tree_id));

		$row = $ilDB->fetchObject($res);
		return $row->parent;
	}

	/**
	* get left value of given node
	* @access	public
	* @param	integer	node id
	* @return	integer	left value
	*/
	function getLeftValue($a_node_id)
	{
		global $ilDB;
		
		if (!isset($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getLeftValued(): No node_id given! ",$this->ilErr->WARNING);
		}

		$query = 'SELECT lft FROM '.$this->table_tree.' '.
			'WHERE child = %s '.
			'AND '.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
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
	*/
	function getChildSequenceNumber($a_node, $type = "")
	{
		global $ilDB;
		
		if (!isset($a_node))
		{
			$this->ilErr->raiseError(get_class($this)."::getChildSequenceNumber(): No node_id given! ",$this->ilErr->WARNING);
		}
		
		if($type)
		{
			$query = 'SELECT count(*) cnt FROM '.$this->table_tree.' '.
				$this->buildJoin().
				'WHERE lft <= %s '.
				'AND type = %s '.
				'AND parent = %s '.
				'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s ';

			$res = $ilDB->queryF($query,array('integer','text','integer','integer'),array(
				$a_node['lft'],
				$type,
				$a_node['parent'],
				$this->tree_id));
		}
		else
		{
			$query = 'SELECT count(*) cnt FROM '.$this->table_tree.' '.
				$this->buildJoin().
				'WHERE lft <= %s '.
				'AND parent = %s '.
				'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s ';

			$res = $ilDB->queryF($query,array('integer','integer','integer'),array(
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
	function readRootId()
	{
		global $ilDB;
		
		$query = 'SELECT child FROM '.$this->table_tree.' '.
			'WHERE parent = %s '.
			'AND '.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
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
	function getRootId()
	{
		return $this->root_id;
	}
	function setRootId($a_root_id)
	{
		$this->root_id = $a_root_id;
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

	/**
	* get node data of successor node
	*
	* @access	public
	* @param	integer		node id
	* @return	array		node data array
	*/
	function fetchSuccessorNode($a_node_id, $a_type = "")
	{
		global $ilDB;
		
		if (!isset($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getNodeData(): No node_id given! ",$this->ilErr->WARNING);
		}

		// get lft value for current node
		$query = 'SELECT lft FROM '.$this->table_tree.' '.
			'WHERE '.$this->table_tree.'.child = %s '.
			'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$a_node_id,
			$this->tree_id));

		$curr_node = $ilDB->fetchAssoc($res);
		
		if($a_type)
		{
			$query = 'SELECT * FROM '.$this->table_tree.' '.
				$this->buildJoin().
				'WHERE lft > %s '.
				'AND '.$this->table_obj_data.'.type = %s '.
				'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s '.
				'ORDER BY lft ';
			$ilDB->setLimit(1);
			$res = $ilDB->queryF($query,array('integer','text','integer'),array(
				$curr_node['lft'],
				$a_type,
				$this->tree_id));
		}
		else
		{
			$query = 'SELECT * FROM '.$this->table_tree.' '.
				$this->buildJoin().
				'WHERE lft > %s '.
				'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s '.
				'ORDER BY lft ';
			$ilDB->setLimit(1);
			$res = $ilDB->queryF($query,array('integer','integer'),array(
				$curr_node['lft'],
				$this->tree_id));
		}

		if ($res->numRows() < 1)
		{
			return false;
		}
		else
		{
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
	*/
	function fetchPredecessorNode($a_node_id, $a_type = "")
	{
		global $ilDB;
		
		if (!isset($a_node_id))
		{
			$this->ilErr->raiseError(get_class($this)."::getNodeData(): No node_id given! ",$this->ilErr->WARNING);
		}

		// get lft value for current node
		$query = 'SELECT lft FROM '.$this->table_tree.' '.
			'WHERE '.$this->table_tree.'.child = %s '.
			'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$a_node_id,
			$this->tree_id));

		$curr_node = $ilDB->fetchAssoc($res);
		
		if($a_type)
		{
			$query = 'SELECT * FROM '.$this->table_tree.' '.
				$this->buildJoin().
				'WHERE lft < %s '.
				'AND '.$this->table_obj_data.'.type = %s '.
				'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s '.
				'ORDER BY lft DESC';
			$ilDB->setLimit(1);
			$res = $ilDB->queryF($query,array('integer','text','integer'),array(
				$curr_node['lft'],
				$a_type,
				$this->tree_id));
		}
		else
		{
			$query = 'SELECT * FROM '.$this->table_tree.' '.
				$this->buildJoin().
				'WHERE lft < %s '.
				'AND '.$this->table_tree.'.'.$this->tree_pk.' = %s '.
				'ORDER BY lft DESC';
			$ilDB->setLimit(1);
			$res = $ilDB->queryF($query,array('integer','integer'),array(
				$curr_node['lft'],
				$this->tree_id));
		}
		
		if ($res->numRows() < 1)
		{
			return false;
		}
		else
		{
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
	function renumber($node_id = 1, $i = 1)
	{
		// LOCKED ###################################
		if($this->__isMainTree())
		{
			ilDB::_lockTables(array($this->table_tree => 'WRITE',
									 $this->table_obj_data => 'WRITE',
									 $this->table_obj_reference => 'WRITE',
									 'object_translation' => 'WRITE',
									 'object_data od' => 'WRITE',
									 'container_reference cr' => 'WRITE'));
		}
		$return = $this->__renumber($node_id,$i);
		if($this->__isMainTree())
		{
			ilDB::_unlockTables();
		}
		// LOCKED ###################################
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
	function __renumber($node_id = 1, $i = 1)
	{
		global $ilDB;
		
		$query = 'UPDATE '.$this->table_tree.' SET lft = %s WHERE child = %s';
		$res = $ilDB->manipulateF($query,array('integer','integer'),array(
			$i,
			$node_id));

		$childs = $this->getChilds($node_id);

		foreach ($childs as $child)
		{
			$i = $this->__renumber($child["child"],$i+1);
		}
		$i++;
		
		// Insert a gap at the end of node, if the node has children
		if (count($childs) > 0)
		{
			$i += $this->gap * 2;
		}
		
		
		$query = 'UPDATE '.$this->table_tree.' SET rgt = %s WHERE child = %s';
		$res = $ilDB->manipulateF($query,array('integer','integer'),array(
			$i,
			$node_id));
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
	function checkForParentType($a_ref_id,$a_type)
	{
		// Try to return a cached result
		if ($this->use_cache &&
				array_key_exists($a_ref_id.'.'.$a_type, $this->parent_type_cache)) {
			return $this->parent_type_cache[$a_ref_id.'.'.$a_type];
		}

		if(!$this->isInTree($a_ref_id))
		{
            // Store up to 1000 results in cache
            if ($this->use_cache && count($this->parent_type_cache) < 1000) {
                $this->parent_type_cache[$a_ref_id.'.'.$a_type] = false;
            }
			return false;
		}
		$path = array_reverse($this->getPathFull($a_ref_id));

		foreach($path as $node)
		{
			if($node["type"] == $a_type)
			{
            // Store up to 1000 results in cache
            if ($this->use_cache && count($this->parent_type_cache) < 1000) {
                $this->parent_type_cache[$a_ref_id.'.'.$a_type] = $node["child"];
            }
				return $node["child"];
			}
		}
		// Store up to 1000 results in cache
		if ($this->use_cache && count($this->parent_type_cache) < 1000) {
			$this->parent_type_cache[$a_ref_id.'.'.$a_type] = false;
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
	*/
	function _removeEntry($a_tree,$a_child,$a_db_table = "tree")
	{
		global $ilDB,$ilLog,$ilErr;

		if($a_db_table === 'tree')
		{
			if($a_tree == 1 and $a_child == ROOT_FOLDER_ID)
			{
				$message = sprintf('%s::_removeEntry(): Tried to delete root node! $a_tree: %s $a_child: %s',
								   get_class($this),
								   $a_tree,
								   $a_child);
				$ilLog->write($message,$ilLog->FATAL);
				$ilErr->raiseError($message,$ilErr->WARNING);
			}
		}
		
		$query = 'DELETE FROM '.$a_db_table.' '.
			'WHERE tree = %s '.
			'AND child = %s ';
		$res = $ilDB->manipulateF($query,array('integer','integer'),array(
			$a_tree,
			$a_child));
		
	}
	
	// PRIVATE METHODS
	/**
	* Check if operations are done on main tree
	*
 	* @access	private
	* @return boolean
	*/
	function __isMainTree()
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
	*/
	function __checkDelete($a_node)
	{
		global $ilDB;
		
		// get subtree by lft,rgt
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE lft >= %s '.
			'AND rgt <= %s '.
			'AND '.$this->tree_pk.' = %s ';
		$res = $ilDB->queryF($query,array('integer','integer','integer'),array(
			$a_node['lft'],
			$a_node['rgt'],
			$a_node[$this->tree_pk]));

		$counter = (int) $lft_childs = array();
		while($row = $ilDB->fetchObject($res))
		{
			$lft_childs[$row->child] = $row->parent;
			++$counter;
		}

		// CHECK FOR DUPLICATE CHILD IDS
		if($counter != count($lft_childs))
		{
			$message = sprintf('%s::__checkTree(): Duplicate entries for "child" in maintree! $a_node_id: %s',
								   get_class($this),
							   $a_node['child']);
			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		// GET SUBTREE BY PARENT RELATION
		$parent_childs = array();
		$this->__getSubTreeByParentRelation($a_node['child'],$parent_childs);
		$this->__validateSubtrees($lft_childs,$parent_childs);

		return true;
	}

	function __getSubTreeByParentRelation($a_node_id,&$parent_childs)
	{
		global $ilDB;
		
		// GET PARENT ID
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE child = %s '.
			'AND tree = %s ';
		$res = $ilDB->queryF($query,array('integer','integer'),array(
			$a_node_id,
			$this->tree_id));

		$counter = 0;
		while($row = $ilDB->fetchObject($res))
		{
			$parent_childs[$a_node_id] = $row->parent;
			++$counter;
		}
		// MULTIPLE ENTRIES
		if($counter > 1)
		{
			$message = sprintf('%s::__getSubTreeByParentRelation(): Multiple entries in maintree! $a_node_id: %s',
							   get_class($this),
							   $a_node_id);
			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		// GET ALL CHILDS
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE parent = %s ';
		$res = $ilDB->queryF($query,array('integer'),array($a_node_id));

		while($row = $ilDB->fetchObject($res))
		{
			// RECURSION
			$this->__getSubTreeByParentRelation($row->child,$parent_childs);
		}
		return true;
	}

	function __validateSubtrees(&$lft_childs,$parent_childs)
	{
		// SORT BY KEY
		ksort($lft_childs);
		ksort($parent_childs);

		if(count($lft_childs) != count($parent_childs))
		{
			$message = sprintf('%s::__validateSubtrees(): (COUNT) Tree is corrupted! Left/Right subtree does not comply .'.
							   'with parent relation',
							   get_class($this));
			$this->log->write($message,$this->log->FATAL);
			$this->ilErr->raiseError($message,$this->ilErr->WARNING);
		}

		foreach($lft_childs as $key => $value)
		{
			if($parent_childs[$key] != $value)
			{
				$message = sprintf('%s::__validateSubtrees(): (COMPARE) Tree is corrupted! Left/Right subtree does not comply '.
								   'with parent relation',
								   get_class($this));
				$this->log->write($message,$this->log->FATAL);
				$this->ilErr->raiseError($message,$this->ilErr->WARNING);
			}
			if($key == ROOT_FOLDER_ID)
			{
				$message = sprintf('%s::__validateSubtrees(): (ROOT_FOLDER) Tree is corrupted! Tried to delete root folder',
								   get_class($this));
				$this->log->write($message,$this->log->FATAL);
				$this->ilErr->raiseError($message,$this->ilErr->WARNING);
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
	*
	*/
	public function moveTree($a_source_id,$a_target_id,$a_location = IL_LAST_NODE)
    {
		global $ilDB;
		
		if($this->__isMainTree())
		{
			ilDB::_lockTables(array('tree' => 'WRITE'));
		}
		// Receive node infos for source and target
		$query = 'SELECT * FROM '.$this->table_tree.' '.
			'WHERE ( child = %s OR child = %s ) '.
			'AND tree = %s ';
		$res = $ilDB->queryF($query,array('integer','integer','integer'),array(
			$a_source_id,
			$a_target_id,
			$this->tree_id));
		
		// Check in tree
		if($res->numRows() != 2)
		{
			if($this->__isMainTree())
			{
				ilDB::_unlockTables();
			}
			$this->log->write(__METHOD__.' Objects not found in tree!',$this->log->FATAL);
			$this->ilErr->raiseError('Error moving node',$this->ilErr->WARNING);
		}
		while($row = $ilDB->fetchObject($res))
		{
			if($row->child == $a_source_id)
			{
				$source_lft = $row->lft;
				$source_rgt = $row->rgt;
				$source_depth = $row->depth;
				$source_parent = $row->parent;
			}
			else
			{
				$target_lft = $row->lft;
				$target_rgt = $row->rgt;
				$target_depth = $row->depth;
			}
		}
		
		#var_dump("<pre>",$source_lft,$source_rgt,$source_depth,$target_lft,$target_rgt,$target_depth,"<pre>");
		// Check target not child of source
		if($target_lft >= $source_lft and $target_rgt <= $source_rgt)
		{
			if($this->__isMainTree())
			{
			        ilDB::_unlockTables();
			}
			$this->log->write(__METHOD__.' Target is child of source',$this->log->FATAL);
			$this->ilErr->raiseError('Error moving node',$this->ilErr->WARNING);
		}
		
		// Now spread the tree at the target location. After this update the table should be still in a consistent state.
		// implementation for IL_LAST_NODE
		$spread_diff = $source_rgt - $source_lft + 1;
		#var_dump("<pre>","SPREAD_DIFF: ",$spread_diff,"<pre>");
		        
		$query = 'UPDATE '.$this->table_tree.' SET '.
			'lft = CASE WHEN lft >  %s THEN lft + %s ELSE lft END, '.
			'rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END '.
			'WHERE tree = %s ';
		$res = $ilDB->manipulateF($query,array('integer','integer','integer','integer','integer'),array(
			$target_rgt,
			$spread_diff,
			$target_rgt,
			$spread_diff,
			$this->tree_id));
		
		// Maybe the source node has been updated, too.
		// Check this:
		if($source_lft > $target_rgt)
		{
			$where_offset = $spread_diff;
			$move_diff = $target_rgt - $source_lft - $spread_diff;
		}
		else
		{
			$where_offset = 0;
			$move_diff = $target_rgt - $source_lft;
		}
		$depth_diff = $target_depth - $source_depth + 1;
		
		
		$query = 'UPDATE '.$this->table_tree.' SET '.
			'parent = CASE WHEN parent = %s THEN %s ELSE parent END, '.
			'rgt = rgt + %s, '.
			'lft = lft + %s, '.
			'depth = depth + %s '.
			'WHERE lft >= %s '.
			'AND rgt <= %s '.
			'AND tree = %s ';
		$res = $ilDB->manipulateF($query,
			array('integer','integer','integer','integer','integer','integer','integer','integer'),
			array(
			$source_parent,
			$a_target_id,
			$move_diff,
			$move_diff,
			$depth_diff,
			$source_lft + $where_offset,
			$source_rgt + $where_offset,
			$this->tree_id));
		
		// done: close old gap
		$query = 'UPDATE '.$this->table_tree.' SET '.
			'lft = CASE WHEN lft >= %s THEN lft - %s ELSE lft END, '.
			'rgt = CASE WHEN rgt >= %s THEN rgt - %s ELSE rgt END '.
			'WHERE tree = %s ';

		$res = $ilDB->manipulateF($query,
			array('integer','integer','integer','integer','integer'),
			array(
			$source_lft + $where_offset,
			$spread_diff,
			$source_rgt +$where_offset,
			$spread_diff,
			$this->tree_id));
			
		if($this->__isMainTree())
		{
			ilDB::_unlockTables();
		}
		return true;
    }


} // END class.tree
?>
