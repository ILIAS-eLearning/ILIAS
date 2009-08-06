<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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

require_once("classes/class.ilObjectGUI.php");
require_once("./Modules/Glossary/classes/class.ilObjGlossary.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryTermGUI.php");
require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
require_once("./Modules/Glossary/classes/class.ilTermDefinitionEditorGUI.php");
require_once("./Services/COPage/classes/class.ilPCParagraph.php");

/**
* Class ilGlossaryPresentationGUI
*
* GUI class for glossary presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilGlossaryPresentationGUI: ilNoteGUI, ilInfoScreenGUI
*
* @ingroup ModulesGlossary
*/
class ilGlossaryPresentationGUI
{
	var $admin_tabs;
	var $glossary;
	var $ilias;
	var $tpl;
	var $lng;

	/**
	* Constructor
	* @access	public
	*/
	function ilGlossaryPresentationGUI()
	{
		global $lng, $ilias, $tpl, $ilCtrl;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->offline = false;
		$this->ctrl->saveParameter($this, array("ref_id"));

		// Todo: check lm id
		include_once("./Modules/Glossary/classes/class.ilObjGlossaryGUI.php");
		$this->glossary_gui =& new ilObjGlossaryGUI("", $_GET["ref_id"], true, "");
		$this->glossary =& $this->glossary_gui->object;

	}
	
	
	/**
	* set offline mode (content is generated for offline package)
	*/
	function setOfflineMode($a_offline = true)
	{
		$this->offline = $a_offline;
	}
	
	
	/**
	* checks wether offline content generation is activated 
	*/
	function offlineMode()
	{
		return $this->offline;
	}

	/**
	* Set offline directory.
	*/
	function setOfflineDirectory($a_dir)
	{
		$this->offline_dir = $a_dir;
	}
	
	
	/**
	* Get offline directory.
	*/
	function getOfflineDirectory()
	{
		return $this->offline_dir;
	}


	/**
	* executer command ("listTerms" | "listDefinitions")
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $ilias;
		
		$lng->loadLanguageModule("content");

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("listTerms");

		// check write permission
		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]) &&
			!($ilAccess->checkAccess("visible", "", $_GET["ref_id"]) &&
				($cmd == "infoScreen" || strtolower($next_class) == "ilinfoscreengui")))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		if ($cmd != "listDefinitions")
		{
			$this->prepareOutput();
		}

		switch($next_class)
		{
			case "ilnotegui":
				$this->setTabs();
				$ret =& $this->listDefinitions();
				break;
				
			case "ilinfoscreengui":
				$ret =& $this->outputInfoScreen();
				break;
				
			default: 
				$ret =& $this->$cmd();
				break;
		}

		$this->tpl->show();
	}

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->glossary->getTitle();

		$this->tpl->setTitle($title);
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo_b.gif"));

		$this->setLocator();
	}

	function clearTerms()
	{
		$_REQUEST["term"] = "";
		$_GET["offset"] = $_GET["oldoffset"];
		$this->searchTerms();
	}
	
	function searchTerms ()
	{
		$term_list = $this->glossary->getTermList($_REQUEST["term"]);
		$this->listTermByGiven($term_list, $_REQUEST["term"]);
	}


	/**
	 * List all terms
	 */
	function listTerms()
	{
		global $ilNavigationHistory, $ilAccess, $ilias, $lng;

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}
		
