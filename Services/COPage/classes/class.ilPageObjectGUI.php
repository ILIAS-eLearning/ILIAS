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

define ("IL_PAGE_PRESENTATION", "presentation");
define ("IL_PAGE_EDIT", "edit");
define ("IL_PAGE_PREVIEW", "preview");
define ("IL_PAGE_OFFLINE", "offline");
define ("IL_PAGE_PRINT", "print");

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
* @ilCtrl_Calls ilPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
* @ilCtrl_Calls ilPageObjectGUI: ilPublicUserProfileGUI
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
	var $editpreview = false;
	var $use_meta_data = false;
	var $enabledtabs = true;
	var $enabledpctabs = false;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI($a_parent_type, $a_id = 0, $a_old_nr = 0)
	{
		global $ilias, $tpl, $lng, $ilCtrl,$ilTabs;

		$this->ctrl =& $ilCtrl;

		if ($a_old_nr == 0 && $_GET["old_nr"] > 0)
		{
			$a_old_nr = $_GET["old_nr"];
		}
		
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->setOutputMode(IL_PAGE_PRESENTATION);
		
		// defaults (as in learning modules)
		$this->setEnabledMaps(false);
		$this->setEnabledPCTabs(false);
		$this->setEnabledFileLists(true);
		$this->setEnabledRepositoryObjects(false);
		
		if ($a_id > 0)
		{
			$this->initPageObject($a_parent_type, $a_id, $a_old_nr);
		}
		$this->output2template = true;
		$this->question_xml = "";
		$this->question_html = "";
		$this->tabs_gui =& $ilTabs;

		// USED FOR TRANSLATIONS
		$this->template_output_var = "PAGE_CONTENT";
		$this->citation = false;
		$this->change_comments = false;
		$this->page_back_title = $this->lng->txt("page");
		$lng->loadLanguageModule("content");
		$this->setPreventHTMLUnmasking(false);
		
		$this->setTemplateOutput(false);
	}
	
	function initPageObject($a_parent_type, $a_id, $a_old_nr)
	{
		$page = new ilPageObject($a_parent_type, $a_id, $a_old_nr);
		$this->setPageObject($page);
	}

	/**
	* Set Bib Id
	*/
	function setBibId($a_id)
	{
		// USED FOR SELECTION WHICH PAGE TURNS AND LATER PAGES SHOULD BE SHOWN
		$this->bib_id = $a_id;
	}

	/**
	* Get Bib Id
	*/
	function getBibId()
	{
		return $this->bib_id ? $this->bib_id : 0;
	}

	/**
	* Set Page Object
	*
	* @param	object		Page Object
	*/
	function setPageObject($a_pg_obj)
	{
		$this->obj = $a_pg_obj;
	}

	/**
	* Get Page Object
	*
	* @return	object		Page Object
	*/
	function getPageObject()
	{
		return $this->obj;
	}

	/**
	* Set Output Mode
	*
	* @param	string		Mode IL_PAGE_PRESENTATION | IL_PAGE_EDIT | IL_PAGE_PREVIEW
	*/
	function setOutputMode($a_mode = IL_PAGE_PRESENTATION)
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
	* Set Display first Edit tab, then Preview tab, instead of Page and Edit.
	*
	* @param	boolean	$a_editpreview		Edit/preview mode
	*/
	function setEditPreview($a_editpreview)
	{
		$this->editpreview = $a_editpreview;
	}

	/**
	* Get Display first Edit tab, then Preview tab, instead of Page and Edit.
	*
	* @return	boolean		Edit/Preview mode
	*/
	function getEditPreview()
	{
		return $this->editpreview;
	}

	/**
	* Set Prevent HTML Unmasking (true/false).
	*
	* @param	boolean	$a_preventhtmlunmasking	Prevent HTML Unmasking (true/false)
	*/
	function setPreventHTMLUnmasking($a_preventhtmlunmasking)
	{
		$this->preventhtmlunmasking = $a_preventhtmlunmasking;
	}

	/**
	* Get Prevent HTML Unmasking (true/false).
	*
	* @return	boolean	Prevent HTML Unmasking (true/false)
	*/
	function getPreventHTMLUnmasking()
	{
		return $this->preventhtmlunmasking;
	}

	/**
	* Set Output tabs.
	*
	* @param	boolean	$a_enabledtabs	Output tabs
	*/
	function setEnabledTabs($a_enabledtabs)
	{
		$this->enabledtabs = $a_enabledtabs;
	}

	/**
	* Get Output tabs.
	*
	* @return	boolean	Output tabs
	*/
	function getEnabledTabs()
	{
		return $this->enabledtabs;
	}

		/**
	* Set Enable Repository Objects Content Component.
	*
	* @param	boolean	$a_enabledrepositoryobjects	Enable Repository Objects Content Component
	*/
	function setEnabledRepositoryObjects($a_enabledrepositoryobjects)
	{
		$this->enabledrepositoryobjects = $a_enabledrepositoryobjects;
	}

	/**
	* Get Enable Repository Objects Content Component.
	*
	* @return	boolean	Enable Repository Objects Content Component
	*/
	function getEnabledRepositoryObjects()
	{
		return $this->enabledrepositoryobjects;
	}

	/**
	* Set Enable Maps Content Component.
	*
	* @param	boolean	$a_enabledmaps	Enable Maps Content Component
	*/
	function setEnabledMaps($a_enabledmaps)
	{
		$this->enabledmaps = $a_enabledmaps;
	}

	/**
	* Get Enable Maps Content Component.
	*
	* @return	boolean	Enable Maps Content Component
	*/
	function getEnabledMaps()
	{
		return $this->enabledmaps;
	}

	/**
	* Set Enable Tabs Content Component.
	*
	* @param	boolean	$a_enabledpctabs	Enable Tabs Content Component
	*/
	function setEnabledPCTabs($a_enabledpctabs)
	{
		$this->enabledpctabs = $a_enabledpctabs;
	}

	/**
	* Get Enable Tabs Content Component.
	*
	* @return	boolean	Enable Tabs Content Component
	*/
	function getEnabledPCTabs()
	{
		return $this->enabledpctabs;
	}

	/**
	* Set Enable File Lists Content Componente (Default is true).
	*
	* @param	boolean	$a_enabledfilelists	Enable File Lists Content Componente (Default is true)
	*/
	function setEnabledFileLists($a_enabledfilelists)
	{
		$this->enabledfilelists = $a_enabledfilelists;
	}

	/**
	* Get Enable File Lists Content Componente (Default is true).
	*
	* @return	boolean	Enable File Lists Content Componente (Default is true)
	*/
	function getEnabledFileLists()
	{
		return $this->enabledfilelists;
	}

	/**
	* Activate meda data editor
	*
	* @param	int		$a_rep_obj_id		object id as used in repository
	* @param	int		$a_sub_obj_id		sub object id
	* @param	string	$a_type				object type
	* @param	object	$a_observer_obj		observer object
	* @param	object	$a_observer_func	observer function
	*/
	function activateMetaDataEditor($a_rep_obj_id, $a_sub_obj_id, $a_type,
		$a_observer_obj = NULL, $a_observer_func = "")
	{
		$this->use_meta_data = true;
		$this->meta_data_rep_obj_id = $a_rep_obj_id;
		$this->meta_data_sub_obj_id = $a_sub_obj_id;
		$this->meta_data_type = $a_type;
		$this->meta_data_observer_obj = $a_observer_obj;
		$this->meta_data_observer_func = $a_observer_func;
	}

	/**
	* Put information about activated plugins into XML
	*/
	function getComponentPluginsXML()
	{
		$xml = "";
		if($this->getOutputMode() == "edit")
		{
			global $ilPluginAdmin;
			
			$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE,
				"COPage", "pgcp");
			foreach ($pl_names as $pl_name)
			{
				$plugin = $ilPluginAdmin->getPluginObject(IL_COMP_SERVICE,
					"COPage", "pgcp", $pl_name);
				if ($plugin->isValidParentType($this->getPageObject()->getParentType()))
				{
					$xml = '<ComponentPlugin Name="'.$plugin->getPluginName().
						'" InsertText="'.$plugin->getUIText(ilPageComponentPlugin::TXT_CMD_INSERT).'" />';
				}
			}
		}
		if ($xml != "")
		{
			$xml = "<ComponentPlugins>".$xml."</ComponentPlugins>";
		}
		
		return $xml;
	}
	
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd();
		//$this->ctrl->addTab("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
		//	, "view", "ilEditClipboardGUI");
		$this->getTabs();
		
		$ilCtrl->setReturn($this, "edit");

		switch($next_class)
		{
			case 'ilmdeditorgui':
				//$this->setTabs();
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
				$md_gui =& new ilMDEditorGUI($this->meta_data_rep_obj_id,
					$this->meta_data_sub_obj_id, $this->meta_data_type);
				if (is_object($this->meta_data_observer_obj))
				{
					$md_gui->addObserver($this->meta_data_observer_obj,
						$this->meta_data_observer_func, "General");
				}
				$this->ctrl->forwardCommand($md_gui);
				break;
			
			case "ileditclipboardgui":
				$this->tabs_gui->clearTargets();
				//$this->ctrl->setReturn($this, "view");
				$clip_gui = new ilEditClipboardGUI();
				$clip_gui->setPageBackTitle($this->page_back_title);
				//$ret =& $clip_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($clip_gui);
				break;
				
			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$ret = $this->ctrl->forwardCommand($profile_gui);
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

			default:
				$ret = $this->$cmd();
				break;
		}
