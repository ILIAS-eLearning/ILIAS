<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. | 
   +----------------------------------------------------------------------------+
*/

/**
* Class for exporting XML documents stored in a relational database to a domxml representation
*  
* @author	Sascha Hofmann <shofmann@databay.de>
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
*/
class SQL2XML
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

	var $doc;
	/**
	* mapping db_id to internal dom_id
	* 
	* @var array mapping
	* @access public 
	*/
	var $hash;

	/**
	* db object
	* 
	* @var object db
	* @access public 
	*/
	var $db;
	
	var $nestedset;
	/**
	* constructor
	* init db-handler
	* 
	* @access public 
	*/
	function SQL2XML ($database_connection, $a_obj_id)
	{
		$this->db = $database_connection;
		$this->obj_id = $a_obj_id;
		$this->getXMLDocument();
		register_shutdown_function(array(&$this, '_SQL2XML'));
 	}

	function _SQL2XML() {
		if ($this->doc) {
			$this->doc->free();
		}
	}
	
	function retrieveHeader() {
		$q = sprintf("SELECT * FROM xml_object WHERE ID=%s",
			$this->db->quote($this->obj_id)
		);
		$result = $this->db->query($q);
		if ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			return "<?xml version=\"$row->version\" encoding=\"$row->encoding\"?>";
		} else {
			return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		}
	}

	/**
	* Gets a XML document from the database and
	* returns the domxml representation of it
	* 
	* @return	object	domDocument
	* @access	private
	*/ 
	function getXMLDocument()
	{
		$this->nestedset = $this->getStructure($this->obj_id);
		// create the xml string (workaround for domxml_new_doc) ***
		$xmlHeader = $this->retrieveHeader() . "<root />";
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlHeader);
		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		// build XML document
		$this->buildXML();
		return $this->doc->dump_mem(true);
	}
	
	function buildXML ()	
	{
		foreach ($this->nestedset as $key => $node_data)
		{
			$insert = false;
			switch ($node_data["node_type_id"])
			{
				case XML_ELEMENT_NODE:
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
				case XML_TEXT_NODE:
					$node = $this->doc->create_text_node($node_data["textnode"]);
					$insert = true;
					break;
				case XML_COMMENT_NODE:
					$node = $this->doc->create_comment($node_data["comment"]);
					$insert = true;
					break;
			}

			if ($insert)
			{
				//get parent node
				$parent = $this->hash[$node_data["parent_node_id"]];
				//build node
				if (!$parent) {
					$node = $this->doc->append_child($node);
				} else {
					$node = $parent->append_child($node);
				}
				$this->hash[$this->nestedset[$key]["node_id"]] = $node;
			}
		}
	}

	function getStructure($a_xml_id)
	{
		$q = "SELECT lo.node_id, lo.node_type_id, lo.xml_id, lo.parent_node_id, lo.struct, tx.textnode, comm.comment, el.element, a_name.attribute, a_value.value ".
			 "FROM xml_tree AS lo ".
			 "LEFT OUTER JOIN xml_element_idx AS e_idx ON lo.node_id = e_idx.node_id ".
			 "LEFT OUTER JOIN xml_element_name AS el ON e_idx.element_id = el.element_id ".
			 "LEFT OUTER JOIN xml_text AS tx ON lo.node_id = tx.node_id ".
			 "LEFT OUTER JOIN xml_comment AS comm ON lo.node_id = comm.node_id " .
			 "LEFT OUTER JOIN xml_attribute_idx AS a_idx ON lo.node_id = a_idx.node_id ".
			 "LEFT JOIN xml_attribute_name AS a_name ON a_idx.attribute_id=a_name.attribute_id ".
			 "LEFT JOIN xml_attribute_value AS a_value ON a_idx.value_id=a_value.value_id ".
			 "WHERE xml_id='".$a_xml_id."' ".
			 "ORDER BY lft ASC";
		// 2. variant: I think this is the fastest but you need mysql 4.x in order to use UNION statement
/*
		$q = "SELECT rgt FROM lo_tree ".
			 "WHERE lo_id = '".$a_lo_id."' ".
			 "AND lft = 1";
		
		$res = $this->db->query($q);
		
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

		$res = $this->db->query($q);
		
		if ($res->numRows() == 0)
		{
			print("no LearningObject ID given");
		}

		while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$data[] = $row["parsed_text"];
		}
		
		echo TUtil::StopWatch($T1)." get_structure<br/>";
		
		return implode($data);
*/

		$res = $this->db->query($q);
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
		$q = "SELECT leaf_text FROM xml_element_name_leaf ".
			 "WHERE node_id='".$a_node_id."' LIMIT 1";
		//$res = $this->db->query($q);
		$res = $this->db->getRow($q, DB_FETCHMODE_ASSOC);

		return $res["leaf_text"];
	}
	
	function getElementText ($a_node_id)
	{
		$q = "SELECT leaf_text FROM xml_text_leaf ".
			 "WHERE node_id='".$a_node_id."' LIMIT 1";
		//$res = $this->db->query($q);
		$res = $this->db->getRow($q, DB_FETCHMODE_ASSOC);

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
			 "FROM xml_attribute_idx AS a_idx ".
			 "LEFT JOIN xml_attribute_name AS a_name ON a_idx.attribute_id=a_name.attribute_id ".
			 "LEFT JOIN xml_attribute_value AS a_value ON a_idx.value_id=a_value.value_id ".
			 "WHERE a_idx.node_id = '".$a_node_id."'";
			 
		 //echo $q;exit;

		$res = $this->db->query($q);
		
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

		$q = "SELECT a_value.value ".
			 "FROM xml_tree AS lo ".
			 "LEFT JOIN lo_element_idx AS e_idx ON lo.node_id = e_idx.node_id ".
			 "LEFT JOIN lo_element_name AS el ON e_idx.element_id = el.element_id ".
			 "LEFT JOIN lo_attribute_idx AS a_idx ON lo.node_id = a_idx.node_id ".
			 "LEFT JOIN lo_attribute_name AS a_name ON a_idx.attribute_id=a_name.attribute_id ".
			 "LEFT JOIN lo_attribute_value AS a_value ON a_idx.value_id=a_value.value_id ".
			 "WHERE xml_id = '".$a_lo_id."' ".
			 "AND lo.struct > 0 ". // <-- need a bitwise AND against 1
			 "AND el.element = '".$a_element."' ".
			 "AND a_name.attribute = '".$a_attribute."'";
			 
		//echo $q;exit;

		$res = $this->db->query($q);
		$row = $res->fetchRow();
	
		return $row[0];
	}

} // END class.SQL2XML

?>