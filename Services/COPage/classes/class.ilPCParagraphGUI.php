<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilPCParagraph.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");
require_once("./Services/COPage/classes/class.ilWysiwygUtil.php");

/**
* Class ilPCParagraphGUI
*
* User Interface for Paragraph Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesCOPage
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
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	* edit paragraph form
	*/
	function edit()
	{
		global $ilUser, $ilias;
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paragraph_edit.html", "Services/COPage");
		//$content = $this->pg_obj->getContent();
		//$cnt = 1;
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_edit_par"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("REF_ID", $_GET["ref_id"]);
		
		$this->tpl->setVariable("EDITOR_URL", ILIAS_HTTP_PATH."/Services/COPage/htmlarea/");
		$this->tpl->setVariable("JS_HTMLAREA", ILIAS_HTTP_PATH."/Services/COPage/htmlarea/htmlarea.js");
		$this->tpl->setVariable("JS_HANDLETAGS", ILIAS_HTTP_PATH."/Services/COPage/js/handletags.js");
		$this->ctrl->setParameter($this, "ptype", "footnote");
		$this->tpl->setVariable("POPUP_TARGET_FOOTNOTE",
			$this->ctrl->getLinkTarget($this, "popup"));	
		$this->ctrl->setParameter($this, "ptype", "xtl");
		$this->tpl->setVariable("POPUP_TARGET_XTL",
			$this->ctrl->getLinkTarget($this, "popup"));
			
		$this->tpl->setVariable("TXT_STR", $this->lng->txt("cont_text_str"));
		$this->tpl->setVariable("TXT_EMP", $this->lng->txt("cont_text_emp"));
		$this->tpl->setVariable("TXT_COM", $this->lng->txt("cont_text_com"));
		$this->tpl->setVariable("TXT_FN", $this->lng->txt("cont_text_fn"));
		$this->tpl->setVariable("TXT_QUOT", $this->lng->txt("cont_text_quot"));
		$this->tpl->setVariable("TXT_CODE", $this->lng->txt("cont_text_code"));
		$this->tpl->setVariable("TXT_ILN", $this->lng->txt("cont_text_iln"));
		$this->tpl->setVariable("TXT_XLN", $this->lng->txt("cont_text_xln"));
		$this->tpl->setVariable("TXT_TEX", $this->lng->txt("cont_text_tex"));

		include_once("./classes/class.ilObjStyleSheet.php");
		
		if ($this->pg_obj->getParentType() == "gdf" ||
			$this->pg_obj->getParentType() == "lm" ||
			$this->pg_obj->getParentType() == "dbk")
		{
			$this->tpl->setVariable("TXT_FORMATERROR",$this->lng->txt("cont_format_error"));
			
			$this->tpl->setVariable("LOCATION_STYLESHEET_HTMLAREA",
				ilUtil::getStyleSheetLocation());
				
			if ($this->pg_obj->getParentType() != "gdf")
			{
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA",
					ilObjStyleSheet::getContentStylePath(
						ilObjContentObject::_lookupStyleSheetId($this->pg_obj->getParentId())));
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(
						ilObjContentObject::_lookupStyleSheetId($this->pg_obj->getParentId())));
			}
			else
			{
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
			}

			if (!ilPageEditorGUI::_doJSEditing())
			{
				$this->tpl->setCurrentBlock("bb_ilink_button");
				$this->tpl->setVariable("BB_LINK_ILINK",
					$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp"));
				$this->tpl->setVariable("BB_TXT_ILINK", "[".$this->lng->txt("cont_internal_link")."]");
				$this->tpl->setCurrentBlock("bb_buttons");
				$this->tpl->setVariable("TXT_BB_TIP", $this->lng->txt("cont_bb_tip"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable("LINK_ILINK",
					$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp"));
				$this->tpl->setVariable("TXT_ILINK", "[".$this->lng->txt("cont_internal_link")."]");
			}
			
			$this->tpl->setVariable("REMOVELINK",$this->lng->txt("cont_removeiln"));
						
			$this->tpl->touchBlock("internal_link_active1");
			$this->tpl->touchBlock("internal_link_active2");
		}
		else
		{
			$this->tpl->setVariable("LOCATION_STYLESHEET_HTMLAREA",
				ilUtil::getStyleSheetLocation());
			$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA",
				ilObjStyleSheet::getContentStylePath(0));
			if (!ilPageEditorGUI::_doJSEditing())
			{
				$this->tpl->setCurrentBlock("bb_buttons");
				$this->tpl->setVariable("TXT_BB_TIP", $this->lng->txt("cont_bb_tip"));
				$this->tpl->parseCurrentBlock();
			}
		}

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
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$select_lang = ilUtil::formSelect ($s_lang,"par_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);
		$char = array("" => $this->lng->txt("none"),
			"Headline1" => $this->lng->txt("cont_Headline1"),
			"Headline2" => $this->lng->txt("cont_Headline2"),
			"Headline3" => $this->lng->txt("cont_Headline3"),
			"Example" => $this->lng->txt("cont_Example"),
			"Citation" => $this->lng->txt("cont_Citation"),
			"Mnemonic" => $this->lng->txt("cont_Mnemonic"),
			"Additional" => $this->lng->txt("cont_Additional"),
			"List" => $this->lng->txt("cont_List"),
			"Remark" => $this->lng->txt("cont_Remark"),
			// "Code" => $this->lng->txt("cont_Code"),
			"TableContent" => $this->lng->txt("cont_TableContent")
			);
		$this->tpl->setVariable("TXT_CHARACTERISTIC", $this->lng->txt("cont_characteristic"));
		$select_char = ilUtil::formSelect ($s_char,
			"par_characteristic",$char,false,true);
		$this->tpl->setVariable("SELECT_CHARACTERISTIC", $select_char);

		if (key($_POST["cmd"]) == "update")
		{
			$s_text = ilUtil::stripSlashes($_POST["par_content"], false);
		}
		else
		{
			$s_text = $this->content_obj->xml2output($this->content_obj->getText());
		}
		$this->tpl->setVariable("PAR_TA_NAME", "par_content");

		if (ilPageEditorGUI::_doJSEditing())
		{
			// this line is necessary to prevent unwanted converts
			// e.g. an entered "&lt;" to a "<"
			$s_text = str_replace("&","&amp;", $s_text);
			
			// to enable normal editing after links:
			if (substr($s_text, strlen($s_text) - 6) == "[/xln]" ||
				substr($s_text, strlen($s_text) - 6) == "[/iln]")
			{
				$s_text = $s_text." ";
			}
			
			$this->tpl->setVariable("PAR_TA_CONTENT", $s_text);
			$this->tpl->touchBlock("initwysiwygeditor");
		}
		else
		{
			$this->tpl->setVariable("PAR_TA_CONTENT", $s_text);
		}

		$this->tpl->parseCurrentBlock();

		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "update");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelUpdate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}


	/**
	* insert paragraph form
	*/
	function insert()
	{
		global $ilUser;

		// add paragraph edit template
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.paragraph_edit.html", "Services/COPage");
		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("cont_insert_par"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("REF_ID", $_GET["ref_id"]);

		$this->tpl->setVariable("EDITOR_URL", ILIAS_HTTP_PATH."/Services/COPage/htmlarea/");
		$this->tpl->setVariable("JS_HTMLAREA", ILIAS_HTTP_PATH."/Services/COPage/htmlarea/htmlarea.js");
		$this->tpl->setVariable("JS_HANDLETAGS", ILIAS_HTTP_PATH."/Services/COPage/js/handletags.js");
		$this->ctrl->setParameter($this, "ptype", "footnote");
		$this->tpl->setVariable("POPUP_TARGET_FOOTNOTE",
			$this->ctrl->getLinkTarget($this, "popup"));	
		$this->ctrl->setParameter($this, "ptype", "xtl");
		$this->tpl->setVariable("POPUP_TARGET_XTL",
			$this->ctrl->getLinkTarget($this, "popup"));	

		$this->tpl->setVariable("TXT_STR", $this->lng->txt("cont_text_str"));
		$this->tpl->setVariable("TXT_EMP", $this->lng->txt("cont_text_emp"));
		$this->tpl->setVariable("TXT_COM", $this->lng->txt("cont_text_com"));
		$this->tpl->setVariable("TXT_FN", $this->lng->txt("cont_text_fn"));
		$this->tpl->setVariable("TXT_QUOT", $this->lng->txt("cont_text_quot"));
		$this->tpl->setVariable("TXT_CODE", $this->lng->txt("cont_text_code"));
		$this->tpl->setVariable("TXT_ILN", $this->lng->txt("cont_text_iln"));
		$this->tpl->setVariable("TXT_XLN", $this->lng->txt("cont_text_xln"));
		$this->tpl->setVariable("TXT_TEX", $this->lng->txt("cont_text_tex"));

		include_once("./classes/class.ilObjStyleSheet.php");
		
		if ($this->pg_obj->getParentType() == "gdf" ||
			$this->pg_obj->getParentType() == "lm" ||
			$this->pg_obj->getParentType() == "dbk")
		{
			$this->tpl->setVariable("TXT_FORMATERROR",$this->lng->txt("cont_format_error"));
			
			if ($this->pg_obj->getParentType() != "gdf")
			{
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA",
					ilObjStyleSheet::getContentStylePath(
						ilObjContentObject::_lookupStyleSheetId($this->pg_obj->getParentId())));
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(
						ilObjContentObject::_lookupStyleSheetId($this->pg_obj->getParentId())));
			}
			else
			{
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA",
					ilObjStyleSheet::getContentStylePath(0));
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath(0));
			}					
					
			$this->tpl->setVariable("LOCATION_STYLESHEET_HTMLAREA",
				ilUtil::getStyleSheetLocation());			

			if (!ilPageEditorGUI::_doJSEditing())
			{
				$this->tpl->setCurrentBlock("bb_ilink_button");
				$this->tpl->setVariable("BB_LINK_ILINK",
					$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp"));
				$this->tpl->setVariable("BB_TXT_ILINK", "[".$this->lng->txt("cont_internal_link")."]");
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("bb_buttons");
				$this->tpl->setVariable("TXT_BB_TIP", $this->lng->txt("cont_bb_tip"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable("LINK_ILINK",
					$this->ctrl->getLinkTargetByClass("ilInternalLinkGUI", "showLinkHelp"));
				$this->tpl->setVariable("TXT_ILINK", "[".$this->lng->txt("cont_internal_link")."]");
			}
			
			$this->tpl->touchBlock("internal_link_active1");
			$this->tpl->touchBlock("internal_link_active2");
		}
		else
		{
			$this->tpl->setVariable("LOCATION_STYLESHEET_HTMLAREA",
				ilUtil::getStyleSheetLocation());
			$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET_HTMLAREA",
				ilObjStyleSheet::getContentStylePath(0));
			if (!ilPageEditorGUI::_doJSEditing())
			{
				$this->tpl->setCurrentBlock("bb_buttons");
				$this->tpl->setVariable("TXT_BB_TIP", $this->lng->txt("cont_bb_tip"));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->displayValidationError();

		// language and characteristic selection
		$this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();

		// get values from new object (repeated form display on error)
		//if (is_object($this->content_obj))
		if (key($_POST["cmd"]) == "create_par")
		{
			$s_lang = $_POST["par_language"];
			$s_char = $_POST["par_characteristic"];
		}
		else
		{
			if ($_SESSION["il_text_lang_".$_GET["ref_id"]] != "")
			{
				$s_lang = $_SESSION["il_text_lang_".$_GET["ref_id"]];
			}
			else
			{
				$s_lang = $ilUser->getLanguage();
			}

			// set characteristic of new paragraphs in list items to "List"
			$cont_obj =& $this->pg_obj->getContentObject($this->getHierId());
			if (is_object($cont_obj))
			{
				if ($cont_obj->getType() == "li" ||
					($cont_obj->getType() == "par" && $cont_obj->getCharacteristic() == "List"))
				{
					$s_char = "List";
				}
								
				if ($cont_obj->getType() == "td" ||
					($cont_obj->getType() == "par" && $cont_obj->getCharacteristic() == "TableContent"))
				{
					$s_char = "TableContent";
				}

			}
		}

		require_once("Services/MetaData/classes/class.ilMDLanguageItem.php");
		$lang = ilMDLanguageItem::_getLanguages();
		$select_lang = ilUtil::formSelect ($s_lang,"par_language",$lang,false,true);
		$this->tpl->setVariable("SELECT_LANGUAGE", $select_lang);
		$char = array("" => $this->lng->txt("none"),
			"Headline1" => $this->lng->txt("cont_Headline1"),
			"Headline2" => $this->lng->txt("cont_Headline2"),
			"Headline3" => $this->lng->txt("cont_Headline3"),
			"Example" => $this->lng->txt("cont_Example"),
			"Citation" => $this->lng->txt("cont_Citation"),
			"Mnemonic" => $this->lng->txt("cont_Mnemonic"),
			"Additional" => $this->lng->txt("cont_Additional"),
			"List" => $this->lng->txt("cont_List"),
			"Remark" => $this->lng->txt("cont_Remark"),
			//"Code" => $this->lng->txt("cont_Code"),
			"TableContent" => $this->lng->txt("cont_TableContent")
			);
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
			$this->tpl->setVariable("PAR_TA_CONTENT",
				ilUtil::stripSlashes($_POST["par_content"], false));
		}
		else
		{
			$this->tpl->setVariable("PAR_TA_CONTENT", "");
		}
		$this->tpl->parseCurrentBlock();

		if (ilPageEditorGUI::_doJSEditing()) 
		{
			$this->tpl->touchBlock("initwysiwygeditor");
		}
		
		// operations
		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "create_par");	//--
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->setVariable("BTN_CANCEL", "cancelCreate");
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();

	}


    
	/**
	* update paragraph in dom and update page in db
	*/
	function update()
	{
		global $ilBench;

		$ilBench->start("Editor","Paragraph_update");
		// set language and characteristic
		$this->content_obj->setLanguage($_POST["par_language"]);
		$this->content_obj->setCharacteristic($_POST["par_characteristic"]);

//echo "<br>PARupdate1:".$_POST["par_content"].":";
//echo "<br>PARupdate2:".htmlentities($_POST["par_content"]).":";
//echo "<br>PARupdate3:".htmlentities($this->content_obj->input2xml($_POST["par_content"])).":";
//echo "<br>PARupdate4:".$this->content_obj->input2xml($_POST["par_content"]).":";

		//$this->updated = $this->content_obj->setText(
		//	$this->content_obj->input2xml(stripslashes($_POST["par_content"]),
		//		$_POST["usedwsiwygeditor"]));
		$this->updated = $this->content_obj->setText(
			$this->content_obj->input2xml($_POST["par_content"],
				$_POST["usedwsiwygeditor"]));
//echo "<br>PARupdate2";
		if ($this->updated !== true)
		{
			$ilBench->stop("Editor","Paragraph_update");
			$this->edit();
			return;
		}

		$this->updated = $this->pg_obj->update();
//echo "<br>PARupdate_after:".htmlentities($this->pg_obj->dom->dump_mem(0, "UTF-8")).":";

		$ilBench->stop("Editor","Paragraph_update");

		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
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
		$_SESSION["il_text_lang_".$_GET["ref_id"]] = $_POST["par_language"];
		$this->content_obj->setCharacteristic($_POST["par_characteristic"]);

		$this->updated = $this->content_obj->setText(
			$this->content_obj->input2xml($_POST["par_content"],
				$_POST["usedwsiwygeditor"]));

		if ($this->updated !== true)
		{
			$this->insert();
			return;
		}
		$this->updated = $this->pg_obj->update();

		if ($this->updated === true)
		{
			$this->ctrl->returnToParent($this, "jump".$this->hier_id);
		}
		else
		{
			$this->insert();
		}
	}
	
	/**
	* popup window for wysiwyg editor
	*/
	function popup()
	{
		include_once "./Services/COPage/classes/class.ilWysiwygUtil.php";
		$popup = new ilWysiwygUtil();
		$popup->show($_GET["ptype"]);
		exit;
	}
}
?>
