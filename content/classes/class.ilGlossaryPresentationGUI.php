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

require_once("classes/class.ilObjectGUI.php");
require_once("classes/class.ilMetaDataGUI.php");
require_once("content/classes/class.ilObjGlossary.php");
require_once("content/classes/class.ilGlossaryTermGUI.php");
require_once("content/classes/class.ilGlossaryDefinition.php");
require_once("content/classes/class.ilTermDefinitionEditorGUI.php");
require_once("content/classes/Pages/class.ilPCParagraph.php");

/**
* Class ilGlossaryPresentationGUI
*
* GUI class for glossary presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
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
		global $lng, $ilias, $tpl;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->offline = false;

		// Todo: check lm id
		$this->glossary =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

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
	* executer command ("listTerms" | "listDefinitions")
	*/
	function executeCommand()
	{
		$cmd = $_GET["cmd"];
		if ($cmd != "listDefinitions")
		{
			$this->prepareOutput();
		}
		if($cmd == "")
		{
			$cmd = "listTerms";
		}

		$this->$cmd();

		$this->tpl->show();
	}

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->glossary->getTitle();

		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setLocator();
	}

	function searchTerms () {
		if (isset ($_POST["clear"])) 
		{
			$searchterm ="";
			$_GET["offset"] = $_GET["oldoffset"];
		} else 
		{
			$searchterm = $_REQUEST ["term"];
		}			
		$term_list = $this->glossary->getTermList($searchterm);
		$this->listTermByGiven($term_list, $searchterm);

	}


	function listTerms()
	{
		$term_list = $this->glossary->getTermList();		
		
		return $this->listTermByGiven($term_list);
	}

	/**
	* list glossary terms
	*/
	function listTermByGiven($term_list, $filter ="")
	{
		$this->lng->loadLanguageModule("meta");
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
//		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		
		$oldoffset = (is_numeric ($_GET["oldoffset"]))?$_GET["oldoffset"]:$_GET["offset"];

		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.glossary_search_term.html", true);
		if (!$this->offlineMode())
		{
			$this->tpl->setCurrentBlock("search_form");
			$this->tpl->setVariable("FORMACTION1", "glossary_presentation.php?ref_id=".$_GET["ref_id"]."&cmd=searchTerms&offset=0&oldoffset=$oldoffset");
			$this->tpl->setVariable("TXT_TERM", $this->lng->txt("cont_term"));
			$this->tpl->setVariable("TXT_SEARCH", $this->lng->txt("search"));
			$this->tpl->setVariable("TXT_CLEAR", $this->lng->txt("clear"));
			$this->tpl->setVariable("TERM", $filter);
			$this->tpl->parseCurrentBlock();
		}

		// load template for table
		$this->tpl->addBlockfile("TERM_TABLE", "term_table", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.term_tbl_pres_row.html", true);

		$num = 2;

		//$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		//$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$this->ref_id."$obj_str&cmd=post&offset=".$_GET["offset"]);

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("cont_terms").(($filter=="")?"":"*"));
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

		$header_params = array("ref_id" => $_GET["ref_id"], "cmd" => "listTerms");

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
		$tbl->setMaxCount($this->maxcount);

		//$this->tpl->setVariable("COLUMN_COUNTS", 4);

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

//		$term_list = $this->glossary->getTermList();
		$tbl->setMaxCount(count($term_list));

		// sorting array
		//$term_list = ilUtil::sortArray($term_list, $_GET["sort_by"], $_GET["sort_order"]);
		$term_list = array_slice($term_list, $_GET["offset"], $_GET["limit"]);

		// render table
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
					$short_str = ilPCParagraph::xml2output($def["short_text"]);
					//$short_str = str_replace("<", "&lt;", $short_str);
					//$short_str = str_replace(">", "&gt;", $short_str);
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
				if (!empty ($filter)) {
					$append = "&term=$filter&oldoffset=".$_GET["oldoffset"];
				}
				if (!$this->offlineMode())
				{
					$this->tpl->setVariable("LINK_VIEW_TERM", "glossary_presentation.php?ref_id=".
						$_GET["ref_id"]."&cmd=listDefinitions&term_id=".$term["id"]."&offset=".$_GET["offset"].$append);
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
			}
		} //if is_array
		else
		{
			$this->tpl->setCurrentBlock("notfound");
			$this->tpl->setVariable("TXT_OBJECT_NOT_FOUND", $this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("NUM_COLS", $num);
			$this->tpl->parseCurrentBlock();
		}

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
		global $ilUser;
		
		require_once("content/classes/Pages/class.ilPageObjectGUI.php");
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
		$this->tpl->setVariable("HEADER",
			$this->lng->txt("cont_term").": ".$term->getTerm());


		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "def_list", "tpl.glossary_definition_list.html", true);
		//$this->tpl->addBlockfile("STATUSLINE", "statusline", "tpl.statusline.html");

		$this->tpl->setVariable("FORMACTION", "glossary_edit.php?ref_id=".$_GET["ref_id"].
			"&cmd=post&term_id=".$_GET["term_id"]."&offset=".$_GET["offset"]);
		/*
		$this->tpl->setVariable("TXT_ADD_DEFINITION",
			$this->lng->txt("cont_add_definition"));
		$this->tpl->setVariable("BTN_ADD", "addDefinition");*/

		$defs = ilGlossaryDefinition::getDefinitionList($_GET["term_id"]);

		$this->tpl->setVariable("TXT_TERM", $term->getTerm());
		$this->mobs = array();

		for($j=0; $j<count($defs); $j++)
		{
			$def = $defs[$j];
			$page =& new ilPageObject("gdf", $def["id"]);
			$page_gui =& new ilPageObjectGUI($page);

			// internal links
			$page->buildDom();
			$int_links = $page->getInternalLinks();
			$link_xml = $this->getLinkXML($int_links);
			$page_gui->setLinkXML($link_xml);

			$page_gui->setSourcecodeDownloadScript("glossary_presentation.php?ref_id=".$_GET["ref_id"]);
			$page_gui->setFullscreenLink("glossary_presentation.php?cmd=fullscreen".
				"&amp;ref_id=".$_GET["ref_id"]."&amp;def_id=".$def["id"]);

			//$page_gui->setOutputMode("edit");
			//$page_gui->setPresentationTitle($this->term->getTerm());
			$page_gui->setTemplateOutput(false);
			$page_gui->setFileDownloadLink("glossary_presentation.php?cmd=downloadFile".
					"&amp;ref_id=".$_GET["ref_id"]);
			$output = $page_gui->preview();

			if (count($defs) > 1)
			{
				$this->tpl->setCurrentBlock("definition_header");
				$this->tpl->setVariable("TXT_DEFINITION",
					$this->lng->txt("cont_definition")." ".($j+1));
				$this->tpl->parseCurrentBlock();
			}
			
			/*
			if ($j > 0)
			{
				$this->tpl->setCurrentBlock("up");
				$this->tpl->setVariable("TXT_UP", $this->lng->txt("up"));
				$this->tpl->setVariable("LINK_UP",
					"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=moveUp&def=".$def["id"]."&offset=".$_GET["offset"]);
				$this->tpl->parseCurrentBlock();
			}

			if ($j+1 < count($defs))
			{
				$this->tpl->setCurrentBlock("down");
				$this->tpl->setVariable("TXT_DOWN", $this->lng->txt("down"));
				$this->tpl->setVariable("LINK_DOWN",
					"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=moveDown&def=".$def["id"]."&offset=".$_GET["offset"]);
				$this->tpl->parseCurrentBlock();
			}*/

			$this->tpl->setCurrentBlock("definition");
			$this->tpl->setVariable("PAGE_CONTENT", $output);
			/*$this->tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
			$this->tpl->setVariable("LINK_EDIT",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=view&def=".$def["id"]."&offset=".$_GET["offset"]);
			$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("LINK_DELETE",
				"glossary_edit.php?ref_id=".$_GET["ref_id"]."&cmd=confirmDefinitionDeletion&def=".$def["id"]."&offset=".$_GET["offset"]);
				*/
			$this->tpl->parseCurrentBlock();
		}
		//$this->tpl->setCurrentBlock("def_list");
		//$this->tpl->parseCurrentBlock();
		
		// display possible backlinks
		$sources = ilInternalLink::_getSourcesOfTarget('git',$_GET['term_id'],0);
		
		if ($sources)
		{
			$this->tpl->setVariable("BACKLINK_TITLE",$this->lng->txt('glo_term_used_in'));
			
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
						$this->tpl->setVariable("BACKLINK_LINK",ILIAS_HTTP_PATH."/goto.php?target=".$type[1]."_".$src['id']);
						$this->tpl->setVariable("BACKLINK_ITEM",$lm_title.": ".$title);
						$this->tpl->parseCurrentBlock();
					}
				}
			}
		}
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
		$this->media("fullscreen");
	}

	/**
	* show media object
	*/
	function media($a_mode = "media")
	{
		$this->tpl =& new ilTemplate("tpl.fullscreen.html", true, true, "content");
		include_once("classes/class.ilObjStyleSheet.php");
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath(0));

		//$int_links = $page_object->getInternalLinks();
		$med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);

		// later
		//$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());

		$link_xlm = "";

		require_once("content/classes/Media/class.ilObjMediaObject.php");
		$media_obj =& new ilObjMediaObject($_GET["mob_id"]);

		$xml = "<dummy>";
		// todo: we get always the first alias now (problem if mob is used multiple
		// times in page)
		$xml.= $media_obj->getXML(IL_MODE_ALIAS);
		$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
		$xml.= $link_xml;
		$xml.="</dummy>";

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

		$wb_path = ilUtil::getWebspaceDir("output");

		$mode = $a_mode;
		$enlarge_path = ilUtil::getImagePath("enlarge.gif", false, "output");
		$fullscreen_link = "glossary_presentation.php?ref_id=".$_GET["ref_id"]."&obj_type=MediaObject&cmd=fullscreen"
			;
		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => $fullscreen_link,
			'ref_id' => $_GET["ref_id"], 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);

		$this->tpl->parseCurrentBlock();
		if (!$this->offlineMode())
		{
			$this->tpl->show();
		}
		else
		{
			return $this->tpl->get();
		}

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
		require_once ("content/classes/class.ilGlossaryLocatorGUI.php");
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
		return;


		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		//$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		if (!empty($_GET["term_id"]))
		{
			$this->tpl->touchBlock("locator_separator");
		}

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->glossary->getTitle());
		// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
		$this->tpl->setVariable("LINK_ITEM", "glossary_presentation.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms");
		$this->tpl->parseCurrentBlock();

		// ### AA 03.11.10 added new locator GUI class ###
		// navigate locator
		$ilias_locator->navigate($i++,$this->glossary->getTitle(),"glossary_presentation.php?ref_id=".$_GET["ref_id"]."&cmd=listTerms"."&offset=".$_GET["offset"],"bottom");

		if (!empty($_GET["term_id"]))
		{
			$term =& new ilGlossaryTerm($_GET["term_id"]);
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $term->getTerm());
			$this->tpl->setVariable("LINK_ITEM", "glossary_presentation.php?ref_id=".$_GET["ref_id"].
				"&cmd=listDefinitions&term_id=".$term->getId()."&offset=".$_GET["offset"]);
			$this->tpl->parseCurrentBlock();

			// ### AA 03.11.10 added new locator GUI class ###
			// navigate locator
			$ilias_locator->navigate($i++,$term->getTerm(),"glossary_edit.php?ref_id=".$_GET["ref_id"].
				"&cmd=listDefinitions&term_id=".$term->getId()."&offset=".$_GET["offset"],"bottom");
		}

		//$this->tpl->touchBlock("locator_separator");

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* download file of file lists
	*/
	function downloadFile()
	{
		$file = explode("_", $_GET["file_id"]);
		include_once("classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}

	/**
	* output tabs
	*/
	function setTabs()
	{

		// catch feedback message
		include_once("classes/class.ilTabsGUI.php");
		$tabs_gui =& new ilTabsGUI();
		$this->getTabs($tabs_gui);

		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());

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
			$target = $int_link["Target"];
			if (substr($target, 0, 4) == "il__")
			{
				$target_arr = explode("_", $target);
				$target_id = $target_arr[count($target_arr) - 1];
				$type = $int_link["Type"];
				$targetframe = ($int_link["TargetFrame"] != "")
					? $int_link["TargetFrame"]
					: "None";

				switch($type)
				{
					case "PageObject":
					case "StructureObject":
						$lm_id = ilLMObject::_lookupContObjID($target_id);
						$cont_obj =& $this->content_object;
						if ($type == "PageObject")
						{
							$href = "../goto.php?target=pg_".$target_id;
						}
						else
						{
							$href = "../goto.php?target=st_".$target_id;
						}
						$ltarget = "ilContObj".$lm_id;
						break;

					case "GlossaryItem":
						//$ltarget = $nframe = "_new";
						$href = "glossary_presentation.php?cmd=listDefinitions&amp;ref_id=".$_GET["ref_id"].
							"&amp;term_id=".$target_id;
						break;

					case "MediaObject":
						$ltarget = $nframe = "_new";
						$href = "glossary_presentation.php?obj_type=$type&amp;cmd=media&amp;ref_id=".$_GET["ref_id"].
							"&amp;mob_id=".$target_id;
						break;
				}
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" />";
			}
		}
		$link_info.= "</IntLinkInfos>";

		return $link_info;
	}



	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		// back to upper context
		if (!empty ($_REQUEST["term"])) {
			$append = "&cmd=searchTerms&term=".$_REQUEST["term"]."&oldoffset=".$_GET["oldoffset"];
		}		
		
		if (!$this->offlineMode())
		{
			$tabs_gui->addTarget("cont_back",
				"glossary_presentation.php?ref_id=".$_GET["ref_id"]."&offset=".$_GET["offset"].$append, "",
				"");
		}
		else
		{
			$tabs_gui->addTarget("cont_back",
				"index.html#term_".$_GET["term_id"], "",
				"");
		}
	}
	
	function download_paragraph () {
		include_once("content/classes/Pages/class.ilPageObject.php");
		$pg_obj =& new ilPageObject("gdf", $_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
	}


}

?>
