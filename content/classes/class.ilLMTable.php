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
define("IL_AFTER_PRED", 1);
define("IL_BEFORE_SUCC", 0);

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
	var $row;			// current row and col position
	var $col;
	var $rowcnt;		// counter for total row and col number
	var $colcnt;
	var $cell;			// content array
	var $dom;
	var $node;

	/**
	* Constructor
	* @access	public
	*/
	function ilLMTable(&$a_dom)
	{
		parent::ilPageContent();
		$this->setType("tab");

		$this->row = 0;
		$this->col = 0;
		$this->rowcnt = 0;
		$this->colcnt = 0;
		$this->cell = array();
		$this->dom =& $a_dom;
	}

	function setNode(&$a_node)
	{
		$this->node =& $a_node;
	}

	function &getNode()
	{
		return $this->node;
	}

	function createNode()
	{
		$this->node =& $this->dom->create_element("Table");
	}

	function addRows($a_nr_rows, $a_nr_cols)
	{
		for ($i=1; $i<=$a_nr_rows; $i++)
		{
			$new_tr =& $this->dom->create_element("TableRow");
			$new_tr =& $this->node->append_child($new_tr);
			for ($j=1; $j<=$a_nr_cols; $j++)
			{
				$new_td =& $this->dom->create_element("TableData");
				$new_td =& $new_tr->append_child($new_td);
			}
		}
	}

	/**
	* get table width
	*/
	function getWidth()
	{
		return $this->node->get_attribute("Width");
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
			$this->node->set_attribute("Width", $a_width);
		}
		else
		{
			$this->node->remove_attribute("Width");
		}
	}

	/**
	* get table border width
	*/
	function getBorder()
	{
		return $this->node->get_attribute("Border");
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
			$this->node->set_attribute("Border", $a_border);
		}
		else
		{
			$this->node->remove_attribute("Border");
		}
	}

	/**
	* get table cell spacing
	*/
	function getCellSpacing()
	{
		return $this->node->get_attribute("CellSpacing");
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
			$this->node->set_attribute("CellSpacing", $a_spacing);
		}
		else
		{
			$this->node->remove_attribute("CellSpacing");
		}
	}

	/**
	* get table cell padding
	*/
	function getCellPadding()
	{
		return $this->node->get_attribute("CellPadding");
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
			$this->node->set_attribute("CellPadding", $a_padding);
		}
		else
		{
			$this->node->remove_attribute("CellPadding");
		}
	}



	/*
	function newCol()
	{
		$this->col++;
		if ($this->col > $this->colcnt)
		{
			$this->colcnt = $this->col;
		}
		$this->cell[$this->row][$this->col] = array();
	}

	function newRow()
	{
		$this->row++;
		$this->col = 0;
		if ($this->row > $this->rowcnt)
		{
			$this->rowcnt = $this->row;
		}
		$this->cell[$this->row][$this->col] = array();
	}

	function setCol($a_col)
	{
		$this->col = $a_col;
	}

	function setRow($a_row)
	{
		$this->row = $a_row;
	}

	function appendContent(&$a_content_obj)
	{
		$this->cell[$this->row][$this->col][] =& $a_content_obj;
	}

	function &getCellContent($a_nr = 0)
	{
		if($a_nr == 0)
		{
			return $this->cell[$this->row][$this->col];
		}
		else
		{
			return $this->cell[$this->row][$this->col][$a_nr];
		}
	}*/

	/*
	function getXML($a_utf8_encoded = false, $a_short_mode = false, $a_incl_ed_ids = false)
	{
		$ed_id = ($a_incl_ed_ids)
			? "ed_id=\"".$this->getEdId()."\""
			: "";
		$xml = "<Table $ed_id >";			// todo: attributes
		$xml.= "<Title Language=\"de\">No Title</Title>";		// todo: make this work
		for ($r=1; $r<=$this->rowcnt; $r++)
		{
			$this->setRow($r);
			$xml.= "<TableRow>";						// todo: attributes
			for ($c=1; $c<=$this->colcnt; $c++)
			{
				$this->setCol($c);
				$td_ed_id = ($a_incl_ed_ids)
					? "ed_id=\"".$this->getEdId()."_r$r"."c$c\""
					: "";
				$xml.= "<TableData $td_ed_id>";					// todo: attributes

				reset($this->cell[$this->row][$this->col]);
				foreach($this->cell[$this->row][$this->col] as $co_object)
				{
					if (get_class($co_object) == "ilparagraph")
					{
						$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode, $a_incl_ed_ids);
					}
					if (get_class($co_object) == "illmtable")
					{
						$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode, $a_incl_ed_ids);
					}
				}
				$xml.= "</TableData>";
			}
			$xml.= "</TableRow>";
		}
		$xml.= "</Table>";

		return $xml;
	}*/

	/**
	* get content object by hierarchical id
	*/
	/*
	function &getContent($a_hier_id)
	{
		$cnt = explode("_", $a_hier_id);
		if(isset($cnt[1]))		// content is within a container (e.g. table)
		{
			$container_obj =& $this->getContent($cnt[0]);
			$unset[$cnt[0]];
			return $container_obj->getContent(implode($cnt, "_"));
		}
		else		// content object is direct child of this table
		{
			$tpos = $this->seq2TablePos($cnt[0]);
			$co_object =& $this->cell[$tpos["row"]][$tpos["col"]][$tpos["pos"]];
			return $co_object;
		}
	}*/

	/*
	function insertContent(&$a_cont_obj, $a_pos, $a_mode = IL_AFTER_PRED)
	{
		$pos = explode("_", $a_pos);
//echo "TI1";
		if(isset($pos[1]))		// content should be child of a container
		{
//echo "TI2";
			$tpos = $this->seq2TablePos($pos[0]);
			unset($pos[0]);
			$this->cell[$tpos["row"]][$tpos["col"]][$tpos["pos"]]->insertContent($a_cont_obj, implode($pos, "_"), $a_mode);
		}
		else		// insert as child element of the table
		{
//echo "TI3";
			// if $pos[0] has format "r...c..." then an insert at the top
			// of a table cell should be made
			$r = strpos($pos[0] ,"r");
			$c = strpos($pos[0] ,"c");
			if(is_int($r) && is_int($c))
			{
				$row = substr($pos[0], 1, $c - 1);
				$col = substr($pos[0], $c + 1, strlen($pos[0]) - $c - 1);

				// todo: das stimmt net!
				for($i = count($this->cell[$row][$col]); $i >= 1; $i--)
				{
					$this->cell[$row][$col][$i] =& $this->cell[$row][$col][$i - 1];
				}
				$this->cell[$row][$col][0] =& $a_cont_obj;
//echo "TIsetting:r$row:c$col:".htmlentities($a_cont_obj->getText()).":";
			}
			else		// if $pos[0] is number, insert object at sequential position $pos[0]
			{
				$tpos = $this->seq2TablePos($pos[0] - $a_mode);
//echo "seq:".$pos[0]."<br>";
//echo "mode:$a_mode:<br>";
//echo "r:".$tpos["row"].":c:".$tpos["col"].":p:".$tpos["pos"].":<br>";
				for($i = count($this->cell[$tpos["row"]][$tpos["col"]]); $i >= 0; $i--)
				{
					if($i >= ($tpos["pos"] + 1 + $a_mode))
					{
						$this->cell[$tpos["row"]][$tpos["col"]][$i] =& $this->cell[$tpos["row"]][$tpos["col"]][$i - 1];
					}
				}
				$this->cell[$tpos["row"]][$tpos["col"]][$tpos["pos"] + $a_mode] =& $a_cont_obj;
			}
		}
	}*/

	/**
	* delete content object at position $a_pos
	*/
	/*
	function deleteContent($a_pos)
	{
		$pos = explode("_", $a_pos);
		if(isset($pos[1]))		// object of child container should be deleted
		{
			$tpos = $this->seq2TablePos($pos[0]);
			unset($pos[0]);
			$this->cell[$tpos["row"]][$tpos["col"]][$tpos["pos"]]->deleteContent(implode($pos, "_"));
		}
		else		// direct child should be deleted
		{
			$tpos = $this->seq2TablePos($pos[0]);
			$cnt = 0;
			for($i=1; $i<count($this->cell[$tpos["row"]][$tpos["col"]]); $i++)
			{
				$cnt++;
				if ($i > $tpos["pos"])
				{
					$this->cell[$tpos["row"]][$tpos["col"]][$i - 1] =& $this->cell[$tpos["row"]][$tpos["col"]][$i];
				}
			}
			array_pop($this->cell[$tpos["row"]][$tpos["col"]]);
		}
	}*/


	/**
	* converts a sequential content position into row, column
	* and content position within the cell
	*/
	/*
	function seq2TablePos($a_seq_pos)
	{
		$current = 0;
		for ($r=1; $r<=$this->rowcnt; $r++)
		{
			$this->setRow($r);
			for ($c=1; $c<=$this->colcnt; $c++)
			{
				$this->setCol($c);

				reset($this->cell[$this->row][$this->col]);
				for($j=0; $j<count($this->cell[$this->row][$this->col]); $j++)
				{
//echo ":jr".$r."c".$c.":$j:";
					$co_object =& $this->cell[$this->row][$this->col][$j];
					$current++;
					if($current == $a_seq_pos)
					{
						return array("row" => $this->row, "col" => $this->col, "pos" => $j);
					}
				}
			}
		}
	}*/

	/*
	function setLanguage($a_lang)
	{
		$this->language = $a_lang;
	}

	function getLanguage()
	{
		return $this->language;
	}*/

}
?>