		if (!$this->offlineMode())
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				$this->ctrl->getLinkTarget($this, "listTerms"), "glo");
		}
		
		$term_list = $this->glossary->getTermList();		
		
		return $this->listTermByGiven($term_list);
	}

	/**
	* list glossary terms
	*/
	function listTermByGiven($term_list, $filter ="")
	{
		global $ilCtrl, $ilAccess, $ilias, $lng;
		
		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule("meta");
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		$this->setTabs();
		
		// load template for table
//		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		
		$oldoffset = (is_numeric ($_GET["oldoffset"]))?$_GET["oldoffset"]:$_GET["offset"];

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_presentation.html", "Modules/Glossary");
		
		// search form
		if (!$this->offlineMode())
		{
			$this->tpl->setCurrentBlock("search_form");
			$this->ctrl->setParameter($this, "offset", 0);
			$this->ctrl->setParameter($this, "oldoffset", $oldoffset);
			$this->tpl->setVariable("FORMACTION1",
				$this->ctrl->getFormAction($this, "searchTerms"));
			$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
			$this->tpl->setVariable("TXT_SEARCH", $this->lng->txt("search"));
			$this->tpl->setVariable("TXT_CLEAR", $this->lng->txt("clear"));
			$this->tpl->setVariable("TERM", $filter);
			$this->tpl->parseCurrentBlock();
		}
		

		// load template for table
		$this->tpl->addBlockfile("TERM_TABLE", "term_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.term_tbl_pres_row.html", "Modules/Glossary");

		$num = 2;

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_terms").(($filter=="")?"":"*"));
		$tbl->disable("sort");
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		// display additional column 'glossary' for meta glossaries
		if ($this->glossary->isVirtual())
		{
			$tbl->setHeaderNames(array($this->lng->txt("cont_term"),
				 $this->lng->txt("cont_definitions"),$this->lng->txt("obj_glo")));

			$cols = array("term", "definitions", "glossary");
			
			$tbl->setColumnWidth(array("30%", "35%", "35%"));
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("cont_term"),
				 $this->lng->txt("cont_definitions")));
	
			$cols = array("term", "definitions");
			
			$tbl->setColumnWidth(array("30%", "70%"));
		}
		
		if (!$this->offlineMode())
		{
			$header_params = $this->ctrl->getParameterArrayByClass("ilglossarypresentationgui", "listTerms");
		}
		//$header_params = array("ref_id" => $_GET["ref_id"], "cmd" => "listTerms");

		if (!empty ($filter)) {
			$header_params ["cmd"] = "searchTerms";
			$header_params ["term"] = $filter;
			$header_params ["oldoffset"] = $_GET["oldoffset"];
		}

		$tbl->setHeaderVars($cols, $header_params);

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		if ($this->offlineMode())
		{
			$_GET["limit"] = 99999;
			$_GET["offset"] = 0;
			$tbl->disable("sort");
			$tbl->disable("footer");
		}
		$tbl->setOffset($_GET["offset"]);
		$tbl->setLimit($_GET["limit"]);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

