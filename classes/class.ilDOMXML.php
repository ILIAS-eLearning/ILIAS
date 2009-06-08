<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* domxml wrapper class
* This class provides some more complex methods to access a domDocument
* via DOMXML.
* For basic tasks when building xml-documents please use
* the standard functions from the domxml extension of PHP
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class ilDOMXML
{
	/**
	* domxml object
	* 
	* @var		object	domDocument
	* @access	public 
	*/
	var $doc;

	/**
	* tree representation of domDocumnet
	* 
	* @var		array	tree
	* @access	public 
	*/
	var $tree;

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
	* init domxml handler
	* You may (a) initiate this class with an existing domDocument 
	* OR (b) create a new domDocument
	* depending on the parameters you pass to this constructor:
	* 
	* (a) init existing domDocument
	* @param	object	domDocument
	* 
	* (b) init new domDocument
	* @param	string	xml version (optional)
	* @param	string	encoding charset (optional)
	* @param	string	charset (optional)
	* @access	public 
	*/
	function ilDOMXML ()
	{
		$num = func_num_args();
		$args = func_get_args();
		
		if (($num == 1) && is_object($args[0]))
		{
			$this->doc = $args[0];
		}
		else
		{
			$this->initNewDocument($args[0],$args[1],$args[2]);
		}
	}
	
	function _ilDOMXML ()
	{
		if (DEBUG)
		{
            printf("domxml destructor called, class=%s\n", get_class($this)."<br/>");
        }
	}
	
	

	/**
	* init new domDocument
	* private method. Please use constructor to init a new domDocument
	* 
	* @param	string	xml version (default: 1.0)
	* @param	string	encoding charset (default: UTF-8)
	* @param	string	charset (default: UTF-8)
	* @access	private 
	*/
	function initNewDocument ($a_version = "", $a_encoding = "", $a_charset = "")
	{
		if (!$a_version) {
			$a_version = "1.0";
		}

		if (!$a_encoding) {
			$a_encoding = "UTF-8";
		}

		if (!$a_charset) {
			$a_charset = "UTF-8";
		}

		// create the xml string (workaround for domxml_new_doc) ***
		$xmlHeader = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>". // *** ISO-8859-1
					 "<root />"; // dummy node
		
		// create a domxml document object
		$this->doc = domxml_open_mem($xmlHeader); // *** Fehlerabfrage

		// delete dummy node 
		$root = $this->doc->document_element();
		$root->unlink_node();
		
		//$this->doc = new_xmldoc($a_version);
		$this->setEncoding($a_encoding);
		$this->setCharset($a_charset);
	}

	/**
	* wrapper for crating a root element
	* this methods avoids creating multiple elements on root level.
	* This is necessary since PHP crashes if you try to create another element
	* on top level of domDocument :-(
	* 
	* @param	string	taggname of root element
	* @access	public 
	*/
	function createRootElement ($a_element)
	{
		// check if rootNode already exists
		if ($root = $this->getRoot()) {
			return false;
		}
		
		return $this->appendChild($this->createElement($a_element));
	}

	/**
	* loads a xml-document from file and build a DOM representation
	* in $this->doc.
	* The xml-document is parsed automatically. You may also validate it
	* against a DTD by setting the 3rd parameter to 'true'
	* 
	* @param	string	filename
	* @param	string	filepath
	* @param	boolean	set mode: parsing (false,default) or validating (true)
	* @access	public 
	*/
	function loadDocument ($a_filename, $a_filepath, $a_validate = false)
	{
		if ($a_validate) {
			$mode = DOMXML_LOAD_VALIDATING;
		} else {
			$mode = DOMXML_LOAD_PARSING;
		} 

		$this->doc = domxml_open_file($a_filepath . "/" . $a_filename, $mode, $this->error);

		// stop parsing if an error occured
		if ($this->error) {
			$error_msg = "Error(s) while parsing the document!<br><br>";

			foreach ($this->error as $error) {
				$error_msg .= $error["errormessage"]." in line: ".$error["line"]."<br>";
			}
			
			// error handling with ilias object?
			echo $error_msg;
			exit();
		}
		
		// set encoding to UTF-8 if empty
		$this->setEncoding("iso-8859-1",true);
		// set charset to UTF-8
		$this->setCharset("iso-8859-1");

		return $this->doc; 
	}

	/**
	* traverse domDocument and removes all useless nodes
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

				if (empty($content)) {
					$child->unlink_node();
				} else {
					$this->trim($child);
				} 
			} 
		} 
	}

	/**
	* wrapper for $this->trim
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
	* traverse the domDocument and build a tree which contains
	* additional information about domDocument's structure:
	* $arr[id] = array(	content (str) = node_value (only text nodes have a value)
	* 					name	(str) = tagname or entityname
	* 					type	(int) = element_type_id
	* 					depth	(int) = depth of node in tree
	* 					parent  (int) = id of parent node
	* 					first	(int) = id of first child node
	* 					prev	(int) = id of previous sibling node
	* 					next	(int) = id of next sibling node
	* 					left	(int) = left value (for traversing tree in relational DB)
	* 					right	(int) = right value (for traversing tree in relational DB))
	* The key is the internal id of the domDocument. Also the ids of other nodes are internal references.
	* The array is written to $this->tree. Use $this->buildTree() to return the variable.
	*
	* @param	object		domNode
	* @param	integer		left value (optional; only needed for recursion))
	* @param	integer		depth of node in tree (optional, default is 0)
	* @access	private
	*/
	function transform ($node, $left2 = -1, $lvl = 0)
	{ 
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

		$node2 = (array)$node;
		
		// init structure data
		// provides additional information about document structure
		// bitwise:
		// 1: has attributes
		// 2: has text element 
		$this->tree[$node2[0]]["struct"] = 0;

		if ($parent = $node->parent_node()) {
			$parent = (array)$parent;
		} 

		if ($first = $node->first_child())
		{
			$first = (array)$first;
		} 

		if ($prev = $node->previous_sibling()) {
			$prev = (array)$prev;
		} 

		if ($next = $node->next_sibling()) {
			$next = (array)$next;
		} 

		$this->tree[$node2[0]]["content"] = trim($node->node_value());
		$this->tree[$node2[0]]["name"] = $node->node_name();
		$this->tree[$node2[0]]["type"] = $node->type;
		$this->tree[$node2[0]]["depth"] = $lvl;
		$this->tree[$node2[0]]["parent"] = $parent[0];
		$this->tree[$node2[0]]["first"] = $first[0];
		$this->tree[$node2[0]]["prev"] = $prev[0];
		$this->tree[$node2[0]]["next"] = $next[0];
		$this->tree[$node2[0]]["left"] = $left;
		$left++;

		// write attributes to sub-array
		if ($node->has_attributes())
		{
			$data = "";
			
			foreach ($node->attributes() as $attribute)
			{
				$data[$attribute->name] = $attribute->value;
			}

			$this->tree[$node2[0]]["attr_list"] = $data;
			$this->tree[$node2[0]]["struct"] += 1;
		}

		// check if one child is a text_node
		foreach ($node->child_nodes() as $child)
		{
			if ($child->node_type() == XML_TEXT_NODE)
			{
				$this->tree[$node2[0]]["struct"] += 2;
				break;
			}
		}
		
		// recursive call
		// please don't merge this loop with the one above together! 
		foreach ($node->child_nodes() as $child)
		{
			$this->transform($child, $left, $lvl);
		}
		
		$this->tree[$node2[0]]["right"] = $left;
		$left++;
	}

	/**
	* wrapper for $this->transform
	* defaults to $this->doc if no node given
	* and returns $this->tree
	*
	* @param	object		domNode
	* @return	array		tree structure of domDocument.
	* 						Returns the array described in $this->transform
	* @access	public
	*/
	function buildTree ($a_node = "")
	{
		if (empty($a_node)) {
			$a_node = $this->doc;
		}
		
		$this->transform($a_node,1);
		
		return $this->tree;
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
	* fetch all text parts from an element even when
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
	* find leaf elements. In this context leaf elements are defined as
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
	* wrapper for get_elements_by_tagname
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
	* creates an element entry for the removed LearningObject:
	* <LO id=[obj_id of child LO] />
	* 
	* @param	object	domNode
	* @param	integer	object_id of removed LO
	* @access	public
	* 
	* TODO: Moved this method to class where it fits better (LearningObject? xml2sql?)
	*/
	function appendReferenceNodeForLO ($a_node, $a_lo_id, $a_lm_id, $a_prev_sibling)
	{
		$newnode = $this->createElement("LO");
		
		if (empty($a_prev_sibling))
		{
			$node = $a_node->append_child($newnode);
		}
		else
		{
			$node = $a_prev_sibling->append_sibling($newnode);
		}

		$node->set_attribute("id",$a_lo_id);
		$node->set_attribute("lm",$a_lm_id);
	}

	/**
	* wrapper for append_child
	* Main purpose of this method is to simplify access
	* of DOM-Functions.
	* @param	object	domNode
	* @return	object	domNode
	* @access	public 
	*/
	function appendChild ($a_node)
	{
		return $this->doc->append_child($a_node);
	}

	/**
	* wrapper for document_element
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
	* wrapper for create_element
	* Main purpose of this method is to simplify access
	* of DOM-Functions.
	* @param	object	domNode
	* @return	object	domNode
	* @access	public 
	*/
	function createElement ($a_node)
	{
		return $this->doc->create_element($a_node);
	}

	function addElement ($a_parent, $a_node)
	{
		$node = $this->doc->create_element($a_node);
		$node = $a_parent->append_child($node);
		
		return $node;
	}
		
	/**
	* wrapper for create_element
	* Main purpose of this method is to simplify access
	* of DOM-Functions.
	* @param	object	domNode
	* @return	object	domNode
	* @access	public 
	*/
	function createText ($a_text)
	{
		return $this->doc->create_text_node($a_text);
	}

	/**
	* creates a complete node of type element
	* with a text_node within the element and attributes
	* The node is append to domDocument under the given parent_node
	* 
	* @param	object	domNode the place where the new created node will be inserted 
	* @param	string	name of the element
	* @param	array	list of attributes (optional). syntax: $arr["attr_name"] = attr_value
	* @param	string	if any text is given a text_node will be created (optional).
	* @return	object	domNode just created
	* @access	public
	*/
	function createNode ($a_parent, $a_elementname, $a_attr_list = NULL, $a_text = NULL)
	{
		// create new element node
		$node = $this->createElement($a_elementname);
		
		// set attributes
		if (is_array($a_attr_list)) {
			foreach ($a_attr_list as $attr => $value) {
				$node->set_attribute($attr, $value);
			}
		}
		
		// create and add a text node to the new element node
		if (is_string($a_text)) {
			$node_text = $this->doc->create_text_node($a_text);
			$node_text = $node->append_child($node_text);
		}
		
		// add element node at at the end of the children of the parent
		$node = $a_parent->append_child($node);
		
		return $node;
	}

	/**
	* get internal reference id of a domNode
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
	* get node_name
	* 
	* @param	object	domNode
 	* @return	string	name of domNode
	* @access	public
	*/
	function getElementName($a_node)
	{
		return $a_node->node_name();
	}

	/**
	* set encoding of domDocument
	*
	* @param	string	encoding charset
	* @param	boolean	overwrite existing encoding charset (true) or not (false)
	* @return	boolean	returns true when encoding was sucessfully changed
	* @access	public 
	*/
	function setEncoding ($a_encode,$a_overwrite = false)
	{
		if (empty($this->doc->encoding) or ($a_overwrite)) {
			$this->doc->encoding = $a_encode;
			return true;
		}
		
		return false;
	}

	/**
	* get encoding of domDocument
	* 
	* @return	string	encoding charset
	* @access	public 
	*/
	function getEncoding ()
	{
		return $this->doc->encoding;
	}

	/**
	* set charset of domDocument
	* 
	* @param	string	charset
	* @param	boolean	overwrite existing charset (true) or not (false)
	* @return	boolean	returns true when charset was sucessfully changed
	* @access	public 
	*/
	function setCharset ($a_charset,$a_overwrite = false)
	{
		if (is_integer($this->doc->charset) or ($a_overwrite)) {
			$this->doc->charset = $a_charset;
			return true;
		}
		
		return false;
	}

	/**
	* get charset of domDocument
	* 
	* @return	string	charset
	* @access	public
	*/
	function getCharset ()
	{
		return $this->doc->charset;
	}

	/**
	* fetch Title & Description from MetaData-Section of domDocument
	*
	* @return	array	Titel & Description
	* @access	public
	*/
	function getInfo ()
	{
		$node = $this->getElementsByTagname("MetaData");

		if($node !== false)
		{
			$childs = $node[0]->child_nodes();

			foreach ($childs as $child)
			{
					if (($child->node_type() == XML_ELEMENT_NODE) && ($child->tagname == "General"))
					{
						$childs2 = $child->child_nodes();

						foreach ($childs2 as $child2)
						{
							if (($child2->node_type() == XML_ELEMENT_NODE) && ($child2->tagname == "Title" || $child2->tagname == "Description"))
							{
								$arr[$child2->tagname] = $child2->get_content();
							}
						}

						// General-tag was found. Stop foreach-loop
						break;
					}
			}
		}

		// for compatibility reasons:
		$arr["title"] = $arr["Title"];
		$arr["desc"] = $arr["Description"];

		return $arr;
	}

	/**
	* get all LO references in Learning Object
	* 
	* @return	array	object ids of LearningObjects
	* @access	public
	*/ 
	function getReferences()
	{
		if ($nodes = $this->getElementsByTagname("LO"))
		{
			foreach ($nodes as $node)
			{
				$attr[] = $node->get_attribute("id");
			}
		}

		return $attr;
	}
} // END class.domxml
?>
