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
* Class ilInternalLinkGUI
*
* Some gui methods to handle internal links
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilInternalLinkGUI
{
	var $default_type;
	var $default_obj;
	var $link_type;
	var $link_target;
	var $target_script;
	var $lng;
	var $mode;			// "text" | "link"
	var $set_link_script;

	function ilInternalLinkGUI($a_target_script, $a_default_type, $a_default_obj)
	{
		global $lng, $ilias;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->target_script = $a_target_script;
		$this->default_type = $a_default_type;
		$this->default_obj = $a_default_obj;
		$this->filter_link_types = array();
		$this->mode = "text";

		// determine content object id
		$this->content_obj = (empty($_SESSION["il_link_cont_obj"]))
			? $this->default_obj
			: $_SESSION["il_link_cont_obj"];

		// determine link type and target
		$ltype = ($_SESSION["il_link_type"] == "")
			? $a_default_type
			: $_SESSION["il_link_type"];
		$ltype_arr = explode("_", $ltype);
		$this->link_type = ($ltype_arr[0] == "")
			? $this->default_type
			: $ltype_arr[0];
		$this->link_target = $ltype_arr[1];

		$target_str = ($link_target == "")
			? ""
			: " target=\"".$link_target."\" ";
	}

	function setMode($a_mode = "text")
	{
		$this->mode = $a_mode;
	}

	function setSetLinkTargetScript($a_script)
	{
		$this->set_link_script = $a_script;
	}

	function getSetLinkTargetScript()
	{
		return $this->set_link_script;
	}

	function getTargetScript()
	{
		return $this->target_script;
	}

	function filterLinkType($a_link_type)
	{
		$this->filter_link_types[] = $a_link_type;
	}

	function showLinkHelp()
	{
		$target_str = ($this->link_target == "")
			? ""
			: " target=\"".$this->link_target."\" ";


		$glossary = $_SESSION["il_link_glossary"];		// todo

		if(($this->link_type == "GlossaryItem") &&
			empty($_SESSION["il_link_glossary"]))
		{
			$this->changeTargetObject("glossary");
		}

		$tpl =& new ilTemplate("tpl.link_help.html", true, true, true);
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		switch($this->link_type)
		{
			case "GlossaryItem":
				$typestr = "&target_type=glossary";
				break;

			default:
				$typestr = "";
				break;
		}

		$tpl->setVariable("FORMACTION",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
				"cmdClass=ilInternalLinkGUI&cmd=post".$typestr));
		$tpl->setVariable("FORMACTION2",
			ilUtil::appendUrlParameterString($this->getTargetScript(),
				"cmdClass=ilInternalLinkGUI&cmd=post".$typestr));
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

		// filter link types
		foreach($this->filter_link_types as $link_type)
		{
			unset($ltypes[$link_type]);
		}

		$ltype = ($this->link_target != "")
			? $this->link_type."_".$this->link_target
			: $this->link_type;

//echo "<br><br>".$ltype;

		$select_ltype = ilUtil::formSelect ($ltype,
			"ltype", $ltypes, false, true);
		$tpl->setVariable("SELECT_TYPE", $select_ltype);
		$tpl->setVariable("CMD_CHANGETYPE", "changeLinkType");
		$tpl->setVariable("BTN_CHANGETYPE", $this->lng->txt("cont_change_type"));
		$tpl->setVariable("CMD_CLOSE", "closeLinkHelp");
		$tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));

		switch($this->link_type)
		{
			// page link
			case "PageObject":
				require_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($this->content_obj, true);

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
						switch ($this->mode)
						{
							case "link":
								require_once("content/classes/Media/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=PageObject".
									"&linktarget=il__pg_".$node["obj_id"].
									"&linktargetframe=".$link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
								$tpl->setCurrentBlock("chapter_row");
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("LINK_CHAPTER",
									"[iln page=\"".$node["obj_id"]."\"".$target_str."] [/iln]");
								$tpl->parseCurrentBlock();
								break;
						}
					}
					$tpl->setCurrentBlock("row");
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;

			// chapter link
			case "StructureObject":
				require_once("./content/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($this->content_obj, true);

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

						switch ($this->mode)
						{
							case "link":
								require_once("content/classes/Media/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", $css_row);
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=StructureObject".
									"&linktarget=il__st_".$node["obj_id"].
									"&linktargetframe=".$link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
								$tpl->setCurrentBlock("chapter_row");
								$tpl->setVariable("ROWCLASS", $css_row);
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK_CHAPTER",
									"[iln chap=\"".$node["obj_id"]."\"".$target_str."] [/iln]");
								$tpl->parseCurrentBlock();
								break;
						}
					}
					$tpl->setCurrentBlock("row");
					$tpl->parseCurrentBlock();
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

					switch ($this->mode)
					{
						case "link":
							require_once("content/classes/Media/class.ilObjMediaObjectGUI.php");
							ilObjMediaObjectGUI::_recoverParameters();
							$tpl->setCurrentBlock("link_row");
							$tpl->setVariable("ROWCLASS", "tblrow2");
							$tpl->setVariable("TXT_CHAPTER", $term["term"]);
							$tpl->setVariable("LINK",
								ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=GlossaryItem".
									"&linktarget=il__git_".$term["id"].
									"&linktargetframe=".$link_target));
							$tpl->parseCurrentBlock();
							break;

						default:
							$tpl->setCurrentBlock("chapter_row");
							$tpl->setVariable("ROWCLASS", $css_row);
							$tpl->setVariable("TXT_CHAPTER", $term["term"]);
							$tpl->setVariable("LINK_CHAPTER",
								"[iln term=\"".$term["id"]."\"".$target_str."]".$term["term"]."[/iln]");
							$tpl->parseCurrentBlock();
							$tpl->setCurrentBlock("row");
							$tpl->parseCurrentBlock();
							break;
					}
				}

				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;

			// media object
			case "Media":
				$tpl->setVariable("TARGET2", " target=\"content\" ");

				// content object id = 0 --> get clipboard objects
				if ($this->content_obj == 0)
				{
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
						switch ($this->mode)
						{
							case "link":
								require_once("content/classes/Media/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=MediaObject".
									"&linktarget=il__mob_".$obj["id"].
									"&linktargetframe=".$link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
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
								$tpl->setCurrentBlock("row");
								$tpl->parseCurrentBlock();
								break;
						}
					}
					$tpl->setCurrentBlock("chapter_list");
					$tpl->parseCurrentBlock();
				}
				break;

		}

		$tpl->show();
		exit;
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
			"target_type" => $a_type, "linkmode" => $_GET["linkmode"]));

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
		$exp->setFilterMode(IL_FM_POSITIVE);

		$exp->setClickable("cat", false);
		$exp->setClickable("grp", false);
		$exp->setClickable("crs", false);

		$exp->setFrameTarget("");
		$exp->setOutput(0);

		$output = $exp->getOutput();
//echo "<br><br><br>out:".$output.":<br>";

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
			"&target_type=".$a_type."&linkmode=".$_GET["linkmode"]);
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

}
?>