//echo "+$ret+";
		return $ret;
	}

	function deactivatePage()
	{
		$act_meth = $this->act_meth;
		$this->act_obj->$act_meth(false);
		$this->ctrl->redirectByClass("illmpageobjectgui", "edit");
	}

	function activatePage()
	{
		$act_meth = $this->act_meth;
		$this->act_obj->$act_meth(true);
		$this->ctrl->redirectByClass("illmpageobjectgui", "edit");
	}

	/*
	* display content of page
	*/
	function showPage()
	{
		global $tree, $ilUser, $ilias, $lng, $ilCtrl;

		// init template
		//if($this->outputToTemplate())
		//{
			if($this->getOutputMode() == "edit")
			{
//echo ":".$this->getTemplateTargetVar().":";
				$tpl = new ilTemplate("tpl.page_edit_wysiwyg.html", true, true, "Services/COPage");
				//$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_edit_wysiwyg.html", "Services/COPage");

				// to do: status dependent class
				$tpl->setVariable("CLASS_PAGE_TD", "ilc_Page");

				// user comment
				if ($this->isEnabledChangeComments())
				{
					$tpl->setCurrentBlock("change_comment");
					$tpl->setVariable("TXT_ADD_COMMENT", $this->lng->txt("cont_add_change_comment"));
					$tpl->parseCurrentBlock();
					$tpl->setCurrentBlock("adm_content");
				}


/*				$tpl->setVariable("TXT_INSERT_BEFORE", $this->lng->txt("cont_set_before"));
				$tpl->setVariable("TXT_INSERT_AFTER", $this->lng->txt("cont_set_after"));
				$tpl->setVariable("TXT_INSERT_CANCEL", $this->lng->txt("cont_set_cancel"));
				$tpl->setVariable("TXT_CONFIRM_DELETE", $this->lng->txt("cont_confirm_delete"));
*/
				$tpl->setVariable("WYSIWYG_ACTION",
					$ilCtrl->getFormActionByClass("ilpageeditorgui", "", "", true));

				if (!ilPageEditorGUI::_isBrowserJSEditCapable())
				{
					$tpl->setVariable("TXT_JAVA_SCRIPT_CAPABLE", "<br />".$this->lng->txt("cont_browser_not_js_capable"));
				}
				$tpl->setVariable("TXT_CHANGE_EDIT_MODE", $this->lng->txt("cont_set_edit_mode"));

				if ($this->getEnabledActivation())
				{
					$tpl->setCurrentBlock("de_activate_page");
					if ($this->getActivated())
					{
						$tpl->setVariable("TXT_DE_ACTIVATE_PAGE", $this->lng->txt("cont_deactivate_page"));
						$tpl->setVariable("CMD_DE_ACTIVATE_PAGE", "deactivatePage");
					}
					else
					{
						$tpl->setVariable("TXT_DE_ACTIVATE_PAGE", $this->lng->txt("cont_activate_page"));
						$tpl->setVariable("CMD_DE_ACTIVATE_PAGE", "activatePage");
					}
					$tpl->parseCurrentBlock();
				}


				$med_mode = array("enable" => $this->lng->txt("cont_enable_media"),
					"disable" => $this->lng->txt("cont_disable_media"));
				$sel_media_mode = ($ilUser->getPref("ilPageEditor_MediaMode") == "disable")
					? "disable"
					: "enable";

				$js_mode = array("enable" => $this->lng->txt("cont_enable_js"),
					"disable" => $this->lng->txt("cont_disable_js"));

				$tpl->setVariable("SEL_MEDIA_MODE",
					ilUtil::formSelect($sel_media_mode, "media_mode", $med_mode, false, true,
					0, "ilEditSelect"));

				// HTML active/inactive
				$html_mode = array("enable" => $this->lng->txt("cont_enable_html"),
					"disable" => $this->lng->txt("cont_disable_html"));
				$sel_html_mode = ($ilUser->getPref("ilPageEditor_HTMLMode") == "disable")
					? "disable"
					: "enable";
				$tpl->setVariable("SEL_HTML_MODE",
					ilUtil::formSelect($sel_html_mode, "html_mode", $html_mode, false, true,
					0, "ilEditSelect"));

				if ($this->getViewPageLink() != "")
				{
					$tpl->setCurrentBlock("view_link");
					$tpl->setVariable("LINK_VIEW_PAGE",
						$this->getViewPageLink());
					$tpl->setVariable("TARGET_VIEW_PAGE",
						$this->getViewPageTarget());
					$tpl->setVariable("TXT_VIEW_PAGE", $this->lng->txt("view"));
					$tpl->parseCurrentBlock();
				}

				// javascript activation
				$sel_js_mode = "disable";
				if($ilias->getSetting("enable_js_edit"))
				{
					$sel_js_mode = (ilPageEditorGUI::_doJSEditing())
						? "enable"
						: "disable";
				}
				
				// get js files for JS enabled editing
				if ($sel_js_mode == "enable")
				{
					include_once("./Services/YUI/classes/class.ilYuiUtil.php");
					ilYuiUtil::initDragDrop();
					ilYuiUtil::initConnection();
					$GLOBALS["tpl"]->addJavaScript("./Services/COPage/js/ilcopagecallback.js");
					//$GLOBALS["tpl"]->addJavaScript("./Services/RTE/tiny_mce/tiny_mce.js");
					$GLOBALS["tpl"]->addJavaScript("./Services/COPage/js/ilpageedit.js");
					//$GLOBALS["tpl"]->addJavascript("Services/COPage/js/wz_dragdrop.js");
					$GLOBALS["tpl"]->addJavascript("Services/COPage/js/page_editing.js");
					$tpl->touchBlock("init_dragging");
				}

				if($ilias->getSetting("enable_js_edit"))
				{
					$tpl->setVariable("SEL_JAVA_SCRIPT", 
						ilUtil::formSelect($sel_js_mode, "js_mode", $js_mode, false, true,
						0, "ilEditSelect"));
				}

				// multiple actions
				$tpl->setCurrentBlock("multi_actions");
				if ($sel_js_mode == "enable")
				{
					$tpl->setVariable("ONCLICK_DE_ACTIVATE_SELECTED", 'onclick="return ilEditMultiAction(\'activateSelected\');"');
					$tpl->setVariable("ONCLICK_DELETE_SELECTED", 'onclick="return ilEditMultiAction(\'deleteSelected\');"');
				}
				$tpl->setVariable("TXT_DE_ACTIVATE_SELECTED", $this->lng->txt("cont_ed_enable"));
				$tpl->setVariable("TXT_DELETE_SELECTED", $this->lng->txt("cont_delete_selected"));
				$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
				$tpl->parseCurrentBlock();
			}
			else
			{
				if($this->getOutputSubmode() == 'translation')
				{
					$tpl = new ilTemplate("tpl.page_translation_content.html", true, true, "Services/COPage");
				}
				else
				{
					// presentation
					if ($this->getOutputMode() != IL_PAGE_PREVIEW)
					{
						$tpl = new ilTemplate("tpl.page_content.html", true, true, "Services/COPage");
					}
					else	// preview
					{
						$tpl = new ilTemplate("tpl.page_preview.html", true, true, "Services/COPage");
						
						// 
						if ($_GET["old_nr"] > 0)
						{
							$hist_info =
								$this->getPageObject()->getHistoryInfo($_GET["old_nr"]);
								
							// previous revision
							if (is_array($hist_info["previous"]))
							{
								$tpl->setCurrentBlock("previous_rev");
								$tpl->setVariable("TXT_PREV_REV", $lng->txt("cont_previous_rev"));
								$ilCtrl->setParameter($this, "old_nr", $hist_info["previous"]["nr"]);
								$tpl->setVariable("HREF_PREV",
									$ilCtrl->getLinkTarget($this, "preview"));
								$tpl->parseCurrentBlock();
							}
							else
							{
								$tpl->setCurrentBlock("previous_rev_disabled");
								$tpl->setVariable("TXT_PREV_REV", $lng->txt("cont_previous_rev"));
								$tpl->parseCurrentBlock();
							}
							
							// next revision
							$tpl->setCurrentBlock("next_rev");
							$tpl->setVariable("TXT_NEXT_REV", $lng->txt("cont_next_rev"));
							$ilCtrl->setParameter($this, "old_nr", $hist_info["next"]["nr"]);
							$tpl->setVariable("HREF_NEXT",
								$ilCtrl->getLinkTarget($this, "preview"));
							$tpl->parseCurrentBlock();
							
							// latest revision
							$tpl->setCurrentBlock("latest_rev");
							$tpl->setVariable("TXT_LATEST_REV", $lng->txt("cont_latest_rev"));
							$ilCtrl->setParameter($this, "old_nr", "");
							$tpl->setVariable("HREF_LATEST",
								$ilCtrl->getLinkTarget($this, "preview"));
							$tpl->parseCurrentBlock();
							
							$tpl->setCurrentBlock("hist_nav");
							$tpl->setVariable("TXT_REVISION", $lng->txt("cont_revision"));
							$tpl->setVariable("VAL_REVISION_DATE", $hist_info["current"]["hdate"]);
							$login = ilObjUser::_lookupLogin($hist_info["current"]["user"]);
							$name = ilObjUser::_lookupName($hist_info["current"]["user"]);
							$tpl->setVariable("VAL_REV_USER",
								$name["lastname"].", ".$name["firstname"]." [".$login."]");
							$tpl->parseCurrentBlock();
						}
					}
				}
			}
			if ($this->getOutputMode() != IL_PAGE_PRESENTATION &&
				$this->getOutputMode() != IL_PAGE_OFFLINE &&
				$this->getOutputMode() != IL_PAGE_PREVIEW &&
				$this->getOutputMode() != IL_PAGE_PRINT)
			{
				$tpl->setVariable("FORMACTION", $this->ctrl->getFormActionByClass("ilpageeditorgui"));
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
					$tpl->setCurrentBlock("med_link");
					$tpl->setVariable("TXT_LINKED_MOBS", $this->lng->txt("cont_linked_mobs"));
					$tpl->setVariable("SEL_MED_LINKS",
						ilUtil::formSelect(0, "mob_id", $mob_links, false, true));
					$tpl->setVariable("TXT_EDIT_MEDIA", $this->lng->txt("cont_edit_mob"));
					$tpl->setVariable("TXT_COPY_TO_CLIPBOARD", $this->lng->txt("cont_copy_to_clipboard"));
					//$this->tpl->setVariable("TXT_COPY_TO_POOL", $this->lng->txt("cont_copy_to_mediapool"));
					$tpl->parseCurrentBlock();
				}
			}

			if ($_GET["reloadTree"] == "y")
			{
				$tpl->setCurrentBlock("reload_tree");
				if ($this->obj->getParentType() == "dbk")
				{
					$tpl->setVariable("LINK_TREE",
						$this->ctrl->getLinkTargetByClass("ilobjdlbookgui", "explorer"));
				}
				else
				{
					$tpl->setVariable("LINK_TREE",
						$this->ctrl->getLinkTargetByClass("ilobjlearningmodulegui", "explorer"));
				}
				$tpl->parseCurrentBlock();
			}
