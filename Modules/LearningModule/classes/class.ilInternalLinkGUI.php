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

require_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");

/**
* Class ilInternalLinkGUI
*
* Some gui methods to handle internal links
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilInternalLinkGUI
{
	var $default_type;
	var $default_obj; 
	var $link_type;
	var $link_target;
	var $lng;
	var $mode;			// "text" | "link"
	var $set_link_script;
	var $ctrl;
	var $tree;

	function ilInternalLinkGUI($a_default_type, $a_default_obj)
	{
		global $lng, $ilias, $ilCtrl, $tree;
		
		if (($_SESSION["il_link_cont_obj"] != "" && !$tree->isInTree($_SESSION["il_link_cont_obj"])) ||
			($_SESSION["il_link_glossary"] != "" && !$tree->isInTree($_SESSION["il_link_glossary"])) ||
			($_SESSION["il_link_mep"] != "" && !$tree->isInTree($_SESSION["il_link_mep"])))
		{
			$this->resetSessionVars();
		}

		$this->lng =& $lng;
		$this->tree =& $tree;
		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("linkmode", "target_type"));
		$this->default_type = $a_default_type;
		$this->default_obj = $a_default_obj;
		$this->filter_link_types = array();
		$this->mode = "text";

		// determine link type and target
		$this->determineLinkType();

		$def_type = ilObject::_lookupType($this->default_obj, true);

		// determine content object id
		switch($this->link_type)
		{
			case "PageObject":
			case "StructureObject":
				if  (empty($_SESSION["il_link_cont_obj"]) &&
					($def_type != "mep" && $def_type != "glo"))
				{
					$_SESSION["il_link_cont_obj"] = $this->default_obj;
				}
				break;

			case "GlossaryItem":
				if  (empty($_SESSION["il_link_glossary"]) && $def_type == "glo")
				{
					$_SESSION["il_link_glossary"] = $this->default_obj;
				}
				break;

			case "Media":
				if  (empty($_SESSION["il_link_mep"]) && $def_type == "mep")
				{
					$_SESSION["il_link_mep"] = $this->default_obj;
				}
				break;
		}

		/*
		$target_str = ($link_target == "")
			? ""
			: " target=\"".$link_target."\" ";*/
	}

	function determineLinkType()
	{
		// determine link type and target
		$ltype = ($_SESSION["il_link_type"] == "")
			? $this->default_type
			: $_SESSION["il_link_type"];
		$ltype_arr = explode("_", $ltype);
		$this->link_type = ($ltype_arr[0] == "")
			? $this->default_type
			: $ltype_arr[0];
		$this->link_target = $ltype_arr[1];
	}

	function setMode($a_mode = "text")
	{
		$this->mode = $a_mode;
	}

	function setSetLinkTargetScript($a_script)
	{
		$this->set_link_script = $a_script;
	}
	
	function setReturn($a_return)
	{
		$this->return = $a_return;
	}

	function getSetLinkTargetScript()
	{
		return $this->set_link_script;
	}

	function filterLinkType($a_link_type)
	{
		$this->filter_link_types[] = $a_link_type;
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd("showLinkHelp");
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	function resetSessionVars()
	{
		$_SESSION["il_link_mep"] = "";
		$_SESSION["il_link_mep_obj"] = "";
		$_SESSION["il_link_type"] = "";
	}
	
	function resetLinkList()
	{
		$this->resetSessionVars();
		$this->determineLinkType();
		$this->showLinkHelp();
	}

	function closeLinkHelp()
	{
		if ($this->return == "")
		{
			$this->ctrl->returnToParent($this);
		}
		else
		{
			ilUtil::redirect($this->return);
		}
	}

	
	function prepareJavascriptOutput($str)
	{
		global $ilUser;
		
		include_once("./Services/COPage/classes/class.ilPageEditorGUI.php");
		if (ilPageEditorGUI::_doJSEditing())
		{
			$str = htmlspecialchars($str);
		}
		return($str);
	}
	
	
	/**
	* show link help list
	*/
	function showLinkHelp()
	{
		global $ilUser;
		
		$target_str = ($this->link_target == "")
			? ""
			: " target=\"".$this->link_target."\"";

		if(($this->link_type == "GlossaryItem") &&
			empty($_SESSION["il_link_glossary"]))
		{
			$this->changeTargetObject("glo");
		}
		if(($this->link_type == "PageObject" || $this->link_type == "StructureObject") &&
			empty($_SESSION["il_link_cont_obj"]))
		{
			$this->changeTargetObject("cont_obj");
		}

		$tpl =& new ilTemplate("tpl.link_help.html", true, true, "Modules/LearningModule");
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		switch($this->link_type)
		{
			case "GlossaryItem":
				$this->ctrl->setParameter($this, "target_type", "glo");
				break;

			case "PageObject":
			case "StructureObject":
				$this->ctrl->setParameter($this, "target_type", "cont_obj");
				break;

			case "Media":
				$this->ctrl->setParameter($this, "target_type", "mep");
				break;

			default:
				break;
		}
//echo "<br><br>:".$this->ctrl->getFormAction($this).":";
//echo "<br>link_type:".$this->link_type;
//echo "<br>cont_obj:".$_SESSION["il_link_cont_obj"];
//echo "<br>link_mep".$_SESSION["il_link_mep"];
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("FORMACTION2", $this->ctrl->getFormAction($this));
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
			"Media_New" => $this->lng->txt("cont_lk_media_new"),
			"RepositoryItem" => $this->lng->txt("cont_repository_item")
			);

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
		
		if ($this->isEnabledJavaScript())
		{
			$tpl->setVariable("BTN_CLOSE_JS", $this->lng->txt("close"));
		}
		else 
		{
			$tpl->setVariable("CMD_CLOSE", "closeLinkHelp");
			$tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));
		}

		$chapterRowBlock = "chapter_row";
		if ($this->isEnabledJavaScript())
		{
			$chapterRowBlock .= "_js";
		}
		
		switch($this->link_type)
		{
			// page link
			case "PageObject":
				require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($_SESSION["il_link_cont_obj"], true);

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
								require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK_TARGET", "content");
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=PageObject".
									"&linktarget=il__pg_".$node["obj_id"].
									"&linktargetframe=".$this->link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
								$tpl->setCurrentBlock($chapterRowBlock);
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("LINK_CHAPTER",
									$this->prepareJavascriptOutput("[iln page=\"".$node["obj_id"]."\"".$target_str."] [/iln]"));
								$tpl->parseCurrentBlock();
								break;
						}
					}
					$tpl->setCurrentBlock("row");
					$tpl->parseCurrentBlock();
				}

				// get all free pages
				$pages = ilLMPageObject::getPageList($cont_obj->getId());
				$free_pages = array();
				foreach ($pages as $page)
				{
					if (!$ctree->isInTree($page["obj_id"]))
					{
						$free_pages[] = $page;
					}
				}
				if(count($free_pages) > 0)
				{
					$tpl->setCurrentBlock(str_replace("_js","",$chapterRowBlock));
					$tpl->setVariable("TXT_CHAPTER", $this->lng->txt("cont_free_pages"));
					$tpl->setVariable("ROWCLASS", "tblrow1");
					$tpl->parseCurrentBlock();

					foreach ($free_pages as $node)
					{
						switch ($this->mode)
						{
							case "link":
								require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK_TARGET", "content");
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=PageObject".
									"&linktarget=il__pg_".$node["obj_id"].
									"&linktargetframe=".$this->link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
								$tpl->setCurrentBlock($chapterRowBlock);
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("LINK_CHAPTER",
									$this->prepareJavascriptOutput("[iln page=\"".$node["obj_id"]."\"".$target_str."] [/iln]"));
								$tpl->parseCurrentBlock();
								break;
						}
					}
				}

				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();

				break;

			// chapter link
			case "StructureObject":
				require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
				$cont_obj =& new ilObjContentObject($_SESSION["il_link_cont_obj"], true);

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
						$css_row = ($css_row == "tblrow1")
							? "tblrow2"
							: "tblrow1";

						switch ($this->mode)
						{
							case "link":
								require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", $css_row);
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK_TARGET", "content");
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=StructureObject".
									"&linktarget=il__st_".$node["obj_id"].
									"&linktargetframe=".$this->link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
								$tpl->setCurrentBlock($chapterRowBlock);
								$tpl->setVariable("ROWCLASS", $css_row);
								$tpl->setVariable("TXT_CHAPTER", $node["title"]);
								$tpl->setVariable("LINK_CHAPTER",
									$this->prepareJavascriptOutput("[iln chap=\"".$node["obj_id"]."\"".$target_str."] [/iln]"));
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
				require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
				$glossary =& new ilObjGlossary($_SESSION["il_link_glossary"], true);

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
							require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
							ilObjMediaObjectGUI::_recoverParameters();
							$tpl->setCurrentBlock("link_row");
							$tpl->setVariable("ROWCLASS", "tblrow2");
							$tpl->setVariable("TXT_CHAPTER", $term["term"]);
							$tpl->setVariable("LINK_TARGET", "content");
							$tpl->setVariable("LINK",
								ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=GlossaryItem".
									"&linktarget=il__git_".$term["id"].
									"&linktargetframe=".$this->link_target));
							$tpl->parseCurrentBlock();
							break;

						default:
							$tpl->setCurrentBlock($chapterRowBlock);
							$tpl->setVariable("ROWCLASS", $css_row);
							$tpl->setVariable("TXT_CHAPTER", $term["term"]);
							$tpl->setVariable("LINK_CHAPTER",
											  $this->prepareJavascriptOutput("[iln term=\"".$term["id"]."\"".$target_str."]"." "."[/iln]"));
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
				//$tpl->setVariable("TARGET2", " target=\"content\" ");
				// content object id = 0 --> get clipboard objects
				if ($_SESSION["il_link_mep"] == 0)
				{
					$tpl->setCurrentBlock("change_cont_obj");
					$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
					$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
					$tpl->parseCurrentBlock();
					$mobjs = $this->ilias->account->getClipboardObjects("mob");
					// sort by name
					$objs = array();
					foreach ($mobjs as $obj)
					{
						$objs[$obj["title"].":".$obj["id"]] = $obj;
					}
					ksort($objs);
					$tpl->setCurrentBlock("chapter_list");
					$tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("cont_media_source"));
					$tpl->setVariable("TXT_CONT_TITLE", $this->lng->txt("cont_personal_clipboard"));
					$tpl->setVariable("COLSPAN", "2");

					foreach($objs as $obj)
					{
						switch ($this->mode)
						{
							case "link":
								require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
								ilObjMediaObjectGUI::_recoverParameters();
								$tpl->setCurrentBlock("link_row");
								$this->outputThumbnail($tpl, $obj["id"], "link");
								$tpl->setCurrentBlock("link_row");

								$tpl->setCurrentBlock("link_row");
								$tpl->setVariable("ROWCLASS", "tblrow2");
								$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
								$tpl->setVariable("LINK_TARGET", "content");
								$tpl->setVariable("LINK",
									ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
									"linktype=MediaObject".
									"&linktarget=il__mob_".$obj["id"].
									"&linktargetframe=".$this->link_target));
								$tpl->parseCurrentBlock();
								break;

							default:
								$tpl->setCurrentBlock($chapterRowBlock);
								$this->outputThumbnail($tpl, $obj["id"]);
								$tpl->setCurrentBlock($chapterRowBlock);
								$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
								$tpl->setVariable("ROWCLASS", "tblrow1");
								if (!empty($target_str))
								{
									$tpl->setVariable("LINK_CHAPTER",
										$this->prepareJavascriptOutput("[iln media=\"".$obj["id"]."\"".$target_str."] [/iln]"));
								}
								else
								{
									$tpl->setVariable("LINK_CHAPTER",
										$this->prepareJavascriptOutput("[iln media=\"".$obj["id"]."\"/]"));
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
				else
				{
					require_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
					$med_pool =& new ilObjMediaPool($_SESSION["il_link_mep"], true);

					// get current folders
					$fobjs = $med_pool->getChilds($_SESSION["il_link_mep_obj"], "fold");
					$f2objs = array();
					foreach ($fobjs as $obj)
					{
						$f2objs[$obj["title"].":".$obj["id"]] = $obj;
					}
					ksort($f2objs);

					// get current media objects
					$mobjs = $med_pool->getChilds($_SESSION["il_link_mep_obj"], "mob");
					$m2objs = array();
					foreach ($mobjs as $obj)
					{
						$m2objs[$obj["title"].":".$obj["id"]] = $obj;
					}
					ksort($m2objs);
					
					// merge everything together
					$objs = array_merge($f2objs, $m2objs);
					
					$tpl->setCurrentBlock("chapter_list");
					$tpl->setVariable("TXT_CONTENT_OBJECT", $this->lng->txt("mep"));
					$tpl->setVariable("TXT_CONT_TITLE", $med_pool->getTitle());
					$tpl->setCurrentBlock("change_cont_obj");
					$tpl->setVariable("CMD_CHANGE_CONT_OBJ", "changeTargetObject");
					$tpl->setVariable("BTN_CHANGE_CONT_OBJ", $this->lng->txt("change"));
					$tpl->setVariable("COLSPAN", "2");
					$tpl->parseCurrentBlock();

					if ($parent_id = $med_pool->getParentId($_SESSION["il_link_mep_obj"]))
					{
						$css_row = "tblrow1";
						$tpl->setCurrentBlock("icon");
						$tpl->setVariable("ICON_SRC", ilUtil::getImagePath("icon_fold.gif"));
						$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("link_row");
						$tpl->setVariable("ROWCLASS", $css_row);
						$tpl->setVariable("TXT_CHAPTER", "..");
						$this->ctrl->setParameter($this, "mep_fold", $parent_id);
						$tpl->setVariable("LINK",
							$this->ctrl->getLinkTarget($this, "setMedPoolFolder"));
						$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("row");
						$tpl->parseCurrentBlock();
					}

					foreach($objs as $obj)
					{
						if($obj["type"] == "fold")
						{
							$css_row = ($css_row == "tblrow2")
								? "tblrow1"
								: "tblrow2";
							$tpl->setCurrentBlock("icon");
							$tpl->setVariable("ICON_SRC", ilUtil::getImagePath("icon_fold.gif"));
							$tpl->parseCurrentBlock();
							$tpl->setCurrentBlock("link_row");
							$tpl->setVariable("ROWCLASS", $css_row);
							$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
							$this->ctrl->setParameter($this, "mep_fold", $obj["obj_id"]);
							$tpl->setVariable("LINK",
								$this->ctrl->getLinkTarget($this, "setMedPoolFolder"));
							$tpl->parseCurrentBlock();
						}
						else
						{
							$css_row = ($css_row == "tblrow2")
								? "tblrow1"
								: "tblrow2";
							switch ($this->mode)
							{
								case "link":
									require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
									ilObjMediaObjectGUI::_recoverParameters();
									$tpl->setCurrentBlock("link_row");
									$this->outputThumbnail($tpl, $obj["obj_id"], "link");
									$tpl->setCurrentBlock("link_row");
									$tpl->setVariable("ROWCLASS", $css_row);
									$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
									$tpl->setVariable("LINK_TARGET", "content");
									$tpl->setVariable("LINK",
										ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
										"linktype=MediaObject".
										"&linktarget=il__mob_".$obj["obj_id"].
										"&linktargetframe=".$this->link_target));
									$tpl->parseCurrentBlock();
									break;

								default:
									$tpl->setCurrentBlock($chapterRowBlock);
									$this->outputThumbnail($tpl, $obj["obj_id"]);
									$tpl->setCurrentBlock($chapterRowBlock);
									$tpl->setVariable("ROWCLASS", $css_row);
									$tpl->setVariable("TXT_CHAPTER", $obj["title"]);
									if ($target_str != "")
									{
										$tpl->setVariable("LINK_CHAPTER",
											$this->prepareJavascriptOutput("[iln media=\"".$obj["obj_id"]."\"".$target_str."] [/iln]"));
									}
									else
									{
										$tpl->setVariable("LINK_CHAPTER",
											$this->prepareJavascriptOutput("[iln media=\"".$obj["obj_id"]."\"/]"));
									}
									$tpl->parseCurrentBlock();
									break;
							}
						}
						$tpl->setCurrentBlock("row");
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("chapter_list");
					$tpl->parseCurrentBlock();
				}
				break;
				
			// repository item
			case "RepositoryItem":
				$tpl->setVariable("LINK_HELP_CONTENT", $this->selectRepositoryItem());
				break;

		}

		
		$tpl->show();
		exit;
	}
	
	
	/**
	* output thumbnail
	*/
	function outputThumbnail(&$tpl, $a_id, $a_mode = "")
	{
		// output thumbnail
		$mob =& new ilObjMediaObject($a_id);
		$med =& $mob->getMediaItem("Standard");
		$target = $med->getThumbnailTarget("small");
		if ($a_mode == "link")
		{
			$tpl->setCurrentBlock("thumbnail_link");
		}
		else if ($this->isEnabledJavaScript())
		{
			$tpl->setCurrentBlock("thumbnail_js");
		}
		else
		{
			$tpl->setCurrentBlock("thumbnail");
		}
		if ($target != "")
		{
			$tpl->setCurrentBlock("thumb");
			$tpl->setVariable("SRC_THUMB", $target);
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setVariable("NO_THUMB", "&nbsp;");
		}
		
		if ($a_mode == "link")
		{
			$tpl->setCurrentBlock("thumbnail_link");
		}
		else if ($this->isEnabledJavaScript())

		{
			$tpl->setCurrentBlock("thumbnail_js");
		}
		else
		{
			$tpl->setCurrentBlock("thumbnail");
		}
		$tpl->parseCurrentBlock();
	}


	/**
	* change link type
	*/
	function changeLinkType()
	{
		$_SESSION["il_link_type"] = $_POST["ltype"];
		$this->determineLinkType();
		$this->showLinkHelp();
	}

	/**
	* select media pool folder
	*/
	function setMedPoolFolder()
	{
		$_SESSION["il_link_mep_obj"] = $_GET["mep_fold"];
		$this->showLinkHelp();
	}

	/**
	* cange target object
	*/
	function changeTargetObject($a_type = "")
	{
		$_SESSION["il_link_mep_obj"] = "";

		if($_GET["do"] == "set")
		{
			switch ($_GET["target_type"])
			{
				case "glo":
					$_SESSION["il_link_glossary"] = $_GET["sel_id"];
					break;

				case "mep":
					$_SESSION["il_link_mep"] = $_GET["sel_id"];
					break;

				default:
					$_SESSION["il_link_cont_obj"] = $_GET["sel_id"];
					break;
			}
			$this->showLinkHelp();
			return;
		}

		if(empty($a_type))
		{
			$a_type = $_GET["target_type"];
		}

		$tpl =& new ilTemplate("tpl.link_help_explorer.html", true, true, "Modules/LearningModule");
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		require_once "classes/class.ilExplorer.php";
		//$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");


//echo "<br><br>:".$this->ctrl->getLinkTarget($this).":<br>";
		$exp = new ilExplorer(ilUtil::appendUrlParameterString(
			$this->ctrl->getTargetScript(), "do=set"));
		if ($_GET["expand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);
//echo "<br><br>exp:$expanded:<br>";

		$exp->setTargetGet("sel_id");
		$this->ctrl->setParameter($this, "target_type", $a_type);
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "changeTargetObject"));
//echo "<br>"; var_dump($this->ctrl->getParameterArray($this, "changeTargetObject"));
		/*$exp->setParamsGet(array("ref_id" => $_GET["ref_id"],
			"cmd" => "changeTargetObject", "mode" => "page_edit", "obj_id" => $_GET["obj_id"],
			"target_type" => $a_type, "linkmode" => $_GET["linkmode"]));*/

		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");

		switch ($a_type)
		{
			case "glo":
				$exp->addFilter("glo");
				break;

			case "mep":
				$exp->addFilter("mep");
				break;

			default:
				$exp->addFilter("lm");
				$exp->addFilter("dbk");
				break;
		}
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);

		$exp->setClickable("cat", false);
		$exp->setClickable("grp", false);
		$exp->setClickable("fold", false);
		$exp->setClickable("crs", false);

		$exp->setFrameTarget("");
		$exp->setOutput(0);

		$output = $exp->getOutput();
