<?php
/**
* Class ilLearningObjectGUI
* 
* @author	Stefan Meyer <smeyer@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*
* @extends	ilObjectGUI
* @package	ilias-core
*/
class ilLearningObjectGUI extends ilObjectGUI
{
	/**
	* Constructor
	* 
	* @access public 
	*/
	function ilLearningObjectGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "lo";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	} 

	function viewObject()
	{
		global $rbacsystem, $tree, $tpl;
		
		if (empty($_GET["lo_id"]))
		{
			$_GET["lo_id"] = $_GET["obj_id"];
			$_GET["lo_parent"] = $_GET["parent"];
		}

		if (empty($_GET["lo_parent"]))
		{
			$_GET["lo_parent"] = $_GET["lm_id"];
		}
		
		// TODO get rid of these $_GET variables
		$lotree = new Tree($_GET["lm_id"],$_GET["lm_id"]);
		//prepare objectlist
		$this->data = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();

		$this->data["cols"] = array("", "view", "title", "description", "last_change");
		
		$lo_childs = $lotree->getChilds($_GET["lo_id"], $a_order, $a_direction);

		foreach ($lo_childs as $key => $val)
	    {
			// visible
			//if (!$rbacsystem->checkAccess("visible",$val["id"]))
			//{
			//	continue;
			//}
	
			//visible data part
			$this->data["data"][] = array(
				"type" => "<img src=\"".$tpl->tplPath."/images/enlarge.gif\" border=\"0\">",
				"title" => $val["title"],
				"description" => $val["desc"],
				"last_change" => $val["last_update"]
			);

			//control information
			$this->data["ctrl"][] = array(
				"type" => $val["type"],
				"obj_id" => $_GET["obj_id"],
				"parent" => $_GET["parent"],
				"parent_parent" => $val["parent_parent"],
				"lm_id" => $_GET["lm_id"],
				"lo_id" => $val["child"],
				"lo_parent" => $val["parent"]
			);
	    } //foreach

		$this->setLOLocator($lotree, $_GET["lo_id"], $_GET["lo_parent"]); 
		
		parent::displayList();
	}
	/**
	* DESC MISSING
	* 
	* 
	*
	function viewObject()
	{
		global $lotree;
		
		parent::viewObject();
		
		//$lotree = new Tree($_GET["lo_id"],$_GET["lo_parent"],$_GET["lm_id"],$_GET["lm_id"]);
		//$this->tree->tree_id = $this->id; //_GET["lm_id"];
		if (empty($_GET["lo_parent"]))
		{
			$_GET["lo_parent"] = $_GET["lm_id"];
		}

		$this->setLOLocator($lotree, $_GET["lo_id"], $_GET["lo_parent"]); 
	}*/

	/**
	* DESC MISSING
	* 
	*/
	function setLOLocator($a_tree = "", $a_obj_id = "", $a_parent = "", $a_parent_parent = "")
	{
		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}
		
		if (!($a_obj_id))
		{
			$a_obj_id = $_GET["obj_id"]; 
		}
		
		if (!($a_parent))
		{
			$a_parent = $_GET["parent"]; 
		}

		if (!($a_parent_parent))
		{
			$a_parent_parent = $_GET["parent_parent"]; 
		}

		global $lng;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		if ($a_parent_parent)
		{
			$path = $a_tree->getPathFull($a_parent);
		}
		else
		{
			$path = $a_tree->getPathFull($a_obj_id);
		}

		//check if object isn't in tree, this is the case if parent_parent is set
		if ($a_parent_parent)
		{
			$subObj = getObject($a_obj_id);

			$path[] = array(
				"id"	 => $a_obj_id,
				"title"  => $this->lng->txt($subObj["title"]),
				"parent" => $a_parent,
				"parent_parent" => $a_parent_parent
				);
		}

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-1)
			{
				$this->tpl->touchBlock("locator_separator");
			}
			
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $row["title"]);
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?".$type_lo."obj_id=".$_GET["obj_id"].
							  "&lm_id=".$_GET["lm_id"]."&lo_id=".$row["child"]."&lo_parent=".$row["parent"]);
			$this->tpl->parseCurrentBlock();

			if ($row["child"] == $_GET["lm_id"])
			{
				$type_lo = "type=lo&";
			}
		}

		$this->tpl->setCurrentBlock("locator");

		$this->tpl->setVariable("TXT_PATH","LO-Path: ");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display tree structure of a LearningObject
	* DEBUG function
	* 
	* @access public 
	*/
	function displayStructure ($a_tree)
	{
		echo "<table border=\"1\">" . "<tr>" . "<th>id</th>" . "<th>value</th>" . "<th>name</th>" . "<th>type</th>" . "<th>depth</th>" . "<th>parent</th>" . "<th>first</th>" . "<th>prev</th>" . "<th>next</th>" . "<th>left</th>" . "<th>right</th>" . "<th>db_id</th>" . "</tr>";

		foreach ($a_tree as $id => $node)
		{
			echo "<tr>";
			echo "<td>" . $id . "</td>";

			foreach ($node as $key => $value)
			{
				echo "<td>" . $value . "</td>";
			} 
			echo "</tr>";
		} 
		echo "</table>";
	}
}
?>
