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

	function getXML($a_utf8_encoded, $a_short_mode)
	{
		$xml = "<Table>";			// todo: attributes
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
						$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode);
					}
					if (get_class($co_object) == "illmtable")
					{
						$xml .= $co_object->getXML($a_utf8_encoded, $a_short_mode);
					}
				}
				$xml.= "</TableData>";
			}
			$xml.= "</TableRow>";
		}
		$xml.= "</Table>";

		return $xml;
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
