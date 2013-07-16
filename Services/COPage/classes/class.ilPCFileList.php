<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPCFileList
*
* File List content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
*/
class ilPCFileList extends ilPageContent
{
	var $list_node;

	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("flst");
	}

	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->list_node =& $a_node->first_child();		// this is the Table node
	}

	function create(&$a_pg_obj, $a_hier_id, $a_pc_id = "")
	{
		$this->node = $this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER, $a_pc_id);
		$this->list_node =& $this->dom->create_element("FileList");
		$this->list_node =& $this->node->append_child($this->list_node);
	}

	/*
	function addItems($a_nr)
	{
		for ($i=1; $i<=$a_nr; $i++)
		{
			$new_item =& $this->dom->create_element("ListItem");
			$new_item =& $this->list_node->append_child($new_item);
		}
	}*/

	function appendItem($a_id, $a_location, $a_format)
	{
		// File Item
		$new_item =& $this->dom->create_element("FileItem");
		$new_item =& $this->list_node->append_child($new_item);

		// Identifier
		$id_node =& $this->dom->create_element("Identifier");
		$id_node =& $new_item->append_child($id_node);
		$id_node->set_attribute("Catalog", "ILIAS");
		$id_node->set_attribute("Entry", "il__file_".$a_id);

		// Location
		$loc_node =& $this->dom->create_element("Location");
		$loc_node =& $new_item->append_child($loc_node);
		$loc_node->set_attribute("Type", "LocalFile");
		$loc_node->set_content($a_location);

		// Format
		$form_node =& $this->dom->create_element("Format");
		$form_node =& $new_item->append_child($form_node);
		$form_node->set_content($a_format);
	}

	function setListTitle($a_title, $a_language)
	{
		ilDOMUtil::setFirstOptionalElement($this->dom,
			$this->list_node, "Title", array("FileItem"),
			$a_title, array("Language" => $a_language));
	}

	function getListTitle()
	{
		$chlds =& $this->list_node->child_nodes();
		for($i=0; $i<count($chlds); $i++)
		{
			if ($chlds[$i]->node_name() == "Title")
			{
				return $chlds[$i]->get_content();
			}
		}
		return "";
	}

	function getLanguage()
	{
		$chlds =& $this->list_node->child_nodes();
		for($i=0; $i<count($chlds); $i++)
		{
			if ($chlds[$i]->node_name() == "Title")
			{
				return $chlds[$i]->get_attribute("Language");
			}
		}
		return "";
	}
	
	/**
	* Get list of files
	*/
	function getFileList()
	{
		$files = array();
		
		// File Item
		$childs = $this->list_node->child_nodes();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "FileItem")
			{
				$id = $entry = "";
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				$class = $childs[$i]->get_attribute("Class");
				
				// Identifier
				$id_node = $childs[$i]->first_child();
				if ($id_node->node_name() == "Identifier")
				{
					$entry = $id_node->get_attribute("Entry");
					if (substr($entry, 0, 9) == "il__file_")
					{
						$id = substr($entry, 9);
					}
				}
				$files[] = array("entry" => $entry, "id" => $id,
					"pc_id" => $pc_id, "hier_id" => $hier_id,
					"class" => $class);
			}
		}
		
		return $files;
	}

	/**
	* Delete file items
	*/
	function deleteFileItems($a_ids)
	{
		$files = array();
		
		// File Item
		$childs = $this->list_node->child_nodes();

		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "FileItem")
			{
				$id = $entry = "";
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				
				if (in_array($hier_id.":".$pc_id, $a_ids))
				{
					$childs[$i]->unlink($childs[$i]);
				}
			}
		}
	}

	/**
	* Save positions of file items
	*/
	function savePositions($a_pos)
	{
		asort($a_pos);
		
		// File Item
		$childs = $this->list_node->child_nodes();
		$nodes = array();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "FileItem")
			{
				$id = $entry = "";
				$pc_id = $childs[$i]->get_attribute("PCID");
				$hier_id = $childs[$i]->get_attribute("HierId");
				$nodes[$hier_id.":".$pc_id] = $childs[$i];
				$childs[$i]->unlink($childs[$i]);
			}
		}
		
		foreach($a_pos as $k => $v)
		{
			if (is_object($nodes[$k]))
			{
				$nodes[$k] = $this->list_node->append_child($nodes[$k]);
			}
		}
	}

	/**
	* Get all style classes
	*/
	function getAllClasses()
	{
		$classes = array();
		
		// File Item
		$childs = $this->list_node->child_nodes();

		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "FileItem")
			{
				$classes[$childs[$i]->get_attribute("HierId").":".
					$childs[$i]->get_attribute("PCID")] = $childs[$i]->get_attribute("Class");
			}
		}
		
		return $classes;
	}

	/**
	* Save style classes of file items
	*/
	function saveStyleClasses($a_class)
	{
		// File Item
		$childs = $this->list_node->child_nodes();
		for ($i=0; $i<count($childs); $i++)
		{
			if ($childs[$i]->node_name() == "FileItem")
			{
				$childs[$i]->set_attribute("Class",
					$a_class[$childs[$i]->get_attribute("HierId").":".
					$childs[$i]->get_attribute("PCID")]);
			}
		}
	}

	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("ed_edit_files", "ed_insert_filelist", "pc_flist");
	}

}
?>
