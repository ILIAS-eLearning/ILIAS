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

require_once("./content/classes/Pages/class.ilPCParagraph.php");
require_once("./content/classes/Pages/class.ilPageContentGUI.php");

/**
* Class ilPCParagraphGUI
*
* User Interface for Paragraph Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPCParagraphGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCParagraphGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id)
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id);
	}


	/**
	* edit paragraph form
	*/
	function edit()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paragraph_edit.html", true);
		//$content = $this->pg_obj->getContent();
		//$cnt = 1;
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_par"));
		$this->tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
			"hier_id=".$this->hier_id."&cmd=edpost"));

		$this->displayValidationError();

		// language and characteristic selection
		if (key($_POST["cmd"]) == "update")
		{
			$s_lang = $_POST["par_language"];
			$s_char = $_POST["par_characteristic"];
		}
		else
		{
			$s_lang = $this->content_obj->getLanguage();
			$s_char = $this->content_obj->getCharacteristic();
		}
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("classes/class.ilMetaData.php");
		$lang = ilMetaData::getLanguages();
		$select_lang = ilUtil::formSelect ($s_lang,"par_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);
		$char = array("" => $this->lng->txt("none"),
			"Headline" => $this->lng->txt("cont_Headline"),
			"Example" => $this->lng->txt("cont_Example"),
			"Citation" => $this->lng->txt("cont_Citation"),
			"Mnemonic" => $this->lng->txt("cont_Mnemonic"),
			"Additional" => $this->lng->txt("cont_Additional"),
			"List" => $this->lng->txt("cont_List"));
		$this->tpl->setVariable("TXT_CHARACTERISTIC", $this->lng->txt("cont_characteristic"));
		$select_char = ilUtil::formSelect ($s_char,
			"par_characteristic",$char,false,true);
		$this->tpl->setVariable("SELECT_CHARACTERISTIC", $select_char);


		// content is in utf-8, todo: set globally
		header('Content-type: text/html; charset=UTF-8');

//echo "cmd:".key($_POST["cmd"]).":<br>";
		if (key($_POST["cmd"]) == "update")
		{
			$s_text = stripslashes($_POST["par_content"]);
		}
		else
		{
			$s_text = $this->content_obj->xml2output($this->content_obj->getText());
		}
		$this->tpl->setVariable("PAR_TA_NAME", "par_content");
		$this->tpl->setVariable("PAR_TA_CONTENT", $s_text);
		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "update");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* insert paragraph form
	*/
	function insert()
	{
		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paragraph_edit.html", true);
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_par"));
		$this->tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
			"hier_id=".$this->hier_id."&cmd=edpost"));

		$this->displayValidationError();

		// language and characteristic selection
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("classes/class.ilMetaData.php");
		$lang = ilMetaData::getLanguages();

		// get values from new object (repeated form display on error)
		//if (is_object($this->content_obj))
		if (key($_POST["cmd"]) == "create_par")
		{
			$s_lang = $_POST["par_language"];
			$s_char = $_POST["par_characteristic"];
		}
		else
		{
			// set characteristic of new paragraphs in list items to "List"
			if (is_object($this->content_obj))
			{
				if ($this->content_obj->getType() == "li")
				{
					$s_char = "List";
				}
			}
		}

		require_once("classes/class.ilMetaData.php");
		$lang = ilMetaData::getLanguages();
		$select_lang = ilUtil::formSelect ($s_lang,"par_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);
		$char = array("" => $this->lng->txt("none"),
			"Headline" => $this->lng->txt("cont_Headline"),
			"Example" => $this->lng->txt("cont_Example"),
			"Citation" => $this->lng->txt("cont_Citation"),
			"Mnemonic" => $this->lng->txt("cont_Mnemonic"),
			"Additional" => $this->lng->txt("cont_Additional"),
			"List" => $this->lng->txt("cont_List"));
		$this->tpl->setVariable("TXT_CHARACTERISTIC", $this->lng->txt("cont_characteristic"));
		$select_char = ilUtil::formSelect ($s_char,
			"par_characteristic",$char,false,true);
		$this->tpl->setVariable("SELECT_CHARACTERISTIC", $select_char);

		// content is in utf-8, todo: set globally
		// header('Content-type: text/html; charset=UTF-8');

		// input text area
		$this->tpl->setVariable("PAR_TA_NAME", "par_content");
		if (key($_POST["cmd"]) == "create_par")
		{
			$this->tpl->setVariable("PAR_TA_CONTENT", stripslashes($_POST["par_content"]));
		}
		else
		{
			$this->tpl->setVariable("PAR_TA_CONTENT", "");
		}
		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_par");	//--
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* update paragraph in dom and update page in db
	*/
	function update()
	{
		// set language and characteristic
		$this->content_obj->setLanguage($_POST["par_language"]);
		$this->content_obj->setCharacteristic($_POST["par_characteristic"]);

//echo "PARupdate:".$this->content_obj->input2xml($_POST["par_content"]).":<br>"; exit;
		$this->updated = $this->content_obj->setText($this->content_obj->input2xml(stripslashes($_POST["par_content"])));

		if ($this->updated !== true)
		{
//echo "Did not update!";
			$this->edit();
			return;
		}

		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			header("Location: ".$this->getReturnLocation());
			exit;
		}
		else
		{
			$this->edit();
		}
	}

	/**
	* create new paragraph in dom and update page in db
	*/
	function create()
	{
		$this->content_obj =& new ilPCParagraph($this->dom);
		$this->content_obj->create($this->pg_obj, $this->hier_id);
		$this->content_obj->setLanguage($_POST["par_language"]);
		$this->content_obj->setCharacteristic($_POST["par_characteristic"]);
		$this->updated = $this->content_obj->setText($this->content_obj->input2xml($_POST["par_content"]));
		if ($this->updated !== true)
		{
			$this->insert();
			return;
		}
		$this->updated = $this->pg_obj->update();
		if ($this->updated === true)
		{
			header("Location: ".$this->getReturnLocation());
			exit;
		}
		else
		{
			$this->insert();
		}
	}


}
?>