//		$term_list = $this->glossary->getTermList();
		$tbl->setMaxCount(count($term_list));

		// sorting array
		//$term_list = ilUtil::sortArray($term_list, $_GET["sort_by"], $_GET["sort_order"]);

		$term_list = array_slice($term_list, $_GET["offset"], $_GET["limit"]);
		// render table
		
		$tbl->setBase("ilias.php");
		$tbl->render();

		if (count($term_list) > 0)
		{
			$i=1;
			foreach($term_list as $key => $term)
			{
				$css_row = ilUtil::switchColor($i++,"tblrow1","tblrow2");
				$defs = ilGlossaryDefinition::getDefinitionList($term["id"]);

				for($j=0; $j<count($defs); $j++)
				{
					$def = $defs[$j];
					if (count($defs) > 1)
					{
						$this->tpl->setCurrentBlock("definition");
						$this->tpl->setVariable("DEF_TEXT", $this->lng->txt("cont_definition")." ".($j + 1));
						$this->tpl->parseCurrentBlock();
					}

					//
					$this->tpl->setCurrentBlock("definition");
					$short_str = $def["short_text"];
					// replace tex
					// if a tex end tag is missing a tex end tag
					$ltexs = strrpos($short_str, "[tex]");
					$ltexe = strrpos($short_str, "[/tex]");
					if ($ltexs > $ltexe)
					{
						$page =& new ilPageObject("gdf", $def["id"]);
						$page->buildDom();
						$short_str = $page->getFirstParagraphText();
						$short_str = strip_tags($short_str, "<br>");
						$ltexe = strpos($short_str, "[/tex]", $ltexs);
						$short_str = ilUtil::shortenText($short_str, $ltexe+6, true);
					}
					if (!$this->offlineMode())
					{
						$short_str = ilUtil::insertLatexImages($short_str);
					}
					else
					{
						$short_str = ilUtil::buildLatexImages($short_str,
							$this->getOfflineDirectory());
					}
					$short_str = ilPCParagraph::xml2output($short_str);
					
					$this->tpl->setVariable("DEF_SHORT", $short_str);
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("definition_row");
					$this->tpl->parseCurrentBlock();
				}
				
				// display additional column 'glossary' for meta glossaries
				if ($this->glossary->isVirtual())
				{
					$this->tpl->setCurrentBlock("glossary_row");
					$glo_title = ilObject::_lookupTitle($term["glo_id"]);
					$this->tpl->setVariable("GLO_TITLE", $glo_title);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("view_term");
				$this->tpl->setVariable("TEXT_TERM", $term["term"]);
				if (!$this->offlineMode())
				{
					if (!empty ($filter))
					{
						$this->ctrl->setParameter($this, "term", $filter);
						$this->ctrl->setParameter($this, "oldoffset", $_GET["oldoffset"]);
					}
					$this->ctrl->setParameter($this, "term_id", $term["id"]);
					$this->ctrl->setParameter($this, "offset", $_GET["offset"]);
					$this->tpl->setVariable("LINK_VIEW_TERM",
						$this->ctrl->getLinkTarget($this, "listDefinitions"));
					$this->ctrl->clearParameters($this);
				}
				else
				{
					$this->tpl->setVariable("LINK_VIEW_TERM", "term_".$term["id"].".html");
				}
				$this->tpl->setVariable("ANCHOR_TERM", "term_".$term["id"]);
				$this->tpl->parseCurrentBlock();

				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->setVariable("TEXT_LANGUAGE", $this->lng->txt("meta_l_".$term["language"]));
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->parseCurrentBlock();
				
				$this->ctrl->clearParameters($this);
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}
		
		// edit link
		if (!$this->offlineMode() && $ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->tpl->setCurrentBlock("edit_glossary");
			$this->tpl->setVariable("EDIT_TXT", $this->lng->txt("edit"));
			$this->tpl->setVariable("EDIT_LINK",
				"ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=".$_GET["ref_id"]);
			$this->tpl->setVariable("EDIT_TARGET", "_top");
			$this->tpl->parseCurrentBlock();
		}
		
		// permanent link
		$this->tpl->setCurrentBlock("perma_link");
		$this->tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=glo_".$_GET["ref_id"]."&client_id=".CLIENT_ID);
		$this->tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$this->tpl->setVariable("PERMA_TARGET", "_top");
		$this->tpl->parseCurrentBlock();

		if ($this->offlineMode())
		{
			return $this->tpl->get();
		}
	}

	/**
	* list definitions of a term
	*/
	function listDefinitions()
	{
		global $ilUser, $ilAccess, $ilias, $lng;
		
		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}

		require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		//$this->setLocator();
		$this->setTabs();
		
		if ($this->offlineMode())
		{
			$style_name = $ilUser->prefs["style"].".css";;
			$this->tpl->setVariable("LOCATION_STYLESHEET","./".$style_name);
		}
		else
		{
			$this->setLocator();
		}

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		if (!$this->offlineMode())
		{
			$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
				ilObjStyleSheet::getContentStylePath(0));
		}
		else
		{
			$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET","content.css");
		}
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		if (!$this->offlineMode())
		{
			$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
				ilObjStyleSheet::getSyntaxStylePath());
		}
		else
		{
			$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
				"syntaxhighlight.css");
		}
		$this->tpl->parseCurrentBlock();

		$term =& new ilGlossaryTerm($_GET["term_id"]);
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_term_b.gif"));
		$this->tpl->setTitle($this->lng->txt("cont_term").": ".$term->getTerm());

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_list.html", "Modules/Glossary");
		//$this->tpl->addBlockfile("STATUSLINE", "statusline", "tpl.statusline.html");

		$defs = ilGlossaryDefinition::getDefinitionList($_GET["term_id"]);

		$this->tpl->setVariable("TXT_TERM", $term->getTerm());
		$this->mobs = array();

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page_gui =& new ilPageObjectGUI("gdf", $def["id"]);
			$page = $page_gui->getPageObject();

			// internal links
			$page->buildDom();
			$int_links = $page->getInternalLinks();
			$link_xml = $this->getLinkXML($int_links);
			$page_gui->setLinkXML($link_xml);

			if ($this->offlineMode())
			{
				$page_gui->setOutputMode("offline");
				$page_gui->setOfflineDirectory($this->getOfflineDirectory());
			}
			$page_gui->setSourcecodeDownloadScript($this->getLink($_GET["ref_id"]));
			$page_gui->setFullscreenLink($this->getLink($_GET["ref_id"], "fullscreen", $_GET["term_id"], $def["id"]));

			$page_gui->setTemplateOutput(false);
			$page_gui->setRawPageContent(true);
			$page_gui->setFileDownloadLink($this->getLink($_GET["ref_id"], "downloadFile"));
			if (!$this->offlineMode())
			{
				$output = $page_gui->preview();
			}
			else
			{
				$output = $page_gui->presentation($page_gui->getOutputMode());
			}

			if (count($defs) > 1)
			{
				$this->tpl->setCurrentBlock("definition_header");
				$this->tpl->setVariable("TXT_DEFINITION",
					$this->lng->txt("cont_definition")." ".($j+1));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			$this->tpl->parseCurrentBlock();
		}
		
		// display possible backlinks
		$sources = ilInternalLink::_getSourcesOfTarget('git',$_GET['term_id'],0);
		
		if ($sources)
		{
			$backlist_shown = false;
			foreach ($sources as $src)
			{
				$type = explode(':',$src['type']);
				
				if ($type[0] == 'lm')
				{
					if ($type[1] == 'pg')
					{
						$title = ilLMPageObject::_getPresentationTitle($src['id']);
						$lm_id = ilLMObject::_lookupContObjID($src['id']);
						$lm_title = ilObject::_lookupTitle($lm_id);
						$this->tpl->setCurrentBlock('backlink_item');
						$ref_ids = ilObject::_getAllReferences($lm_id);
						$access = false;
						foreach($ref_ids as $rid)
						{
							if ($ilAccess->checkAccess("read", "", $rid))
							{
								$access = true;
							}
						}
						if ($access)
						{
							$this->tpl->setCurrentBlock("backlink_item");
							$this->tpl->setVariable("BACKLINK_LINK",ILIAS_HTTP_PATH."/goto.php?target=".$type[1]."_".$src['id']);
							$this->tpl->setVariable("BACKLINK_ITEM",$lm_title.": ".$title);
							$this->tpl->parseCurrentBlock();
							$backlist_shown = true;
						}
					}
				}
			}
			if ($backlist_shown)
			{
				$this->tpl->setCurrentBlock("backlink_list");
				$this->tpl->setVariable("BACKLINK_TITLE",$this->lng->txt('glo_term_used_in'));
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->tpl->setCurrentBlock("perma_link");
		$this->tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=".
			"git".
			"_".$_GET["term_id"]."_".$_GET["ref_id"]."&client_id=".CLIENT_ID);
		$this->tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$this->tpl->setVariable("PERMA_TARGET", "_top");
		$this->tpl->parseCurrentBlock();

		
		if ($this->offlineMode())
		{
//echo "<br>glo_pres_return";
			return $this->tpl->get();
		}
	}
	

	/**
	* show fullscreen view
	*/
	function fullscreen()
	{
		$html = $this->media("fullscreen");
		return $html;
	}

	/**
	* show media object
	*/
	function media($a_mode = "media")
	{
		$this->tpl =& new ilTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));

		//$int_links = $page_object->getInternalLinks();
		$med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);

		// later
		//$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());

		$link_xlm = "";

		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$media_obj =& new ilObjMediaObject($_GET["mob_id"]);

		$xml = "<dummy>";
		// todo: we get always the first alias now (problem if mob is used multiple
		// times in page)
		$xml.= $media_obj->getXML(IL_MODE_ALIAS);
		$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
		$xml.= $link_xml;
		$xml.="</dummy>";

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

		if (!$this->offlineMode())
		{
			$enlarge_path = ilUtil::getImagePath("enlarge.gif", false, "output");
			$wb_path = ilUtil::getWebspaceDir("output");
		}
		else
		{
			$enlarge_path = "images/enlarge.gif";
			$wb_path = ".";
		}

		$mode = $a_mode;

		$this->ctrl->setParameter($this, "obj_type", "MediaObject");
		$fullscreen_link =
			$this->getLink($_GET["ref_id"], "fullscreen");
		$this->ctrl->clearParameters($this);

		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => $fullscreen_link,
			'ref_id' => $_GET["ref_id"], 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);

		$this->tpl->parseCurrentBlock();
		if ($this->offlineMode())
		{
			$html = $this->tpl->get();
			return $html;
		}

	}

	/**
	* show download list
	*/
	function showDownloadList()
	{
		global $ilBench, $ilAccess, $ilias, $lng;

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.glo_download_list.html", "Modules/Glossary");

		$this->setTabs();
		
		// set title header
		$this->tpl->setTitle($this->glossary->getTitle());
		//$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_glo_b.gif"));
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_glo_b.gif"));

		// create table
		require_once("./Services/Table/classes/class.ilTableGUI.php");
		$tbl = new ilTableGUI();

		// load files templates
		$this->tpl->addBlockfile("DOWNLOAD_TABLE", "download_table", "tpl.table.html");

		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.download_file_row.html", "Modules/Glossary");

		$export_files = array();
		$types = array("xml", "html");
		foreach($types as $type)
		{
			if ($this->glossary->getPublicExportFile($type) != "")
			{
				$dir = $this->glossary->getExportDirectory($type);
				if (is_file($this->glossary->getExportDirectory($type)."/".
					$this->glossary->getPublicExportFile($type)))
				{
					$size = filesize($this->glossary->getExportDirectory($type)."/".
						$this->glossary->getPublicExportFile($type));
					$export_files[] = array("type" => $type,
						"file" => $this->glossary->getPublicExportFile($type),
						"size" => $size);
				}
			}
		}
		
		$num = 0;
		
		$tbl->setTitle($this->lng->txt("download"));

		$tbl->setHeaderNames(array($this->lng->txt("cont_format"),
			$this->lng->txt("cont_file"),
			$this->lng->txt("size"), $this->lng->txt("date"),
			""));

		$cols = array("format", "file", "size", "date", "download");
		$header_params = array("ref_id" => $_GET["ref_id"], "obj_id" => $_GET["obj_id"],
			"cmd" => "showDownloadList", "cmdClass" => strtolower(get_class($this)));
		$tbl->setHeaderVars($cols, $header_params);
		$tbl->setColumnWidth(array("10%", "30%", "20%", "20%","20%"));
		$tbl->disable("sort");

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);		// ???

		$this->tpl->setVariable("COLUMN_COUNTS", 5);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		$tbl->setMaxCount(count($export_files));
		$export_files = array_slice($export_files, $_GET["offset"], $_GET["limit"]);

		$tbl->render();
		if(count($export_files) > 0)
		{
			$i=0;
			foreach($export_files as $exp_file)
			{
				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("TXT_FILENAME", $exp_file["file"]);

				$css_row = ilUtil::switchColor($i++, "tblrow1", "tblrow2");
				$this->tpl->setVariable("CSS_ROW", $css_row);

				$this->tpl->setVariable("TXT_SIZE", $exp_file["size"]);
				$this->tpl->setVariable("TXT_FORMAT", strtoupper($exp_file["type"]));
				$this->tpl->setVariable("CHECKBOX_ID", $exp_file["type"].":".$exp_file["file"]);

				$file_arr = explode("__", $exp_file["file"]);
				$this->tpl->setVariable("TXT_DATE", date("Y-m-d H:i:s",$file_arr[0]));

				$this->tpl->setVariable("TXT_DOWNLOAD", $this->lng->txt("download"));
				$this->ctrl->setParameter($this, "type", $exp_file["type"]);
				$this->tpl->setVariable("LINK_DOWNLOAD",
					$this->ctrl->getLinkTarget($this, "downloadExportFile"));
					
				$this->tpl->parseCurrentBlock();
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", 5);
			$this->tpl->parseCurrentBlock();
		}

		//$this->tpl->show();
	}

	/**
	* send download file (xml/html)
	*/
	function downloadExportFile()
	{
		global $ilAccess, $ilias, $lng;
		
		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}

		$file = $this->glossary->getPublicExportFile($_GET["type"]);
		if ($this->glossary->getPublicExportFile($_GET["type"]) != "")
		{
			$dir = $this->glossary->getExportDirectory($_GET["type"]);
			if (is_file($dir."/".$file))
			{
				ilUtil::deliverFile($dir."/".$file, $file);
				exit;
			}
		}
		$this->ilias->raiseError($this->lng->txt("file_not_found"),$this->ilias->error_obj->MESSAGE);
	}

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "")
	{
		global $ilias_locator;

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		require_once ("./Modules/Glossary/classes/class.ilGlossaryLocatorGUI.php");
		$gloss_loc =& new ilGlossaryLocatorGUI();
		$gloss_loc->setMode("presentation");
		if (!empty($_GET["term_id"]))
		{
			$term =& new ilGlossaryTerm($_GET["term_id"]);
			$gloss_loc->setTerm($term);
		}
		$gloss_loc->setGlossary($this->glossary);
		//$gloss_loc->setDefinition($this->definition);
		$gloss_loc->display();
	}

	/**
	* download file of file lists
	*/
	function downloadFile()
	{
		global $ilAccess, $ilias, $lng;
		
		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}

		$file = explode("_", $_GET["file_id"]);
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		global $ilTabs;
		// catch feedback message
		#include_once("classes/class.ilTabsGUI.php");
		#$tabs_gui =& new ilTabsGUI();
		$this->getTabs($ilTabs);

		#$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

	}

	/**
	* get link targets
	*/
	function getLinkXML($a_int_links)
	{

		if ($a_layoutframes == "")
		{
			$a_layoutframes = array();
		}
		$link_info = "<IntLinkInfos>";
		foreach ($a_int_links as $int_link)
		{
//echo "<br>+".$int_link["Type"]."+".$int_link["TargetFrame"]."+".$int_link["Target"]."+";
			$target = $int_link["Target"];
			if (substr($target, 0, 4) == "il__")
			{
				$target_arr = explode("_", $target);
				$target_id = $target_arr[count($target_arr) - 1];
				$type = $int_link["Type"];
				$targetframe = ($int_link["TargetFrame"] != "")
					? $int_link["TargetFrame"]
					: "None";
					
				// anchor
				$anc = $anc_add = "";
				if ($int_link["Anchor"] != "")
				{
					$anc = $int_link["Anchor"];
					$anc_add = "_".rawurlencode($int_link["Anchor"]);
				}

				if ($targetframe == "New")
				{
					$ltarget = "_blank";
				}
				else
				{
					$ltarget = "";
				}

				switch($type)
				{
					case "PageObject":
					case "StructureObject":
						$lm_id = ilLMObject::_lookupContObjID($target_id);
						$cont_obj =& $this->content_object;
						if ($type == "PageObject")
						{
							$href = "./goto.php?target=pg_".$target_id.$anc_add;
						}
						else
						{
							$href = "./goto.php?target=st_".$target_id;
						}
						//$ltarget = "ilContObj".$lm_id;
						break;

					case "GlossaryItem":
						if (ilGlossaryTerm::_lookGlossaryID($target_id) == $this->glossary->getId())
						{
							if ($this->offlineMode())
							{
								$href = "term_".$target_id.".html";
							}
							else
							{
								$this->ctrl->setParameter($this, "term_id", $target_id);
								$href = $this->ctrl->getLinkTarget($this, "listDefinitions");
								$href = str_replace("&", "&amp;", $href);
							}
						}
						else
						{
							$href = "./goto.php?target=git_".$target_id;
						}
						break;

					case "MediaObject":
						if ($this->offlineMode())
						{
							$href = "media_".$target_id.".html";
						}
						else
						{
							$this->ctrl->setParameter($this, "obj_type", $type);
							$this->ctrl->setParameter($this, "mob_id", $target_id);
							$href = $this->ctrl->getLinkTarget($this, "media");
							$href = str_replace("&", "&amp;", $href);
						}
						break;

					case "RepositoryItem":
						$obj_type = ilObject::_lookupType($target_id, true);
						$obj_id = ilObject::_lookupObjId($target_id);
						$href = "./goto.php?target=".$obj_type."_".$target_id;
						$t_frame = ilFrameTargetInfo::_getFrame("MainContent", $obj_type);
						$ltarget = $t_frame;
						break;

				}
				
				$anc_par = 'Anchor="'.$anc.'"';
				
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" $anc_par/>";
				
				$this->ctrl->clearParameters($this);
			}
		}
		$link_info.= "</IntLinkInfos>";

		return $link_info;
	}


	/**
	* handles links for learning module presentation
	*/
	function getLink($a_ref_id, $a_cmd = "", $a_term_id = "", $a_def_id = "",
		$a_frame = "", $a_type = "")
	{
		if ($a_cmd == "")
		{
			$a_cmd = "layout";
		}
		//$script = "glossary_presentation.php";

		// handle online links
		if (!$this->offlineMode())
		{
			//$link = $script."?ref_id=".$a_ref_id;
			switch ($a_cmd)
			{
				case "fullscreen":
					$this->ctrl->setParameter($this, "def_id", $a_def_id);
					$link = $this->ctrl->getLinkTarget($this, "fullscreen");
					$link = str_replace("&", "&amp;", $link);
					break;
				
				default:
					$link.= "&amp;cmd=".$a_cmd;
					if ($a_frame != "")
					{
						$this->ctrl->setParameter($this, "frame", $a_frame);
					}
					if ($a_obj_id != "")
					{
						switch ($a_type)
						{
							case "MediaObject":
								$this->ctrl->setParameter($this, "mob_id", $a_obj_id);
								break;
								
							default:
								$this->ctrl->setParameter($this, "def_id", $a_def_id);
								break;
						}
					}
					if ($a_type != "")
					{
						$this->ctrl->setParameter($this, "obj_type", $a_type);
					}
					$link = $this->ctrl->getLinkTarget($this, $a_cmd);
					$link = str_replace("&", "&amp;", $link);
					break;
			}
		}
		else	// handle offline links
		{
			switch ($a_cmd)
			{
				case "downloadFile":
					break;
					
				case "fullscreen":
					$link = "fullscreen.html";		// id is handled by xslt
					break;
					
				case "layout":
					break;
					
				case "glossary":
					$link = "term_".$a_obj_id.".html";
					break;
				
				case "media":
					$link = "media_".$a_obj_id.".html";
					break;
					
				default:
					break;
			}
		}
		$this->ctrl->clearParameters($this);
		return $link;
	}


	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess;
		
		$oldoffset = (is_numeric ($_GET["oldoffset"]))?$_GET["oldoffset"]:$_GET["offset"];
		
		if (!$this->offlineMode())
		{
			if ($this->ctrl->getCmd() != "listDefinitions")
			{
				if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
				{
					$tabs_gui->addTarget("cont_terms",
						$this->ctrl->getLinkTarget($this, "listTerms"),
						array("listTerms", "searchTerms", "clearTerms", ""),
						"");
				}
	
				$force_active = false;
				if ($this->ctrl->getCmd() == "showSummary" ||
					strtolower($this->ctrl->getNextClass()) == "ilinfoscreengui")
				{
					$force_active = true;
				}
				$tabs_gui->addTarget("information_abbr",
					$this->ctrl->getLinkTarget($this, "infoScreen"), array("infoScreen"),
					"ilInfoScreenGUI", "", $force_active);
	
				// glossary menu
				if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
				{
					if ($this->glossary->isActiveGlossaryMenu())
					{
						// download links
						if ($this->glossary->isActiveDownloads())
						{
							$tabs_gui->addTarget("download",
								$this->ctrl->getLinkTarget($this, "showDownloadList"), "showDownloadList",
								"");
						}
					}
				}
			}
			else
			{
				$this->ctrl->setParameter($this, "offset", $_GET["offset"]);
				if (!empty ($_REQUEST["term"]))
				{
					$this->ctrl->setParameter($this, "term", $_REQUEST["term"]);
					$this->ctrl->setParameter($this, "oldoffset", $_GET["oldoffset"]);
					$back = $this->ctrl->getLinkTarget($this, "searchTerms");
				}
				else
				{
					$back = $this->ctrl->getLinkTarget($this, "listTerms");
				}
				$tabs_gui->setBackTarget($this->lng->txt("obj_glo"),
					$back, "", "");
			}
			
		}
		else
		{
			$tabs_gui->addTarget("cont_back",
				"index.html#term_".$_GET["term_id"], "",
				"");
		}
	}
	
	function download_paragraph () {
		include_once("./Services/COPage/classes/class.ilPageObject.php");
		$pg_obj =& new ilPageObject("gdf", $_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
	}


	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreen()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->outputInfoScreen();
	}

	/**
	* info screen call from inside learning module
	*/
	/*
	function showInfoScreen()
	{
		$this->outputInfoScreen(true);
	}*/

	/**
	* info screen
	*/
	function outputInfoScreen()
	{
		global $ilBench, $ilAccess;

		$this->setTabs();
		$this->lng->loadLanguageModule("meta");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

		$info = new ilInfoScreenGUI($this->glossary_gui);
		$info->enablePrivateNotes();
		//$info->enableLearningProgress();

		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
			}
		}

		// add read / back button
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			/*
			if ($_GET["obj_id"] > 0)
			{
				$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
				$info->addButton($this->lng->txt("back"),
					$this->ctrl->getLinkTarget($this, "layout"));
			}
			else
			{
				$info->addButton($this->lng->txt("view"),
					$this->ctrl->getLinkTarget($this, "layout"));
			}*/
		}
		
		// show standard meta data section
		$info->addMetaDataSections($this->glossary->getId(),0, $this->glossary->getType());

		if ($this->offlineMode())
		{
			$this->tpl->setContent($info->getHTML());
			return $this->tpl->get();
		}
		else
		{
			// forward the command
			$this->ctrl->forwardCommand($info);
		}
	}

}

?>
