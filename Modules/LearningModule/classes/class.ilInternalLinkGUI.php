<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	var $ltypes = array();
	
	function ilInternalLinkGUI($a_default_type, $a_default_obj)
	{
		global $lng, $ilias, $ilCtrl, $tree;

		$this->initLinkTypes();
		
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

	/**
	 * Initialize link types
	 */
	function initLinkTypes()
	{
		global $lng;
		
		$this->ltypes = array(
			"StructureObject" => $lng->txt("cont_lk_chapter"),
			"StructureObject_New" => $lng->txt("cont_lk_chapter_new"),
			"PageObject" => $lng->txt("cont_lk_page"),
			"PageObject_FAQ" => $lng->txt("cont_lk_page_faq"),
			"PageObject_New" => $lng->txt("cont_lk_page_new"),
			"GlossaryItem" => $lng->txt("cont_lk_term"),
			"GlossaryItem_New" => $lng->txt("cont_lk_term_new"),
			"Media" => $lng->txt("cont_lk_media_inline"),
			"Media_Media" => $lng->txt("cont_lk_media_media"),
			"Media_FAQ" => $lng->txt("cont_lk_media_faq"),
			"Media_New" => $lng->txt("cont_lk_media_new"),
			"File" => $lng->txt("cont_lk_file"),
			"RepositoryItem" => $lng->txt("cont_repository_item")
			);		
	}
	
	/**
	 * Determine current link type
	 */
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

	/**
	 * Set mode
	 */
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

	/**
	 * Set filter list as white list (per detault it is a black list)
	 *
	 * @return boolean white list
	 */
	function setFilterWhiteList($a_white_list)
	{
		$this->filter_white_list = $a_white_list;
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

	/**
	 * Prepare output for JS enabled editing
	 */
	function prepareJavascriptOutput($str)
	{
		global $ilUser;
		
		include_once("./Services/COPage/classes/class.ilPageEditorGUI.php");
		if (ilPageEditorGUI::_doJSEditing())
		{
			$str = htmlspecialchars($str, ENT_QUOTES);
		}
		return($str);
	}
	
	
	/**
	* Show link help list
	*/
	function showLinkHelp()
	{
		global $ilUser, $ilCtrl;
		
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

		if ($ilCtrl->isAsynch())
		{
			$tpl = new ilTemplate("tpl.link_help_asynch.html", true, true, "Modules/LearningModule");
		}
		else
		{
			$tpl =& new ilTemplate("tpl.link_help.html", true, true, "Modules/LearningModule");
			$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		}

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
//echo $this->ctrl->getFormAction($this, "", "", true);
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "changeLinkType", "", true));
		$tpl->setVariable("FORMACTION2", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_HELP_HEADER", $this->lng->txt("cont_link_select"));
		$tpl->setVariable("TXT_TYPE", $this->lng->txt("cont_link_type"));

		// filter link types
		if (!$this->filter_white_list)
		{
			foreach($this->filter_link_types as $link_type)
			{
				unset($this->ltypes[$link_type]);
			}
		}
		else
		{
			$ltypes = array();
			foreach($this->ltypes as $k => $l)
			{
				if (in_array($k, $this->filter_link_types))
				{
					$ltypes[$k] = $l;
				}
			}
			$this->ltypes = $ltypes;
		}

		$ltype = ($this->link_target != "")
			? $this->link_type."_".$this->link_target
			: $this->link_type;

//echo "<br><br>".$ltype;

		$select_ltype = ilUtil::formSelect ($ltype,
			"ltype", $this->ltypes, false, true, "0", "", array("id" => "ilIntLinkTypeSelector"));
		$tpl->setVariable("SELECT_TYPE", $select_ltype);
		$tpl->setVariable("CMD_CHANGETYPE", "changeLinkType");
		$tpl->setVariable("BTN_CHANGETYPE", $this->lng->txt("cont_change_type"));
		
/*		if ($this->isEnabledJavaScript())
		{
			$tpl->setVariable("BTN_CLOSE_JS", $this->lng->txt("close"));
		}
		else 
		{*/
			$tpl->setVariable("CMD_CLOSE", "closeLinkHelp");
			$tpl->setVariable("BTN_CLOSE", $this->lng->txt("close"));
//		}

		$chapterRowBlock = "chapter_row";
		$anchor_row_block = "anchor_link";
		if ($this->isEnabledJavaScript())
		{
			$chapterRowBlock .= "_js";
			$anchor_row_block .= "_js";
		}

		$obj_id = ilObject::_lookupObjId($_SESSION["il_link_cont_obj"]);
		$type = ilObject::_lookupType($obj_id);
		
		// switch link type
		switch($this->link_type)
		{
			// page link
			case "PageObject":
				if ($type == "lm")
				{
					require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
					$cont_obj =& new ilObjLearningModule($_SESSION["il_link_cont_obj"], true);
				}
				else if ($type == "dbk")
				{
					require_once("./Modules/LearningModule/classes/class.ilObjDlBook.php");
					$cont_obj =& new ilObjDlBook($_SESSION["il_link_cont_obj"], true);
				}

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
						$tpl->setCurrentBlock("row");
						$tpl->parseCurrentBlock();
					}

					if($node["type"] == "pg")
					{
						$this->renderLink($tpl, $node["title"], $node["obj_id"],
							"PageObject", "pg", "page",
							ilPageObject::_readAnchors($type, $node["obj_id"]));	
					}
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
						$this->renderLink($tpl, $node["title"], $node["obj_id"],
							"PageObject", "pg", "page",
							ilPageObject::_readAnchors($type, $node["obj_id"]));	
					}
				}

				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();

				break;

			// chapter link
			case "StructureObject":
			
				// check whether current object matchs to type
				if (!in_array(ilObject::_lookupType($_SESSION["il_link_cont_obj"], true),
					array("lm", "dbk")))
				{
					$this->changeTargetObject("lm");
				}

				if ($type == "lm")
				{
					require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
					$cont_obj = new ilObjLearningModule($_SESSION["il_link_cont_obj"], true);
				}
				else if ($type == "dbk")
				{
					require_once("./Modules/LearningModule/classes/class.ilObjDlBook.php");
					$cont_obj = new ilObjDlBook($_SESSION["il_link_cont_obj"], true);
				}

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
						$this->renderLink($tpl, $node["title"], $node["obj_id"],
							"StructureObject", "st", "chap");
					}
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
					$this->renderLink($tpl, $term["term"], $term["id"],
						"GlossaryItem", "git", "term");
				}
				
				$tpl->setCurrentBlock("chapter_list");
				$tpl->parseCurrentBlock();
				break;

			// media object
			case "Media":
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
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
						$this->renderLink($tpl, $obj["title"], $obj["id"],
							"MediaObject", "mob", "media");
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
						$f2objs[$obj["title"].":".$obj["child"]] = $obj;
					}
					ksort($f2objs);
					// get current media objects
					$mobjs = $med_pool->getChilds($_SESSION["il_link_mep_obj"], "mob");
					$m2objs = array();
					foreach ($mobjs as $obj)
					{
						$m2objs[$obj["title"].":".$obj["child"]] = $obj;
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
							$this->ctrl->setParameter($this, "mep_fold", $obj["child"]);
							$tpl->setVariable("LINK",
								$this->ctrl->getLinkTarget($this, "setMedPoolFolder"));
							$tpl->parseCurrentBlock();
						}
						else
						{
							$fid = ilMediaPoolItem::lookupForeignId($obj["child"]);
							if (ilObject::_lookupType($fid) == "mob")
							{
								$this->renderLink($tpl, $obj["title"], $fid,
									"MediaObject", "mob", "media");
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

			// file download link
			case "File":
				$tpl->setVariable("LINK_HELP_CONTENT", $this->getFileLinkHTML());
				break;
				
		}

		if ($ilCtrl->isAsynch())
		{
			echo $tpl->get();
			exit;
		}
		
		$tpl->show();
		exit;
	}
	
	/**
	 * Get HTML for file link
	 * @return	string		file link html
	 */
	function getFileLinkHTML()
	{
		global $ilCtrl, $lng;

		if (!is_object($this->uploaded_file))
		{
			$tpl = new ilTemplate("tpl.link_file.html", true, true, "Modules/LearningModule");
			$tpl->setCurrentBlock("form");
			$tpl->setVariable("FORM_ACTION",
				$ilCtrl->getFormAction($this, "saveFileLink", "", true));
			$tpl->setVariable("TXT_SELECT_FILE", $lng->txt("cont_select_file"));
			$tpl->setVariable("TXT_SAVE_LINK", $lng->txt("cont_create_link"));
			$tpl->setVariable("CMD_SAVE_LINK", "saveFileLink");
			include_once("./Services/Form/classes/class.ilFileInputGUI.php");
			$fi = new ilFileInputGUI("", "link_file");
			$fi->setSize(15);
			$tpl->setVariable("INPUT", $fi->getToolbarHTML());
			$tpl->parseCurrentBlock();
			return $tpl->get();
		}
		else
		{
			$tpl = new ilTemplate("tpl.link_file.html", true, true, "Modules/LearningModule");
			$tpl->setCurrentBlock("link_js");
//			$tpl->setVariable("LINK_FILE",
//				$this->prepareJavascriptOutput("[iln dfile=\"".$this->uploaded_file->getId()."\"] [/iln]")
//				);
			$tpl->setVariable("TAG_B",
				'[iln dfile=\x22'.$this->uploaded_file->getId().'\x22]');
			$tpl->setVariable("TAG_E",
				"[/iln]");
			$tpl->setVariable("TXT_FILE",
				$this->uploaded_file->getTitle());
			$tpl->parseCurrentBlock();
			return $tpl->get();
		}		
	}
	
	/**
	 * Save file link
	 */
	function saveFileLink()
	{
		$mtpl =& new ilTemplate("tpl.link_help.html", true, true, "Modules/LearningModule");
		$mtpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		if ($_FILES["link_file"]["name"] != "")
		{
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$fileObj = new ilObjFile();
			$fileObj->setType("file");
			$fileObj->setTitle($_FILES["link_file"]["name"]);
			$fileObj->setDescription("");
			$fileObj->setFileName($_FILES["link_file"]["name"]);
			$fileObj->setFileType($_FILES["link_file"]["type"]);
			$fileObj->setFileSize($_FILES["link_file"]["size"]);
			$fileObj->setMode("filelist");
			$fileObj->create();
			// upload file to filesystem
			$fileObj->createDirectory();
			$fileObj->raiseUploadError(false);
			$fileObj->getUploadFile($_FILES["link_file"]["tmp_name"],
				$_FILES["link_file"]["name"]);
			$this->uploaded_file = $fileObj;

		}
		$this->showLinkHelp();
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
		if ($this->getSetLinkTargetScript() != "")
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
		
		if ($this->getSetLinkTargetScript() != "")
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
	 * Cange target object
	 */
	function getTargetExplorer($a_type)
	{
		global $ilCtrl;

		include_once("./Services/IntLink/classes/class.ilLinkTargetObjectExplorer.php");
		$exp = new ilLinkTargetObjectExplorer(ilUtil::appendUrlParameterString(
			$ilCtrl->getTargetScript(), "do=set"));
		if ($_GET["expand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["expand"];
		}
		$exp->setExpand($expanded);
		$exp ->setAsynchExpanding(true);

		$exp->setTargetGet("sel_id");
		$ilCtrl->setParameter($this, "target_type", $a_type);
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "refreshTargetExplorer"));

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

		return $exp->getOutput();
	}

	/**
	 * Cange target object
	 */
	function changeTargetObject($a_type = "")
	{
		global $ilCtrl;

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

		$output = $this->getTargetExplorer($a_type);

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
		$tpl->setVariable("ACTION", $this->ctrl->getFormAction($this, "resetLinkList", "", true));
		$tpl->setVariable("BTN_RESET", "resetLinkList");
		$tpl->setVariable("TXT_RESET", $this->lng->txt("back"));

		if ($a_type == "mep")
		{
			$tpl->setCurrentBlock("sel_clipboard");
			$this->ctrl->setParameter($this, "do", "set");
			$tpl->setVariable("LINK_CLIPBOARD", $this->ctrl->getLinkTarget($this, "changeTargetObject"));
			$tpl->setVariable("TXT_PERS_CLIPBOARD", $this->lng->txt("clipboard"));
			$tpl->parseCurrentBlock();
		}

		$tpl->parseCurrentBlock();

		echo $tpl->get();
		exit;
	}

	/**
	 * Refresh target explorer
	 */
	function refreshTargetExplorer()
	{
		$output = $this->getTargetExplorer($_GET["target_type"]);
		echo $output;
		exit;
	}

	
	/**
	* select repository item explorer
	*/
	function selectRepositoryItem()
	{
		global $ilCtrl;

		$_SESSION["il_link_mep_obj"] = "";

		if(empty($a_type))
		{
			$a_type = $_GET["target_type"];
		}

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
		$exp->setParamsGet($this->ctrl->getParameterArray($this, "refreshRepositorySelector"));
		
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
		$exp->addFilter("wiki");
		$exp->addFilter("mep");

		$sel_types = array('lm','dbk','htlm','glo','frm','exc','tst','svy','webr','chat',
			'cat','crs','grp','file','fold','sahs','mcst','wiki','mep');
		$exp->setSelectableTypes($sel_types);


		$exp->setFrameTarget("");
		$exp->setOutput(0);
		$output = $exp->getOutput();

		return $output;
	}

	/**
	 * Refresh Repository Selector
	 */
	function refreshRepositorySelector()
	{
		$output = $this->selectRepositoryItem();
		echo $output;
		exit;
	}


	/**
	* determine, wether js is used
	*/
	function isEnabledJavaScript()
	{
		global $ilias;
		
		include_once("./Services/COPage/classes/class.ilPageEditorGUI.php");

//		if($ilias->getSetting("enable_js_edit"))
//		{
			if (ilPageEditorGUI::_doJSEditing())
			{
				return true;
			}
//		}
		return false;
	}


	/**
	 * Get initialisation HTML to use interna link editing
	 */
	function getInitHTML($a_url)
	{
		global $tpl;

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initPanel(false);
		ilYuiUtil::initConnection();
		ilYuiUtil::initDragDrop();
		$tpl->addJavaScript("./Services/Explorer/js/ilexplorercallback.js");
		$tpl->addJavascript("./Services/IntLink/js/ilIntLink.js");

		$ltpl = new ilTemplate("tpl.int_link_panel.html", true, true, "Services/IntLink");
		$ltpl->setVariable("IL_INT_LINK_URL", $a_url);
		return $ltpl->get();
	}

	/**
	 * Render internal link item
	 */
	function renderLink($tpl, $a_title, $a_obj_id, $a_type, $a_type_short, $a_bb_type,
		$a_anchors = array())
	{
		$chapterRowBlock = "chapter_row";
		$anchor_row_block = "anchor_link";
		if ($this->isEnabledJavaScript())
		{

			$chapterRowBlock .= "_js";
			$anchor_row_block .= "_js";
		}

		$target_str = ($this->link_target == "")
			? ""
			: " target=\"".$this->link_target."\"";

		if (count($a_anchors) > 0)
		{
			foreach ($a_anchors as $anchor)
			{
				$tpl->setCurrentBlock($anchor_row_block);
				$tpl->setVariable("TXT_LINK", "#".$anchor);
				$tpl->setVariable("HREF_LINK",
					$this->prepareJavascriptOutput("[iln ".$a_bb_type."=\"".$node["obj_id"]."\"".$target_str." anchor=\"$anchor\"] [/iln]"));
				$tpl->parseCurrentBlock();
			}
		}

		$this->css_row = ($this->css_row == "tblrow1")
			? "tblrow2"
			: "tblrow1";

		if ($this->getSetLinkTargetScript() != "")
		{
			require_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
			require_once("./Services/MediaObjects/classes/class.ilImageMapEditorGUI.php");
			ilImageMapEditorGUI::_recoverParameters();
			if ($a_type == "MediaObject")
			{
				$this->outputThumbnail($tpl, $a_obj_id);
			}
			$tpl->setCurrentBlock("link_row");
			$tpl->setVariable("ROWCLASS", $this->css_row);
			$tpl->setVariable("TXT_CHAPTER", $a_title);
			//$tpl->setVariable("LINK_TARGET", "content");
			$tpl->setVariable("LINK",
				ilUtil::appendUrlParameterString($this->getSetLinkTargetScript(),
				"linktype=".$a_type.
				"&linktarget=il__".$a_type_short."_".$a_obj_id.
				"&linktargetframe=".$this->link_target));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock($chapterRowBlock);
			if ($a_type == "MediaObject")
			{
				$this->outputThumbnail($tpl, $a_obj_id);
			}

			$tpl->setVariable("ROWCLASS", $this->css_row);
			$tpl->setVariable("TXT_CHAPTER", $a_title);
			if ($this->isEnabledJavaScript())
			{
//				$tpl->setVariable("TXT_CHAPTER_JS", htmlspecialchars(str_replace("'", "\'", $a_title)));
			}
			if ($a_type == "MediaObject" && empty($target_str))
			{
				$tpl->setVariable("LINK_BEGIN",
					$this->prepareJavascriptOutput("[iln ".$a_bb_type."=\"".$a_obj_id."\"/]"));
				$tpl->setVariable("LINK_END", "");
			}
			else
			{
				$tpl->setVariable("LINK_BEGIN",
					$this->prepareJavascriptOutput("[iln ".$a_bb_type."=\"".$a_obj_id."\"".$target_str."]"));
				$tpl->setVariable("LINK_END", "[/iln]");
			}
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

	}

}
?>
