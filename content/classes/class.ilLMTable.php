<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("content/classes/class.ilPageContent.php");

/**
* Class ilLMTable
*
* Table content object (see ILIAS DTD)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMTable extends ilPageContent
{
	var $dom;
	var $tab_node;


	/**
	* Constructor
	* @access	public
	*/
	function ilLMTable(&$a_dom)
	{
		parent::ilPageContent();
		$this->setType("tab");

		$this->dom =& $a_dom;
	}

	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->tab_node =& $a_node->first_child();		// this is the Table node
	}

	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->node =& $this->dom->create_element("PageContent");
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->tab_node =& $this->dom->create_element("Table");
		$this->tab_node =& $this->node->append_child($this->tab_node);
		$this->tab_node->set_attribute("Language", "");
	}

	function addRows($a_nr_rows, $a_nr_cols)
	{
		for ($i=1; $i<=$a_nr_rows; $i++)
		{
			$new_tr =& $this->dom->create_element("TableRow");
			$new_tr =& $this->tab_node->append_child($new_tr);
			for ($j=1; $j<=$a_nr_cols; $j++)
			{
				$new_td =& $this->dom->create_element("TableData");
				$new_td =& $new_tr->append_child($new_td);
			}
		}
	}

	/**
	* get table language
	*/
	function getLanguage()
	{
		return $this->tab_node->get_attribute("Language");
	}

	/**
	* set table language
	*
	* @param	string		$a_lang		language code
	*/
	function setLanguage($a_lang)
	{
		if($a_lang != "")
		{
			$this->tab_node->set_attribute("Language", $a_lang);
		}
	}

	/**
	* get table width
	*/
	function getWidth()
	{
		return $this->tab_node->get_attribute("Width");
	}

	/**
	* set table width
	*
	* @param	string		$a_width		table width
	*/
	function setWidth($a_width)
	{
		if($a_width != "")
		{
			$this->tab_node->set_attribute("Width", $a_width);
		}
		else
		{
			$this->tab_node->remove_attribute("Width");
		}
	}

	/**
	* get table border width
	*/
	function getBorder()
	{
		return $this->tab_node->get_attribute("Border");
	}

	/**
	* set table border
	*
	* @param	string		$a_border		table border
	*/
	function setBorder($a_border)
	{
		if($a_border != "")
		{
			$this->tab_node->set_attribute("Border", $a_border);
		}
		else
		{
			$this->tab_node->remove_attribute("Border");
		}
	}

	/**
	* get table cell spacing
	*/
	function getCellSpacing()
	{
		return $this->tab_node->get_attribute("CellSpacing");
	}

	/**
	* set table cell spacing
	*
	* @param	string		$a_spacing		table cell spacing
	*/
	function setCellSpacing($a_spacing)
	{
		if($a_spacing != "")
		{
			$this->tab_node->set_attribute("CellSpacing", $a_spacing);
		}
		else
		{
			$this->tab_node->remove_attribute("CellSpacing");
		}
	}

	/**
	* get table cell padding
	*/
	function getCellPadding()
	{
		return $this->tab_node->get_attribute("CellPadding");
	}

	/**
	* set table cell padding
	*
	* @param	string		$a_padding		table cell padding
	*/
	function setCellPadding($a_padding)
	{
		if($a_padding != "")
		{
			$this->tab_node->set_attribute("CellPadding", $a_padding);
		}
		else
		{
			$this->tab_node->remove_attribute("CellPadding");
		}
	}

	/**
	* set width of table data cell
	*/
	function setTDWidth($a_hier_id, $a_width)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//TableData[@HierId = '".$a_hier_id."']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			if($a_width != "")
			{
				$res->nodeset[0]->set_attribute("Width", $a_width);
			}
			else
			{
				$res->nodeset[0]->remove_attribute("Width");
			}
		}
	}

	/**
	* set class of table data cell
	*/
	function setTDClass($a_hier_id, $a_class)
	{
		$xpc = xpath_new_context($this->dom);
		$path = "//TableData[@HierId = '".$a_hier_id."']";
		$res =& xpath_eval($xpc, $path);
		if (count($res->nodeset) == 1)
		{
			if($a_class != "")
			{
				$res->nodeset[0]->set_attribute("Class", $a_class);
			}
			else
			{
				$res->nodeset[0]->remove_attribute("Class");
			}
		}
	}

	/**
	* get caption
	*/
	function getCaption()
	{
		$hier_id = $this->getHierId();
		if(!empty($hier_id))
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//Table[@HierId = '".$hier_id."']/Caption";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				return $res->nodeset[0]->get_content();
			}
		}
	}

	/**
	* get caption alignment (Top | Bottom)
	*/
	function getCaptionAlign()
	{
		$hier_id = $this->getHierId();
		if(!empty($hier_id))
		{
			$xpc = xpath_new_context($this->dom);
			$path = "//Table[@HierId = '".$hier_id."']/Caption";
			$res =& xpath_eval($xpc, $path);
			if (count($res->nodeset) == 1)
			{
				return $res->nodeset[0]->get_attribute("Align");
			}
		}
	}

	function setCaption($a_content, $a_align)
	{
		if ($a_content != "")
		{
			$this->setFirstOptionalElement("Caption",
				array("Summary", "TableRow"), $a_content,
				array("Align" => $a_align));
		}
		else
		{
			$this->deleteAllChildsByName(array("Caption"));
		}
	}

	function deleteAllChildsByName($a_node_names)
	{
		$childs = $this->tab_node->child_nodes();
		foreach($childs as $child)
		{
			$child_name = $child->node_name();
			if (in_array($child_name, $a_node_names))
			{
				$child->unlink_node();
			}
		}
	}

	function setFirstOptionalElement($a_node_name, $a_predecessors, $a_content, $a_attributes)
	{
		$search = $a_predecessors;
		$search[] = $a_node_name;

		$childs = $this->tab_node->child_nodes();
		$cnt_childs = count($childs);
		$found = false;
		foreach($childs as $child)
		{
			$child_name = $child->node_name();
			if (in_array($child_name, $search))
			{
				$found = true;
				break;
			}
		}
		// didn't found element
		if(!$found)
		{
			$new_node =& $this->dom->create_element($a_node_name);
			if($cnt_childs == 0)
			{
				$new_node =& $this->tab_node->append_child($new_node);
			}
			else
			{
				$new_node =& $childs[0]->insert_before($new_node, $childs[0]);
			}
			$new_node->set_content($a_content);
			$this->set_attributes($new_node, $a_attributes);
		}
		else
		{
//echo "Hier:$child_name:$a_node_name:<br>";
			if ($child_name == $a_node_name)
			{
				$childs2 = $child->child_nodes();
				for($i=0; $i<count($childs2); $i++)
				{
					$child->remove_child($childs2[$i]);
				}
				$child->set_content($a_content);
				$this->set_attributes($child, $a_attributes);
			}
			else
			{
				$new_node =& $this->dom->create_element($a_node_name);
				$new_node =& $child->insert_before($new_node, $child);
				$new_node->set_content($a_content);
				$this->set_attributes($new_node, $a_attributes);
			}
		}
	}

	function set_attributes(&$a_node, $a_attributes)
	{
		foreach ($a_attributes as $attribute => $value)
		{
			$a_node->set_attribute($attribute, $value);
		}
	}


}
?>
