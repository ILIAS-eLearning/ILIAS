<?php
/**
* Tree class
* data representation in hierachical trees using the Nested Set Model by Joe Celco
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author Stefan Meyer <smeyer@databay.de>
* @package ilias-core
* @version $Id$
*/

class Tree extends PEAR
{
	/**
	* database handle
	* @var object
	* @access private
	*/
	var $db;

	/**
	* points to actual position in tree (node)
	* @var integer
	* @access public
	*/
	var $node_id;

	/**
	* parent of current node. This information is needed for multi-refering the same child in the tree
	* @var integer
	* @access public
	*/
	var $parent_id;

	/**
	* points to root node (may be a subtree)
	* @var integer
	* @access public
	*/
	var $root_id;

	/**
	* to use different trees in one db-table
	* @var integer
	* @access public
	*/
	var $tree_id;

	/**
	* contains the path from root to current node (node_id)
	* @var array
	* @access public
	*/
	var $Path;
	
	/**
	* contains all subnodes of node (node_id)
	* @var array
	* @access public
	*/
	var $Childs;

	/**
	* contains leaf nodes of tree
	* @var array
	* @access public
	*/
	var $Leafs;

	/**
	* max level of tree for display
	* @var integer
	* @access private
	*/
	var $maxlvl;

	/**
	* local error object
	* @var object
	* @access private
	*/
	var $error_obj;
	
	/**
	* constructor
	* @param	integer	$a_node_id		node_id
	* @param	integer	$a_parent_id	parent_id
	* @param	integer	$a_root_id		root_id (optional)
	* @param	integer	$a_tree_id		tree_id (optional)
	*/
	function Tree($a_node_id, $a_parent_id, $a_root_id = 1, $a_tree_id = 1)
	{
		global $ilias;

		// set db-handler
		$this->db =& $ilias->db;
		
		// init error-handler
		$this->PEAR();
		$this->error_obj = new ErrorHandling();
		$this->setErrorHandling(PEAR_ERROR_CALLBACK,array($this->error_obj,'errorHandler'));
		
		//init variables
		$this->node_id		= $a_node_id;
		$this->parent_id	= $a_parent_id;
		$this->root_id		= $a_root_id;
		$this->tree_id		= $a_tree_id;
	}

	/**
	* get leaf-nodes of tree
	* @access	public
	* @return	object	error object in case of an error
	*/
	function getLeafs()
	{
		$query = "SELECT * FROM tree ".
				 "LEFT JOIN object_data ON tree.child=object_data.obj_id ".
				 "WHERE lft = (rgt -1) ".
				 "AND tree = '".$this->tree_id."'";
		
		$res = $this->db->query($query);
		
		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}

