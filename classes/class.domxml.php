<?php
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
class domxml
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
	* constructor
	* init new domDocument
	* 
	* @param	string	xml version
	* @access	public 
	*/
	function domxml ($a_version = "1.0")
	{
		$this->doc = new_xmldoc($a_version);
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
	function createRoot ($a_element)
	{
		// check if rootNode already exists
		if ($root = $this->doc->document_element()) {
			return false;
		}
		
		return $this->doc->add_child($this->doc->create_element($a_element));
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
			
			echo $error_msg;
			exit();
		}
		
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
	* traverse the domDocument and builds a tree which contains
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
	* attributes are NOT converted yet!
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

		if ($parent = $node->parent_node()) {
			$parent = (array)$parent;
		} 

		if ($first = $node->first_child()) {
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

		foreach ($node->child_nodes() as $child) {
			$this->transform($child, $left, $lvl);
		} 
		
		$this->tree[$node2[0]]["right"] = $left;
		$left++;

		/**
		* if ($child->has_attributes())
		* {
		* foreach ($child->attributes() as $attribute)
		* {
		* $attribute2 = (array)$attribute;
		* //echo "<b>ATTR: ".$attribute->name."</b>";
		* //echo " (".$attribute2[0].")<br>";
		* $tree[$attribute2[0]]["name"] = $attribute->name;
		* 
		* $tree[$attribute2[0]]["left"] = $left;
		* $left++;
		* $tree[$attribute2[0]]["right"] = $left;
		* $left++;
		* //echo "<pre>";var_dump($attribute);echo "</pre>";
		* }
		* }
		*/
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
		
		$this->transform($a_node);
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
		if ($a_element->node_type() == XML_ELEMENT_NODE)
		{
			$value = "";
		
			foreach ($a_element->child_nodes() as $child)
			{
				if ($child->node_type() == XML_TEXT_NODE)
				{
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
} // END class.domxml
?>