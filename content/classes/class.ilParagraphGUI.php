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

require_once("./content/classes/class.ilParagraph.php");
require_once("./content/classes/class.ilPageContentGUI.php");

/**
* Class ilParagraphGUI
*
* User Interface for Paragraph Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilParagraphGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilParagraphGUI(&$a_lm_obj, &$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_lm_obj, $a_pg_obj, $a_content_obj, $a_hier_id);
	}

	function edit()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paragraph_edit.html", true);
		//$content = $this->pg_obj->getContent();
		//$cnt = 1;

		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_par"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		$this->tpl->setVariable("PAR_TA_NAME", "par_content");
		$this->tpl->setVariable("PAR_TA_CONTENT", $this->content_obj->xml2output($this->content_obj->getText()));
		$this->tpl->parseCurrentBlock();

		/*
		reset($content);
		foreach ($content as $content_obj)
		{
			switch (get_class($content_obj))
			{
				case "ilparagraph":
					$cont_sel[$cnt] = ilUtil::shortenText($content_obj->getText(),40);
					break;
			}
			$cnt++;
		}
		$this->tpl->setCurrentBlock("content_selection");
		$this->tpl->setVariable("SELECT_CONTENT" ,
			ilUtil::formSelect($this->hier_id, "new_hier_id",$cont_sel, false, true));
		$this->tpl->setVariable("BTN_NAME", "edit");
		$this->tpl->setVariable("TXT_SELECT",$this->lng->txt("select"));
		$this->tpl->parseCurrentBlock();*/

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "update");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	function insert()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paragraph_edit.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_par"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->pg_obj->getId().
			"&hier_id=".$this->hier_id."&cmd=edpost");

		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

		// input text area
		$this->tpl->setVariable("PAR_TA_NAME", "par_content");
		$this->tpl->setVariable("PAR_TA_CONTENT", "");
		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_par");	//--
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	function update()
	{
		//$content = $this->pg_obj->getContent();

		//$cur_content_obj =& $content[$_GET["hier_id"] - 1];
//echo "PARupdate:".$this->content_obj->input2xml($_POST["par_content"]).":<br>";
		$this->content_obj->setText($this->content_obj->input2xml($_POST["par_content"]));
		$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
		exit;

	}

	function create()
	{
		$new_par = new ilParagraph($this->dom);
		$new_par->createNode();
		$new_par->setText($new_par->input2xml($_POST["par_content"]));
		$this->pg_obj->insertContent($new_par, $this->hier_id, IL_INSERT_AFTER);
		$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
		exit;
	}

	/**
	* create paragraph as first child of a container (e.g. a TableData Element)
	*/
	/*
	function create_child()
	{
		$new_par = new ilParagraph($this->dom);
		$new_par->createNode();
		$new_par->setText($new_par->input2xml($_POST["par_content"]));
		$this->pg_obj->insertContent($new_par, $this->hier_id, IL_INSERT_CHILD);
		//$this->pg_obj->update();
		header("location: lm_edit.php?cmd=viewWysiwyg&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
			$this->pg_obj->getId());
	}*/

}
?>
