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

require_once "class.XML2SQL.php";
require_once "class.SQL2XML.php";

/**
* This class provides a nested set wrapper for DOM XML trees
* 
* XMLNestedSet reads xml files, creates a DOM XML tree and creates
* a nested set transformation for that tree.
*
* @author Sascha Hofmann <shofmann@databay.de>
* @author	Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
* @module   class.XMLNestedSet.php
* @modulegroup   Assessment
*/
class XMLNestedSet
{
	/**
	* domxml object
	* 
	* @var		object	domDocument
	* @access	public 
	*/
	var $doc;

	/**
	* nested set representation of domDocumnet
	* 
	* @var		array	nested set representation
	* @access	public 
	*/
	var $nestedset;

	/**
	* error messages
	* contains errors occured during parsing/validating xml-document
	* 
	* @var		array	error
	* @access	public 
	*/
	var $error;

	/**
	* Constructor
	* init XMLNestedSet handler
	* You may initiate this class with an existing domDocument 
	* @param	object	domDocument
	* @access	public 
	*/
	function XMLNestedSet ()
	{
		$num = func_num_args();
		$args = func_get_args();
		
		if (($num == 1) && is_object($args[0]))
		{
			$this->doc = $args[0];
		}
		$this->nestedset = array();
		register_shutdown_function(array(&$this, '_XMLNestedSet'));
	}
	
	function _XMLNestedSet() {
		if ($this->doc) {
			$this->doc->free();
		}
	}
	

	/**
	* Loads a XML document from a file and builds a DOM representation
	* in $this->doc.
	* The xml-document is parsed automatically. You may also validate it
	* against a DTD by setting the second parameter to 'true'
	* 
	* @param	string	Complete path to the xml file
	* @param	boolean	set mode: parsing (false,default) or validating (true)
	* @access	public 
	*/
	function loadDocument ($a_filename, $a_validate = false)
	{
		if ($a_validate) {
			$mode = DOMXML_LOAD_VALIDATING;
		} else {
			$mode = DOMXML_LOAD_PARSING;
		} 

		if (!file_exists($a_filename)) {
			array_push($this->error, array(
				"line" => "",
				"errormessage" => "The file $a_filename does not exist!"
			));
			return;
		}
		
		$this->doc = domxml_open_file($a_filename, $mode, $this->error);

		// stop parsing if an error occured
		if ($this->error) {
			$error_msg = "Error(s) while parsing the document!<br><br>";
			
			foreach ($this->error as $error) {
				$error_msg .= $error["errormessage"]." in line: ".$error["line"]."<br>";
			}
			print $error_msg;
			exit();
		}
		
		// set encoding to UTF-8 if empty
		$this->setEncoding("iso-8859-1", true);
		// set charset to UTF-8
		$this->setCharset("iso-8859-1");

		return $this->doc; 
	}

	/**
	* Set the encoding of the DOM XML document
	* 
	* Set the encoding of the DOM XML document. If the document has a standard
	* encoding you have to force that the encoding will be overwritten.
  *
	* @param	string	encoding charset
	* @param	boolean	overwrite existing encoding charset (true) or not (false)
	* @return	boolean	returns true when encoding was sucessfully changed
	* @access	public 
	*/
	function setEncoding ($a_encode, $a_overwrite = false)
	{
		if (empty($this->doc->encoding) or ($a_overwrite)) {
			$this->doc->encoding = $a_encode;
			return true;
		}
		return false;
	}

	/**
	* Get the encoding of the DOM XML document
	* 
	* Get the encoding of the DOM XML document
	* 
	* @return	string	encoding charset
	* @access	public 
	*/
	function getEncoding ()
	{
		return $this->doc->encoding;
	}

