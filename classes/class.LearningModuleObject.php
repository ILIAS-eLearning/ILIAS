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
	* @access public
	*/
	function LearningModuleObject()
	{
		$this->Object();
	}

	function importObject()
	{
		//echo "import";
	}
	
	/**
	* additional to normal saveObject method,
	* a tree entry for each LearnungModule is created
	*/
	function saveObject($a_obj_id = '', $a_parent = '' ,$a_type = '' , $a_new_type = '' , $a_data = '')
	{
		global $tree;
		
		$obj_id = parent::saveObject($a_obj_id, $a_parent, $a_type, $a_new_type, $a_data);
		
		$tree->addTree($obj_id);
	}

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
		
		//parse and/or validate document
		//$xmldoc = new xml2sql();
		$domxml = new domxml();
				
		$file = $HTTP_POST_FILES["xmldoc"]["name"];
		//$xmldoc->loadDocument(basename($source),dirname($source),1,$_POST["parse_mode"]);
		$domxml->loadDocument(basename($source),dirname($source),$_POST["parse_mode"]);

		//$xmldoc->prepareXMLTree($xmldoc->domxml);
		$domxml->trimDocument();
		
		//$lo_elements = $xmldoc->domxml->get_elements_by_tagname("LearningObject");
		//$elements = $domxml->getElementsByTagname("LearningObject");

##################### separating Learning Objects in one XML-File ####################
		// step 1: Identify Leaf-LOS (LOs not containing other LOs)
		// step 2: save Element-Id somewhere
		// foreach Leaf-LO:		
		// step 3: create new domxml2
		// step 4: copy dom-tree of Leaf-LO to new domxml
		// step 5: remove dom-tree from main domxml
		// step 6: prepare domxml2
		// step 7: insert domxml2 into db
		// step 8: inser new node 'LearningObject' in main domxml with sql_id from removed leaf-LO as attribute
		// end foreach
		// start over with step 1 until all LOs are inserted into db
		// step 9: insert LM-structure in tree!
		
		
		
		// step 1:
		//$root = $xmldoc->document_element();

		
		// erstes element kann rausgelöscht werden, da immer ein LO alles umschliesst.

		
		
		// step 9: Ich muss alle LOs wieder nach dem tag <LO> durchforsten, um rekursiv den Baum
		// zu erzeugen.
		// Brauche ich den Baum überhaupt?
		
		// step 1: Identify Leaf-LOS (LOs not containing other LOs)			
		while (count($elements = $domxml->getElementsByTagname("LearningObject")) > 1)
		{
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
					
					// create a new DOM object containing the cutted LearningObject in $subtree
					$lo = new LearningObject();
					$node  = $lo->domxml->doc->add_child($subtree);
				
					// write in file (TESTING)
					//$lo->dump_file("c:/htdocs/ilias3/xml/file_".$n.".xml");
					
					// get LO informationen (title & description)
					// a) object_data entry
					$obj_data = $lo->getInfo();
		
					// insert LO into database
					// b) get mysql_last_insert_id
					$lo_id = createNewObject("lo",$obj_data);
					
					// c) insert xml_data into lo_db
					//$xmltmp = new xml2sql();
					//$lo->domxml->doc = $lo;
					$lo->domxml->trimDocument(); 
					$lotree = $lo->domxml->buildTree();
					
					// create new xml2sql object for insertion functions
					$xml2sql = new xml2sql($lotree,$lo_id);
					$xml2sql->insertDocument();
					
					// d) create a reference in main file with global obj_id of inserted LO
					$domxml->appendReferenceNodeForLO ($parent,$lo_id);
					
					// e) continue until only the root LO is left in main file
				}
			}
		}
		
		// write root LO to file (TESTING)
		//$xmldoc->domxml->dump_file("c:/htdocs/ilias3/xml/file".$n.".xml");
		$lo = new LearningObject();
		$node = $lo->domxml->doc->add_child($domxml->doc->document_element());
		$obj_data = $lo->getInfo();
		$lo_id = createNewObject("lo",$obj_data);
		$lo->domxml->doc = $lo;
		$lo->domxml->trimDocument(); 
		$lotree = $lo->domxml->buildTree();
		$xml2sql = new xml2sql($lotree,$lo_id);
		$xml2sql->insertDocument();

		//$this->displayStructure($xmldoc->xmltree);

		// copying file to server if document is valid (soon...)
		//move_uploaded_file($a_source,$path()."/".$a_obj_id."_".$a_name);
					
		exit;
	}
	
	function displayStructure ($tree)
	{
		echo "<table border=\"1\">".
			 "<tr>".
			 "<th>id</th>".
			 "<th>value</th>".
			 "<th>name</th>".
			 "<th>type</th>".
			 "<th>depth</th>".
			 "<th>parent</th>".
			 "<th>first</th>".
			 "<th>prev</th>".
			 "<th>next</th>".
			 "<th>left</th>".
			 "<th>right</th>".
			 "<th>db_id</th>".
			 "</tr>";

		foreach ($tree as $id => $node)
		{	
			echo "<tr>";
			echo "<td>".$id."</td>";
		
			foreach ($node as $key => $value)
			{
				//if ($key != "content")
				//{
					echo "<td>".$value."</td>";
				//}
			}
			echo "</tr>";
		}
		echo "</table>";	
	}
} // END class.LearningModuleObject
?>