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

	function uploadObject()
	{
		global $HTTP_POST_FILES;
		
		require_once "classes/class.xml2sql.php";
		
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
		$file = $HTTP_POST_FILES["xmldoc"]["name"];
		$xmldoc = new xml2sql(basename($source),dirname($source),1,$_POST["parse_mode"]);

		// copying file to server if document is valid (soon...)
		//move_uploaded_file($a_source,$path()."/".$a_obj_id."_".$a_name);
		
		//$xmldoc->insertDocument();
		
		$this->displayStructure($xmldoc->xmltree);
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