	/**
	* Set the charset of the DOM XML document
	* 
	* Set the charset of the DOM XML document. If the document has a standard
	* charset you have to force that the charset will be overwritten.
	* 
	* @param	string	charset
	* @param	boolean	overwrite existing charset (true) or not (false)
	* @return	boolean	returns true when charset was sucessfully changed
	* @access	public 
	*/
	function setCharset ($a_charset, $a_overwrite = false)
	{
		if (is_integer($this->doc->charset) or ($a_overwrite)) {
			$this->doc->charset = $a_charset;
			return true;
		}
		
		return false;
	}

	/**
	* Get the charset of the DOM XML document
	* 
	* Get the charset of the DOM XML document
	* 
	* @return	string	charset
	* @access	public 
	*/
	function getCharset ()
	{
		return $this->doc->charset;
	}
	
	/**
	*
	* Creates a nested set transformation of a DOM XML document node
	*
	* Creates a nested set transformation of a DOM XML document node and calles
	* the function recursive for all child nodes.
	* The following structure informations will be stored:
	* $arr[id] = array(	
	*           content (str) = node_value (only text nodes have a value)
	* 					name	  (str) = tagname or entityname
	* 					type	  (int) = element_type_id
	* 					depth	  (int) = depth of node in tree
	* 					parent  (int) = id of parent node
	* 					first	  (int) = id of first child node
	* 					prev	  (int) = id of previous sibling node
	* 					next	  (int) = id of next sibling node
	* 					left	  (int) = left value (for traversing tree in relational DB)
	* 					right	  (int) = right value (for traversing tree in relational DB))
	* The key is the internal id of the DOM XML document. Also the ids of other nodes are internal references.
	* The array is written to $this->nestedset. Use $this->buildTree() to return the variable.
	*
	* @param	object		domNode
	* @param	integer		left value (optional; only needed for recursion))
	* @param	integer		depth of node in tree (optional, default is 0)
	* @access	private
	*/
	function transform ($node, $left2 = -1, $lvl = 0)
	{ 
		// Static varialbe for the nested set counter
		static $left;
		
		// set depth
		$lvl++;

		// start value given from outside?
		if ($left2 > 0) {
			$left = $left2;
		}
		
		// set default value 1 if no value given
		if (!$left) {
			$left = 1;
		} 

		// convert DomNode object to an array
		$node2 = (array)$node;
		
		// init structure data
		// provides additional information about document structure
		// bitwise:
		// 1: has attributes
		// 2: has text element 
		$this->nestedset[$node2[0]]["struct"] = 0;

		if ($parent = $node->parent_node()) 
		{
			$parent = (array)$parent;
		} 

		if ($first = $node->first_child())
		{
			$first = (array)$first;
		} 

		if ($prev = $node->previous_sibling()) 
		{
			$prev = (array)$prev;
		} 

		if ($next = $node->next_sibling()) {
			$next = (array)$next;
		} 
		
		$this->nestedset[$node2[0]]["content"] = trim($node->node_value());
		$this->nestedset[$node2[0]]["name"] = $node->node_name();
		$this->nestedset[$node2[0]]["type"] = $node->type;
		$this->nestedset[$node2[0]]["depth"] = $lvl;
		$this->nestedset[$node2[0]]["parent"] = $parent[0];
		$this->nestedset[$node2[0]]["first"] = $first[0];
		$this->nestedset[$node2[0]]["prev"] = $prev[0];
		$this->nestedset[$node2[0]]["next"] = $next[0];
		$this->nestedset[$node2[0]]["left"] = $left;

		// increase nested set counter for the next node
		$left++;
		
		// write attributes to sub-array
		if ($node->has_attributes())
		{
			$data = "";
			
			foreach ($node->attributes() as $attribute)
			{
				$data[$attribute->name] = $attribute->value;
			}

			$this->nestedset[$node2[0]]["attr_list"] = $data;
			$this->nestedset[$node2[0]]["struct"] += 1;
		}

		// check if one child is a text_node
		foreach ($node->child_nodes() as $child)
		{
			if ($child->node_type() == XML_TEXT_NODE)
			{
				$this->nestedset[$node2[0]]["struct"] += 2;
				break;
			}
		}
		
		// recursive call
		foreach ($node->child_nodes() as $child)
		{
			$this->transform($child, $left, $lvl);
		}
		
		$this->nestedset[$node2[0]]["right"] = $left;
		$left++;
	}