//echo "<br><br><br>out:".$output.":<br>";

		$tpl->setCurrentBlock("content");
		if ($a_type == "glo")
		{
			$tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_glossary"));
		}
		else if ($a_type == "mep")
		{
			$tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_media_source"));
		}
		else
		{
			$tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_choose_cont_obj"));
		}
		$tpl->setVariable("EXPLORER",$output);
		$tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("BTN_REFRESH", "changeTargetObject");
		$tpl->setVariable("TXT_REFRESH", $this->lng->txt("refresh"));
		$tpl->setVariable("BTN_RESET", "resetLinkList");
		$tpl->setVariable("TXT_RESET", $this->lng->txt("reset"));

		if ($a_type == "mep")
		{
			$tpl->setCurrentBlock("sel_clipboard");
			$this->ctrl->setParameter($this, "do", "set");
			//$this->ctrl->setParameter($this, "sel_id", 0);
			//$this->ctrl->setParameter($this, "target_type", "mep");
			//$this->ctrl->setParameter($this, "linkmode", "");
			//$this->ctrl->setParameter($this, "obj_id", "");
			$tpl->setVariable("LINK_CLIPBOARD", $this->ctrl->getLinkTarget($this, "changeTargetObject"));
			$tpl->setVariable("TXT_PERS_CLIPBOARD", $this->lng->txt("clipboard"));
			$tpl->parseCurrentBlock();
		}

		/*
		$tpl->setVariable("BTN_STRUCTURE", "resetLinkList");
		$tpl->setVariable("TXT_STRUCTURE", $this->lng->txt("reset"));*/
		$tpl->parseCurrentBlock();

		$tpl->show();
		exit;
	}
	
	/**
	* select repository item explorer
	*/
	function selectRepositoryItem()
	{
		$_SESSION["il_link_mep_obj"] = "";

		if(empty($a_type))
		{
			$a_type = $_GET["target_type"];
		}

		$tpl =& new ilTemplate("tpl.link_help_explorer.html", true, true, "Modules/LearningModule");
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		include_once "./Modules/LearningModule/classes/class.ilIntLinkRepItemExplorer.php";
		$exp = new ilIntLinkRepItemExplorer(ilUtil::appendUrlParameterString(
			$this->ctrl->getTargetScript(), "do=set"));
		if ($_GET["expand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setMode($this->mode);
		$exp->setSetLinkTargetScript($this->getSetLinkTargetScript());
		$exp->setExpand($expanded);

		$exp->setTargetGet("sel_id");
		$this->ctrl->setParameter($this, "target_type", $a_type);
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "showLinkHelp"));
		
		// filter
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);
		$exp->addFilter("root");
		$exp->addFilter("cat");
		$exp->addFilter("grp");
		$exp->addFilter("fold");
		$exp->addFilter("crs");
		$exp->addFilter("lm");
		$exp->addFilter("htlm");
		$exp->addFilter("dbk");
		$exp->addFilter("glo");
		$exp->addFilter("frm");
		$exp->addFilter("exc");
		$exp->addFilter("tst");
		$exp->addFilter("svy");
		$exp->addFilter("webr");
		$exp->addFilter("file");
		$exp->addFilter("chat");
		$exp->addFilter("sahs");
		$exp->addFilter("mcst");

		$sel_types = array('lm','dbk','htlm','glo','frm','exc','tst','svy','webr','chat',
			'cat','crs','grp','file','fold','sahs','mcst');
		$exp->setSelectableTypes($sel_types);

		/*
		$exp->setClickable("cat", false);
		$exp->setClickable("grp", false);
		$exp->setClickable("fold", false);
		$exp->setClickable("crs", false);*/

		$exp->setFrameTarget("");
		$exp->setOutput(0);

		$output = $exp->getOutput();
//echo "<br><br><br>out:".$output.":<br>";

		$tpl->setCurrentBlock("content");
		$tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_repository_item_links"));
		$tpl->setVariable("EXPLORER",$output);
		$tpl->setVariable("ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("BTN_REFRESH", "showLinkHelp");
		$tpl->setVariable("TXT_REFRESH", $this->lng->txt("refresh"));
		$tpl->setVariable("BTN_RESET", "resetLinkList");
		$tpl->setVariable("TXT_RESET", $this->lng->txt("reset"));

		/*
		$tpl->setVariable("BTN_STRUCTURE", "resetLinkList");
		$tpl->setVariable("TXT_STRUCTURE", $this->lng->txt("reset"));*/
		$tpl->parseCurrentBlock();

		return $tpl->get();
		//$tpl->show();
		//exit;
	}



	/**
	* determine, wether js is used
	*/
	function isEnabledJavaScript()
	{
		global $ilias;
		
		include_once("./Services/COPage/classes/class.ilPageEditorGUI.php");
		if($ilias->getSetting("enable_js_edit"))
		{
			if (ilPageEditorGUI::_doJSEditing())
			{
				return true;
			}
		}
		return false;
	}


}
?>
