<?php
/**
* Class for exporting XML documents stored in a relational database to a domxml representation
*  
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class sql2xml
{
	/**
	* domxml object
	* 
	* @var object domxml
	* @access public 
	*/
	var $domxml;

	/**
	* unique object id
	* 
	* @var integer obj_id
	* @access public 
	*/
	var $obj_id;

	/**
	* mapping db_id to internal dom_id
	* 
	* @var array mapping
	* @access public 
	*/
	var $mapping;

	/**
	* mapping db_id to internal dom_id
	* 
	* @var array mapping
	* @access public 
	*/
	var $hash;

	/**
	* ilias object
	* 
	* @var object ilias
	* @access public 
	*/
	var $ilias;
	
	/**
	* constructor
	* init db-handler
	* 
	* @access public 
	*/
	function sql2xml ($a_lm_id, $a_lo_id = "")
	{
		global $ilias;
		
		$this->ilias =& $ilias;
		$this->lm_tree = new Tree ($a_lm_id,$a_lm_id);

		if (!$a_lm_id)
		{
			$this->ilias->raiseError("No LearningModule ID given",$this->ilias->error_obj->FATAL);
		}
	
		$this->lm_id = $a_lm_id;
		
		if (!$a_lo_id)
		{
			// get chapter overview or get first LO in tree?
			// get first LO:
			$node_data = $this->lm_tree->getChilds($this->lm_id);
			
			if (!$node_data[0]["obj_id"])
			{
				$this->ilias->raiseError("No LearningObjects found",$this->ilias->error_obj->FATAL);
			}

			$this->lo_id = $node_data[0]["obj_id"];
			
		}
		else
		{
			$this->lo_id = $a_lo_id;
		}
		
		//get parent lo_id
		$this->lo_parent = $this->lm_tree->getParentId($this->lo_id);
		
		/*
		if (empty($_GET["lo_parent"]))
		{
			$this->lo_parent = $this->lm_id;
		}
		else
		{
			$this->lo_parent = $_GET["lo_parent"];
		}
		*/
		
		// get level
		$this->level = $this->getAttributeValue($this->lo_id,"General","AggregationLevel");
	}

	/**
	* gets Learning Object an all its sub LOs
	* builds with this information a domDocument and
	* return the domxml representation of this xml document
	* 
	* @param	integer	object_id where to start fetching the xml data
	* @return	object	domDocument
	* @access	public
	*/ 
	function getLearningObject ()
	{
		global $start;

		$this->lo_struct = $this->getStructure($this->lo_id);
		
		// build path information for DTD location
		if (strstr(php_uname(), "Windows"))
		{
			$path = "file://";
		}

		$path .= getcwd();
		
		// create the xml string (workaround for domxml_new_doc) ***
/*
		$xmlHeader = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>". // *** ISO-8859-1
					 "<!DOCTYPE LearningObject SYSTEM \"".$path."/xml/ilias_lo.dtd\">";
					 
		return $xmlHeader.$this->lo_struct;		
*/
		
		// create the xml string (workaround for domxml_new_doc) ***
		$xmlHeader = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>". // *** ISO-8859-1
					 "<!DOCTYPE LearningObject SYSTEM \"".$path."/xml/ilias_lo.dtd\">".
					 "<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlHeader); // *** Fehlerabfrage
		
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();

		// skip document node
		reset($this->lo_struct);
		$start = next($this->lo_struct);
		
		// create root node
		$node = $this->doc->create_element($start["element"]);

		$root = $this->doc->append_child($node);
		
		$this->hash[$start["node_id"]] = $root;
		
		// build XML document
		$this->buildXML();
			
///*
		// now get the Level-1-LOs
		if (count($subnodes = $this->doc->get_elements_by_tagname("LO")) > 0)
		{
			foreach ($subnodes as $subnode)
			{
				// fetching lo_id and remove placeholder node
				$attributes = $subnode->attributes();
				$lo_id = $attributes[0]->value;
					
				$level = $this->getAttributeValue($lo_id,"General","AggregationLevel");
				// get next LO
				//todo: first check if LO is of Level 1 !!!
			
				//$T6 = TUtil::StopWatch();

				if ($level == "1")
				{
					$this->hash = array();
					$this->lo_struct = $this->getStructure($lo_id);
					
					// skip document node
					reset($this->lo_struct);
					$start = next($this->lo_struct);
		
					// create first node (LearningObject)
					$node = $this->doc->create_element($start["element"]);

					$parent = $subnode->parent_node();
					$subnode->unlink_node();
				
					$root = $parent->append_child($node);
		
					$this->hash[$start["node_id"]] = $root;

					// build next XML
					$this->buildXML();

					//echo TUtil::StopWatch($T6)." get_sub LO<br/>";
				}
				else
				{
					$obj_data = getObject($lo_id);
					$subnode->set_attribute("title", $obj_data["title"]);
					$subnode->set_attribute("level", $level);
				}
				
				//if (($this->level == "3") and ($level == "2" or $level == "3"))

			}
		}
//*/

		return $this->doc->dump_mem(true);
	}
	
	function buildXML ()	
	{
	global $start;

		//$T4 = TUtil::StopWatch();

		foreach ($this->lo_struct as $key => $node_data)
		{
			// exclude root node
			if ($key > $start["node_id"])
			{
				$insert = false;
				switch ($node_data["node_type_id"])
				{
					case 1:
						$node = $this->doc->create_element($node_data["element"]);
	
						// set attributes
						if (is_array($node_data["attr_list"]))
						{
							foreach ($node_data["attr_list"] as $attr => $value)
							{
								$node->set_attribute($attr, $value);
							}
						}
						
						$insert = true;
						break;

					case 3:
						$node = $this->doc->create_text_node($node_data["textnode"]);

						$insert = true;
						break;
						
				}

				if ($insert)
				{
					//get parent node
					$parent = $this->hash[$node_data["parent_node_id"]];
				
					//build node
					$node = $parent->append_child($node);
					$this->hash[$this->lo_struct[$key]["node_id"]] = $node;
				}
			}
		}

		//echo TUtil::StopWatch($T4)." build_XML<br/>";
	}
	

	function getStructure($a_lo_id)
	{
		//$T1 = TUtil::StopWatch();
		
		/*
		$q = "SELECT lo.node_id, lo.node_type_id, lo.lo_id, lo.parent_node_id, lo.struct, tx.textnode, el.element ".
			 "FROM lo_tree AS lo ".
			 "LEFT OUTER JOIN lo_element_idx AS e_idx ON lo.node_id = e_idx.node_id ".
			 "LEFT OUTER JOIN lo_element_name AS el ON e_idx.element_id = el.element_id ".
			 "LEFT OUTER JOIN lo_text AS tx ON lo.node_id = tx.node_id ".
			 "WHERE lo_id='".$a_lo_id."' ".
			 "ORDER BY lft ASC";
		*/
		
		// Variant: get ALL data including attributes. Very fast! Drawback are multiple rows for each nodes 
///*
		$q = "SELECT lo.node_id, lo.node_type_id, lo.lo_id, lo.parent_node_id, lo.struct, tx.textnode, el.element, a_name.attribute, a_value.value ".
			 "FROM lo_tree AS lo ".
			 "LEFT OUTER JOIN lo_element_idx AS e_idx ON lo.node_id = e_idx.node_id ".
			 "LEFT OUTER JOIN lo_element_name AS el ON e_idx.element_id = el.element_id ".
			 "LEFT OUTER JOIN lo_text AS tx ON lo.node_id = tx.node_id ".
			 "LEFT OUTER JOIN lo_attribute_idx AS a_idx ON lo.node_id = a_idx.node_id ".
			 "LEFT JOIN lo_attribute_name AS a_name ON a_idx.attribute_id=a_name.attribute_id ".
			 "LEFT JOIN lo_attribute_value AS a_value ON a_idx.value_id=a_value.value_id ".
			 "WHERE lo_id='".$a_lo_id."' ".
			 "ORDER BY lft ASC";
			 
			 //echo $q;exit;
//*/
		
		// 2. variant: I think this is the fastest but you need mysql 4.x in order to use UNION statement
/*
		$q = "SELECT rgt FROM lo_tree ".
			 "WHERE lo_id = '".$a_lo_id."' ".
			 "AND lft = 1";
		
		$res = $this->ilias->db->query($q);
		
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$rgt_max = $row["rgt"];
		}

		$q = "(SELECT n.lft AS seq_no_1, 4 AS seq_no_2, 0 AS seq_no_3, 0 AS seq_no_4, x.node_id AS seq_no_5, x.textnode AS parsed_text ".
			 "FROM lo_tree n, lo_text x ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."' ".
			 "AND n.node_id = x.node_id) ".
			 "UNION ".
			 "(SELECT n.lft, 1, 0, 0, 0, t.lft_delimiter ".
			 "FROM lo_tree n, lo_node_type t ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.node_type_id = t.node_type_id ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."' ".
			 "AND t.lft_delimiter > '') ".
			 "UNION ".
			 "(SELECT n.lft, 2, 0, 0, 0, e.element ".
			 "FROM lo_element_name e, lo_element_idx e_idx, lo_tree n ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.node_id = e_idx.node_id AND ".
			 "e.element_id = e_idx.element_id) ".
			 "UNION ".
			 "(SELECT n.lft, 3, a.attribute_id, 1, 0, CONCAT(' ', a.attribute, '=\"' ) ".
			 "FROM lo_attribute_name a, lo_attribute_idx a_idx, lo_tree n ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."' ".
			 "AND n.node_id = a_idx.node_id ".
			 "AND a.attribute_id = a_idx.attribute_id) ".
			 "UNION ".
			 "(SELECT n.lft, 3, a_idx.attribute_id, 2, n.node_id, CONCAT( a.value, '\"' ) ".
			 "FROM lo_attribute_value a, lo_attribute_idx a_idx, lo_tree n ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."' ".
			 "AND n.node_id = a_idx.node_id ".
			 "AND a.value_id = a_idx.value_id) ".
			 "UNION ".
			 "(SELECT n.lft, 9, 0, 0, 0, t.rgt_delimiter ".
			 "FROM lo_tree n, lo_node_type t ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.node_type_id = t.node_type_id ".
			 "AND t.rgt_delimiter > '' ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."') ".
			 "UNION ".
			 "(SELECT n.rgt, 10, 0, 0, 0, CONCAT( t.lft_delimiter, '/' ) ".
			 "FROM lo_tree n, lo_node_type t ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.node_type_id = t.node_type_id ".
			 "AND n.node_type_id = 1 ".
			 "AND t.lft_delimiter > '' ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."') ".
			 "UNION ".
			 "(SELECT n.rgt, 10, 0, 3, 0, e.element ".
			 "FROM lo_element_name e, lo_element_idx e_idx, lo_tree n ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.node_id = e_idx.node_id ".
			 "AND e.element_id = e_idx.element_id) ".
			 "UNION ".
			 "(SELECT n.rgt, 10, 0, 4, 0, t.rgt_delimiter FROM lo_tree n, lo_node_type t ".
			 "WHERE n.lo_id = '".$a_lo_id."' ".
			 "AND n.node_type_id = t.node_type_id ".
			 "AND t.rgt_delimiter > '' ".
			 "AND n.lft >= 1 ".
			 "AND n.lft < '".$rgt_max."') ".
			 "ORDER BY seq_no_1, seq_no_2, seq_no_3, seq_no_4, seq_no_5";
		
		
		//echo $q;exit;

		$res = $this->ilias->db->query($q);
		
		if ($res->numRows() == 0)
		{
			$this->ilias->raiseError("no LearningObject ID given",$this->ilias->error_obj->FATAL);
		}

		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data[] = $row["parsed_text"];
		}
		
		echo TUtil::StopWatch($T1)." get_structure<br/>";
		
		return implode($data);
*/

		$res = $this->ilias->db->query($q);
		
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data[$row["node_id"]] = $row;

			if ($row["struct"] & 1)
			{
				$tmp[$row["node_id"]][] = array ($row["attribute"] => $row["value"]);
			}
		}


		foreach ($tmp as $node_id => $node)
		{
			$attr_list = array();
			
			foreach ($node as $attr)
			{
				//var_dump($attr);
				$attr_list[key($attr)] = $attr[key($attr)];
			}

			$data[$node_id]["attr_list"] = $attr_list;
		}

		// remove document node
		//array_shift($data);
		
		//echo TUtil::StopWatch($T1)." get_structure<br/>";
		return $data;
	}
	
	/**
	* gets specified element and all its subelements,
	* builds with this information a domDocument and
	* return the domxml representation of this xml document
	* 
	* @param	integer	object_id where to start fetching the xml data
	* @return	object	domDocument
	* @access	public
	*/ 
	function getTree ($a_lo_id)
	{
		return $tree;
	}

	/**
	* gets specified element with all attributes and text elements
	* return the domDocument
	* 
	* @param	integer	node_id where to start fetching the xml data
	* @return	object	domDocument
	* @access	public
	*/ 
	function getNode ($a_node_id)
	{
		return $node;
	}
	
	/**
	* gets specified element
	* 
	* @param	integer	node_id of domNode
	* @return	object	domNode
	* @access	public
	*/
	function getElementName ($a_node_id)
	{
		$q = "SELECT leaf_text FROM lo_element_name_leaf ".
			 "WHERE node_id='".$a_node_id."' LIMIT 1";
		//$res = $this->ilias->db->query($q);
		$res = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);

		return $res["leaf_text"];
	}
	
	function getElementText ($a_node_id)
	{
		$q = "SELECT leaf_text FROM lo_text_leaf ".
			 "WHERE node_id='".$a_node_id."' LIMIT 1";
		//$res = $this->ilias->db->query($q);
		$res = $this->ilias->db->getRow($q, DB_FETCHMODE_ASSOC);

		return $res["leaf_text"];
	}
	/**
	* get all attributes of specified element
	* returns false if element has no attributes
	* 
	* @param	integer	node_id of domNode
	* @return	array	all attributes (attr[name] = value)
	* @access	public
	*/
	function getAttributes ($a_node_id)
	{
		$q = "SELECT a_name.attribute,a_value.value ".
			 "FROM lo_attribute_idx AS a_idx ".
			 "LEFT JOIN lo_attribute_name AS a_name ON a_idx.attribute_id=a_name.attribute_id ".
			 "LEFT JOIN lo_attribute_value AS a_value ON a_idx.value_id=a_value.value_id ".
			 "WHERE a_idx.node_id = '".$a_node_id."'";
			 
		 //echo $q;exit;

		$res = $this->ilias->db->query($q);
		
		if ($res->numRows())
		{
			while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$data[$row["attribute"]] = $row["value"];
			}

			return $data;
		}

		return false;
	}

	
	/**
	* get a single attribute value of a given element and LearningObject
	* 
	* @param	integer	lo_id
	* @param	string	element name
	* @return	string	attribute value
	* @access	public
	*/
	function getAttributeValue ($a_lo_id,$a_element,$a_attribute)
	{
		//$T3 = TUtil::StopWatch();		

		$q = "SELECT a_value.value ".
			 "FROM lo_tree AS lo ".
			 "LEFT JOIN lo_element_idx AS e_idx ON lo.node_id = e_idx.node_id ".
			 "LEFT JOIN lo_element_name AS el ON e_idx.element_id = el.element_id ".
			 "LEFT JOIN lo_attribute_idx AS a_idx ON lo.node_id = a_idx.node_id ".
			 "LEFT JOIN lo_attribute_name AS a_name ON a_idx.attribute_id=a_name.attribute_id ".
			 "LEFT JOIN lo_attribute_value AS a_value ON a_idx.value_id=a_value.value_id ".
			 "WHERE lo_id = '".$a_lo_id."' ".
			 "AND lo.struct > 0 ". // <-- need a bitwise AND against 1
			 "AND el.element = '".$a_element."' ".
			 "AND a_name.attribute = '".$a_attribute."'";
			 
		//echo $q;exit;

		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow();
		//echo TUtil::StopWatch($T3)." getAttributeValue<br/>";
	
		return $row[0];
	}
	
	function setNavigation()
	{
		// chapter up
	
		// todo: i need the parent parent at this place!
		if ($this->lo_parent != $this->lm_id)
		{
			$up["child"] = $this->lo_parent;
			$up["parent"] = "missing";
		}
		
		// previous & next page
		$node_data = $this->lm_tree->getChilds($this->lo_parent);
		
		foreach ($node_data as $key => $node)
		{
			if ($this->lo_id == $node["child"])
			{
				if ($key > 0)
				{
					$prev["child"] = $node_data[$key-1]["child"];
					$prev["parent"] = $node_data[$key-1]["parent"];
				}
				
				if (count($node_data) > $key + 1)
				{
					$next["child"] = $node_data[$key+1]["child"];
					$next["parent"] = $node_data[$key+1]["parent"];
				}
				else
				{
					// look if there is a subchapter
					$subnode_data = $this->lm_tree->getChilds($this->lo_id);
					
					if (count($subnode_data) > 0)
					{
						// fetch first child
						$next["child"] = $subnode_data[0]["child"];
						$next["parent"] = $subnode_data[0]["parent"];
					}
				}
				
				break;
			}
		}
		
		
		$navbar = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>";
		
		if ($prev)
		{
			$navbar .= "<td>".$this->img_url($prev["child"],$prev["parent"],$this->img_link("arr_left.gif"))."</td>";
		}
		
		if ($up)
		{
			$navbar .= "<td>".$this->img_url($up["child"],$up["parent"],$this->img_link("arr_up.gif"))."</td>";
		}
		
		if ($next)
		{
			$navbar .= "<td>".$this->img_url($next["child"],$next["parent"],$this->img_link("arr_right.gif"))."</td>";
		}
		
		$navbar .= "</tr></table>";
		
		return $navbar;
	}
	
	function img_link($a_img)
	{
		return "<img src=\"./images/navigation/".$a_img."\" border=\"0\"/>";
	}
	
	function img_url($a_id, $a_parent, $a_img)
	{
		return "<a href=\"./lo_view.php?type=lo&obj_id=".$_GET["obj_id"]."&lm_id=".$_GET["lm_id"]."&lo_id=".$a_id."&lo_parent=".$a_parent."\">".$a_img."</a>";
	}	
	
	/**
	* get all childs of specified element and return them in an array of
	* domNode objects in the orginal order (from left zu right)
	* returns false if element has no childs
	* 
	* @param	integer	node_id of domNode
	* @return	array	domNode objects which are childs of $a_node_id
	* @access	public
	*/
	function getChildNodes ($a_node_id)
	{
		return $nodes;
	
		return false;
	}

	/**
	* get parent of specified element
	* and return domNode object
	* returns false if element has no parent (its the root node)
	* 
	* @param	integer	node_id of domNode
	* @return	array	parent domNode object
	* @access	public
	*/
	function getParentNode ($a_node_id)
	{
		return $node;
	
		return false;
	}
	
	function getFirstChild ($a_node_id)
	{
		return $node;
	
		return false;
	}

	function getprevSibling ($a_node_id)
	{
		return $node;
	
		return false;
	}

	function getNextSibling ($a_node_id)
	{
		return $node;
	
		return false;
	}
} // END class.sql2xml
?>