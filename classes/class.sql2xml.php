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
		$this->lm_tree = new Tree ($a_lm_id,0,$a_lm_id,$a_lm_id);

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
		global $sum; //only for debugging

		$this->lo_struct = $this->getStructure($this->lo_id);
		
		// build path information for DTD location
		if (strstr(php_uname(), "Windows"))
		{
			$path = "file://";
		}

		$path .= getcwd();
		
		// create the xml string (workaround for domxml_new_doc) ***
		$xmlHeader = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>". // *** ISO-8859-1
					 "<!DOCTYPE LearningObject SYSTEM \"".$path."/xml/ilias_lo.dtd\">".
					 "<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlHeader); // *** Fehlerabfrage
		
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		
		// create root node
		$node = $this->doc->create_element($this->lo_struct[0]["element"]);

		$root = $this->doc->append_child($node);
		$this->lo_struct[0]["dom_node"] = $root;
		
		
		// build XML document
		$this->buildXML();
			
		// now get the Level-1-LOs
		while (count($subnodes = $this->doc->get_elements_by_tagname("LO")) > 0)
		{
			foreach ($subnodes as $subnode)
			{
				// fetching lo_id and remove placeholder node
				$attributes = $subnode->attributes();
				$lo_id = $attributes[0]->value;

				$parent = $subnode->parent_node();
				$subnode->unlink_node();

				// get next LO
				//todo: first check if LO is of Level 1 !!!
				if ($this->getAttributeValue($lo_id,"General","AggregationLevel") == "1")
				{
					$this->lo_struct = $this->getStructure($lo_id);
			
					// create first node (LearningObject)
					$node = $this->doc->create_element($this->lo_struct[0]["element"]);

					$root = $parent->append_child($node);
					$this->lo_struct[0]["dom_node"] = $root;
				
					// build next XML
					$this->buildXML();
				}
			}
		}

//		echo $sum." get_attributes<br>";	
	
		return $this->doc->dump_mem(true);
	}
	
	function buildXML ()	
	{	
		foreach ($this->lo_struct as $key => $node_data)
		{
			// exclude root node
			if ($key > 0)
			{
				$attr_list = array();
				
				switch ($node_data["node_type_id"])
				{
					case 1:
						$node = $this->doc->create_element($node_data["element"]);
	
						if ($node_data["struct"] & 1)
						{
							$attr_list = $this->getAttributes($node_data["node_id"]);
						}
						
						// set attributes
						if (is_array($attr_list))
						{
							foreach ($attr_list as $attr => $value)
							{
								$node->set_attribute($attr, $value);
							}
						}
						
						//get parent node
						foreach ($this->lo_struct as $data2)
						{
							if ($node_data["parent_node_id"] == $data2["node_id"])
							{
								$parent = $data2["dom_node"];
								break;
							}
						}
	
						$node = $parent->append_child($node);
						$this->lo_struct[$key]["dom_node"] = $node;
						
						//$parent = $node;
						break;

					case 3:
						$node = $this->doc->create_text_node($node_data["textnode"]);
						
						//get parent node
						foreach ($this->lo_struct as $data2)
						{
							if ($node_data["parent_node_id"] == $data2["node_id"])
							{
								$parent = $data2["dom_node"];
								break;
							}
						}					
						
						$node = $parent->append_child($node);
						$this->lo_struct[$key]["dom_node"] = $node;					
						break;
				}
			}
		}
	}
	

	function getStructure($a_lo_id)
	{
		//$T1 = TUtil::StopWatch();
		
		$q = "SELECT lo.node_id, lo.node_type_id, lo.lo_id, lo.parent_node_id, lo.struct, tx.textnode, el.element ".
			 "FROM lo_tree AS lo ".
			 "LEFT OUTER JOIN lo_element_idx AS e_idx ON lo.node_id = e_idx.node_id ".
			 "LEFT OUTER JOIN lo_element_name AS el ON e_idx.element_id = el.element_id ".
			 "LEFT OUTER JOIN lo_text AS tx ON lo.node_id = tx.node_id ".
			 "WHERE lo_id='".$a_lo_id."' ".
			 "ORDER BY lft ASC";
			 
			 //echo $q;exit;

		$res = $this->ilias->db->query($q);
		
		if ($res->numRows() == 0)
		{
			$this->ilias->raiseError("no LearningObject ID given",$this->ilias->error_obj->FATAL);
		}
		
		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data[] = $row;
		}

		// remove document node
		array_shift($data);
		
		//echo TUtil::StopWatch($T1)." get_structure<br>";
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
		global $T3,$sum;

		$T3 = TUtil::StopWatch();		

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

			$diff = TUtil::StopWatch($T3);
			$sum = $sum + $diff;		

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