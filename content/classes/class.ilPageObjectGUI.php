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


/**
* Class ilPageObjectGUI
*
* User Interface for Page Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageObjectGUI
{
	var $pg_obj;
	var $lm_obj;
	var $ilias;
	var $tpl;
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI()
	{
		global $ilias, $tpl, $lng;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
	}

	function setPageObject(&$a_pg_object)
	{
		$this->pg_obj =& $a_pg_object;
	}

	function setLMObject(&$a_lm_object)
	{
		$this->lm_obj =& $a_lm_object;
	}


	/*
	* display content of page
	*/
	function view()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.obj_view.html");
		$num = 0;

		$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
			$this->lm_obj->getId()."&obj_id=".$this->pg_obj->getId()."&cmd=post");

		//table header
		$this->tpl->setCurrentBlock("table_header_cell");
		$cols = array("", "type", "title", "content");
		foreach ($cols as $key)
		{
			if ($key != "")
			{
			    $out = $this->lng->txt($key);
			}
			else
			{
				$out = "&nbsp;";
			}
			$num++;

			$this->tpl->setVariable("HEADER_TEXT", $out);
			//$this->tpl->setVariable("HEADER_LINK", "usr_bookmarks.php?bmf_id=".$this->id."&order=type&direction=".
			//$_GET["dir"]."&cmd=".$_GET["cmd"]);

			$this->tpl->parseCurrentBlock();
		}

		/*
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$childs = $this->tree->getChilds($this->id, "title");

		$objects = array();
		$bookmarks = array();
		foreach ($childs as $key => $child)
		{
			switch ($child["type"])
			{
				case "bmf":
					$objects[] = $child;
					break;

				case "bm":
					$bookmarks[] = $child;
					break;
			}
		}
		foreach ($bookmarks as $key => $bookmark)
		{
			$objects[] = $bookmark;
		}*/

		$cnt = 0;
		$content = $this->pg_obj->getContent();
		foreach ($content as $content_obj)
		{
			// color changing
			$css_row = ilUtil::switchColor($cnt++,"tblrow1","tblrow2");

			// checkbox
			$this->tpl->setCurrentBlock("checkbox");
			$type = (get_class($content_obj) == "ilparagraph") ? "par" : "mob";
			$this->tpl->setVariable("CHECKBOX_ID", $type.":".$cnt);
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("table_cell");
			$this->tpl->parseCurrentBlock();

			// type icon
			$link = "lm_edit.php?";
			$val = ilUtil::getImageTagByType("par", $this->tpl->tplPath);

			$this->add_cell($val, $link);

			// title
			$link = "";
			$this->add_cell($this->lng->txt("cont_paragraph"), $link);

			// target
			$this->add_cell($content_obj->getText(),"");

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW", $css_row);
			$this->tpl->parseCurrentBlock();
			$cnt++;
		}
		if($cnt == 0)
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("NUM_COLS", 4);
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			// SHOW VALID ACTIONS
			//$this->tpl->setVariable("NUM_COLS", 4);
			//$this->showActions();
		}

		// SHOW POSSIBLE SUB OBJECTS
		/*
		$this->tpl->setVariable("NUM_COLS", 4);
		$this->showPossibleSubObjects();

		$this->tpl->show();*/

	}

	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}


}
?>
