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

require_once("./content/classes/Pages/class.ilPageObject.php");
require_once("./classes/class.ilDOMUtil.php");

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
	var $ilias;
	var $tpl;
	var $lng;
	var $obj;
	var $output_mode;
	var $presentation_title;
	var $target_script;
	var $return_location;
	var $target_var;
	var $output2template;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI(&$a_page_object)
	{
		global $ilias, $tpl, $lng;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->obj =& $a_page_object;
		$this->output_mode = "presentation";
		$this->setPageObject($a_page_object);
		$this->output2template = true;
	}

	function setPageObject(&$a_pg_obj)
	{
		$this->obj =& $a_pg_obj;
	}

	function &getPageObject()
	{
		return $this->obj;
	}

	function setTargetScript($a_script)
	{
		$this->target_script = $a_script;
	}

	function getTargetScript()
	{
		return $this->target_script;
	}

	function setReturnLocation($a_location)
	{
		$this->return_location = $a_location;
	}

	function getReturnLocation()
	{
		return $this->return_location;
	}


	/**
	* mode: "presentation" | "edit" | "preview"
	*/
	function setOutputMode($a_mode = "presentation")
	{
		$this->output_mode = $a_mode;
	}

	function getOutputMode()
	{
		return $this->output_mode;
	}

	function setTemplateOutput($a_output = true)
	{
		$this->output2template = $a_output;
	}

	function outputToTemplate()
	{
		return $this->output2template;
	}

	function setPresentationTitle($a_title = "")
	{
		$this->presentation_title = $a_title;
	}

	function getPresentationTitle()
	{
		return $this->presentation_title;
	}

	function setTemplateTargetVar($a_variable)
	{
		$this->target_var = $a_variable;
	}

	function getTemplateTargetVar()
	{
		return $this->target_var;
	}

	/*
	* display content of page
	*/
	function showPage()
	{
		global $tree;

		// init template
		if($this->outputToTemplate())
		{
			$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_edit_wysiwyg.html", true);
			$this->tpl->setVariable("FORMACTION", $this->getTargetScript()."&cmd=edpost");
		}

		// get content
		$builded = $this->obj->buildDom();
		if($this->getOutputMode() == "edit")
		{
			$this->obj->addHierIDs();
		}
		$content = $this->obj->getXMLFromDom(false, true, true);

		// check validation errors
		if($builded !== true)
		{
			$this->displayValidationError($builded);
		}
		else
		{
			$this->displayValidationError($_SESSION["il_pg_error"]);
		}
		unset($_SESSION["il_pg_error"]);

		// get title
		$pg_title = $this->getPresentationTitle();

		// run xslt
		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
//echo "mode:".$this->getOutputMode().":<br>";
		$enlarge_path = ilUtil::getImagePath("enlarge.gif");
		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => $this->getOutputMode(), 'pg_title' => $pg_title, 'pg_id' => $this->obj->getId(),
			'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
//echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
		$output = str_replace("&amp;", "&", $output);

		// output
		if($this->outputToTemplate())
		{
			$this->tpl->setVariable("PAGE_CONTENT", $output);
		}
		else
		{
			return $output;
		}
	}

	/*
	* preview
	*/
	function preview()
	{
		global $tree;
		$this->setOutputMode("preview");
		return $this->showPage();
	}

	/*
	* edit
	*/
	function view()
	{
		global $tree;
		$this->setOutputMode("edit");
		return $this->showPage();
	}

	function edit()
	{
		//
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


	function save()
	{
		// create new object
		$meta_gui =& new ilMetaDataGUI();
		$meta_data =& $meta_gui->create();
		$this->obj =& new ilPageObject();
		$this->obj->assignMetaData($meta_data);
		$this->obj->setType($_GET["new_type"]);
		$this->obj->setLMId($this->lm_obj->getId());
		$this->obj->create();

		// obj_id is empty, if page is created from "all pages" screen
		// -> a free page is created (not in the tree)
		if (empty($_GET["obj_id"]))
		{
			header("location: lm_edit.php?cmd=pages&ref_id=".$this->lm_obj->getRefId());
		}
		else
		{
			$this->putInTree();
			header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
	}

	function displayValidationError($a_error)
	{
		if(is_array($a_error))
		{
			$error_str = "<b>Validation Error(s):</b><br>";
			foreach ($a_error as $error)
			{
				$err_mess = implode($error, " - ");
				if (!is_int(strpos($err_mess, ":0:")))
				{
					$error_str .= htmlentities($err_mess)."<br />";
				}
			}
			$this->tpl->setVariable("MESSAGE", $error_str);
		}
	}

	function showPageEditor()
	{
		require_once ("content/classes/Pages/class.ilPageEditorGUI.php");
		$page_editor =& new ilPageEditorGUI($this->getPageObject());
		$page_editor->setTargetScript($this->getTargetScript());
		$page_editor->setReturnLocation($this->getReturnLocation());
		$page_editor->executeCommand();
	}

	function showLinkHelp()
	{
		$ltype = $_SESSION["il_link_type"];
		$ltype_arr = explode("_", $ltype);
		$link_type = ($ltype_arr[0] == "")
			? "StructureObject"
			: $ltype_arr[0];
		$link_target = $ltype_arr[1];
		$target_str = ($link_target == "")
			? ""
			: " target=\"".$link_target."\" ";

		$content_obj = (empty($_SESSION["il_link_cont_obj"]))
			? $_GET["ref_id"]
			: $_SESSION["il_link_cont_obj"];

		$glossary = $_SESSION["il_link_glossary"];

		if(($link_type == "GlossaryItem") &&
			empty($_SESSION["il_link_glossary_obj"]))
		{
			$this->changeTargetObject("glossary");
		}

		$tpl =& new ilTemplate("tpl.link_help.html", true, true, true);
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		switch($link_type)
		{
			case "GlossaryItem":
				$typestr = "&target_type=glossary";
				break;

			default:
				$typestr = "";
				break;
		}

		$tpl->setVariable("FORMACTION", $this->getTargetScript()."&cmd=post".$typestr);
		$tpl->setVariable("TXT_HELP_HEADER", $this->lng->txt("cont_link_select"));
		$tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_link_type"));
		$ltypes = array("StructureObject" => $this->lng->txt("cont_lk_chapter"),
			"StructureObject_New" => $this->lng->txt("cont_lk_chapter_new"),
			"PageObject" => $this->lng->txt("cont_lk_page"),
			"PageObject_FAQ" => $this->lng->txt("cont_lk_page_faq"),
			"PageObject_New" => $this->lng->txt("cont_lk_page_new"),
			"GlossaryItem" => $this->lng->txt("cont_lk_term"),
			"GlossaryItem_New" => $this->lng->txt("cont_lk_term_new"),
			"Media" => $this->lng->txt("cont_lk_media_inline"),
			"Media_Media" => $this->lng->txt("cont_lk_media_media"),
			"Media_FAQ" => $this->lng->txt("cont_lk_media_faq"),
			"Media_New" => $this->lng->txt("cont_lk_media_new"));
		$select_ltype = ilUtil::formSelect ($ltype,
			"ltype",$ltypes,false,true);
		$tpl->setVariable("SELECT_TYPE", $select_ltype);
		$tpl->setVariable("CMD_CHANGETYPE", "changeLinkType");
		$tpl->setVariable("BTN_CHANGETYPE", $this->lng->txt("cont_change_type"));

		switch($link_type)
		{
			// page link
			case "PageObject":
				require_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($content_obj, true);

				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
				$tpl->setCurrentBlock("chapter_list");
				$tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("cont_content_obj"));
				$tpl->setVariable("TXT_CONT_TITLE", $cont_obj->getTitle());
				$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));

				foreach($nodes as $node)
				{
					if($node["type"] == "st")
					{
						$tpl->setCurrentBlock("chapter_row");
						$tpl->setVariable("TXT_CHAPTER", $node["title"]);
						//$tpl->setVariable("LINK_CHAPTER",
						//	"[iln chap=\"".$node["obj_id"]."\"".$target_str."] [/iln]");
						$tpl->parseCurrentBlock();
					}
					if($node["type"] == "pg")
					{
						$tpl->setCurrentBlock("chapter_row");
						$tpl->setVariable("TXT_CHAPTER", $node["title"]);
						$tpl->setVariable("LINK_CHAPTER",
							"[iln page=\"".$node["obj_id"]."\"".$target_str."] [/iln]");
						$tpl->parseCurrentBlock();
					}
				}
				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;

			// chapter link
			case "StructureObject":
				require_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($content_obj, true);

				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$nodes = $ctree->getSubtree($ctree->getNodeData($ctree->getRootId()));
				$tpl->setCurrentBlock("chapter_list");
				$tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("cont_content_obj"));
				$tpl->setVariable("TXT_CONT_TITLE", $cont_obj->getTitle());
				$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));

				foreach($nodes as $node)
				{
					if($node["type"] == "st")
					{
						$tpl->setCurrentBlock("chapter_row");
						$tpl->setVariable("TXT_CHAPTER", $node["title"]);
						$tpl->setVariable("LINK_CHAPTER",
							"[iln chap=\"".$node["obj_id"]."\"".$target_str."] [/iln]");
						$tpl->parseCurrentBlock();
					}
				}
				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;

			// glossary item link
			case "GlossaryItem":
				require_once("./content/classes/class.ilObjGlossary.php");
				$glossary =& new ilObjGlossary($glossary, true);

				// get all glossary items
				$terms = $glossary->getTermList();
				$tpl->setCurrentBlock("chapter_list");
				$tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("glossary"));
				$tpl->setVariable("TXT_CONT_TITLE", $glossary->getTitle());
				$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));

				foreach($terms as $term)
				{
					$tpl->setCurrentBlock("chapter_row");
					$tpl->setVariable("TXT_CHAPTER", $term["term"]);
					$tpl->setVariable("LINK_CHAPTER",
						"[iln term=\"".$term["id"]."\"".$target_str."]".$term["term"]."[/iln]");
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;
		}

		$tpl->show();
		exit;
	}

	function changeLinkType()
	{
		$_SESSION["il_link_type"] = $_POST["ltype"];
		$this->showLinkHelp();
	}

	function changeTargetObject($a_type = "")
	{
		if($_GET["do"] == "set")
		{
			if($_GET["target_type"] != "glossary")
			{
				$_SESSION["il_link_cont_obj"] = $_GET["sel_id"];
			}
			else
			{
				$_SESSION["il_link_glossary"] = $_GET["sel_id"];
			}
			$this->showLinkHelp();
			return;
		}

		if(empty($a_type))
		{
			$a_type = $_GET["target_type"];
		}

		$tpl =& new ilTemplate("tpl.link_help_explorer.html", true, true, true);
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		require_once "classes/class.ilExplorer.php";
		$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		$exp = new ilExplorer("lm_edit.php?do=set");

		$exp->setExpand($_GET["expand"]);
		$exp->setTargetGet("sel_id");
		$exp->setParamsGet(array("ref_id" => $_GET["ref_id"],
			"cmd" => "changeTargetObject", "mode" => "page_edit", "obj_id" => $_GET["obj_id"],
			"target_type" => $a_type));

		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("crs");

		if ($a_type != "glossary")
		{
			$exp->addFilter("lm");
			$exp->addFilter("dbk");
		}
		else
		{
			$exp->addFilter("glo");
		}
		$exp->setFiltered(true);

		$exp->setClickable("cat", false);
		$exp->setClickable("grp", false);
		$exp->setClickable("crs", false);

		$exp->setFrameTarget("");
		$exp->setOutput(0);

		$output = $exp->getOutput();

		$tpl->setCurrentBlock("content");
		if ($a_type != "glossary")
		{
			$tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_cont_obj"));
		}
		else
		{
			$tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_glossary"));
		}
		$tpl->setVariable("EXPLORER",$output);
		$tpl->setVariable("ACTION", "lm_edit.php?expand=".$_GET["expand"].
			"&obj_id=".$_GET["obj_id"]."&ref_id=".$_GET["ref_id"]."&cmd=changeTargetObject".
			"&target_type=".$a_type);
		$tpl->parseCurrentBlock();

		$tpl->show();
		exit;
	}

}
?>
