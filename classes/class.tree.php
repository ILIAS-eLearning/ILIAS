<?php


////////////////////////////////////////////////////////////////////////////////
// Name: class.tree.php
// Appl: ILIAS 3
// Vers: 0.7
// Func: data representation in hierachical trees using the Nested Set Model by Joe Celco
//
// (c) 2002 Sascha Hofmann
//
// Autor: Sascha Hofmann
//        Hohenstaufenring 23, 50674 K÷ln
//        +49-179-1305023
//        saschahofmann@gmx.de
//
// Last change: 22.Jan.2002
//
// Description:
// coming soon....
//
////////////////////////////////////////////////////////////////////////////////

/**
* data representation in hierarchical trees using the nested set model by Joe Celco
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
* @package ilias-core
*/
class Tree extends PEAR
{
    var $db;                    // database object

    var $ParId;                 // points to actual position in tree (node)
    var $RootId;                // points to root node (may be a subtree!)
    var $TreeId;                // to use different trees in one db-table
    
    var $Path = array();        // contains the path from root to node
    var $Childs = array();      // contains all subnodes of node
    var $Leafs = array();       // contains leaf nodes of tree
	var $maxlvl = "";			// max level of tree for display

    // constructor
    function Tree($AParId = 0, $ARootId = 1, $ATreeId = 1)
    {
        global $ilias;
        $this->db =& $ilias->db; 
        $this->RootId = $ARootId;
		$this->ParId = $AParId;
        $this->TreeId = $ATreeId;
    }


    // get the root node (always 1)
    function getRoot()
    {
        $query = "SELECT * FROM tree
                  LEFT JOIN object_data ON tree.child=object_data.obj_id
                  WHERE lft=1
                  AND tree = '".$this->TreeId."'";

        $res = $this->db->query($query);
        
        $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
        
		
        $root = array(
                      "id"  => $data["obj_id"],
                      "title" => $data["title"]
                      );
        
        return $root;
    }

    // get leaf-nodes of a tree
    function getLeafs()
    {
        $query = "SELECT * FROM tree
                  LEFT JOIN object_data ON tree.child=object_data.obj_id
                  WHERE lft = (rgt -1)
                  AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);

        if ($res->numRows() > 0)
        {
            while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
            {
                $this->Leafs[] = array(
                                       "id"    => $data["obj_id"],
                                       "title" => $data["title"]
                                       );
            }
            
            return true;
        }

