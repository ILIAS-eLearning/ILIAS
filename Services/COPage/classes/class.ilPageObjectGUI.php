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

include_once ("./Services/COPage/classes/class.ilPageEditorGUI.php");
include_once("./Services/COPage/classes/class.ilPageObject.php");
include_once("./Modules/LearningModule/classes/class.ilEditClipboardGUI.php");
include_once("./Services/COPage/classes/class.ilParagraphPlugins.php");
include_once("./Services/COPage/classes/class.ilParagraphPlugin.php");
include_once("./Services/Utilities/classes/class.ilDOMUtil.php");


/**
* Class ilPageObjectGUI
*
* User Interface for Page Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
*
* @ingroup ServicesCOPage
*/
class ilPageObjectGUI
{
	var $ilias;
	var $tpl;
	var $lng;
	var $ctrl;
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
	var $sourcecode_download_script;
	var $change_comments;
	var $question_html;
	var $activation = false;
	var $activated = true;
	var $enabledinternallinks = true;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI(&$a_page_object)
	{
		global $ilias, $tpl, $lng, $ilCtrl,$ilTabs;

		$this->ctrl =& $ilCtrl;

		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->obj =& $a_page_object;
		$this->output_mode = "presentation";
		$this->setPageObject($a_page_object);
		$this->output2template = true;
		$this->question_xml = "";
		$this->question_html = "";
		$this->tabs_gui =& $ilTabs;

		// USED FOR TRANSLATIONS
		$this->template_output_var = "PAGE_CONTENT";
		$this->citation = false;
		$this->change_comments = false;
		$this->page_back_title = $this->lng->txt("page");
	}

