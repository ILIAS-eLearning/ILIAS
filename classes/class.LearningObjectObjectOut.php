<?php
/**
* Class LearningObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* @author Sascha Hofmann <shofmann@databay.de>
* $Id$
* 
* @extends ObjectOut
* @package ilias-core
*/
class LearningObjectOut extends ObjectOut
{
	/**
	* Constructor
	*
	* @access public
	*/
	function LearningObject($a_data)
	{
		$this->ObjectOut($a_data);
	}

	/**
	* display tree structure of a LearningObject
	* DEBUG function
	* 
	* @access	public
	*/
	function displayStructure ($a_tree)
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

		foreach ($a_tree as $id => $node)
		{	
			echo "<tr>";
			echo "<td>".$id."</td>";
		
			foreach ($node as $key => $value)
			{
				echo "<td>".$value."</td>";
			}
			echo "</tr>";
		}
		echo "</table>";	
	}
} // END class.LeraningObject
?>