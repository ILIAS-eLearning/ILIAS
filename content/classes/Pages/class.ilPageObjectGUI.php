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
	var $output_submode;
	var $presentation_title;
	var $target_script;
	var $return_location;
	var $target_var;
	var $template_output_var;
	var $output2template;
	var $link_params;
	var $bib_id;
	var $citation;

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

		// USED FOR TRANSLATIONS
		$this->template_output_var = "PAGE_CONTENT";
		$this->citation = false;
	}

	function setBibId($a_id)
	{
		// USED FOR SELECTION WHICH PAGE TURNS AND LATER PAGES SHOULD BE SHOWN
		$this->bib_id = $a_id;
	}
	function getBibId()
	{
		return $this->bib_id ? $this->bib_id : 0;
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

	function setHeader($a_title = "")
	{
		$this->header = $a_title;
	}

	function getHeader()
	{
		return $this->header;
	}

	function setLinkParams($l_params = "")
	{
		$this->link_params = $l_params;
	}

	function getLinkParams()
	{
		return $this->link_params;
	}

	function setLinkFrame($l_frame = "")
	{
		$this->link_frame = $l_frame;
	}

	function getLinkFrame()
	{
		return $this->link_frame;
	}

	function setLinkTargets($l_targets = "")
	{
		$this->link_targets = $l_targets;
	}

	function getLinkTargets()
	{
		return $this->link_targets;
	}

	function setTemplateTargetVar($a_variable)
	{
		$this->target_var = $a_variable;
	}

	function getTemplateTargetVar()
	{
		return $this->target_var;
	}

	function setTemplateOutputVar($a_value)
	{
		// USED FOR TRANSLATION PRESENTATION OF dbk OBJECTS
		$this->template_output_var = $a_value;
	}

	function getTemplateOutputVar()
	{
		return $this->template_output_var;
	}

	function setOutputSubmode($a_mode)
	{
		// USED FOR TRANSLATION PRESENTATION OF dbk OBJECTS
		$this->output_submode = $a_mode;
	}

	function getOutputSubmode()
	{
		return $this->output_submode;
	}

	function enableCitation($a_enabled)
	{
		$this->citation = $a_enabled;
	}

	function isEnabledCitation()
	{
		return $this->citation;
	}

	function setLocator(&$a_locator)
	{
		$this->locator =& $a_locator;
	}

	function setTabs($a_tabs)
	{
		$this->tabs = $a_tabs;
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
			if($this->getOutputMode() == "edit")
			{
//echo ":".$this->getTemplateTargetVar().":";
				$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_edit_wysiwyg.html", true);
			}
			else
			{
				if($this->getOutputSubmode() == 'translation')
				{
					$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_translation_content.html", true);
				}
				else
				{
					$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_content.html", true);
				}
			}
			$this->tpl->setVariable("FORMACTION", $this->getTargetScript()."&cmd=edpost");
		}

		// get content
		$builded = $this->obj->buildDom();
		if($this->getOutputMode() == "edit")
		{
			$this->obj->addHierIDs();
		}
		$content = $this->obj->getXMLFromDom(false, true, true, $this->link_targets);

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

		if(isset($_SESSION["citation_error"]))
		{
			sendInfo($this->lng->txt("cont_citation_selection_not_valid"));
			session_unregister("citation_error");
			unset($_SESSION["citation_error"]);
		}


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
		$wb_path = ilUtil::getWebspaceDir("output");
//		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => $this->getOutputMode(), 'pg_title' => $pg_title, 'pg_id' => $this->obj->getId(),
						 'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path, 'link_params' => $this->link_params,
						 'bib_id' => $this->getBibId(),'citation' => (int) $this->isEnabledCitation());

		if($this->link_frame != "")		// todo other link types
			$params["pg_frame"] = $this->link_frame;

		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
//echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
		$output = str_replace("&amp;", "&", $output);
