<?php
/**
* Class for importing XML documents into a relational database
*  
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class xml2sql
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
	function xml2sql ($a_xmltree,$a_lo_id)
	{
		global $ilias;
		
		$this->ilias =& $ilias;
		$this->xmltree = $a_xmltree;
		$this->obj_id = $a_lo_id;
	}
		
	function insertDocument ()
	{ 
		// insert basic structure of document
		foreach ($this->xmltree as $id => $node) {
			$node_id = $this->insertNode($node);
			$this->mapping[$id] = $node_id;
		} 
		// re-map node_ids
		foreach ($this->xmltree as $id => $node) {
			$this->xmltree[$id]["parent"] = $this->mapping[$node["parent"]];
			$this->xmltree[$id]["prev"] = $this->mapping[$node["prev"]];
			$this->xmltree[$id]["next"] = $this->mapping[$node["next"]];
			$this->xmltree[$id]["first"] = $this->mapping[$node["first"]];
			$this->xmltree[$id]["node"] = $this->mapping[$id];
		} 

		foreach ($this->xmltree as $id => $node) {
			$this->updateNode($node);
			$this->insertNodeData($node);
		} 

		return $this->xmltree;
	} 

	function insertNode ($a_node)
	{
		$sql = "INSERT INTO lo_tree " . "(lo_id,lft,rgt,node_type_id,depth) " . "VALUES " . "('" . $this->obj_id . "','" . $a_node["left"] . "','" . $a_node["right"] . "','" . $a_node["type"] . "','" . $a_node["depth"] . "') ";
		$res = $this->ilias->db->query($sql);

		$sql = "SELECT LAST_INSERT_ID()";
		$res = $this->ilias->db->query($sql);
		$row = $res->fetchRow();

		return $row[0];
	} 

	function updateNode ($a_node)
	{
		$sql = "UPDATE lo_tree SET " . "parent_node_id = '" . $a_node["parent"] . "'," . "prev_sibling_node_id = '" . $a_node["prev"] . "'," . "next_sibling_node_id = '" . $a_node["next"] . "'," . "first_child_node_id = '" . $a_node["first"] . "' " . "WHERE node_id = '" . $a_node["node"] . "' " . "AND lo_id = '" . $this->obj_id . "'";
		$res = $this->ilias->db->query($sql);
	} 

	function insertNodeData ($a_node)
	{
		$a_node = $this->prepareData($a_node);

		switch ($a_node["type"]) {
			case 1:
				$this->insertElement($a_node);
				break;
			case 2: 
				// $this->insertAttribute($a_node);
				break;
			case 3:
				$this->insertText($a_node);
				break;
			case 4: 
				// $this->insertCData($a_node);
				break;
			case 5: 
				// $this->insertEntityRef($a_node);
				break;
			case 6: 
				// $this->insertEntity($a_node);
				break;
			case 7: 
				// $this->insertPI($a_node);
				break;
			case 8:
				$this->insertComment($a_node);
				break;
			case 9: 
				// $this->insertDocument($a_node);
				break;
			case 10: 
				// $this->insertDoctype($a_node);
				break;
			default: 
				// nix
				break;
		} // switch
	} 

	function insertElement ($a_node)
	{
		$sql = "INSERT INTO lo_element_name_leaf " . "(node_id,leaf_text) " . "VALUES " . "('" . $a_node["node"] . "','" . $a_node["name"] . "')";

		$res = $this->ilias->db->query($sql);
	} 

	function insertText ($a_node)
	{
		// klappt nicht, weil die spaces maskiert sind :-(
		$content = trimDeluxe($a_node["content"]);
	
		$sql = "INSERT INTO lo_text_leaf " . "(node_id,leaf_text) " . "VALUES " . "('" . $a_node["node"] . "','" . $content . "')";

		$res = $this->ilias->db->query($sql);
	} 

	function insertComment ($a_node)
	{
		$sql = "INSERT INTO lo_comment_leaf " . "(node_id,leaf_text) " . "VALUES " . "('" . $a_node["node"] . "','" . $a_node["content"] . "')";

		$res = $this->ilias->db->query($sql);
	} 

	function prepareData ($a_data)
	{
		foreach ($a_data as $key => $value)
		{
			$data[$key] = addslashes($value);
		}
		
		return $data;
	}
} // END class xml2sql
?>