        // error occured (so ein „rger...)
        return false;
    }

    // get subnodes of given parent node
    function getChilds($AParId = "")
    {
        // to reset the content
		$this->Childs = array();
		
        if (!empty($AParId))
        {
            $this->ParId = $AParId;
        }
		
		$query = "SELECT * FROM tree
                  LEFT JOIN object_data ON tree.child=object_data.obj_id
                  WHERE parent = '".$this->ParId."' 
                  AND tree = '".$this->TreeId."'";

        $res = $this->db->query($query);
        
		$count = $res->numRows();
		
		if ($res->numRows() > 0)
        {
            while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
            {
				$this->Childs[] = array(
                                        "id"  		 => $data["obj_id"],
                                        "title" 	 => $data["title"],
										"desc"		 => $data["description"],
										"type"		 => $data["type"],
										"last_update"=> $data["last_update"],
										"parent"	 => $data["parent"],
										"lft"		 => $data["lft"],
										"rgt"		 => $data["rgt"]
                                        );
            }

			$this->Childs[$count - 1]["last"] = true;
            
            return $this->Childs;
        }

        return false;
    }
	function getAllChildsByType($Aparent,$Atype)
	{
		$data = array();

		$query = "SELECT * FROM tree ".
			"WHERE child = '".$Aparent."'";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$left = $row->lft;
			$right = $row->rgt;
		}
		$query = "SELECT * FROM tree ".
			"LEFT JOIN object_data ON tree.child = object_data.obj_id ".
			"WHERE object_data.type = '".$Atype."' ".
			"AND tree.lft BETWEEN '".$left."' AND '".$right."' ".
			"AND tree.rgt BETWEEN '".$left."' AND '".$right."'";
		$res = $this->db->query($query);
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
    // insert node under parent node
    function insertNode($AObjId,$AParId = "")
	
    {
        if (!empty($AParId))
        {
            $this->ParId = $AParId;
        }

        // get left value
        $query = "SELECT * FROM tree
                  WHERE child = '".$this->ParId.
                  "' AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);
        $data = $res->fetchRow(DB_FETCHMODE_ASSOC);
        
        $left = $data["lft"];
        $lft = $left + 1;
        $rgt = $left + 2;
        
        // spread tree
        $query = "UPDATE tree SET
                  lft = CASE
                  WHEN lft > $left
                  THEN lft + 2
                  ELSE lft
                  END,
                  rgt = CASE
                  WHEN rgt > $left
                  THEN rgt + 2
                  ELSE rgt
                  END
                  WHERE tree = '".$this->TreeId."'";
        $res = $this->db->query($query);

        // insert node
        $query = "INSERT INTO tree (tree,child,parent,lft,rgt)
                  VALUES ('".$this->TreeId."','".$AObjId."','".$this->ParId."','".$lft."','".$rgt."')";
        $res = $this->db->query($query);

    }

    // delete parent node
    function deleteNode($AParId = "")
    {
        if (!empty($AParId))
        {
            $this->ParId = $AParId;
        }
        
        // get left & right value of node to be deleted
        $query = "SELECT * FROM tree
                  WHERE child = '".$this->ParId.
                  "' AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);
        $data = $res->fetchRow(DB_FETCHMODE_ASSOC);

        $left = $data["lft"];
        $right = $data["rgt"];
        $new_parent = $data["parent"];

        // has to be checked by other functions!!!!
        // delete the kat
        $res = $this->db->query("DELETE FROM object_data WHERE obj_id='".$this->ParId."'");

        // delete node
        $query = "DELETE FROM tree
                  WHERE child = '".$this->ParId.
                  "' AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);

        // close up the gap
        $query = "UPDATE tree SET
                  lft = CASE
                  WHEN lft BETWEEN '".$left."' AND '".$right."' THEN lft - 1
                  WHEN lft > '".$right."' THEN lft - 2
                  ELSE lft
                  END,
                  rgt = CASE
                  WHEN rgt BETWEEN '".$left."' AND '".$right."' THEN rgt - 1
                  WHEN rgt > '".$right."' THEN rgt -2
                  ELSE rgt
                  END,
                  parent = CASE
                  WHEN parent = '".$this->ParId."' THEN $new_parent
                  ELSE parent
                  END
                  WHERE tree = '".$this->TreeId."'";
        $res = $this->db->query($query);
        
        $this->ParId = $new_parent;
    }


    // delete parent node and its subtree
    function deleteTree($AParId = "")
    {
        if (!empty($AParId))
        {
            $this->ParId = $AParId;
        }

        // get left & right value (for subtree nodes)
        $query = "SELECT * FROM tree
                  WHERE child = '".$this->ParId.
                  "' AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);
        $data = $res->fetchRow(DB_FETCHMODE_ASSOC);

        $left = $data["lft"];
        $right = $data["rgt"];
        $diff = $right - $left + 1;

        // save parent
        $new_parent = $data["parent"];

        //before deletion fetch kat_ids
        $query = "SELECT child FROM tree
                  WHERE lft BETWEEN '".$left."' AND '".$right.
                  "' AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);
        
        // delete the kats
        while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
        {
            $delete[] = $data["child"];
        }
        foreach ($delete as $val)
        {
            $res = $this->db->query("DELETE FROM object_data WHERE obj_id='".$val."'");
			$res = $this->db->query("DELETE FROM rbac_pa WHERE obj_id='".$val."'");
			$res = $this->db->query("DELETE FROM rbac_fa WHERE parent='".$val."'");
			$res = $this->db->query("DELETE FROM rbac_templates WHERE parent='".$val."'");
        }

        // delete subtree
        $query = "DELETE FROM tree
                  WHERE lft BETWEEN '".$left."' AND '".$right.
                  "' AND tree = '".$this->TreeId."'";
        $res = $this->db->query($query);

        // close gaps
        $query = "UPDATE tree SET
                  lft = CASE
                  WHEN lft > '".$left.
                  "' THEN lft - $diff
                  ELSE lft
                  END,
                  rgt = CASE
                  WHEN rgt > '".$left.
                  "' THEN rgt - '".$diff.
                  "' ELSE rgt
                  END
                  WHERE tree = '".$this->TreeId."'";
        $res = $this->db->query($query);
        
        $this->ParId = $new_parent;
    }


    // get path from given startnode to given endnode
    function getPath ($AEndNode = "",$AStartNode = "")
    {
        if(empty($AEndNode))
        {
            $AEndNode = $this->ParId;
        }

        if(empty($AStartNode))
        {
            $AStartNode = $this->RootId;
        }

        $query = "SELECT T2.parent,object_data.title,T2.child,(T2.rgt - T2.lft) AS sort_col
                  FROM tree AS T1, tree AS T2, tree AS T3
                  LEFT JOIN object_data ON T2.child=object_data.obj_id
                  WHERE T1.child = '".$AStartNode.
                  "' AND T3.child = '".$AEndNode.
                  "' AND T2.lft BETWEEN T1.lft AND T1.rgt
                  AND T3.lft BETWEEN T2.lft AND T2.rgt
                  AND T2.tree = '".$this->TreeId.
                  "' ORDER BY sort_col DESC";
        $res = $this->db->query($query);
        
        if ($res->numRows() > 0)
        {
        	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
            {
                $this->Path[] = array(
                               "id"    => $data["child"],
                               "title" => $data["title"],
							   "parent"=> $data["parent"]
                               );
            }
            return true;
        }
        
        // error occured
        return false;
    }

	function showPathId($AEndNode,$AStartNode)
	{
		$id = array();

        $query = "SELECT T2.parent,object_data.title,T2.child,(T2.rgt - T2.lft) AS sort_col
                  FROM tree AS T1, tree AS T2, tree AS T3
                  LEFT JOIN object_data ON T2.child=object_data.obj_id
                  WHERE T1.child = '".$AStartNode.
                  "' AND T3.child = '".$AEndNode.
                  "' AND T2.lft BETWEEN T1.lft AND T1.rgt
                  AND T3.lft BETWEEN T2.lft AND T2.rgt
                  AND T2.tree = '".$this->TreeId.
                  "' ORDER BY sort_col DESC";
        $res = $this->db->query($query);
        
        if ($res->numRows() > 0)
        {
        	while ($data = $res->fetchRow(DB_FETCHMODE_ASSOC))
            {
				$id[] = $data["child"];
			}
            return $id;
        }
        
        // error occured
        return false;
	}

    // check consistence of tree
    function checkTree()
    {
        $query = "SELECT lft,rgt FROM tree
                  WHERE tree = '".$this->TreeId."'";
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
            echo "Error in Tree!";
            exit;
        }
    }

	// builds an array of tree for output
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

	// fetch all expanded nodes & their childs
	function buildTree ($nodes)
	{
		foreach ($nodes as $val)
		{
			$knoten[$val] = $this->getChilds($val);
		}
		
		return $knoten;		
	}	
	
}
?>