//		}

		// get content
		$builded = $this->obj->buildDom();

		// manage hierarchical ids
		if($this->getOutputMode() == "edit")
		{
			
			// add pc ids, if necessary
			if (!$this->obj->checkPCIds())
			{
				$this->obj->insertPCIds();
				$this->obj->update(true, true);
			}
			
			$this->obj->addFileSizes();
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
				$tpl->setCurrentBlock("add_dhtml");
				$tpl->setVariable("CONTEXTMENU", "contextmenu_".$hid);
				$tpl->parseCurrentBlock();
			}

			// column menues for tables
			foreach($col1_ids as $hid)
			{
				$tpl->setCurrentBlock("add_dhtml");
				$tpl->setVariable("CONTEXTMENU", "contextmenu_r".$hid);
				$tpl->parseCurrentBlock();
			}

			// row menues for tables
			foreach($row1_ids as $hid)
			{
				$tpl->setCurrentBlock("add_dhtml");
				$tpl->setVariable("CONTEXTMENU", "contextmenu_c".$hid);
				$tpl->parseCurrentBlock();
			}

			// list item menues
			foreach($litem_ids as $hid)
			{
				$tpl->setCurrentBlock("add_dhtml");
				$tpl->setVariable("CONTEXTMENU", "contextmenu_i".$hid);
				$tpl->parseCurrentBlock();
			}

			// file item menues
			foreach($fitem_ids as $hid)
			{
				$tpl->setCurrentBlock("add_dhtml");
				$tpl->setVariable("CONTEXTMENU", "contextmenu_i".$hid);
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$this->obj->addFileSizes();
		}

		$this->obj->addSourceCodeHighlighting($this->getOutputMode());
