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
	function LearningModuleObject($a_id)
	{
		$this->Object($a_id);
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

		switch(key($_POST["cmd"]))
		{
			case "import":
				return $this->importObject();
				break;

			case "export":
				// NO FUNCTION IN THE MOMENT
				return;
				break;

			case "upload":
				return $this->uploadObject($_POST["parse_mode"]);
				break;
		}
		parent::gatewayObject();
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
	function saveObject($a_obj_id, $a_parent,$a_type, $a_new_type, $a_data)
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
	function uploadObject($a_parse_mode)
	{
		global $HTTP_POST_FILES;
		
		require_once "classes/class.xml2sql.php";
		require_once "classes/class.domxml.php";
		
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
		$root = $domxml->loadDocument(basename($source),dirname($source),$a_parse_mode);

		// remove empty text nodes
		$domxml->trimDocument();
		
		$n = 0;
		
		// Identify Leaf-LOS (LOs not containing other LOs)			
		while (count($elements = $domxml->getElementsByTagname("LearningObject")) > 1)
		{
			// delete first element since this is always the root LearningObject
			array_shift($elements);
			
			foreach ($elements as $element)
			{
				if ($domxml->isLeafElement($element,"LearningObject",1))
				{
					$n++;

					$leaf_elements[] = $element;
					
					// copy whole LearningObject to $subtree
					$subtree = $element->clone_node(true);
					
					$prev_sibling = $element->previous_sibling();
					$parent = $element->parent_node();
					
					// remove the LearningObject from main file
					$element->unlink_node();

					// create a new domDocument containing the isolated LearningObject in $subtree
					$lo = new domxml();
					$node  = $lo->appendChild($subtree);
				
					// get LO informationen (title & description)
					$obj_data = $lo->getInfo();
		
					// get unique obj_id of LO
					$lo_id = createNewObject("lo",$obj_data["title"],$obj_data["desc"]);
					
					// prepare LO for database insertion
					$lotree = $lo->buildTree();
					
					// create a reference in main file with global obj_id of inserted LO
					// DIRTY: save lm_id too for xsl linking
					$domxml->appendReferenceNodeForLO ($parent,$lo_id,$this->id,$prev_sibling);
					
					// write to file
//					$lo->domxml->doc->dump_file("c:/htdocs/ilias3/test2/".$lo_id.".xml");
					//echo "<b>LearningObject ".$n."</b><br/>";
					//echo "<pre>".htmlentities($lo->domxml->dumpDocument())."</pre>";
					
					// insert LO into lo_database
					$xml2sql = new xml2sql($lotree,$lo_id);
					$xml2sql->insertDocument();

					//fetch internal element id, parent_id and save them to reconstruct tree later on
					$mapping[] = array ($lo_id => $lo->getReferences());
				}
			}
		} // END: while. Continue until only the root LO is left in main file
		
		$n++;
		
		// write root LO to file (TESTING)
//		$domxml->doc->dump_file("c:/htdocs/ilias3/test2/root.xml");
		//echo "<b>LearningObject ".$n."</b><br/>";
		//echo "<pre>".htmlentities($domxml->dumpDocument())."</pre>";
		
		// insert the remaining root-LO into DB
		$lo = new domxml($domxml->doc);
		$obj_data = $lo->getInfo();
		$lo_id = createNewObject("lo",$obj_data["title"],$obj_data["desc"]);
		$lotree = $lo->buildTree();
		$xml2sql = new xml2sql($lotree,$lo_id);
		$xml2sql->insertDocument();

		// copying file to server if document is valid (soon...)
		//move_uploaded_file($a_source,$path()."/".$a_obj_id."_".$a_name);
		$last[$lo_id] = $lo->getReferences();
		array_push($mapping,$last);
		
		// MOVE TO xml2sql class
		$xml2sql->insertStructureIntoTree(array_reverse($mapping),$this->id);

		// for output
		return $data;
	}
} // END class.LearningModuleObject
?>
