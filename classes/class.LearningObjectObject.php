<?php
/**
* Class LearningObject
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/

class LearningObjectObject extends Object
{
	/**
	* domxml object
	* 
	* @var		object	domxml object
	* @access	public 
	*/
	var $domxml;	
	
	/**
	* Constructor
	* @access public
	*/
	function LearningObjectObject($a_domdocument = "")
	{
		require_once "classes/class.domxml.php";
		$this->Object();
		$this->domxml = new domxml($a_domdocument);
	}

	function viewObject()
	{
		global $rbacsystem, $tree, $lotree, $tpl;
		
		if (empty($_GET["lo_id"]))
		{
			$_GET["lo_id"] = $_GET["obj_id"];
			$_GET["lo_parent"] = $_GET["parent"];
		}
		
		$lotree = new Tree($_GET["lo_id"],$_GET["lo_parent"],$_GET["lm_id"],$_GET["lm_id"]);
		//prepare objectlist
		$this->objectList = array();
		$this->objectList["data"] = array();
		$this->objectList["ctrl"] = array();

		$this->objectList["cols"] = array("", "view", "title", "description", "last_change");
		
		if ($lotree->getChilds($_GET["lo_id"], $_GET["order"], $_GET["direction"]))
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
					"lm_id" => $_GET["lm_id"],
					"lo_id" => $val["id"],
					"lo_parent" => $val["parent"]
				);
				
		    } //foreach
		} //if 
//var_dump($this->objectList);
		return $this->objectList;
	}

	/**
	* fetch Title & Description from MetaData-Section of domDocument
	* 
	* @return	array	Titel & Description
	* @access	public
	*/ 
	function getInfo ()
	{
		$node = $this->domxml->getElementsByTagname("MetaData");
		$childs = $node[0]->child_nodes();
		
		foreach ($childs as $child)
		{
				if (($child->node_type() == XML_ELEMENT_NODE) && ($child->tagname == "General"))
				{
					$childs2 = $child->child_nodes();

					foreach ($childs2 as $child2)
					{
						if (($child2->node_type() == XML_ELEMENT_NODE) && ($child2->tagname == "Title" || $child2->tagname == "Description"))
						{
							$arr[$child2->tagname] = $child2->get_content();
						}
					}
					
					// General-tag was found. Stop foreach-loop
					break;
				}
		}
		
		// for compatibility reasons:
		$arr["title"] = $arr["Title"];
		$arr["desc"] = $arr["Description"];
		
		return $arr;
	}

	/**
	* get all LO references in Learning Object
	* 
	* @return	array	object ids of LearningObjects
	* @access	public
	*/ 
	function getReferences()
	{
		if ($nodes = $this->domxml->getElementsByTagname("LO"))
		{
			foreach ($nodes as $node)
			{
				$attr[] = $node->get_attribute("id");			
			}
		}

		return $attr;
	}
} // END class.LearningObject
?>