//echo "<br>-".htmlentities($this->obj->getXMLContent())."-<br><br>";
//echo "<br>-".htmlentities($this->getLinkXML())."-";
		$content = $this->obj->getXMLFromDom(false, true, true,
			$this->getLinkXML().$this->getQuestionXML().$this->getComponentPluginsXML());
			
		// get page component plugins

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


        if ($this->getOutputMode() == IL_PAGE_PRESENTATION)
		{
		    $paragraph_plugin_string = $paragraph_plugins->serializeToString();
	        $_SESSION ["paragraph_plugins"] = $paragraph_plugins;
		}

		$img_path = ilUtil::getImagePath("", false, $this->getOutputMode(), $this->getOutputMode() == "offline");

        //$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
        //echo "-".$this->sourcecode_download_script.":";
		
		if ($this->getEnabledPCTabs())
		{
			include_once("./Services/YUI/classes/class.ilYuiUtil.php");
			ilYuiUtil::initTabView();
		}

		$file_download_link = $this->getFileDownloadLink();
		if ($file_download_link ==  "")
		{
			$file_download_link = $ilCtrl->getLinkTarget($this, "downloadFile");
		}
		$fullscreen_link = ($this->getFullscreenLink() == "")
			? $ilCtrl->getLinkTarget($this, "displayMediaFullscreen")
			: $this->getFullscreenLink();
		
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
						 'file_download_link' => $file_download_link,
						 'fullscreen_link' => $fullscreen_link,
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
						 'enable_rep_objects' => $this->getEnabledRepositoryObjects() ? "y" : "n",
						 'enable_map' => $this->getEnabledMaps() ? "y" : "n",
						 'enable_tabs' => $this->getEnabledPCTabs() ? "y" : "n",
						 'enable_file_list' => $this->getEnabledFileLists() ? "y" : "n",
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
//echo htmlentities($output);
		// unmask user html
		if (($this->getOutputMode() != "edit" ||
			$ilUser->getPref("ilPageEditor_HTMLMode") != "disable")
			&& !$this->getPreventHTMLUnmasking())
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
//echo htmlentities($output);
		$output = $this->postOutputProcessing($output);
