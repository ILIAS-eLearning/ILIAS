<?php
/**
* Class LearningModuleObject
*
* @author Sascha Hofmann <shofmann@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/

class LearningModuleObject extends Object
{
	/**
	* Constructor
	* 
	* @access public
	*/
	function LearningModuleObject()
	{
		$this->Object();
	}
	
	/**
	* Overwritten method from class.Object.php
	* It handles all button commands from Learning Modules
	* 
	* @access public
	*/
	function gatewayObject()
	{
		global $lng;

		switch($_POST["cmd"])
		{
			case $lng->txt("import"):
				return $this->importObject();
				break;

			case $lng->txt("export"):
				// NO FUNCTION IN THE MOMENT
				return;
				break;

			case $lng->txt("upload"):
				return $this->uploadObject();
				break;
		}
		parent::gatewayObject();
	}

	function viewObject()
	{
		global $rbacsystem, $tree, $tpl;
		
		$lotree = new Tree($_GET["obj_id"],0,0,$_GET["obj_id"]);
		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "view", "title", "description", "last_change");
		
		if ($lotree->getChilds($this->id, $_GET["order"], $_GET["direction"]))
		{
			foreach ($lotree->Childs as $key => $val)
		    {
				// visible
				//if (!$rbacsystem->checkAccess("visible",$val["id"],$val["parent"]))
				//{
				//	continue;
				//}
		
				//visible data part
				$this->objectList["data"][] = array(
					"type" => "<img src=\"".$tpl->tplPath."/images/enlarge.gif\" border=\"0\">",
					"title" => $val["title"],
					"description" => $val["desc"],
					"last_change" => $val["last_update"]
				);

				//control information
				$this->objectList["ctrl"][] = array(
					"type" => $val["type"],
					"obj_id" => $_GET["obj_id"],
					"parent" => $_GET["parent"],
					"parent_parent" => $val["parent_parent"],
					"lm_id" => $_GET["obj_id"],
					"lo_id" => $val["id"]
				);
				
		    } //foreach
		} //if 
		return $this->objectList;
	}

	function importObject()
	{
		// nothing to do. just display the dialogue in Out
		return;
	}
	
	/**
	* extents inherited saveObject method by
	* creating a tree entry for each LearningModule
	* 
	* @param	integer	object id of new object
	* @param	integer	object id of parent object
	* @param	string	object type of parent object
	* @param	string	object type of new object
	* @param	array	object data (title,description,owner)
	* @access	public
	*/
	function saveObject($a_obj_id = '', $a_parent = '' ,$a_type = '' , $a_new_type = '' , $a_data = '')
	{
		global $tree;
		
		$obj_id = parent::saveObject($a_obj_id, $a_parent, $a_type, $a_new_type, $a_data);
		
		$tree->addTree($obj_id);
	}

	/**
	* uploads a complete LearningModule from a LO-XML file
	* 
	* @access	public
	*/
	function uploadObject()
	{
		global $HTTP_POST_FILES;
		
		require_once "classes/class.xml2sql.php";
		require_once "classes/class.domxml.php";
		require_once "classes/class.LearningObjectObject.php";
		require_once "classes/class.LearningObjectObjectOut.php";
		
		// check if file is posted
		$source = $HTTP_POST_FILES["xmldoc"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}

		// check correct file type
		if ($HTTP_POST_FILES["xmldoc"]["type"] != "text/xml")
		{
			$this->ilias->raiseError("Wrong file type!",$this->ilias->error_obj->MESSAGE);
		}
		
		// create domxml-handler
		$domxml = new domxml();
				
		//get XML-file, parse and/or validate the document
		$file = $HTTP_POST_FILES["xmldoc"]["name"];
		$root = $domxml->loadDocument(basename($source),dirname($source),$_POST["parse_mode"]);

		// remove empty text nodes
		$domxml->trimDocument();
		
		// Identify Leaf-LOS (LOs not containing other LOs)			
		while (count($elements = $domxml->getElementsByTagname("LearningObject")) > 1)
		{
			// delete first element since this is always the root LearningObject
			array_shift($elements);
			
			foreach ($elements as $element)
			{
				if ($domxml->isLeafElement($element,"LearningObject",1))
				{
					$leaf_elements[] = $element;
					
					// copy whole LearningObject to $subtree
					$subtree = $element->clone_node(true);
					$parent = $element->parent_node();
					
					// remove the LearningObject from main file
					$element->unlink_node();
					
					// create a new domDocument containing the isolated LearningObject in $subtree
					$lo = new LearningObjectObject();
					$node  = $lo->domxml->appendChild($subtree);
				
					// get LO informationen (title & description)
					$obj_data = $lo->getInfo();
		
					// get unique obj_id of LO
					$lo_id = createNewObject("lo",$obj_data);
					
					// prepare LO for database insertion
					$lotree = $lo->domxml->buildTree();
					
					// create a reference in main file with global obj_id of inserted LO
					$domxml->appendReferenceNodeForLO ($parent,$lo_id);
					
					// insert LO into lo_database
					$xml2sql = new xml2sql($lotree,$lo_id);
					$xml2sql->insertDocument();

					//fetch internal element id, parent_id and save them to reconstruct tree later on
					$mapping[] = array ($lo_id => $lo->getReferences());
				}
			}
		} // END: while. Continue until only the root LO is left in main file
		
		// write root LO to file (TESTING)
		//$xmldoc->domxml->dump_file("c:/htdocs/ilias3/xml/file".$n.".xml");
		
		// insert the remaining root-LO into DB
		$lo = new LearningObjectObject($domxml->doc);
		$obj_data = $lo->getInfo();
		$lo_id = createNewObject("lo",$obj_data);
		$lotree = $lo->domxml->buildTree();
		$xml2sql = new xml2sql($lotree,$lo_id);
		$xml2sql->insertDocument();

		// copying file to server if document is valid (soon...)
		//move_uploaded_file($a_source,$path()."/".$a_obj_id."_".$a_name);
		$last[$lo_id] = $lo->getReferences();
		array_push($mapping,$last);
		
		// MOVE TO xml2sql class
		$this->insertStructureIntoTree(array_reverse($mapping));
		
		// for output
		return $data;
	}
	
	// information saved in $mapping how the LOs are connected in the this Module is
	// written to tree
	function insertStructureIntoTree($a_nodes)
	{
		// init tree
		$lm_tree = new Tree($this->id,$this->id,$this->id,$this->id);
		
		//prepare array and kick all nodes with no children
		foreach ($a_nodes as $key => $nodes)
		{
			if (!is_array($nodes[key($nodes)]))
			{
				array_splice($a_nodes,$key);
				break;
			}
		}

		// insert first_node
		$parent_id = $this->id;
		$lm_tree->insertNode(key($a_nodes[0]),$parent_id,0);
		
		// traverse array to build tree structure by inserting nodes to db-table tree
		foreach ($a_nodes as $key => $nodes)
		{
			$parent_parent_id = $parent_id;
			$parent_id = key($nodes);

			foreach (array_reverse($nodes[$parent_id]) as $child_id)
			{
				$lm_tree->insertNode($child_id,$parent_id,$parent_parent_id);
			}
		}
	}
} // END class.LearningModuleObject
?>