	/**
	* get all gui classes that are called from this one (see class ilCtrl)
	*
	* @param	array		array of gui classes that are called
	*/
	function _forwards()
	{
		return array("ilPageEditorGUI");
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

	function setLinkXML($link_xml)
	{
		$this->link_xml = $link_xml;
	}

	function getLinkXML()
	{
		return $this->link_xml;
	}

	function setQuestionXML($question_xml)
	{
		$this->question_xml = $question_xml;
	}

	function setQuestionHTML($question_html)
	{
		$this->question_html = $question_html;
	}

	function getQuestionXML()
	{
		return $this->question_xml;
	}

	function getQuestionHTML()
	{
		return $this->question_html;
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


	function setSourcecodeDownloadScript ($script_name) {
		$this->sourcecode_download_script = $script_name;
	}

	function getSourcecodeDownloadScript () {
		return $this->sourcecode_download_script;
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
		$this->tabs_gui = $a_tabs;
	}

	function setPageBackTitle($a_title)
	{
		$this->page_back_title = $a_title;
	}

	function setFileDownloadLink($a_download_link)
	{
		$this->file_download_link = $a_download_link;
	}

	function getFileDownloadLink()
	{
		return $this->file_download_link;
	}

	function setFullscreenLink($a_fullscreen_link)
	{
		$this->fullscreen_link = $a_fullscreen_link;
	}

	function getFullscreenLink()
	{
		return $this->fullscreen_link;
	}

	function setIntLinkHelpDefault($a_type, $a_id)
	{
		$this->int_link_def_type = $a_type;
		$this->int_link_def_id = $a_id;
	}

	function setIntLinkReturn($a_return)
	{
		$this->int_link_return = $a_return;
	}

	function enableChangeComments($a_enabled)
	{
		$this->change_comments = $a_enabled;
	}

	function isEnabledChangeComments()
	{
		return $this->change_comments;
	}

	/**
	 * set offline directory to offdir
	 *
	 * @param offdir contains diretory where to store files
	 */
	function setOfflineDirectory ($offdir) {
		$this->offline_directory = $offdir;
	}


	/**
	 * get offline directory
	 * @return directory where to store offline files
	 */
	function getOfflineDirectory () {
		return $this->offline_directory;
	}


	/**
	* set link for "view page" button
	*
	* @param	string		link target
	* @param	string		target frame
	*/
	function setViewPageLink($a_link, $a_target = "")
	{
		$this->view_page_link = $a_link;
		$this->view_page_target = $a_target;
	}

	/**
	* get view page link
	*/
	function getViewPageLink()
	{
		return $this->view_page_link;
	}

	/**
	* get view page target frame
	*/
	function getViewPageTarget()
	{
		return $this->view_page_target;
	}

	function setActivationListener(&$a_obj, $a_meth)
	{
		$this->act_obj =& $a_obj;
		$this->act_meth = $a_meth;
	}

	function setActivated($a_act)
	{
		$this->activated = $a_act;
	}

	function getActivated()
	{
		return $this->activated;
	}

	function setEnabledActivation($a_act)
	{
		$this->activation = $a_act;
	}

	function getEnabledActivation()
	{
		return $this->activation;
	}
	
	/**
	* Set Enable internal links.
	*
	* @param	boolean	$a_enabledinternallinks	Enable internal links
	*/
	function setEnabledInternalLinks($a_enabledinternallinks)
	{
		$this->enabledinternallinks = $a_enabledinternallinks;
	}

	/**
	* Get Enable internal links.
	*
	* @return	boolean	Enable internal links
	*/
	function getEnabledInternalLinks()
	{
		return $this->enabledinternallinks;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
//echo ":$next_class:";
		$cmd = $this->ctrl->getCmd();
		$this->ctrl->addTab("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
			, "view", "ilEditClipboardGUI");

		switch($next_class)
		{
			case "ileditclipboardgui":
				$this->tabs_gui->clearTargets();
				//$this->ctrl->setReturn($this, "view");
				$clip_gui = new ilEditClipboardGUI();
				$clip_gui->setPageBackTitle($this->page_back_title);
				//$ret =& $clip_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($clip_gui);
				break;

			case "ilpageeditorgui":
				$page_editor =& new ilPageEditorGUI($this->getPageObject(), $this);
				$page_editor->setLocator($this->locator);
				$page_editor->setHeader($this->getHeader());
				$page_editor->setPageBackTitle($this->page_back_title);
				$page_editor->setIntLinkHelpDefault($this->int_link_def_type,
					$this->int_link_def_id);
				$page_editor->setIntLinkReturn($this->int_link_return);
				//$page_editor->executeCommand();
				$ret =& $this->ctrl->forwardCommand($page_editor);
				break;

			// does not exist
			case "ilmediapooltargetselector":
				include_once("./classes/class.ilMediaPoolTargetSelector.php");
				$target_sel =& new ilMediaPoolTargetSelector();
				$ret =& $this->ctrl->forwardCommand($target_sel);
				break;

			default:
				$ret =& $this->$cmd();
				break;
		}
	}


	function deactivatePage()
	{
		$act_meth = $this->act_meth;
		$this->act_obj->$act_meth(false);
		$this->ctrl->redirectByClass("illmpageobjectgui", "view");
	}

	function activatePage()
	{
		$act_meth = $this->act_meth;
		$this->act_obj->$act_meth(true);
		$this->ctrl->redirectByClass("illmpageobjectgui", "view");
	}

	/*
	* display content of page
	*/
	function showPage()
	{
		global $tree, $ilUser, $ilias;

		// init template
		if($this->outputToTemplate())
		{
			if($this->getOutputMode() == "edit")
			{
//echo ":".$this->getTemplateTargetVar().":";
				$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_edit_wysiwyg.html", "Services/COPage");

				// to do: status dependent class
				$this->tpl->setVariable("CLASS_PAGE_TD", "ilc_Page");

				// user comment
				if ($this->isEnabledChangeComments())
				{
					$this->tpl->setCurrentBlock("change_comment");
					$this->tpl->setVariable("TXT_ADD_COMMENT", $this->lng->txt("cont_add_change_comment"));
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("adm_content");
				}


				$this->tpl->setVariable("TXT_INSERT_BEFORE", $this->lng->txt("cont_set_before"));
				$this->tpl->setVariable("TXT_INSERT_AFTER", $this->lng->txt("cont_set_after"));
				$this->tpl->setVariable("TXT_INSERT_CANCEL", $this->lng->txt("cont_set_cancel"));
				$this->tpl->setVariable("TXT_CONFIRM_DELETE", $this->lng->txt("cont_confirm_delete"));
				$this->tpl->setVariable("JS_DRAGDROP", ILIAS_HTTP_PATH."/Services/COPage/js/wz_dragdrop.js");
				$this->tpl->setVariable("IMG_DRAGDROP",
					ilUtil::getImagePath("icon_drag.gif"));

				if (!ilPageEditorGUI::_isBrowserJSEditCapable())
				{
					$this->tpl->setVariable("TXT_JAVA_SCRIPT_CAPABLE", "<br />".$this->lng->txt("cont_browser_not_js_capable"));
				}
				$this->tpl->setVariable("TXT_CHANGE_EDIT_MODE", $this->lng->txt("cont_set_edit_mode"));

				if ($this->getEnabledActivation())
				{
					$this->tpl->setCurrentBlock("de_activate_page");
					if ($this->getActivated())
					{
						$this->tpl->setVariable("TXT_DE_ACTIVATE_PAGE", $this->lng->txt("cont_deactivate_page"));
						$this->tpl->setVariable("CMD_DE_ACTIVATE_PAGE", "deactivatePage");
					}
					else
					{
						$this->tpl->setVariable("TXT_DE_ACTIVATE_PAGE", $this->lng->txt("cont_activate_page"));
						$this->tpl->setVariable("CMD_DE_ACTIVATE_PAGE", "activatePage");
					}
					$this->tpl->parseCurrentBlock();
				}


				$med_mode = array("enable" => $this->lng->txt("cont_enable_media"),
					"disable" => $this->lng->txt("cont_disable_media"));
				$sel_media_mode = ($ilUser->getPref("ilPageEditor_MediaMode") == "disable")
					? "disable"
					: "enable";

				$js_mode = array("enable" => $this->lng->txt("cont_enable_js"),
					"disable" => $this->lng->txt("cont_disable_js"));

				$this->tpl->setVariable("SEL_MEDIA_MODE",
					ilUtil::formSelect($sel_media_mode, "media_mode", $med_mode, false, true,
					0, "ilEditSelect"));

				// HTML active/inactive
				$html_mode = array("enable" => $this->lng->txt("cont_enable_html"),
					"disable" => $this->lng->txt("cont_disable_html"));
				$sel_html_mode = ($ilUser->getPref("ilPageEditor_HTMLMode") == "disable")
					? "disable"
					: "enable";
				$this->tpl->setVariable("SEL_HTML_MODE",
					ilUtil::formSelect($sel_html_mode, "html_mode", $html_mode, false, true,
					0, "ilEditSelect"));

				if ($this->getViewPageLink() != "")
				{
					$this->tpl->setCurrentBlock("view_link");
					$this->tpl->setVariable("LINK_VIEW_PAGE",
						$this->getViewPageLink());
					$this->tpl->setVariable("TARGET_VIEW_PAGE",
						$this->getViewPageTarget());
					$this->tpl->setVariable("TXT_VIEW_PAGE", $this->lng->txt("view"));
					$this->tpl->parseCurrentBlock();
				}

				// javascript activation
				$sel_js_mode = "disable";
				if($ilias->getSetting("enable_js_edit"))
				{
					$sel_js_mode = (ilPageEditorGUI::_doJSEditing())
						? "enable"
						: "disable";
					$this->tpl->setVariable("SEL_JAVA_SCRIPT",
						ilUtil::formSelect($sel_js_mode, "js_mode", $js_mode, false, true,
						0, "ilEditSelect"));
				}

				// multiple actions
				if ($sel_js_mode == "disable")
				{
					$this->tpl->setCurrentBlock("multi_actions");
					$this->tpl->setVariable("TXT_DE_ACTIVATE_SELECTED", $this->lng->txt("cont_ed_enable"));
					$this->tpl->setVariable("TXT_DELETE_SELECTED", $this->lng->txt("cont_delete_selected"));
					$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
					$this->tpl->parseCurrentBlock();
				}
			}
			else
			{
				if($this->getOutputSubmode() == 'translation')
				{
					$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_translation_content.html", "Services/COPage");
				}
				else
				{
					// presentation
					if($this->getOutputMode() != 'preview')
					{
						$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_content.html", "Services/COPage");
					}
					else	// preview
					{
						$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_preview.html", "Services/COPage");
					}
				}
			}
			if ($this->getOutputMode() != "presentation" &&
				$this->getOutputMode() != "offline" &&
				$this->getOutputMode() != "print")
			{
				$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormActionByClass("ilpageeditorgui"));
			}

			// output media object edit list (of media links)
			if($this->getOutputMode() == "edit")
			{
				$links = ilInternalLink::_getTargetsOfSource($this->obj->getParentType().":pg",
					$this->obj->getId());
				$mob_links = array();
				foreach($links as $link)
				{
					if ($link["type"] == "mob")
					{
						if (ilObject::_exists($link["id"]))
						{
							$mob_links[$link["id"]] = ilObject::_lookupTitle($link["id"])." [".$link["id"]."]";
						}
					}
				}

				if (count($mob_links) > 0)
				{
					$this->tpl->setCurrentBlock("med_link");
					$this->tpl->setVariable("TXT_LINKED_MOBS", $this->lng->txt("cont_linked_mobs"));
					$this->tpl->setVariable("SEL_MED_LINKS",
						ilUtil::formSelect(0, "mob_id", $mob_links, false, true));
					$this->tpl->setVariable("TXT_EDIT_MEDIA", $this->lng->txt("cont_edit_mob"));
					$this->tpl->setVariable("TXT_COPY_TO_CLIPBOARD", $this->lng->txt("cont_copy_to_clipboard"));
					//$this->tpl->setVariable("TXT_COPY_TO_POOL", $this->lng->txt("cont_copy_to_mediapool"));
					$this->tpl->parseCurrentBlock();
				}
			}

			if ($_GET["reloadTree"] == "y")
			{
				$this->tpl->setCurrentBlock("reload_tree");
				if ($this->obj->getParentType() == "dbk")
				{
					$this->tpl->setVariable("LINK_TREE",
						$this->ctrl->getLinkTargetByClass("ilobjdlbookgui", "explorer"));
				}
				else
				{
					$this->tpl->setVariable("LINK_TREE",
						$this->ctrl->getLinkTargetByClass("ilobjlearningmodulegui", "explorer"));
				}
				$this->tpl->parseCurrentBlock();
			}
		}

		// get content
		$builded = $this->obj->buildDom();
		$this->obj->addFileSizes();

		// manage hierarchical ids
		if($this->getOutputMode() == "edit")
		{
			$this->obj->addHierIDs();

			$hids = $this->obj->getHierIds();
			$row1_ids = $this->obj->getFirstRowIds();
			$col1_ids = $this->obj->getFirstColumnIds();
			$litem_ids = $this->obj->getListItemIds();
			$fitem_ids = $this->obj->getFileItemIds();

			// standard menues
			$hids = $this->obj->getHierIds();
			foreach($hids as $hid)
			{
				$this->tpl->setCurrentBlock("add_dhtml");
				$this->tpl->setVariable("CONTEXTMENU", "contextmenu_".$hid);
				$this->tpl->parseCurrentBlock();
			}

			// column menues for tables
			foreach($col1_ids as $hid)
			{
				$this->tpl->setCurrentBlock("add_dhtml");
				$this->tpl->setVariable("CONTEXTMENU", "contextmenu_r".$hid);
				$this->tpl->parseCurrentBlock();
			}

			// row menues for tables
			foreach($row1_ids as $hid)
			{
				$this->tpl->setCurrentBlock("add_dhtml");
				$this->tpl->setVariable("CONTEXTMENU", "contextmenu_c".$hid);
				$this->tpl->parseCurrentBlock();
			}

			// list item menues
			foreach($litem_ids as $hid)
			{
				$this->tpl->setCurrentBlock("add_dhtml");
				$this->tpl->setVariable("CONTEXTMENU", "contextmenu_i".$hid);
				$this->tpl->parseCurrentBlock();
			}

			// file item menues
			foreach($fitem_ids as $hid)
			{
				$this->tpl->setCurrentBlock("add_dhtml");
				$this->tpl->setVariable("CONTEXTMENU", "contextmenu_i".$hid);
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->obj->addSourceCodeHighlighting($this->getOutputMode());
//echo "<br>-".htmlentities($this->obj->getXMLContent())."-<br><br>";
//echo "<br>-".htmlentities($this->getLinkXML())."-";
		$content = $this->obj->getXMLFromDom(false, true, true,
			$this->getLinkXML().$this->getQuestionXML());

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
			ilUtil::sendInfo($this->lng->txt("cont_citation_selection_not_valid"));
			session_unregister("citation_error");
			unset($_SESSION["citation_error"]);
		}

		// get title
		$pg_title = $this->getPresentationTitle();

		//$content = str_replace("&nbsp;", "", $content);

		// run xslt
		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();

//echo "<b>XML</b>:".htmlentities($content).":<br>";
		//		echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		//		echo "mode:".$this->getOutputMode().":<br>";

		$add_path = ilUtil::getImagePath("add.gif");
		$col_path = ilUtil::getImagePath("col.gif");
		$row_path = ilUtil::getImagePath("row.gif");
		$item_path = ilUtil::getImagePath("item.gif");
		$med_disabled_path = ilUtil::getImagePath("media_disabled.gif");

		if ($this->getOutputMode() != "offline")
		{
			$enlarge_path = ilUtil::getImagePath("enlarge.gif");
			$wb_path = ilUtil::getWebspaceDir("output");
		}
		else
		{
			$enlarge_path = "images/enlarge.gif";
			$wb_path = ".";
		}
		$pg_title_class = ($this->getOutputMode() == "print")
			? "ilc_PrintPageTitle"
			: "";

		// page splitting only for learning modules and
		// digital books
		$enable_split_new = ($this->obj->getParentType() == "lm" ||
			$this->obj->getParentType() == "dbk")
			? "y"
			: "n";

		// page splitting to next page only for learning modules and
		// digital books if next page exists in tree
		if (($this->obj->getParentType() == "lm" ||
			$this->obj->getParentType() == "dbk") &&
			ilObjContentObject::hasSuccessorPage($this->obj->getParentId(),
				$this->obj->getId()))
		{
			$enable_split_next = "y";
		}
		else
		{
			$enable_split_next = "n";
		}


        $paragraph_plugins = new ilParagraphPlugins();
	    $paragraph_plugins->initialize ();


        if ($this->getOutputMode() == "presentation" )
		{
		    $paragraph_plugin_string = $paragraph_plugins->serializeToString();
	        $_SESSION ["paragraph_plugins"] = $paragraph_plugins;
		}

		$img_path = ilUtil::getImagePath("", false, $this->getOutputMode(), $this->getOutputMode() == "offline");

        //$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
        //echo "-".$this->sourcecode_download_script.":";

		// added UTF-8 encoding otherwise umlaute are converted too
		$params = array ('mode' => $this->getOutputMode(), 'pg_title' => htmlentities($pg_title,ENT_QUOTES,"UTF-8"),
						 'pg_id' => $this->obj->getId(), 'pg_title_class' => $pg_title_class,
						 'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path,
						 'img_add' => $add_path,
						 'img_col' => $col_path,
						 'img_row' => $row_path,
						 'img_item' => $item_path,
						 'enable_split_new' => $enable_split_new,
						 'enable_split_next' => $enable_split_next,
						 'link_params' => $this->link_params,
						 'file_download_link' => $this->getFileDownloadLink(),
						 'fullscreen_link' => $this->getFullscreenLink(),
						 'med_disabled_path' => $med_disabled_path,
						 'img_path' => $img_path,
						 'parent_id' => $this->obj->getParentId(),
						 'download_script' => $this->sourcecode_download_script,
						 'encoded_download_script' => urlencode($this->sourcecode_download_script),
						 // digilib
						 'bib_id' => $this->getBibId(),'citation' => (int) $this->isEnabledCitation(),
						 'pagebreak' => $this->lng->txt('dgl_pagebreak'),
						 'page' => $this->lng->txt('page'),
						 'citate_page' => $this->lng->txt('citate_page'),
						 'citate_from' => $this->lng->txt('citate_from'),
						 'citate_to' => $this->lng->txt('citate_to'),
						 'citate' => $this->lng->txt('citate'),

						 'media_mode' => $ilUser->getPref("ilPageEditor_MediaMode"),
						 'javascript' => $sel_js_mode,
						 'paragraph_plugins' => $paragraph_plugin_string);
		if($this->link_frame != "")		// todo other link types
			$params["pg_frame"] = $this->link_frame;

		//if (version_compare(PHP_VERSION,'5','>='))
		//{
		//	$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params, true);
		//}
		//else
		//{
			$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		//}

//echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		if ($this->getOutputMode() != "edit" ||
			$ilUser->getPref("ilPageEditor_HTMLMode") != "disable")
		{
			$output = str_replace("&lt;","<",$output);
			$output = str_replace("&gt;",">",$output);
		}
		$output = str_replace("&amp;", "&", $output);
		// replace latex code: todo: finish
		if ($this->getOutputMode() != "offline")
		{
			$output = ilUtil::insertLatexImages($output);
		}
		else
		{
			$output = ilUtil::buildLatexImages($output,
				$this->getOfflineDirectory());
		}

		// (horrible) workaround for preventing template engine
		// from hiding paragraph text that is enclosed
		// in curly brackets (e.g. "{a}", see ilLMEditorGUI::executeCommand())
		$output = str_replace("{", "&#123;", $output);
		$output = str_replace("}", "&#125;", $output);

//echo "<b>HTML</b>:".htmlentities($output).":<br>";

		// remove all newlines (important for code / pre output)
		$output = str_replace("\n", "", $output);

		$qhtml = $this->getQuestionHTML();
		if (strlen($qhtml))
		{
			// removed simple str_replace with preg_replace because if the question content
			// is part of a table, the xmlns isn't added and the question content wasn't visible then
			// Helmut Schottmüller, 2006-11-15
			$output = preg_replace("/(\<div( xmlns:xhtml\=\"http:\/\/www.w3.org\/1999\/xhtml\"){0,1} class\=\"ilc_Question\">)/ims", "\\1" . $qhtml, $output);
			// old source code prior to the preg_replace change
			// $question_prefix = "<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_Question\">";
			// $output = str_replace($question_prefix, $question_prefix . $qhtml, $output);
		}

		if($this->getOutputMode() == "edit" && !$this->getActivated())
		{
			$output = '<div class="il_editarea_disabled">'.$output.'</div>';
		}

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
	function presentation($mode = "presentation")
	{
		global $tree;
		$this->setOutputMode($mode);
		return $this->showPage();
	}


	/**
	* show fullscreen view of media object
	*/
	function showMediaFullscreen($a_style_id = 0)
	{
		$this->tpl = new ilTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", 0);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("PAGETITLE", " - ".ilObject::_lookupTitle($_GET["mob_id"]));
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setCurrentBlock("ilMedia");

		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$media_obj =& new ilObjMediaObject($_GET["mob_id"]);
		if (!empty ($_GET["pg_id"]))
		{
			require_once("./Services/COPage/classes/class.ilPageObject.php");
			$pg_obj =& new ilPageObject($this->obj->getParentType(), $_GET["pg_id"]);
			$pg_obj->buildDom();

			$xml = "<dummy>";
			// todo: we get always the first alias now (problem if mob is used multiple
			// times in page)
			$xml.= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
			$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
			$xml.="</dummy>";
		}
		else
		{
			$xml = "<dummy>";
			$xml.= $media_obj->getXML(IL_MODE_ALIAS);
			$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
			$xml.="</dummy>";
		}

//echo htmlentities($xml); exit;

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

//echo "<b>XML:</b>".htmlentities($xml);
		// determine target frames for internal links
		//$pg_frame = $_GET["frame"];
		$wb_path = ilUtil::getWebspaceDir("output");
//		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$mode = "fullscreen";
		$params = array ('mode' => $mode, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);
	}

	/**
	* display validation error
	*
	* @param	string		$a_error		error string
	*/
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

}
?>
