<?php
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
     * tree representation of xmldoc
     * 
     * @var array xmltree
     * @access public 
     */
    var $xmltree;

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
     * error messages
     * contains errors occured during parsing/validating xml-document
     * 
     * @var array error
     * @access public 
     */
    var $error;

    /**
     * constructor
     * create a file handler to specified xml-file, parse file
     * & prepare file for db-upload
     * 
     * @param string $ filename of xml-file
     * @param string $ path to xml-file
     * @param integer $ object id of container where LOs in xml-file are assigned to
     * @param boolean $ validate (true) or only parse (false) document (default is parsing)
     * @access public 
     */
    function xml2sql ($a_filename, $a_filepath, $a_obj_id = -1, $a_validate = false)
    {
        global $ilias;
		
		$this->ilias =& $ilias;

        if ($a_obj_id <= 0)
		{
            $this->ilias->raiseError("lo_id is not set!",$this->ilias->error_obj->MESSAGE);
        }
		else
		{
            $this->obj_id = $a_obj_id;
        } 

        if ($a_validate) {
            $mode = DOMXML_LOAD_VALIDATING;
        } else {
            $mode = DOMXML_LOAD_PARSING;
        } 

        $this->domxml = domxml_open_file($a_filepath . "/" . $a_filename, $mode, $this->error);

        // stop parsing if an error occured
        if ($this->error)
		{
			$error_msg = "Error(s) while parsing the document!<br><br>";
			
			foreach ($this->error as $error)
			{
				$error_msg .= $error["errormessage"]." in line: ".$error["line"]."<br>";
			}

			$this->ilias->raiseError($error_msg,$this->ilias->error_obj->MESSAGE);
        } 
        // clean up empty text elements
        $this->prepareXMLTree($this->domxml); 
        // build xmltree
        $this->buildXMLTree($this->domxml);
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
        $sql = "INSERT INTO lo_text_leaf " . "(node_id,leaf_text) " . "VALUES " . "('" . $a_node["node"] . "','" . $a_node["content"] . "')";

        $res = $this->ilias->db->query($sql);
    } 

    function insertComment ($a_node)
    {
        $sql = "INSERT INTO lo_comment_leaf " . "(node_id,leaf_text) " . "VALUES " . "('" . $a_node["node"] . "','" . $a_node["content"] . "')";

        $res = $this->ilias->db->query($sql);
    } 

    function prepareXMLTree ($node)
    {
        if ($node->has_child_nodes()) {
            $childs = $node->child_nodes();

            foreach ($childs as $child) {
                $content = trim($child->get_content());

                if (empty($content)) {
                    $child->unlink_node();
                } else {
                    $this->prepareXMLTree($child);
                } 
            } 
        } 
    } 
    // rekursion
    function buildXMLTree ($node, $left2 = -1, $lvl = 0)
    { 
        static $left;
		
        // set depth
        $lvl++;
 
        // start value given from outside?
        if ($left2 > 0)
		{
            $left = $left2;
        } 
        // set default value 1 if no value given
        if (!$left)
		{
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

        $content = trim($node->node_value());
        $this->xmltree[$node2[0]]["content"] = $content;
        $this->xmltree[$node2[0]]["name"] = $node->node_name();
        $this->xmltree[$node2[0]]["type"] = $node->type;
        $this->xmltree[$node2[0]]["depth"] = $lvl;
        $this->xmltree[$node2[0]]["parent"] = $parent[0];
        $this->xmltree[$node2[0]]["first"] = $first[0];
        $this->xmltree[$node2[0]]["prev"] = $prev[0];
        $this->xmltree[$node2[0]]["next"] = $next[0];
        $this->xmltree[$node2[0]]["left"] = $left;
        $left++;

        if ($node->has_child_nodes())
		{
            $childs = $node->child_nodes();

            foreach ($childs as $child)
			{
                $this->buildXMLTree($child, $left, $lvl);
            } 
        } 

        $this->xmltree[$node2[0]]["right"] = $left;
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