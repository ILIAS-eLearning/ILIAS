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

	function importObject()
	{
		// nothing to do. just display the dialogue in Out
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
		$domxml->loadDocument(basename($source),dirname($source),$_POST["parse_mode"]);

		// remove empty text nodes
		$domxml->trimDocument();

		// step 1: Identify Leaf-LOS (LOs not containing other LOs)			
		while (count($elements = $domxml->getElementsByTagname("LearningObject")) > 1)
		{
			// delete first element since its always the root LearningObject
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
					$lo = new LearningObject();
					$node  = $lo->domxml->appendChild($subtree);
				
					// get LO informationen (title & description)
					$obj_data = $lo->getInfo();
		
					// get unique obj_id of LO
					$lo_id = createNewObject("lo",$obj_data);
					
					// prepare LO for database insertion
					$lo->domxml->trimDocument(); // maybe obsolete?
					$lotree = $lo->domxml->buildTree();
					
					// insert LO into lo_database
					$xml2sql = new xml2sql($lotree,$lo_id);
					$xml2sql->insertDocument();
					
					// create a reference in main file with global obj_id of inserted LO
					$domxml->appendReferenceNodeForLO ($parent,$lo_id);
				}
			}
		} // END: while. Continue until only the root LO is left in main file
		
		// write root LO to file (TESTING)
		//$xmldoc->domxml->dump_file("c:/htdocs/ilias3/xml/file".$n.".xml");
		
		// insert the remaining root-LO into DB
		$lo = new LearningObject($domxml->doc);
		$obj_data = $lo->getInfo();
		$lo_id = createNewObject("lo",$obj_data);
		$lotree = $lo->domxml->buildTree();
		$xml2sql = new xml2sql($lotree,$lo_id);
		$xml2sql->insertDocument();

		// copying file to server if document is valid (soon...)
		//move_uploaded_file($a_source,$path()."/".$a_obj_id."_".$a_name);
	}
} // END class.LearningModuleObject
?>