		if ($res->numRows() > 0)
		{
			while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$this->Leafs[] = array(
										"id"	=> $data["obj_id"],
										"title"	=> $data["title"]
										);
			}
		}
		else
		{
			// No Leafs found? An error occured
			return $this->raiseError("Error: No Leafs found!",$this->error_obj->WARNING);
		}
	}

	
	/**
	* get subnodes of given node
	* @param	integer	$a_node_id		node_id (optional)
	* @access	public
	* @return	boolean	true when node has childs, otherwise false
	*/
	function getChilds($a_node_id = "")
	{
		// number of childs
		$count = 0;
		
		// init childs
		$this->Childs = array();
		
		if (empty($a_node_id))
		{
			$a_node_id = $this->node_id;
		}
		
		$query = "SELECT * FROM tree ".
				 "LEFT JOIN object_data ON tree.child=object_data.obj_id ".
				 "WHERE parent = '".$a_node_id."' ".
				 "AND tree = '".$this->tree_id."'";
				 
		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
		
		$count = $res->numRows();
		
		if ($count > 0)
		{
			while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->Childs[] = array(
										"tree"			=> $row->tree,
										"child"			=> $row->child,
										"parent"		=> $row->parent,
										"lft"			=> $row->lft,
										"rgt"			=> $row->rgt,
										"obj_id"		=> $row->obj_id,
										"type"			=> $row->type,
										"title"			=> $row->title,
										"description"	=> $row->description,
										"owner"			=> $row->owner,
										"create_date"	=> $row->create_date,
										"last_update"	=> $row->last_update,
										"desc"			=> $row->description,
										"id"			=> $row->obj_id										
										);
										// last both entries for compatibility-reasons
			}

			// mark the last child node (important for display)
			$this->Childs[$count - 1]["last"] = true;

			return $this->Childs;
		}
		else
		{
			return false;
		}
	}

	/**
	* get subnodes of given node by type 
	* @param	integer	$a_node_id		node_id
	* @param	integer	$a_parent_id	parent_id
	* @param	string	$a_type			object type definition
	* @access	public
	* @return	array	childs by type
	*/
	function getAllChildsByType($a_node_id,$a_parent_id,$a_type)
	{
		$data = array();	// node_data
		$row = "";			// fetched row
		$left = "";			// tree_left
		$right = "";		// tree_right

		$query = "SELECT * FROM tree ".
				 "WHERE child = '".$a_node_id."' ".
				 "AND parent = '".$a_parent_id."'";

		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$left = $row->lft;
			$right = $row->rgt;
		}
		$query = "SELECT * FROM tree ".
				 "LEFT JOIN object_data ON tree.child = object_data.obj_id ".
				 "WHERE object_data.type = '".$a_type."' ".
				 "AND tree.lft BETWEEN '".$left."' AND '".$right."' ".
				 "AND tree.rgt BETWEEN '".$left."' AND '".$right."'";

		$res = $this->db->query($query);
		
		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data[] = array(
				"tree"        => $row->tree,
				"child"       => $row->child,
				"parent"      => $row->parent,
				"lft"         => $row->lft,
				"rgt"         => $row->rgt,
				"obj_id"      => $row->obj_id,
				"type"        => $row->type,
				"title"       => $row->title,
				"description" => $row->description,
				"owner"       => $row->owner,
				"create_date" => $row->create_date,
				"last_update" => $row->last_update);
		}
		
		return $data;
	}

	/**
	* insert node under parent node 
	* @param	integer	$a_node_id		node_id
	* @param	integer	$a_parent_id	parent_id (optional)
	* @access	public
	* @return	object	$error	error object on error
	*/
	function insertNode($a_node_id,$a_parent_id = "")
	{
		$left = "";			// first tree_left
		$lft = "";			// second tree_left
		$rgt = "";			// second tree_right
	
		if (empty($a_parent_id))
		{
			$a_parent_id = $this->parent_id;
		}

		// get left value
		$query = "SELECT * FROM tree ".
				 "WHERE child = '".$a_parent_id."' ".
				 "AND tree = '".$this->tree_id."'";

		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}

		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$left = $data["lft"];
		$lft = $left + 1;
		$rgt = $left + 2;

		// spread tree
		$query = "UPDATE tree SET ".
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
				 "WHERE tree = '".$this->tree_id."'";

		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
		
		// insert node
		$query = "INSERT INTO tree (tree,child,parent,lft,rgt) ".
				 "VALUES ".
				 "('".$this->tree_id."','".$a_node_id."','".$a_parent_id."','".$lft."','".$rgt."')";
				 
		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
	}

	/**
	* delete node under parent node 
	* @param	integer	$a_node_id		node_id
	* @param	integer	$a_parent_id	parent_id (optional)
	* @access	public
	* @return	object	$error	error object on error
	*/
	function deleteNode($a_node_id = "",$a_parent_id = "")
	{
		$left = "";				// tree_left
		$right = "";			// tree_right
		$new_parent = "";		// new parent_id

		if (empty($a_node_id))
		{
			$a_node_id = $this->node_id;
		}
		
		if (empty($a_parent_id))
		{
			$a_parent_id = $this->parent_id;
		}

		// get left & right value of the node to be deleted
		$query = "SELECT * FROM tree ".
				 "WHERE child = '".$a_node_id."' ".
				 "AND tree = '".$this->tree_id."'";
				 
		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
		
		while($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($data["parent"] == $a_parent_id)
			{
				$left = $data["lft"];
				$right = $data["rgt"];
				$new_parent = $data["parent"];
				break;
			}
		}

		// has to be checked by other functions!!!!
		// delete the kat
		if ($res->numRows() == 1)
		{
			$res = $this->db->query("DELETE FROM object_data WHERE obj_id='".$a_node_id."'");
			
		 	if (DB::isError($res))
			{
				return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
			}
		}

		// delete node
		$query = "DELETE FROM tree ".
				 "WHERE child = '".$a_node_id."' ".
				 "AND parent = '".$a_parent_id."' ".
				 "AND tree = '".$this->tree_id."'";

		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}

		// close up the gap
		$query = "UPDATE tree SET ".
				 "lft = CASE ".
				 "WHEN lft BETWEEN '".$left."' AND '".$right."' THEN lft - 1 ".
				 "WHEN lft > '".$right."' THEN lft - 2 ".
				 "ELSE lft ".
				 "END, ".
				 "rgt = CASE ".
				 "WHEN rgt BETWEEN '".$left."' AND '".$right."' THEN rgt - 1 ".
				 "WHEN rgt > '".$right."' THEN rgt -2 ".
				 "ELSE rgt ".
				 "END, ".
				 "parent = CASE ".
				 "WHEN parent = '".$a_node_id."' THEN '".$new_parent."' ".
				 "ELSE parent ".
				 "END ".
				 "WHERE tree = '".$this->tree_id."'";

		$res = $this->db->query($query);

		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}		
		
		$this->node_id = $new_parent;
	}

	/**
	* move a node into another position within the tree 
	* @param	integer	$a_node_id		node_id
	* @param	integer	$a_parent_id	parent_id
	* @param	integer	$a_target_id	node_id of parent node where the node is moved to
	* @access	public
	* @return	void
	*/
	function moveNode ($a_node_id,$a_parent_id,$a_target_id)
	{
		$this->insertNode($a_node_id,$a_target_id);
		$this->deleteNode($a_node_id,$a_parent_id);
	}

	/**
	* delete node and the whole subtree under this node 
	* @param	integer	$a_node_id		node_id (optional)
	* @param	integer	$a_parent_id	parent_id (optional)
	* @access	public
	* @return	object	$error	error object on error
	*/
	function deleteTree($a_node_id = "", $a_parent_id = "")
	{
		$left = "";			// tree_left
		$right = "";			// tree_right
		$diff = "";			// difference between lft & rgt
		$new_parent = "";		// new parent_id


		if (empty($a_node_id))
		{
			$a_node_id = $this->node_id;
		}
		
		if (empty($a_parent_id))
		{
			$a_parent_id = $this->parent_id;
		}

		// get left & right value (for subtree nodes)
		$query = "SELECT * FROM tree ".
				 "WHERE child = '".$a_node_id."' ".
				 "AND parent = '".$a_parent_id."' ".
				 "AND tree = '".$this->tree_id."'";
		
		$res = $this->db->query($query);
		
		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}	
		
		$data = $res->fetchRow(DB_FETCHMODE_ASSOC);

		$left = $data["lft"];
		$right = $data["rgt"];
		$diff = $right - $left + 1;

		// save parent
		$new_parent = $data["parent"];

		//before deletion fetch all child_ids
		$query = "SELECT child FROM tree ".
				 "WHERE lft BETWEEN '".$left."' AND '".$right."' ".
				 "AND tree = '".$this->tree_id."'";
		
		$res = $this->db->query($query);
		
		if (DB::isError($res))
		{
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}	

		// delete the the childs from tree
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$delete[] = $data["child"];
		}
		
		foreach ($delete as $val)
		{
			$res = $this->db->query("DELETE FROM object_data WHERE obj_id='".$val."'");
			
			if (DB::isError($res))
			{
				return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
			}
				
			$res = $this->db->query("DELETE FROM rbac_pa WHERE obj_id='".$val."'");

			if (DB::isError($res))
			{
				return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
			}
			
			$res = $this->db->query("DELETE FROM rbac_fa WHERE parent='".$val."'");

			if (DB::isError($res))
			{
				return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
			}

			$res = $this->db->query("DELETE FROM rbac_templates WHERE parent='".$val."'");

			if (DB::isError($res))
			{
				return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
			}
		}

		// delete subtree
		$query = "DELETE FROM tree ".
				 "WHERE lft BETWEEN '".$left."' AND '".$right." '".
				 "AND tree = '".$this->tree_id."'";
		
		$res = $this->db->query($query);

		// close gaps
		$query = "UPDATE tree SET ".
				 "lft = CASE ".
				 "WHEN lft > '".$left." '".
				 "THEN lft - '".$diff." '".
				 "ELSE lft ".
				 "END, ".
				 "rgt = CASE ".
				 "WHEN rgt > '".$left." '".
				 "THEN rgt - '".$diff." '".
				 "ELSE rgt ".
				 "END ".
				 "WHERE tree = '".$this->tree_id."'";

		$res = $this->db->query($query);

		if (DB::isError($res))
		{	
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}

		$this->parent_id = $new_parent;
	}

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* if endnode is not given the current node is endnode
	* @param	integer	$a_endnode		node_id of endnode (optional)
	* @param	integer	$a_startnode	node_id of startnode (optional)
	* @access	private
	* @return	object	$res			query result
	*/
	function fetchPath ($a_endnode = "",$a_startnode = "")
	{
		if(empty($a_endnode))
		{
			$a_endnode = $this->node_id;
		}

		if(empty($a_startnode))
		{
			$a_startnode = $this->root_id;
		}

		$query = "SELECT T2.parent,object_data.title,T2.child,(T2.rgt - T2.lft) AS sort_col ".
				 "FROM tree AS T1, tree AS T2, tree AS T3 ".
				 "LEFT JOIN object_data ON T2.child=object_data.obj_id ".
				 "WHERE T1.child = '".$a_startnode." '".
				 "AND T3.child = '".$a_endnode." '".
				 "AND T2.lft BETWEEN T1.lft AND T1.rgt ".
				 "AND T3.lft BETWEEN T2.lft AND T2.rgt ".
				 "AND T2.tree = '".$this->tree_id." '".
				 "ORDER BY sort_col DESC";

		$res = $this->db->query($query);
		
		if (DB::isError($res))
		{	
			return $this->raiseError($res->getMessage().": ".$res->getDebugInfo(),$this->error_obj->FATAL);
		}
		
		if ($res->numRows() > 0)
		{
			return $res;
		}
		else
		{
			return $this->raiseError("Error: No path found!",$this->error_obj->WARNING);
		}
	}

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* if endnode is not given the current node is endnode
	* @param	integer	$a_endnode		node_id of endnode (optional)
	* @param	integer	$a_startnode	node_id of startnode (optional)
	* @access	public
	* @return	array	$this->Path		ordered path info (id,title,parent) from start to end
	*/
	function getPathFull ($a_endnode = "", $a_startnode = "")
	{
		$res = $this->fetchPath($a_endnode,$a_startnode);
		
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->Path[] = array(
							   "id"		=> $data["child"],
							   "title"	=> $data["title"],
							   "parent"	=> $data["parent"]
							   );
		}
	
		return $this->Path;
	}	

	/**
	* get path from a given startnode to a given endnode
	* if startnode is not given the rootnode is startnode
	* if endnode is not given the current node is endnode
	* @param	integer	$a_endnode		node_id of endnode (optional)
	* @param	integer	$a_startnode	node_id of startnode (optional)
	* @access	public
	* @return	array	$id				all path ids from startnode to endnode
	*/
	function getPathId ($a_endnode = "", $a_startnode = "")
	{
		$res = $this->fetchPath($a_endnode,$a_startnode);
		
		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$id[] = $data["child"];
		}
		
		return $id;
	}

	/**
	* check consistence of tree
	* @access	public
	* @return	boolean		true if tree is ok; otherwise throws error object
	*/
	function checkTree()
	{
		$query = "SELECT lft,rgt FROM tree ".
				 "WHERE tree = '".$this->tree_id."'";
				 
		$res = $this->db->query($query);

		while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$lft[] = $data["lft"];
			$rgt[] = $data["rgt"];
		}
			
		$all = array_merge($lft,$rgt);
		$uni = array_unique($all);
			
		if (count($all) != count($uni))
		{
			return $this->raiseError("Error: Tree is corrupted!!",$this->error_obj->WARNING);
		}
		
		return true;
	}

	/**
	* builds an array of a flattened tree for output purposes
	* @param	array	$nodes		tree information
	* @param	integer	$start		node_id of current startnode
	* @param	integer	$level		level of current node
	* @param	string	$open		information about opened folders (optional; is set automatically)
	* @param	array	$out		end result of recursion (optional, is set automatically)
	* @param	array	$tabarr		information about the needed tabstops for each node (optional, is set automatically)
	* @access	public
	* @return	array 	$out		complete tree in a flat structure to display all elements sequently
	*/
	function display($nodes,$start,$level,$open="",$out="",$tabarr="") {

		global $PHP_SELF;
		
		// intialize some variables for the first run
		if ($level == 0)
		{
			$tab = array();
			$tabarr = array();
			$out = array();		
		}

		if (empty($this->maxlvl))
		{
			$this->maxlvl = $level;
		}

		if ($level > $this->maxlvl)
		{
			$this->maxlvl = $level;
		}	
		
		// copy tabarr
		$tab = $tabarr;
		
		// dive into tree
		$level++;

		// extract nodeIDs from $nodes
		foreach ($nodes as $node_id => $childs)
		{
			$keys = array_keys($nodes);

			// set open category
			if (!empty($open))
			{
				$openlink = "&open=".$open;
			}
		
			if (($node_id == $start) && is_array($childs))
			{
				foreach ($childs as $child)
				{
					// prevent node_data to be filled with wrong nodes
					unset($node_data);
					
					$node_data["tab"] = $tab;
					$node_data["id"] = $child["id"];
					$node_data["type"] = $child["type"];
					$node_data["title"] = $child["title"];
					$node_data["last_update"] = $child["last_update"];
					
					// node has no children
					if ($child["lft"] == ($child["rgt"] - 1))
					{
						if ($level > 1)
						{
							if ($child["last"] == true)
							{
								array_push($node_data["tab"],"ecke","quer2");
							}
							else
							{
								array_push($node_data["tab"],"winkel","quer2");
							}
						}
						else
						{
							array_push($node_data["tab"],"blank");
						}
					}
					// node has children and is expanded
					elseif (in_array($child["id"],$keys))
					{
						$drop = array_search($child["id"],$keys);
						
						$subkeys = $keys;
						array_splice($subkeys,$drop,1);
						
						$string = implode("|",$subkeys);
						
						if ($level > 1)
						{
							if ($child["last"] == true)
							{
								array_push($node_data["tab"],"ecke");
							}
							else
							{
								array_push($node_data["tab"],"winkel");
							}
						}
						
						$node_data["expstr"] = "?id=".$string.$openlink;
						$node_data["expander"] = "minus.gif";							
					}
					// node has children and is collapsed
					else
					{
						if ($level > 1)
						{
							if ($child["last"] == true)
							{
								array_push($node_data["tab"],"ecke");
							}
							else
							{
								array_push($node_data["tab"],"winkel");
							}
						}
					
						$string = implode("|",$keys);
						$node_data["expstr"] = "?id=".$string."|".$child["id"].$openlink;
						$node_data["expander"] = "plus.gif";
					}

					// determine open folder
					if ($child["id"] == $open)
					{
						$node_data["folder"] = "openfolder.gif";
					}
					else
					{
						$node_data["folder"] = "closedfolder.gif";
					}
					
					$node_data["icon"] = $child["type"];
		
					$string = implode("|",$keys);

					if ($level > 1)
					{
						if ($child["last"] == true)
						{
							$tabarr[$level] = "blank";
						}
						else
						{
							$tabarr[$level] = "hoch";
						}
					}
					
					$node_data["open"] = "?obj_id=".$child["id"]."&parent=".$child["parent"];
					
					$node_data["level"] = $level;
				
					
					// only display categories
					//if ($child["type"] == "cat")
					//{
						$out[$child["id"]] = $node_data;
					//}	

					// walk recursive through the whole tree
					$out = $this->display($nodes,$child["id"],$level,$open,$out,$tabarr);
				}
			}
		}

		return $out;
	}	

	/**
	* fetch all expanded nodes & their childs
	* @param	array	$nodes		tree information
	* @access	public
	* @return	array 	$knoten		all expanded nodes & their childs
	*/
	function buildTree ($nodes)
	{
		foreach ($nodes as $val)
		{
			$knoten[$val] = $this->getChilds($val);
		}
	
		return $knoten;		
	}

	/**
	* builds a string in HTML to output path information
	* @param	array	$a_path			full path information
	* @param	string	$a_scriptname	scriptname to use for hyperlinks
	* @access	public
	* @return	string 	$path			HTML-formatted string
	*/
	function showPath($a_path,$a_scriptname)
	{
		foreach ($a_path as $key => $val)
		{
			if ($key < (count($a_path) - 1))
			{
				$path .= "[<a href=\"".$a_scriptname."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a>]";
			}
			else
			{
				$path .= "[<b><a href=\"".$a_scriptname."?obj_id=".$val["id"]."&parent=".$val["parent"]."\">".$val["title"]."</a></b>]";;
			}

			if ($key < (count($a_path) - 1))
			{
				$path .= " :: ";
			}
		}

		return $path;
	}
	
	/**
	* get all childs from a node by depth
	* @param	integer	$a_depth		tree-level
	* @param	integer	$a_parent		node_id
	* @access	public
	* @return	array 	$childs			childs
	*/
	function getChildsByDepth($a_depth,$a_parent)
	{
		// to reset the content
		$this->Childs = array();
		
		$query = "SELECT * FROM tree ".
			"LEFT JOIN object_data ON tree.child=object_data.obj_id ".
			"WHERE depth = '".$a_depth."' ".
			"AND parent = '".$a_parent."' ".
			"AND tree = '".$this->tree_id."'";

		$res = $this->db->query($query);

		$count = $res->numRows();
		
		if ($res->numRows() > 0)
		{
			while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$this->Childs[] = array(
										"tree"			=> $row->tree,
										"child"			=> $row->child,
										"parent"		=> $row->parent,
										"lft"			=> $row->lft,
										"rgt"			=> $row->rgt,
										"obj_id"		=> $row->obj_id,
										"type"			=> $row->type,
										"title"			=> $row->title,
										"description"	=> $row->description,
										"owner"			=> $row->owner,
										"create_date"	=> $row->create_date,
										"last_update"	=> $row->last_update,
										"desc"			=> $row->description,
										"id"			=> $row->obj_id										
										);
			}

			$this->Childs[$count - 1]["last"] = true;

			return $this->Childs;
		}

		return false;
	}
	
	/**
	* Return the maximum depth in tree
	* @access public
	* @return int
	*/
	function getMaximumDepth()
	{
		$query = "SELECT MAX(depth) FROM tree";
		$res = $this->db->query($query);
		
		while($row = $res->fetchRow())
		{
			return $row[0];
		}
	}
	
	/**
	* Return depth of an object
	* @access public
	* @param int
	* @param int
	* @return int
	*/
	function getDepth($a_child,$a_parent,$a_tree=1)
	{
		$query = "SELECT depth FROM tree ".
				 "WHERE child = '".$a_child."' ".
				 "AND parent = '".$a_parent."' ".
				 "AND tree = '".$a_tree."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->depth;
		}
	}

} // END class.tree.php
?>