//echo htmlentities($output);
		if($this->getOutputMode() == "edit" && !$this->getActivated())
		{
			$output = '<div class="il_editarea_disabled">'.$output.'</div>';
		}
		
		$output = $this->insertMaps($output);

		// output
		if ($ilCtrl->isAsynch())
		{
			$tpl->setVariable($this->getTemplateOutputVar(), $output);
			echo $tpl->get();
			exit;

			$tpl->setVariable($this->getTemplateOutputVar(), $output);
			$tpl->setCurrentBlock("adm_content");
			$tpl->parseCurrentBlock();
			echo $tpl->get("adm_content");
			exit;
		}
		if ($this->outputToTemplate())
		{
			$tpl->setVariable($this->getTemplateOutputVar(), $output);
			$this->tpl->setVariable($this->getTemplateTargetVar(), $tpl->get());
            return $output;
		}
		else
		{
			$tpl->setVariable($this->getTemplateOutputVar(), $output);
			return $tpl->get();
		}
	}
	
	/**
	* Download file of file lists
	*/
	function downloadFile()
	{
		$file = explode("_", $_GET["file_id"]);
		require_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}
	
	/**
	* Show media in fullscreen mode
	*/
	function displayMediaFullscreen()
	{
		$tpl = new ilTemplate("tpl.fullscreen.html", true, true, "Modules/LearningModule");
		$tpl->setCurrentBlock("ilMedia");

		//$int_links = $page_object->getInternalLinks();
		$med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);
		
		// @todo
		//$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());
		
		require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$media_obj = new ilObjMediaObject($_GET["mob_id"]);
		require_once("./Services/COPage/classes/class.ilPageObject.php");
		$pg_obj = new ilPageObject($this->getParentObject()->getType(), $this->getId());
		$pg_obj->buildDom();

		$xml = "<dummy>";
		// todo: we get always the first alias now (problem if mob is used multiple
		// times in page)
		$xml.= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
		$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
		$xml.= $link_xml;
		$xml.="</dummy>";

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

