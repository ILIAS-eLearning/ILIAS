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
	var $row;			// current row and col position
	var $col;
	var $rowcnt;		// counter for total row and col number
	var $colcnt;
	var $cell;			// content array

	/**
	* Constructor
	* @access	public
	*/
	function ilLMTable()
	{
		parent::ilPageContent();
		$this->setType("tab");

		$this->row = 0;
		$this->col = 0;
		$this->rowcnt = 0;
		$this->colcnt = 0;
		$this->cell = array();
	}

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
	}

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
				$xml.= "<TableData>";					// todo: attributes

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
	}

	/**
	* get content object by hierarchical id
	*/
	function &getContent($a_cont_cnt)
	{
		$cnt = explode("_", $a_cont_cnt);
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
	}

	function insertContent(&$a_cont_obj, $a_pos)
	{
		$pos = explode("_", $a_pos);
		if(isset($pos[1]))		// content should be child of a container
		{
			$pos_0 = $pos[0];
			unset($pos[0]);
			$this->content[$pos_0 - 1]->insertContent($a_cont_obj, implode($pos, "_"));
		}
		else		// content should be child of table
		{
			$tpos = $this->seq2TablePos($pos[0] - 1);
echo "seq:".$pos[0]."<br>";
echo "row:".$tpos["row"].":col:".$tpos["col"].":pos:".$tpos["pos"].":<br>";
			for($i = count($this->cell[$tpos["row"]][$tpos["col"]]); $i >= 0; $i--)
			{
echo "2";
				if($i >= ($tpos["pos"] + 1))
				{
echo "3";
					$this->cell[$tpos["row"]][$tpos["col"]][$i] =& $this->cell[$tpos["row"]][$tpos["col"]][$i - 1];
				}
			}
			$this->content[$tpos["pos"]] =& $a_cont_obj;

		}
	}


	/**
	* converts a sequential content position into row, column
	* and content position within the cell
	*/
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
					$co_object =& $this->cell[$this->row][$this->col][$j];
					$current++;
					if($current == $a_seq_pos)
					{
						return array("row" => $this->row, "col" => $this->col, "pos" => $j);
					}
				}
			}
		}
	}

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