//echo "<b>HTML</b>:".htmlentities($output).":<br>";

		// output
		if($this->outputToTemplate())
		{
			$this->tpl->setVariable($this->getTemplateOutputVar(), $output);
            return $output;
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

	/*
	* presentation
	*/
	function presentation()
	{
		global $tree;
		$this->setOutputMode("presentation");
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
//echo "PGObjGUI::showPageEditor";
		require_once ("content/classes/Pages/class.ilPageEditorGUI.php");
		$page_editor =& new ilPageEditorGUI($this->getPageObject());
		$page_editor->setTargetScript($this->getTargetScript());
		if(!empty($this->tabs))
		{
			$page_editor->setTabs($this->tabs);
		}
		$page_editor->setLocator($this->locator);
		$page_editor->setHeader($this->getHeader());
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
			empty($_SESSION["il_link_glossary"]))
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
		$tpl->setVariable("FORMACTION2", $this->getTargetScript()."&cmd=post".$typestr);
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
		$tpl->setVariable("CMD_CLOSE", "closeLinkHelp");
		$tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));

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
				$tpl->setCurrentBlock("change_cont_obj");
				$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
				$tpl->parseCurrentBlock();

				foreach($nodes as $node)
				{
					if($node["type"] == "st")
					{
						$tpl->setCurrentBlock("chapter_row");
						$tpl->setVariable("TXT_CHAPTER", $node["title"]);
						$tpl->setVariable("ROWCLASS", "tblrow1");
						//$tpl->setVariable("LINK_CHAPTER",
						//	"[iln chap=\"".$node["obj_id"]."\"".$target_str."] [/iln]");
						$tpl->parseCurrentBlock();
					}
					if($node["type"] == "pg")
					{
						$tpl->setCurrentBlock("chapter_row");
						$tpl->setVariable("TXT_CHAPTER", $node["title"]);
						$tpl->setVariable("ROWCLASS", "tblrow2");
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
				$tpl->setCurrentBlock("change_cont_obj");
				$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
				$tpl->parseCurrentBlock();

				foreach($nodes as $node)
				{
					if($node["type"] == "st")
					{
						$css_row = ($css_row =="tblrow1")
							? "tblrow2"
							: "tblrow1";
						$tpl->setCurrentBlock("chapter_row");
						$tpl->setVariable("TXT_CHAPTER", $node["title"]);
						$tpl->setVariable("ROWCLASS", $css_row);
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
				$tpl->setCurrentBlock("change_cont_obj");
				$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
				$tpl->parseCurrentBlock();

				foreach($terms as $term)
				{
					$css_row = ($css_row =="tblrow1")
						? "tblrow2"
						: "tblrow1";
					$tpl->setCurrentBlock("chapter_row");
					$tpl->setVariable("ROWCLASS", $css_row);
					$tpl->setVariable("TXT_CHAPTER", $term["term"]);
					$tpl->setVariable("LINK_CHAPTER",
						"[iln term=\"".$term["id"]."\"".$target_str."]".$term["term"]."[/iln]");
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;

			// media object
			case "Media":
				$tpl->setVariable("TARGET2", " target=\"content\" ");
				//require_once("./content/classes/class.ilObjMediaObject.php");
				$cont_obj =& new ilObjContentObject($content_obj, true);

				// get all chapters
				$ctree =& $cont_obj->getLMTree();
				$objs = $this->ilias->account->getClipboardObjects("mob");
				$tpl->setCurrentBlock("chapter_list");
				$tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("cont_source"));
				$tpl->setVariable("TXT_CONT_TITLE", $this->lng->txt("cont_personal_clipboard"));
				//$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
				//$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
				$tpl->setCurrentBlock("new_mob");
				$tpl->setVariable("CMD_NEW_MOB", "newMediaObject");
				$tpl->setVariable("BTN_NEW_MOB", $this->lng->txt("cont_new_media_obj"));
				$tpl->parseCurrentBlock();

				foreach($objs as $obj)
				{
					$tpl->setCurrentBlock("chapter_row");
					$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
					$tpl->setVariable("ROWCLASS", "tblrow1");
					if (!empty($target_str))
					{
						$tpl->setVariable("LINK_CHAPTER",
							"[iln media=\"".$obj["id"]."\"".$target_str."] [/iln]");
					}
					else
					{
						$tpl->setVariable("LINK_CHAPTER",
							"[iln media=\"".$obj["id"]."\"/]");
					}
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

	function resetLinkList()
	{
		$_SESSION["il_link_type"] = "StructureObject";
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
		//$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

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
			"&obj_id=".$_GET["obj_id"]."&ref_id=".$_GET["ref_id"]."&cmd=post".
			"&target_type=".$a_type);
		$tpl->setVariable("BTN_REFRESH", "changeTargetObject");
		$tpl->setVariable("TXT_REFRESH", $this->lng->txt("refresh"));
		$tpl->setVariable("BTN_RESET", "resetLinkList");
		$tpl->setVariable("TXT_RESET", $this->lng->txt("reset"));
		$tpl->setVariable("BTN_STRUCTURE", "resetLinkList");
		$tpl->setVariable("TXT_STRUCTURE", $this->lng->txt("reset"));
		$tpl->parseCurrentBlock();

		$tpl->show();
		exit;
	}

	/*
	* display clipboard content
	*/
	function clipboard()
	{
		global $tree;

		// workaround
		if($_GET["limit"] == 0 )
		{
			$_GET["limit"] = 10;
		}

		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.clipboard_tbl_row.html", true);

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=post");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("clipboard"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		$tbl->setHeaderNames(array("", $this->lng->txt("cont_object")));

		$cols = array("", "object");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "clipboard");
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("1%","99%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???
		//$tbl->setMaxCount(30);		// ???

		$this->tpl->setVariable("COLUMN_COUNTS", 2);

		// delete button
		$this->tpl->setCurrentBlock("tbl_action_btn");
		$this->tpl->setVariable("BTN_NAME", "clipboardDeletion");
		$this->tpl->setVariable("BTN_VALUE", "delete");
		$this->tpl->parseCurrentBlock();

		// add list
		$opts = ilUtil::formSelect("","new_type",array("mob" => "mob"));
		$this->tpl->setCurrentBlock("add_object");
		$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
		$this->tpl->setVariable("BTN_NAME", "createMediaInClipboard");
		$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
		$this->tpl->parseCurrentBlock();

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("footer");

		//require_once("./content/classes/class.ilObjMediaObject.php");
		//$cont_obj =& new ilObjContentObject($content_obj, true);

		$objs = $this->ilias->account->getClipboardObjects("mob");
		$objs = ilUtil::sortArray($objs, $_GET["sort_by"], $_GET["sort_order"]);
		$objs = array_slice($objs, $_GET["offset"], $_GET["limit"]);
		$tbl->setMaxCount(count($objs));

		$tbl->render();
		if(count($objs) > 0)
		{
			$i=0;
			foreach($objs as $obj)
			{
				$css_row = ilUtil::switchColor($i++,"tblrow1","tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TEXT_OBJECT", $obj["title"]);
				$this->tpl->setVariable("CHECKBOX_ID", $obj["id"]);

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}

	}

	function clipboardDeletion()
	{
		// check number of objects
		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach($_POST["id"] AS $obj_id)
		{
			$this->ilias->account->removeObjectFromClipboard($obj_id, "mob");
		}
		$this->clipboard();
	}

	function createMediaInClipboard()
	{
		require_once ("content/classes/Pages/class.ilMediaObjectGUI.php");
		$mob_gui =& new ilMediaObjectGUI($this->obj, $this->lm_obj);
		$mob_gui->setTargetScript("lm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		$mob_gui->insert("post", "saveMediaInClipboard");
	}

	function saveMediaInClipboard()
	{
		require_once ("content/classes/Pages/class.ilMediaObjectGUI.php");
		$mob_gui =& new ilMediaObjectGUI($this->obj, $this->lm_obj);
		$mob =& $mob_gui->create(false);
		$this->ilias->account->addObjectToClipboard($mob->getId(), "mob", $mob->getTitle());
		$this->clipboard();
	}

}
?>