	/**
	* Wrapper for $this->transform
	* defaults to $this->doc if no node given
	* and returns $this->nestedset
	*
	* @param	object		domNode
	* @return	array		tree structure of domDocument.
	* 						Returns the array described in $this->transform
	* @access	public
	*/
	function &buildTree ($a_node = "")
	{
		$this->nestedset = array();
		if (empty($a_node)) {
			$a_node = $this->doc;
		}
		
		$this->transform($a_node, 1);
		
		return $this->nestedset;
	}


	/**
	* Traverse domDocument and removes all useless nodes
	* that are created due to whitespaces in the source file
	* 
	* @param	object		domNode
	* @access	private
	*/
	function trim ($a_node)
	{
		if ($a_node->has_child_nodes()) {
			$childs = $a_node->child_nodes();

			foreach ($childs as $child) {
				$content = trim($child->get_content());

				if (empty($content) and ($child->type == XML_TEXT_NODE)) {
					$child->unlink_node();
				} else {
					$this->trim($child);
				} 
			} 
		} 
	}

	/**
	* Wrapper for $this->trim
	* defaults to $this->doc if no node given
	* and returns cleaned dom document
	*
	* @param	object		domNode (optional)
	* @access	public
	*/
	function trimDocument ($a_node = '')
	{
		if (empty($a_node))	{
			$a_node = $this->doc;
		}
	
		$this->trim($a_node);
		return $a_node;
	}

	/**
	* wrapper for dump_mem() and dump_file()
	* converts the entire DOM tree in $this->doc to a string
	* optional: writes the string to a file if path & filename
	* is specified. Otherwise only the string is returned.
	* 
	* @param	string	path/filename
	* @param	boolean	compress XML content
	* @param	boolean	format XML content with whitespaces
	* @return	string	XML content in a string
	* @access	public
	*/
	function dumpDocument ($a_stdout = -1, $a_compress = false, $a_format = false)
	{
		if ($a_stdout != -1) {
			$this->doc->dump_file($a_stdout,$a_compress,$a_format);
		}
		
		return $this->doc->dump_mem();
	}

	/**
	* Fetch all text parts from an element even when
	* the text is interupted by another element
	* example:
	* <paragraph>This text is<b>part</b> of the <i>same element</i>.</paragraph>
	* This method returns:
	* This text is part of the same element.
	* 
	* @param	object	dom node of type ELEMENT (id:1)
	* @return	string	text of entire element
	* @access	public
	*/
	function getTextFromElement ($a_element)
	{
		if ($a_element->node_type() == XML_ELEMENT_NODE) {
			$value = "";
		
			foreach ($a_element->child_nodes() as $child) {
				if ($child->node_type() == XML_TEXT_NODE) {
					$value .= $child->content;
				}
			}
		
			return trim($value);
		}
		
		die("<b>".$a_element."</b> is not a valid element node!");
	}

	/**
	* Find leaf elements. In this context leaf elements are defined as
	* elements that don't contain other elements of the same type!
	* 
	* @param	object	domNode of type ELEMENT
	* @param	string	tagname your are looking for
	* @param	integer	auxilliary variable to avoid counting start node itself. must set to 1 for the first function call
	* @return	array	domNodes (object) which are leaf elements
	* @access	public
	*/
	function isLeafElement ($a_node, $a_elementname, $a_num = 0)
	{
		$var = true;
		
		if ($childs = $a_node->child_nodes()) {
			foreach ($childs as $child) {
				$var = $this->isLeafElement($child, $a_elementname);

				if (!$var) {
					return false;
				}
			}
		}
		
		if (($a_node->node_type() == XML_ELEMENT_NODE) && ($a_node->tagname == $a_elementname) && ($a_num != 1)) {
			return false;
		}
		
		return $var;
	}