//echo "<b>XML:</b>".htmlentities($xml);
		// determine target frames for internal links
		$wb_path = ilUtil::getWebspaceDir("output");
		$enlarge_path = ilUtil::getImagePath("enlarge.gif");
		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => "",
			'ref_id' => $_GET["ref_id"], 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
	//echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$tpl->setVariable("MEDIA_CONTENT", $output);
		$tpl->show();
		exit;
	}


	/**
	* Insert Maps
	*/
	function insertMaps($a_html)
	{
		$c_pos = 0;
		$start = strpos($a_html, "[[[[[Map;");
		if (is_int($start))
		{
			$end = strpos($a_html, "]]]]]", $start);
		}
		$i = 1;
		while ($end > 0)
		{
			$param = substr($a_html, $start + 9, $end - $start - 9);
			
			$param = explode(";", $param);
			if (is_numeric($param[0]) && is_numeric($param[1]) && is_numeric($param[2]))
			{
				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
				$map_gui = new ilGoogleMapGUI();
				$map_gui->setMapId("map_".$i);
				$map_gui->setLatitude($param[0]);
				$map_gui->setLongitude($param[1]);
				$map_gui->setZoom($param[2]);
				$map_gui->setWidth($param[3]."px");
				$map_gui->setHeight($param[4]."px");
				$map_gui->setEnableTypeControl(true);
				$map_gui->setEnableNavigationControl(true);
				$map_gui->setEnableCentralMarker(true);
				$h2 = substr($a_html, 0, $start).
					$map_gui->getHtml().
					substr($a_html, $end + 5);
				$a_html = $h2;
				$i++;
			}
			$start = strpos($a_html, "[[[[[Map;", $start + 5);
			$end = 0;
			if (is_int($start))
			{
				$end = strpos($a_html, "]]]]]", $start);
			}
		}
				
		return $a_html;
	}

	/**
	* Finalizing output processing. Maybe overwritten in derived
	* classes, e.g. in wiki module.
	*/
	function postOutputProcessing($a_output)
	{
		return $a_output;
	}
	
	/*
	* preview
	*/
	function preview()
	{
		global $tree;
		$this->setOutputMode(IL_PAGE_PREVIEW);
		return $this->showPage();
	}

	/*
	* edit ("view" before)
	*/
	function edit()
	{
		global $tree;
		$this->setOutputMode(IL_PAGE_EDIT);
		return $this->showPage();
	}

	/*
	* presentation
	*/
	function presentation()
	{
		global $tree;
		$this->setOutputMode(IL_PAGE_PRESENTATION);

		return $this->showPage();
	}

	function getHTML()
	{
		$this->getTabs("preview");
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

	/**
	* Get history table as HTML.
	*/
	function history()
	{
		global $tpl, $lng, $ilAccess;
		
		include_once("./Services/COPage/classes/class.ilPageHistoryTableGUI.php");
		$table_gui = new ilPageHistoryTableGUI($this, "history");
		$table_gui->setData($this->getPageObject()->getHistoryEntries());
		return $table_gui->getHTML();
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs($a_activate = "")
	{
		global $ilTabs, $ilCtrl;
		
		if (!$this->getEnabledTabs())
		{
			return;
		}

//echo "-".$ilCtrl->getNextClass()."-".$ilCtrl->getCmd()."-";
		// back to upper context
		
		if (!$this->getEditPreview())
		{
			$ilTabs->addTarget("pg", $ilCtrl->getLinkTarget($this, "preview")
				, array("", "preview"));
	
			$ilTabs->addTarget("edit", $ilCtrl->getLinkTarget($this, "edit")
				, array("", "edit"));
		}
		else
		{
			$ilTabs->addTarget("edit", $ilCtrl->getLinkTarget($this, "edit")
				, array("", "edit"));
	
			$ilTabs->addTarget("cont_preview", $ilCtrl->getLinkTarget($this, "preview")
				, array("", "preview"));
		}
			
		//$tabs_gui->addTarget("properties", $this->ctrl->getLinkTarget($this, "properties")
		//	, "properties", get_class($this));

		if ($this->use_meta_data)
		{
			$ilTabs->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
				 "", "ilmdeditorgui");
		}

		$ilTabs->addTarget("history", $this->ctrl->getLinkTarget($this, "history")
			, "history", get_class($this));

/*		$tabs = $this->ctrl->getTabs();
		foreach ($tabs as $tab)
		{
			$tabs_gui->addTarget($tab["lang_var"], $tab["link"]
				, $tab["cmd"], $tab["class"]);
		}
*/
		$ilTabs->addTarget("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
			, "view", "ilEditClipboardGUI");

		//$ilTabs->setTabActive("pg");
	}

	/**
	* Compares two revisions of the page
	*/
	function compareVersion()
	{
		global $lng;
		
		$tpl = new ilTemplate("tpl.page_compare.html", true, true, "Services/COPage");
		$compare = $this->obj->compareVersion($_POST["left"], $_POST["right"]);
		
		// left page
		$lpage = $compare["l_page"];
		$lpage_gui = new ilPageObjectGUI("wpg");
		$lpage_gui->setOutputMode(IL_PAGE_PREVIEW);
		$lpage_gui->setPageObject($lpage);
		$lpage_gui->setPreventHTMLUnmasking(true);
		$lhtml = $lpage_gui->showPage();
		$lhtml = $this->replaceDiffTags($lhtml);
		$tpl->setVariable("LEFT", $lhtml);
		
		// right page
		$rpage = $compare["r_page"];
		$rpage_gui = new ilPageObjectGUI("wpg");
		$rpage_gui->setOutputMode(IL_PAGE_PREVIEW);
		$rpage_gui->setPageObject($rpage);
		$rpage_gui->setPreventHTMLUnmasking(true);
		$rhtml = $rpage_gui->showPage();
		$rhtml = $this->replaceDiffTags($rhtml);
		$tpl->setVariable("RIGHT", $rhtml);
		
		$tpl->setVariable("TXT_NEW", $lng->txt("cont_pc_new"));
		$tpl->setVariable("TXT_MODIFIED", $lng->txt("cont_pc_modified"));
		$tpl->setVariable("TXT_DELETED", $lng->txt("cont_pc_deleted"));

//var_dump($left);
//var_dump($right);

		return $tpl->get();
	}
	
	function replaceDiffTags($a_html)
	{
		$a_html = str_replace("[ilDiffInsStart]", '<span class="ilDiffIns">', $a_html);
		$a_html = str_replace("[ilDiffDelStart]", '<span class="ilDiffDel">', $a_html);
		$a_html = str_replace("[ilDiffInsEnd]", '</span>', $a_html);
		$a_html = str_replace("[ilDiffDelEnd]", '</span>', $a_html);

		return $a_html;
	}
}
?>