	/**
	* Wrapper for get_elements_by_tagname
	* searches domDocument for specified elementname, starting at
	* $a_node. If no node was given searches the entire domDocument in $this->doc
	* returns an array of all nodes found 
	* 
	* @param	string	tagname of element
	* @param	object	domNode where to start searching (optional)
	* @return	array	domNodes (object) which have specified elementname
	* @access	public
	*/	
	function getElementsByTagname ($a_elementname, $a_node = "")
	{
		if (empty($a_node)) {
			$a_node = $this->doc;
		}
		
		if (count($node = $a_node->get_elements_by_tagname($a_elementname)) > 0) {
			return $node;
		}
		
		return false;
	}

	/**
	* Wrapper for document_element
	* Main purpose of this method is to simplify access
	* of DOM-Functions.
	* @return	object	domNode
	* @access	public 
	*/
	function getRoot ()
	{
		return $this->doc->document_element();
	}

	/**
	* Get internal reference id of a domNode
	* 
	* @param	object	domNode
	* @return	integer	internal Id of domNode
	* @access	public
	*/
	function getElementId($a_node)
	{
		$node = (array) $a_node;
		return $node[0];
	}

	/**
	* Get node_name
	* 
	* @param	object	domNode
 	* @return	string	name of domNode
	* @access	public
	*/
	function getElementName($a_node)
	{
		return $a_node->node_name();
	}
	
	function save_to_db($db) {
		if (empty($this->doc)) 
		{
			return;
		}
		if (empty($this->nestedset))
		{
			$this->buildTree();
		}
		$xml2sql = new XML2SQL($db, $this->nestedset, "1.0", $this->getEncoding(), $this->getCharset());
		$xml2sql->insertDocument();
		unset($xml2sql);
	}
	
	function loadFromDb($db, $xml_object_id) {
		$sql2xml = new SQL2XML($db, $xml_object_id);
		if ($this->doc) {
			$this->doc->free();
		}
		$this->doc =& $sql2xml->doc;
		unset($sql2xml);
	}
	
	function delete_from_db($db, $xml_object_id) {
		$tables = array(
			"xml_element_namespace",
			"xml_pi_target",
			"xml_pi_data",
			"xml_cdata",
			"xml_entity_reference",
			"xml_attribute_namespace",
			"xml_text",
			"xml_comment",
			"xml_attribute_idx",
			"xml_element_idx"
		);
		foreach ($tables as $table)
		{
			$q = sprintf ("SELECT $table.* FROM $table, xml_tree WHERE $table.node_id = xml_tree.node_id AND xml_tree.xml_id = %s",
				$db->quote("$xml_object_id")
			);
			$result = $db->query($q);
			if ($result->numRows()) {
				while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
				{
					$q_delete = sprintf ("DELETE FROM $table WHERE node_id = %s",
						$db->quote($row->node_id)
					);
					$result_delete = $db->query($q_delete);
					if (strcmp($table, "xml_attribute_idx") == 0)
					{
						$q_delete = sprintf ("DELETE FROM xml_attribute_value WHERE value_id = %s",
							$db->quote($row->value_id)
						);
						$result_delete = $db->query($q_delete);
						$q_delete = sprintf ("DELETE FROM xml_attribute_name WHERE attribute_id = %s",
							$db->quote($row->attribute_id)
						);
						$result_delete = $db->query($q_delete);
					}
					if (strcmp($table, "xml_element_idx") == 0)
					{
						$q_delete = sprintf ("DELETE FROM xml_element_name WHERE element_id = %s",
							$db->quote($row->element_id)
						);
						$result_delete = $db->query($q_delete);
					}
				}
			}
		}
		$q = sprintf("DELETE FROM xml_object WHERE ID=%s",
			$db->quote($xml_object_id)
		);
		$result = $db->query($q);
		$q = sprintf("DELETE FROM xml_tree WHERE xml_id = %s",
			$db->quote($xml_object_id)
		);
		$result = $db->query($q);
	}
} // END class.XMLNestedSet

?>