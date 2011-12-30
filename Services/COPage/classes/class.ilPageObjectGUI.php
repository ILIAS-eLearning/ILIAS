<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define ("IL_PAGE_PRESENTATION", "presentation");
define ("IL_PAGE_EDIT", "edit");
define ("IL_PAGE_PREVIEW", "preview");
define ("IL_PAGE_OFFLINE", "offline");
define ("IL_PAGE_PRINT", "print");

include_once ("./Services/COPage/classes/class.ilPageEditorGUI.php");
include_once("./Services/COPage/classes/class.ilPageObject.php");
include_once("./Services/Clipboard/classes/class.ilEditClipboardGUI.php");
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
* @ilCtrl_Calls ilPageObjectGUI: ilPublicUserProfileGUI, ilNoteGUI, ilNewsItemGUI
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
	var $enabledinternallinks = false;
	var $editpreview = false;
	var $use_meta_data = false;
	var $enabledpctabs = false;
	var $link_xml_set = false;
	var $enableediting = true;
	var $rawpagecontent = false;
	var $layoutmode = false;
	var $enabledcontentincludes = false;
	var $compare_mode = false;
	var $page_config = null;
	var $enable_keywords = false;
	var $enable_anchors = false;
	var $tabs_enabled = true;
	private $abstract_only = false;
	private $enabledloginpage = false;
	
	//var $pl_start = "&#123;&#123;&#123;&#123;&#123;";
	//var $pl_end = "&#125;&#125;&#125;&#125;&#125;";
	var $pl_start = "{{{{{";
	var $pl_end = "}}}}}";
	
	// name keys like pc class names!
	var $pc_types = array(
		"ContentInclude" => array(
				"enabled" => false),
		"DataTable" => array(
				"enabled" => true),
		"FileList" => array(
				"enabled" => true),
		"InteractiveImage" => array(
				"enabled" => true),
		"List" => array(
				"enabled" => true),
		"LoginPageElement" => array(
				"enabled" => false),
		"Map" => array(
				"enabled" => false),
		"MediaObject" => array(
				"enabled" => true),
		"Paragraph" => array(
				"enabled" => true),
		"PlaceHolder" => array(
				"enabled" => false),
		"Question" => array(
				"enabled" => false),
		"Resources" => array(
				"enabled" => false),
		"Section" => array(
				"enabled" => true),
		"SourceCode" => array(
				"enabled" => true),
		"Table" => array(
				"enabled" => true),
		"Tabs" => array(
				"enabled" => true),
		"Profile" => array(
				"enabled" => false),
		"Verification" => array(
				"enabled" => false),
		"Blog" => array(
				"enabled" => false),
		"QuestionOverview" => array(
				"enabled" => false),
		"Skills" => array(
				"enabled" => false)
		);
	
	
	
	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI($a_parent_type, $a_id = 0, $a_old_nr = 0,
		$a_prevent_get_id = false)
	{
		global $ilias, $tpl, $lng, $ilCtrl,$ilTabs;
		$this->ctrl =& $ilCtrl;

		if ($a_old_nr == 0 && !$a_prevent_get_id && $_GET["old_nr"] > 0)
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
		$this->setEnabledFileLists(true);
		$this->setEnabledRepositoryObjects(false);
		$this->setEnabledLoginPage(false);
		$this->setEnabledSelfAssessment(false);
		$this->setEnabledPageFocus(true);
		$this->setLayoutMode(false);
		
		include_once("./Services/COPage/classes/class.ilPageConfig.php");
		$this->setPageConfig(new ilPageConfig());
		

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
		$this->setEnabledWikiLinks(false);
		
		$this->setTemplateOutput(false);
	}
	
	/**
	 * Set enable pc type
	 *
	 * @param boolean $a_val enable pc type true/false	
	 */
	function setEnablePCType($a_pc_type, $a_val)
	{
		if (isset($a_pc_type))
		{
			$this->pc_types[$a_pc_type]["enabled"] = $a_val;
		}
	}
	
	/**
	 * Get enable pc type
	 *
	 * @return boolean enable pc type true/false
	 */
	function getEnablePCType($a_pc_type)
	{
		return $this->pc_types[$a_pc_type]["enabled"];
	}
	
	/**
	 * Set page config object
	 *
	 * @param	object	config object
	 */
	function setPageConfig($a_val)
	{
		$this->page_config = $a_val;
	}
	
	/**
	 * Get page config object
	 *
	 * @return	object	config object
	 */
	function getPageConfig()
	{
		return $this->page_config;
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
		$this->link_xml_set = true;
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

	/**
	 * Set enable keywords handling
	 *
	 * @param	boolean	keywords handling
	 */
	function setEnableKeywords($a_val)
	{
		$this->enable_keywords = $a_val;
	}
	
	/**
	 * Get enable keywords handling
	 *
	 * @return	boolean	keywords handling
	 */
	function getEnableKeywords()
	{
		return $this->enable_keywords;
	}

	/**
	 * Set enable anchors
	 *
	 * @param	boolean	anchors
	 */
	function setEnableAnchors($a_val)
	{
		$this->enable_anchors = $a_val;
	}
	
	/**
	 * Get enable anchors
	 *
	 * @return	boolean	anchors
	 */
	function getEnableAnchors()
	{
		return $this->enable_anchors;
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

	function enableNotes($a_enabled, $a_parent_id)
	{
		$this->notes_enabled = $a_enabled;
		$this->notes_parent_id = $a_parent_id;
	}

	function isEnabledNotes()
	{
		return $this->notes_enabled;
	}

	/**
	 * Enable content includes
	 */
	function enableContentIncludes($a_enable)
	{
		$this->setEnablePCType("ContentInclude", (bool) $a_enable);
	}
	
	/**
	 * Are content includes enabled?
	 */
	function isEnabledContentIncludes()
	{
		return $this->getEnablePCType("ContentInclude");
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

	function setEnabledActivation($a_act)
	{
		$this->activation = $a_act;
	}

	function getEnabledActivation()
	{
		return $this->activation;
	}

	function setEnabledScheduledActivation($a_act)
	{
		$this->scheduled_activation = $a_act;
	}

	function getEnabledScheduledActivation()
	{
		return $this->scheduled_activation;
	}

	/**
	 * Set page toc
	 *
	 * @param	boolean	$a_val	page toc
	 */
	public function setPageToc($a_val)
	{
		$this->page_toc = $a_val;
	}

	/**
	 * Get page toc
	 *
	 * @return	boolean	page toc
	 */
	public function getPageToc()
	{
		return $this->page_toc;
	}

	/**
	 * Set enabled news
	 *
	 * @param	boolean	enabled news
	 */
	function setEnabledNews($a_enabled, $a_news_obj_id = 0, $a_news_obj_type = 0)
	{
		$this->enabled_news = $a_enabled;
		$this->news_obj_id = $a_news_obj_id;
		$this->news_obj_type = $a_news_obj_type;
	}

	/**
	 * Get enabled news
	 *
	 * @return	boolean	enabled news
	 */
	function getEnabledNews()
	{
		return $this->enabled_news;
	}

	/**
	* Set tab hook
	*/
	function setTabHook($a_object, $a_function)
	{
		$this->tab_hook = array("obj" => $a_object, "func" => $a_function);
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
		$this->tabs_enabled = $a_enabledtabs;
	}

	/**
	* Get Output tabs.
	*
	* @return	boolean	Output tabs
	*/
	function getEnabledTabs()
	{
		return $this->tabs_enabled;
	}

	/**
	* Set Enable Repository Objects Content Component.
	*
	* @param	boolean	$a_enabledrepositoryobjects	Enable Repository Objects Content Component
	*/
	function setEnabledRepositoryObjects($a_enabledrepositoryobjects)
	{
		$this->setEnablePCType("Resources", $a_enabledrepositoryobjects);
	}

	/**
	* Get Enable Repository Objects Content Component.
	*
	* @return	boolean	Enable Repository Objects Content Component
	*/
	function getEnabledRepositoryObjects()
	{
		return $this->getEnablePCType("Resources");
	}

	/**
	 * Set enable login page
	 * @param bool $a_enablegloginpage
	 */
	public function setEnabledLoginPage($a_enablegloginpage)
	{
		$this->setEnablePCType("LoginPageElement", (bool) $a_enablegloginpage);
	}

	/**
	 * Get enable login page
	 * @return bool $enabledloginpage
	 */
	public function getEnabledLoginPage()
	{
		return $this->getEnablePCType("LoginPageElement");
	}

	/**
	* Set Enable Maps Content Component.
	*
	* @param	boolean	$a_enabledmaps	Enable Maps Content Component
	*/
	function setEnabledMaps($a_enabledmaps)
	{
		$this->setEnablePCType("Map", (bool) $a_enabledmaps);
	}

	/**
	* Get Enable Maps Content Component.
	*
	* @return	boolean	Enable Maps Content Component
	*/
	function getEnabledMaps()
	{
		return $this->getEnablePCType("Map");
	}

	/**
	* Set Enable Tabs Content Component.
	*
	* @param	boolean	$a_enabledpctabs	Enable Tabs Content Component
	*/
	function setEnabledPCTabs($a_enabledpctabs)
	{
		$this->setEnablePCType("Tabs", (bool) $a_enabledpctabs);
	}

	/**
	* Get Enable Tabs Content Component.
	*
	* @return	boolean	Enable Tabs Content Component
	*/
	function getEnabledPCTabs()
	{
		return $this->getEnablePCType("Tabs");
	}

	/**
	* Set Enable File Lists Content Componente (Default is true).
	*
	* @param	boolean	$a_enabledfilelists	Enable File Lists Content Componente (Default is true)
	*/
	function setEnabledFileLists($a_enabledfilelists)
	{
		$this->setEnablePCType("FileList", (bool) $a_enabledfilelists);
	}

	/**
	* Get Enable File Lists Content Componente (Default is true).
	*
	* @return	boolean	Enable File Lists Content Componente (Default is true)
	*/
	function getEnabledFileLists()
	{
		return $this->getEnablePCType("FileList");
	}

	/**
	* Set Enable page focus.
	*
	* @param	boolean	$a_enabledpagefocus	Enable page focus
	*/
	function setEnabledPageFocus($a_enabledpagefocus)
	{
		$this->enabledpagefocus = $a_enabledpagefocus;
	}

	/**
	* Get Enable page focus.
	*
	* @return	boolean	Enable page focus
	*/
	function getEnabledPageFocus()
	{
		return $this->enabledpagefocus;
	}

	/**
	* Get Enable Profile Content Component
	*
	* @return	boolean	Enable Profile Content Component
	*/
	function getEnabledProfile()
	{
		return $this->getEnablePCType("Profile");
	}

	/**
	 * Set Enable Profile Content Component
	 * @param bool $a_enableprofile
	 */
	public function setEnabledProfile($a_enableprofile)
	{
		$this->setEnablePCType("Profile", (bool)$a_enableprofile);
	}
	
	/**
	* Get Enable Verification Content Component
	*
	* @return	boolean	Enable Profile Content Component
	*/
	function getEnabledVerification()
	{
		return $this->getEnablePCType("Verification");
	}

	/**
	 * Set Enable Verification Content Component
	 * @param bool $a_enableverification
	 */
	public function setEnabledVerification($a_enableverification)
	{
		$this->setEnablePCType("Verification", (bool)$a_enableverification);
	}
	
	/**
	* Get Enable Blog Content Component
	*
	* @return	boolean	Enable Blog Content Component
	*/
	function getEnabledBlog()
	{
		return $this->getEnablePCType("Blog");
	}

	/**
	 * Set Enable Blog Content Component
	 * @param bool $a_enableblog
	 */
	public function setEnabledBlog($a_enableblog)
	{
		$this->setEnablePCType("Blog", (bool)$a_enableblog);
	}
	
	/**
	* Get Enable Skills Content Component
	*
	* @return	boolean	Enable Skills Content Component
	*/
	function getEnabledSkills()
	{
		return $this->getEnablePCType("Skills");
	}

	/**
	 * Set Enable Skills Content Component
	 * @param bool $a_enableskills
	 */
	public function setEnabledSkills($a_enableskills)
	{
		$this->setEnablePCType("Skills", (bool)$a_enableskills);
	}

	/**
	* Set Explorer Updater
	*
	* @param	object	$a_tree	Tree Object
	*/
	function setExplorerUpdater($a_exp_frame, $a_exp_id, $a_exp_target_script)
	{
		$this->exp_frame = $a_exp_frame;
		$this->exp_id = $a_exp_id;
		$this->exp_target_script = $a_exp_target_script;
	}

	/**
	* Set Prepending HTML.
	*
	* @param	string	$a_prependinghtml	Prepending HTML
	*/
	function setPrependingHtml($a_prependinghtml)
	{
		$this->prependinghtml = $a_prependinghtml;
	}

	/**
	* Get Prepending HTML.
	*
	* @return	string	Prepending HTML
	*/
	function getPrependingHtml()
	{
		return $this->prependinghtml;
	}

	/**
	* Set Enable Wiki Links.
	*
	* @param	boolean	$a_enablewikilinks	Enable Wiki Links
	*/
	function setEnabledWikiLinks($a_enablewikilinks)
	{
		$this->enablewikilinks = $a_enablewikilinks;
	}

	/**
	* Get Enable Wiki Links.
	*
	* @return	boolean	Enable Wiki Links
	*/
	function getEnabledWikiLinks()
	{
		return $this->enablewikilinks;
	}

	/**
	* Set Enable Editing.
	*
	* @param	boolean	$a_enableediting	Enable Editing
	*/
	function setEnableEditing($a_enableediting)
	{
		$this->enableediting = $a_enableediting;
	}

	/**
	* Get Enable Editing.
	*
	* @return	boolean	Enable Editing
	*/
	function getEnableEditing()
	{
		return $this->enableediting;
	}

	/**
	* Set Get raw page content only.
	*
	* @param	boolean	$a_rawpagecontent	Get raw page content only
	*/
	function setRawPageContent($a_rawpagecontent)
	{
		$this->rawpagecontent = $a_rawpagecontent;
	}

	/**
	* Get Get raw page content only.
	*
	* @return	boolean	Get raw page content only
	*/
	function getRawPageContent()
	{
		return $this->rawpagecontent;
	}

	/**
	* Set Layout Mode
	*
	* @param	boolean	$a_layout_mode	SetLayoutMode for editor
	*/
	function setLayoutMode($a_layout_mode)
	{
		$this->layout_mode = $a_layout_mode;
	}

	/**
	* Get Layout Mode enabled/disabled
	*
	* @return	boolean	Get Layout Mode Setting
	*/
	function getLayoutMode()
	{
		return $this->layout_mode;
	}

	/**
	* Set Style Id.
	*
	* @param	int	$a_styleid	Style Id
	*/
	function setStyleId($a_styleid)
	{
		$this->styleid = $a_styleid;
	}

	/**
	* Get Style Id.
	*
	* @return	int	Style Id
	*/
	function getStyleId()
	{
		return $this->styleid;
	}

	/**
	* Set Enable Self Assessment Questions.
	*
	* @param	boolean	$a_enabledselfassessment	Enable Self Assessment Questions
	*/
	function setEnabledSelfAssessment($a_enabledselfassessment, $a_scorm = true)
	{
		$this->setEnablePCType("Question", (bool) $a_enabledselfassessment);
		$this->enabledselfassessment = $a_enabledselfassessment;
		$this->enabledselfassessment_scorm = $a_scorm;
	}


	/**
	* Get Enable Self Assessment Questions.
	*
	* @return	boolean	Enable Self Assessment Questions
	*/
	function getEnabledSelfAssessment()
	{
		return $this->enabledselfassessment;
	}

	/**
	* Is self assessment used in SCORM mode?
	*
	* @return	boolean	Enable Self Assessment Questions
	*/
	function getEnabledSelfAssessmentScorm()
	{
		return $this->enabledselfassessment_scorm;
	}

	/**
	* Set compare mode
	*
	* @param	boolean		compare_mode
	*/
	function setCompareMode($a_val)
	{
		$this->compare_mode = $a_val;
	}
	
	/**
	* Get compare mode
	*
	* @return	boolean		compare_mode
	*/
	function getCompareMode()
	{
		return $this->compare_mode;
	}
	
	/**
	 * Set abstract only
	 *
	 * @param boolean $a_val get only abstract (first text paragraph)	
	 */
	function setAbstractOnly($a_val)
	{
		$this->abstract_only = $a_val;
	}
	
	/**
	 * Get abstract only
	 *
	 * @return boolean get only abstract (first text paragraph)
	 */
	function getAbstractOnly()
	{
		return $this->abstract_only;
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
	 * Determine file download link
	 *
	 * @return	string	file download link
	 */
	function determineFileDownloadLink()
	{
		global $ilCtrl;
		
		$file_download_link = $this->getFileDownloadLink();
		if ($this->getFileDownloadLink() == "" && $this->getOutputMode() != "offline")
		{
			$file_download_link = $ilCtrl->getLinkTarget($this, "downloadFile");
		}
		return $file_download_link;
	}

	/**
	 * Determine fullscreen link
	 *
	 * @return	string	fullscreen link
	 */
	function determineFullscreenLink()
	{
		global $ilCtrl;

		$fullscreen_link = $this->getFullscreenLink();
		if ($this->getFullscreenLink() == "" && $this->getOutputMode() != "offline")
		{
			$fullscreen_link = $ilCtrl->getLinkTarget($this, "displayMediaFullscreen", "", false, false);
		}
		return $fullscreen_link;
	}

	/**
	 * Determine source code download script
	 *
	 * @return	string	sourcecode download script
	 */
	function determineSourcecodeDownloadScript()
	{
		global $ilCtrl;

		$l = $this->sourcecode_download_script;
		if ($this->sourcecode_download_script == "" && $this->getOutputMode() != "offline")
		{
			$l = $ilCtrl->getLinkTarget($this, "");
		}
		return $l;
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
		global $ilCtrl, $ilTabs, $lng;
		
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd();
		//$this->ctrl->addTab("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
		//	, "view", "ilEditClipboardGUI");
		$this->getTabs();
		
		$ilCtrl->setReturn($this, "edit");
//echo "-".$next_class."-";
		switch($next_class)
		{
			case 'ilmdeditorgui':
				//$this->setTabs();
				$ilTabs->setTabActive("meta_data");
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
				//$this->tabs_gui->clearTargets();
				//$this->ctrl->setReturn($this, "view");
				$clip_gui = new ilEditClipboardGUI();
				$clip_gui->setPageBackTitle($this->page_back_title);
				//$ret =& $clip_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($clip_gui);
				break;
				
			// notes
			case "ilnotegui":
				switch($_GET["notes_mode"])
				{
					default:
						$html = $this->edit();
						$ilTabs->setTabActive("edit");
						return $html;
				}
				break;
				
			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				break;

			case "ilpageeditorgui":
				if (!$this->getEnableEditing())
				{
					ilUtil::sendFailure($lng->txt("permission_denied"), true);
					$ilCtrl->redirect($this, "preview");
				}
				$page_editor =& new ilPageEditorGUI($this->getPageObject(), $this);
				$page_editor->setLocator($this->locator);
				$page_editor->setHeader($this->getHeader());
				$page_editor->setPageBackTitle($this->page_back_title);
				$page_editor->setEnableInternalLinks($this->getEnabledInternalLinks());
				$page_editor->setEnableKeywords($this->getEnableKeywords());
				$page_editor->setEnableAnchors($this->getEnableAnchors());
				$page_editor->setIntLinkHelpDefault($this->int_link_def_type,
					$this->int_link_def_id);
				$page_editor->setIntLinkReturn($this->int_link_return);
				//$page_editor->executeCommand();
				$ret =& $this->ctrl->forwardCommand($page_editor);
				break;

			case 'ilnewsitemgui':
				include_once("./Services/News/classes/class.ilNewsItemGUI.php");
				$news_item_gui = new ilNewsItemGUI();
				$news_item_gui->setEnableEdit(true);
				$news_item_gui->setContextObjId($this->news_obj_id);
				$news_item_gui->setContextObjType($this->news_obj_type);
				$news_item_gui->setContextSubObjId($this->obj->getId());
				$news_item_gui->setContextSubObjType("pg");

				$ret = $ilCtrl->forwardCommand($news_item_gui);
				break;

				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$ret = $this->ctrl->forwardCommand($profile_gui);
				break;

			default:
				$cmd = $this->ctrl->getCmd("preview");
				$ret = $this->$cmd();
				break;
		}
//echo "+$ret+";
		return $ret;
	}

	function deactivatePage()
	{
		$this->getPageObject()->setActivationStart(null);
		$this->getPageObject()->setActivationEnd(null);
		$this->getPageObject()->setActive(false);
		$this->getPageObject()->update();
		$this->ctrl->redirect($this, "edit");
	}

	function activatePage()
	{
		$this->getPageObject()->setActivationStart(null);
		$this->getPageObject()->setActivationEnd(null);
		$this->getPageObject()->setActive(true);
		$this->getPageObject()->update();
		$this->ctrl->redirect($this, "edit");
	}

	/*
	* display content of page
	*/
	function showPage()
	{
		global $tree, $ilUser, $ilias, $lng, $ilCtrl, $ilBench, $ilSetting, $ilTabs;

		$ilBench->start("ContentPresentation", "ilPageObjectGUI_showPage");
		
		$this->initSelfAssessmentRendering();

		$GLOBALS["tpl"]->addJavaScript("./Services/COPage/js/ilCOPagePres.js");
		
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

				// explorer updater
				if ($this->exp_frame != "")
				{
					$tpl->setCurrentBlock("updater");
					$tpl->setVariable("UPDATER_FRAME", $this->exp_frame);
					$tpl->setVariable("EXP_ID_UPDATER", $this->exp_id);
					$tpl->setVariable("HREF_UPDATER", $this->exp_target_script);
					$tpl->parseCurrentBlock();
				}

/*				$tpl->setVariable("TXT_INSERT_BEFORE", $this->lng->txt("cont_set_before"));
				$tpl->setVariable("TXT_INSERT_AFTER", $this->lng->txt("cont_set_after"));
				$tpl->setVariable("TXT_INSERT_CANCEL", $this->lng->txt("cont_set_cancel"));
				$tpl->setVariable("TXT_CONFIRM_DELETE", $this->lng->txt("cont_confirm_delete"));
*/
				$tpl->setVariable("WYSIWYG_ACTION",
					$ilCtrl->getFormActionByClass("ilpageeditorgui", "", "", true));


				// determine media, html and javascript mode
				$sel_media_mode = ($ilUser->getPref("ilPageEditor_MediaMode") == "disable")
					? "disable"
					: "enable";
				$sel_html_mode = ($ilUser->getPref("ilPageEditor_HTMLMode") == "disable")
					? "disable"
					: "enable";
				$sel_js_mode = "disable";
				//if($ilSetting->get("enable_js_edit", 1))
				//{
					$sel_js_mode = (ilPageEditorGUI::_doJSEditing())
						? "enable"
						: "disable";
				//}

				// show prepending html
				$tpl->setVariable("PREPENDING_HTML", $this->getPrependingHtml());
				$tpl->setVariable("TXT_CONFIRM_DELETE", $lng->txt("cont_confirm_delete"));

				// presentation view
				if ($this->getViewPageLink() != "")
				{
					$ilTabs->addNonTabbedLink("pres_view", $this->lng->txt("cont_presentation_view"),
						$this->getViewPageLink(), $this->getViewPageTarget());
				}

				// show actions drop down
				$this->addActionsMenu($tpl, $sel_media_mode, $sel_html_mode, $sel_js_mode);

				// get js files for JS enabled editing
				if ($sel_js_mode == "enable")
				{
					$this->insertHelp($tpl);
					include_once("./Services/YUI/classes/class.ilYuiUtil.php");
					ilYuiUtil::initDragDrop();
					ilYuiUtil::initConnection();
					ilYuiUtil::initPanel(false);
					$GLOBALS["tpl"]->addJavaScript("./Services/COPage/js/ilcopagecallback.js");
					$GLOBALS["tpl"]->addJavascript("Services/COPage/js/page_editing.js");

					include_once './Services/Style/classes/class.ilObjStyleSheet.php';
					$GLOBALS["tpl"]->addOnloadCode("var preloader = new Image();
						preloader.src = './templates/default/images/loader.gif';
						ilCOPage.setContentCss('".
						ilObjStyleSheet::getContentStylePath((int) $this->getStyleId()).
						", ".ilUtil::getStyleSheetLocation().
						", ./Services/COPage/css/tiny_extra.css".
						"')");

					$GLOBALS["tpl"]->addJavascript("Services/RTE/tiny_mce_3_3_9_2/il_tiny_mce_src.js");
					$tpl->touchBlock("init_dragging");

					$tpl->setVariable("IL_TINY_MENU",
						self::getTinyMenu(
						$this->getPageObject()->getParentType(),
						$this->getEnabledInternalLinks(),
						$this->getPageObject()->getParentType() == "wpg",
						$this->getEnableKeywords(), $this->getStyleId(), true, true,
						$this->getEnableAnchors()
						));
					
					// add int link parts
					include_once("./Modules/LearningModule/classes/class.ilInternalLinkGUI.php");
					$tpl->setCurrentBlock("int_link_prep");
					$tpl->setVariable("INT_LINK_PREP", ilInternalLinkGUI::getInitHTML(
						$ilCtrl->getLinkTargetByClass(array("ilpageeditorgui", "ilinternallinkgui"),
								"", false, true, false)));
					$tpl->parseCurrentBlock();

					// internal links per js
					//$tpl->setVariable("IL_INT_LINK_URL",
					//	$ilCtrl->getLinkTargetByClass(array("ilpageeditorgui", "ilinternallinkgui"),
					//		"showLinkHelp", false, true, false));

					include_once("./Services/YUI/classes/class.ilYuiUtil.php");
					ilYuiUtil::initConnection();
					$GLOBALS["tpl"]->addJavaScript("./Services/Explorer/js/ilexplorercallback.js");

				}

				// multiple actions
				$cnt_pcs = $this->getPageObject()->countPageContents();
				if ($cnt_pcs > 1 ||
					($this->getPageObject()->getParentType() != "qpl" && $cnt_pcs > 0))
				{
					$tpl->setCurrentBlock("multi_actions");
					if ($sel_js_mode == "enable")
					{
						$tpl->setVariable("ONCLICK_DE_ACTIVATE_SELECTED", 'onclick="return ilEditMultiAction(\'activateSelected\');"');
						$tpl->setVariable("ONCLICK_DELETE_SELECTED", 'onclick="return ilEditMultiAction(\'deleteSelected\');"');
						$tpl->setVariable("ONCLICK_ASSIGN_CHARACTERISTIC", 'onclick="return ilEditMultiAction(\'assignCharacteristicForm\');"');
						$tpl->setVariable("ONCLICK_COPY_SELECTED", 'onclick="return ilEditMultiAction(\'copySelected\');"');
						$tpl->setVariable("ONCLICK_CUT_SELECTED", 'onclick="return ilEditMultiAction(\'cutSelected\');"');
						$tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));
						$tpl->setVariable("ONCLICK_SELECT_ALL", 'onclick="return ilEditMultiAction(\'selectAll\');"');
					}
					$tpl->setVariable("TXT_DE_ACTIVATE_SELECTED", $this->lng->txt("cont_ed_enable"));
					$tpl->setVariable("TXT_ASSIGN_CHARACTERISTIC", $this->lng->txt("cont_assign_characteristic"));
					$tpl->setVariable("TXT_DELETE_SELECTED", $this->lng->txt("cont_delete_selected"));
					$tpl->setVariable("TXT_COPY_SELECTED", $this->lng->txt("copy"));
					$tpl->setVariable("TXT_CUT_SELECTED", $this->lng->txt("cut"));
					$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
					$tpl->parseCurrentBlock();
				}
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
						if ($this->getEnabledPageFocus())
						{
							$tpl->touchBlock("page_focus");
						}
					}
					else	// preview
					{
						$tpl = new ilTemplate("tpl.page_preview.html", true, true, "Services/COPage");
						include_once("./Services/User/classes/class.ilUserUtil.php");
 
						$c_old_nr = $this->getPageObject()->old_nr;
						if ($c_old_nr > 0)
						{
							$hist_info =
								$this->getPageObject()->getHistoryInfo($c_old_nr);
								
							if (!$this->getCompareMode())
							{
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
							
								// rollback
								if ($c_old_nr > 0 && $ilUser->getId() != ANONYMOUS_USER_ID)
								{
									$tpl->setCurrentBlock("rollback");
									$ilCtrl->setParameter($this, "old_nr", $c_old_nr);
									$tpl->setVariable("HREF_ROLLBACK",
										$ilCtrl->getLinkTarget($this, "rollbackConfirmation"));
									$ilCtrl->setParameter($this, "old_nr", "");
									$tpl->setVariable("TXT_ROLLBACK",
										$lng->txt("cont_rollback"));
									$tpl->parseCurrentBlock();
								}
							}
							
							$tpl->setCurrentBlock("hist_nav");
							$tpl->setVariable("TXT_REVISION", $lng->txt("cont_revision"));
							$tpl->setVariable("VAL_REVISION_DATE",
								ilDatePresentation::formatDate(new ilDateTime($hist_info["current"]["hdate"], IL_CAL_DATETIME)));
							$tpl->setVariable("VAL_REV_USER",
								ilUserUtil::getNamePresentation($hist_info["current"]["user_id"]));
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
						if (ilObject::_exists($link["id"]) && ilObject::_lookupType($link["id"]) == "mob")
						{
							$mob_links[$link["id"]] = ilObject::_lookupTitle($link["id"])." [".$link["id"]."]";
						}
					}
				}

				// linked media objects
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
				
				// content snippets used
				$snippets = $this->getPageObject()->collectContentIncludes();
				if (count($snippets) > 0)
				{
					foreach ($snippets as $s)
					{
						include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
						$sn_arr[$s["id"]] = ilMediaPoolPage::lookupTitle($s["id"]);
					}
					$tpl->setCurrentBlock("med_link");
					$tpl->setVariable("TXT_CONTENT_SNIPPETS_USED", $this->lng->txt("cont_snippets_used"));
					$tpl->setVariable("SEL_SNIPPETS",
						ilUtil::formSelect(0, "ci_id", $sn_arr, false, true));
					$tpl->setVariable("TXT_SHOW_INFO", $this->lng->txt("cont_show_info"));
					$tpl->parseCurrentBlock();
				}
				
				// scheduled activation?
				if (!$this->getPageObject()->getActive() &&
					$this->getPageObject()->getActivationStart() != "" &&
					$this->getEnabledScheduledActivation())
				{
					$tpl->setCurrentBlock("activation_txt");
					$tpl->setVariable("TXT_SCHEDULED_ACTIVATION", $lng->txt("cont_scheduled_activation"));
					$tpl->setVariable("SA_FROM",
						ilDatePresentation::formatDate(
						new ilDateTime($this->getPageObject()->getActivationStart(),
						IL_CAL_DATETIME)));
					$tpl->setVariable("SA_TO",
						ilDatePresentation::formatDate(
						new ilDateTime($this->getPageObject()->getActivationEnd(),
						IL_CAL_DATETIME)));
					$tpl->parseCurrentBlock();
				}
			}

			if ($_GET["reloadTree"] == "y")
			{
				$tpl->setCurrentBlock("reload_tree");
				if ($this->obj->getParentType() == "dbk")
				{
					$tpl->setVariable("LINK_TREE",
						$this->ctrl->getLinkTargetByClass("ilobjdlbookgui", "explorer", "", false, false));
				}
				else
				{
					$tpl->setVariable("LINK_TREE",
						$this->ctrl->getLinkTargetByClass("ilobjlearningmodulegui", "explorer", "", false, false));
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

//echo "<br>-".htmlentities($this->obj->getXMLContent())."-<br><br>";
//echo "<br>-".htmlentities($this->getLinkXML())."-";

		// set default link xml, if nothing was set yet
		if (!$this->link_xml_set)
		{
			$this->setDefaultLinkXml();
		}

		//$content = $this->obj->getXMLFromDom(false, true, true,
		//	$this->getLinkXML().$this->getQuestionXML().$this->getComponentPluginsXML());
		$link_xml = $this->getLinkXML();

		// disable/enable auto margins
		if ($this->getStyleId() > 0)
		{
			if (ilObject::_lookupType($this->getStyleId()) == "sty")
			{
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$style = new ilObjStyleSheet($this->getStyleId());
				$template_xml = $style->getTemplateXML();
				$disable_auto_margins = "n";
				if ($style->lookupStyleSetting("disable_auto_margins"))
				{
					$disable_auto_margins = "y";
				}
			}
		}

		if ($this->getAbstractOnly())
		{
			$content = "<dummy><PageObject><PageContent><Paragraph>".
				$this->obj->getFirstParagraphText().$link_xml.
				"</Paragraph></PageContent></PageObject></dummy>";
		}
		else
		{
			$content = $this->obj->getXMLFromDom(false, true, true,
				$link_xml.$this->getQuestionXML().$template_xml);
		}

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
			ilUtil::sendFailure($this->lng->txt("cont_citation_selection_not_valid"));
			session_unregister("citation_error");
			unset($_SESSION["citation_error"]);
		}

		// get title
		$pg_title = $this->getPresentationTitle();

		$add_path = ilUtil::getImagePath("add.gif");
		$col_path = ilUtil::getImagePath("col.gif");
		$row_path = ilUtil::getImagePath("row.gif");
		$item_path = ilUtil::getImagePath("item.gif");
		$med_disabled_path = ilUtil::getImagePath("media_disabled.gif");

		if ($this->getOutputMode() != "offline")
		{
			$enlarge_path = ilUtil::getImagePath("enlarge.gif");
			$wb_path = ilUtil::getWebspaceDir("output")."/";
		}
		else
		{
			$enlarge_path = "images/enlarge.gif";
			$wb_path = "";
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
			//include_once("./Services/YUI/classes/class.ilYuiUtil.php");
			//ilYuiUtil::initTabView();
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
			ilAccordionGUI::addJavaScript();
			ilAccordionGUI::addCss();
		}

		$file_download_link = $this->determineFileDownloadLink();
		$fullscreen_link = $this->determineFullscreenLink();
		$this->sourcecode_download_script = $this->determineSourcecodeDownloadScript();
		
		// default values for various parameters (should be used by
		// all instances in the future)
		$media_mode = ($this->getOutputMode() == "edit")
			? $ilUser->getPref("ilPageEditor_MediaMode")
			: "enable";

		include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
		$paste = (ilEditClipboard::getAction() == "copy" &&
			$this->getOutputMode() == "edit");

		// added UTF-8 encoding otherwise umlaute are converted too
		$params = array ('mode' => $this->getOutputMode(), 'pg_title' => htmlentities($pg_title,ENT_QUOTES,"UTF-8"),
						 'layout_mode' => $this->getLayoutMode() ? "y" : "n",
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
						 'enable_login_page' => $this->getEnabledLoginPage() ? "y" : "n",
						 'enable_map' => $this->getEnabledMaps() ? "y" : "n",
						 'enable_tabs' => $this->getEnabledPCTabs() ? "y" : "n",
						 'enable_sa_qst' => $this->getEnabledSelfAssessment() ? "y" : "n",
						 'enable_file_list' => $this->getEnabledFileLists() ? "y" : "n",
						 'enable_content_includes' => $this->isEnabledContentIncludes() ? "y" : "n",
						 'paste' => $paste ? "y" : "n",
						 'media_mode' => $media_mode,
						 'javascript' => $sel_js_mode,
						 'paragraph_plugins' => $paragraph_plugin_string,
						 'disable_auto_margins' => $disable_auto_margins,
						 'page_toc' => $this->getPageToc() ? "y" : "n",
						 'enable_profile' =>  $this->getEnabledProfile() ? "y" : "n",
						 'enable_verification' =>  $this->getEnabledVerification() ? "y" : "n",
						 'enable_blog' =>  $this->getEnabledBlog() ? "y" : "n",
						 'enable_skills' =>  $this->getEnabledSkills() ? "y" : "n",
						 'enable_qover' =>  $this->getEnablePCType("QuestionOverview") ? "y" : "n"
						);
		if($this->link_frame != "")		// todo other link types
			$params["pg_frame"] = $this->link_frame;

		//$content = str_replace("&nbsp;", "", $content);
		
		// this ensures that cache is emptied with every update
		$params["version"] = ILIAS_VERSION;
		
		// ensure no cache hit, if included files/media objects have been changed
		$params["incl_elements_date"] = $this->obj->getLastUpdateOfIncludedElements();

		// run xslt
		$md5 = md5(serialize($params).$link_xml.$template_xml);
		
//$a = microtime();
		
		// check cache (same parameters, non-edit mode and rendered time
		// > last change
		if (($this->getOutputMode() == "preview" || $this->getOutputMode() == "presentation") &&
			!$this->getAbstractOnly() &&
			$md5 == $this->obj->getRenderMd5() &&
			($this->obj->getLastChange() < $this->obj->getRenderedTime()) &&
			$this->obj->getRenderedTime() != "" &&
			$this->obj->old_nr == 0)
		{
			// cache hit
			$output = $this->obj->getRenderedContent();
		}
		else
		{
			if ($this->getLayoutMode() == true)
			{
//				$xsl = file_get_contents("./Services/COPage/xsl/page_layout.xsl");
				$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
			}
			else
			{
				$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");	
			}
//echo htmlentities($content); exit;
			$args = array( '/_xml' => $content, '/_xsl' => $xsl );
			$xh = xslt_create();
			//		echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
			//		echo "mode:".$this->getOutputMode().":<br>";
			$output = xslt_process($xh, "arg:/_xml","arg:/_xsl", NULL, $args, $params);
			
			if (($this->getOutputMode() == "presentation" || $this->getOutputMode() == "preview")
				&& !$this->getAbstractOnly()
				&& $this->obj->old_nr == 0)
			{
//echo "writerenderedcontent";
				$this->obj->writeRenderedContent($output, $md5);
			}
			//echo xslt_error($xh);
			xslt_free($xh);
		}

//$b = microtime();
//echo "$a - $b";
//echo "<pre>".htmlentities($output)."</pre>";
		
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

		// insert page snippets
		$output = $this->insertContentIncludes($output);

		// insert page toc
		if ($this->getPageToc())
		{
			$output = $this->insertPageToc($output);
		}

		// workaround for preventing template engine
		// from hiding paragraph text that is enclosed
		// in curly brackets (e.g. "{a}", see ilLMEditorGUI::executeCommand())
		$output = $this->replaceCurlyBrackets($output);

		// remove all newlines (important for code / pre output)
		$output = str_replace("\n", "", $output);

		// add question HTML (always after the cache!)
		$qhtml = $this->getQuestionHTML();
		if (is_array($qhtml))
		{
			foreach ($qhtml as $k => $h)
			{
				$output = str_replace($this->pl_start."Question;il__qst_$k".$this->pl_end, " ".$h, $output);
			}
		}

//echo htmlentities($output);
		$output = $this->postOutputProcessing($output);
//echo htmlentities($output);
		if($this->getOutputMode() == "edit" && !$this->getPageObject()->getActive($this->getEnabledScheduledActivation()))
		{
			$output = '<div class="il_editarea_disabled">'.$output.'</div>';
		}
		
		$output = $this->insertMaps($output);
		$output = $this->obj->insertSourceCodeParagraphs($output, $this->getOutputMode());

		$ilBench->stop("ContentPresentation", "ilPageObjectGUI_showPage");

		$output = $this->selfAssessmentRendering($output);

		// output
		if ($ilCtrl->isAsynch() && !$this->getRawPageContent())
		{
			if ($_GET["updated_pc_id_str"] != "")
			{
				echo $_GET["updated_pc_id_str"];
			}
			$tpl->setVariable($this->getTemplateOutputVar(), $output);
			$tpl->setCurrentBlock("edit_page");
			$tpl->parseCurrentBlock();
			echo $tpl->get("edit_page");
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
			if ($this->getRawPageContent())		// e.g. needed in glossaries
			{
				return $output;
			}
			else
			{
				$tpl->setVariable($this->getTemplateOutputVar(), $output);
				return $tpl->get();
			}
		}
	}

	/**
	 * Replace curly brackets
	 *
	 * @param
	 * @return
	 */
	function replaceCurlyBrackets($output)
	{
//echo "<br><br>".htmlentities($output);
		
		while (is_int($start = strpos($output, "<!--ParStart-->")) &&
			is_int($end = strpos($output, "<!--ParEnd-->", $start)))
		{
			$output = substr($output, 0, $start).
				str_replace(array("{","}"), array("&#123;","&#125;"),
					substr($output, $start + 15, $end - ($start + 15))).
				substr($output, $end + 13);
		}

//		$output = str_replace("{", "&#123;", $output);
//		$output = str_replace("}", "&#125;", $output);
//echo "<br><br>".htmlentities($output);
		return $output;
	}
	
	/**
	 * Get captions for activation action menu entries
	 */
	protected function getActivationCaptions()
	{
		global $lng;
		
		return array("deactivatePage" => $lng->txt("cont_deactivate_page"),
				"activatePage" => $lng->txt("cont_activate_page"));
	}	
	
	/**
	 * Add actions menu
	 */
	function addActionsMenu($a_tpl, $sel_media_mode, $sel_html_mode, $sel_js_mode)
	{
		global $lng, $ilCtrl;

		// actions
		include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

		// activate/deactivate
		if ($this->getEnabledActivation())
		{
			$list = new ilAdvancedSelectionListGUI();
			$list->setListTitle($lng->txt("actions"));
			$list->setId("copage_act");
			
			$captions = $this->getActivationCaptions();			
			if ($this->getPageObject()->getActive())
			{
				$list->addItem($captions["deactivatePage"], "",
					$ilCtrl->getLinkTarget($this, "deactivatePage"));
			}
			else
			{
				$list->addItem($captions["activatePage"], "",
					$ilCtrl->getLinkTarget($this, "activatePage"));
			}
			
			$a_tpl->setVariable("PAGE_ACTIONS", $list->getHTML());
		}

		$lng->loadLanguageModule("content");
		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle($lng->txt("cont_edit_mode"));
		$list->setId("copage_ed_mode");

		// media mode
		if ($sel_media_mode == "enable")
		{
			$ilCtrl->setParameter($this, "media_mode", "disable");
			$list->addItem($lng->txt("cont_deactivate_media"), "",
				$ilCtrl->getLinkTarget($this, "setEditMode"));
		}
		else
		{
			$ilCtrl->setParameter($this, "media_mode", "enable");
			$list->addItem($lng->txt("cont_activate_media"), "",
				$ilCtrl->getLinkTarget($this, "setEditMode"));
		}
		$ilCtrl->setParameter($this, "media_mode", "");

		// html mode
		if (!$this->getPreventHTMLUnmasking())
		{
			if ($sel_html_mode == "enable")
			{
				$ilCtrl->setParameter($this, "html_mode", "disable");
				$list->addItem($lng->txt("cont_deactivate_html"), "",
					$ilCtrl->getLinkTarget($this, "setEditMode"));
			}
			else
			{
				$ilCtrl->setParameter($this, "html_mode", "enable");
				$list->addItem($lng->txt("cont_activate_html"), "",
					$ilCtrl->getLinkTarget($this, "setEditMode"));
			}
		}
		$ilCtrl->setParameter($this, "html_mode", "");

		// js mode
		if ($sel_js_mode == "enable")
		{
			$ilCtrl->setParameter($this, "js_mode", "disable");
			$list->addItem($lng->txt("cont_deactivate_js"), "",
				$ilCtrl->getLinkTarget($this, "setEditMode"));
		}
		else
		{
			$ilCtrl->setParameter($this, "js_mode", "enable");
			$list->addItem($lng->txt("cont_activate_js"), "",
				$ilCtrl->getLinkTarget($this, "setEditMode"));
		}
		$ilCtrl->setParameter($this, "js_mode", "");

		$a_tpl->setVariable("EDIT_MODE", $list->getHTML());
	}

	/**
	 * Set edit mode
	 */
	function setEditMode()
	{
		global $ilCtrl, $ilUser;

		if ($_GET["media_mode"] != "")
		{
			if ($_GET["media_mode"] == "disable")
			{
				$ilUser->writePref("ilPageEditor_MediaMode", "disable");
			}
			else
			{
				$ilUser->writePref("ilPageEditor_MediaMode", "");
			}
		}
		if ($_GET["html_mode"] != "")
		{
			if ($_GET["html_mode"] == "disable")
			{
				$ilUser->writePref("ilPageEditor_HTMLMode", "disable");
			}
			else
			{
				$ilUser->writePref("ilPageEditor_HTMLMode", "");
			}
		}
		if ($_GET["js_mode"] != "")
		{
			if ($_GET["js_mode"] == "disable")
			{
				$ilUser->writePref("ilPageEditor_JavaScript", "disable");
			}
			else
			{
				$ilUser->writePref("ilPageEditor_JavaScript", "");
			}
		}

		$ilCtrl->redirect($this, "edit");
	}


	/**
	 * Get Tiny Menu
	 */
	static function getTinyMenu($a_par_type,
		$a_int_links = false, $a_wiki_links = false, $a_keywords = false,
		$a_style_id = 0, $a_paragraph_styles = true, $a_save_return = true,
		$a_anchors = false)
	{
		global $lng;

		$jsMathSetting = new ilSetting("jsMath");
		
		include_once("./Services/COPage/classes/class.ilPageEditorSettings.php");

		include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
		
		$btpl = new ilTemplate("tpl.tiny_menu.html", true, true, "Services/COPage");
		
		// debug ghost element
		if (DEVMODE == 1)
		{
			$btpl->touchBlock("debug_ghost");
		}

		// paste from word
		$btpl->setCurrentBlock("pword_button");
		$btpl->setVariable("IMG_PWORD", ilUtil::img(ilUtil::getImagePath("tn_pword.gif"),
			"", 20, 20));
		$btpl->parseCurrentBlock();
		ilTooltipGUI::addTooltip("il_edm_pword",
			$lng->txt("cont_pword"),
			"iltinymenu_bd");
		
		// bullet list
		$btpl->setCurrentBlock("blist_button");
		$btpl->setVariable("IMG_BLIST", ilUtil::img(ilUtil::getImagePath("tn_blist.gif"),
			"", 20, 20));
		$btpl->parseCurrentBlock();
		ilTooltipGUI::addTooltip("il_edm_blist",
			$lng->txt("cont_blist"),
			"iltinymenu_bd");

		// numbered list
		$btpl->setCurrentBlock("nlist_button");
		$btpl->setVariable("IMG_NLIST", ilUtil::img(ilUtil::getImagePath("tn_nlist.gif"),
			"", 20, 20));
		$btpl->parseCurrentBlock();
		ilTooltipGUI::addTooltip("il_edm_nlist",
			$lng->txt("cont_nlist"),
			"iltinymenu_bd");

		// list indent
		$btpl->setCurrentBlock("list_indent");
		$btpl->setVariable("IMG_LIST_INDENT", ilUtil::img(ilUtil::getImagePath("tn_indent.gif"),
			"", 20, 20));
		$btpl->parseCurrentBlock();
		ilTooltipGUI::addTooltip("ilIndentBut",
			$lng->txt("cont_list_indent"),
			"iltinymenu_bd");


		// list outdent
		$btpl->setCurrentBlock("list_outdent");
		$btpl->setVariable("IMG_LIST_OUTDENT", ilUtil::img(ilUtil::getImagePath("tn_outdent.gif"),
			"", 20, 20));
		$btpl->parseCurrentBlock();
		ilTooltipGUI::addTooltip("ilOutdentBut",
			$lng->txt("cont_list_outdent"),
			"iltinymenu_bd");

		if ($a_int_links)
		{
			$btpl->touchBlock("bb_ilink_button");
			ilTooltipGUI::addTooltip("iosEditInternalLinkTrigger", $lng->txt("cont_link_to_internal"),
				"iltinymenu_bd");
		}
		ilTooltipGUI::addTooltip("il_edm_xlink", $lng->txt("cont_link_to_external"),
			"iltinymenu_bd");

		// remove format
		$btpl->setCurrentBlock("rformat_button");
		$btpl->setVariable("IMG_RFORMAT", ilUtil::img(ilUtil::getImagePath("tn_rformat.gif"),
			"", 20, 20));
		$btpl->parseCurrentBlock();
		ilTooltipGUI::addTooltip("il_edm_rformat", $lng->txt("cont_remove_format"),
			"iltinymenu_bd");

		if ($a_paragraph_styles)
		{
			// new paragraph
			$btpl->setCurrentBlock("new_par");
			$btpl->setVariable("IMG_NEWPAR", "+");
			$btpl->parseCurrentBlock();
			ilTooltipGUI::addTooltip("il_edm_newpar", $lng->txt("cont_insert_new_paragraph"),
				"iltinymenu_bd");
			
			$btpl->setCurrentBlock("par_edit");
			$btpl->setVariable("TXT_PAR_FORMAT", $lng->txt("cont_par_format"));
			include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
			$btpl->setVariable("STYLE_SELECTOR", ilPCParagraphGUI::getStyleSelector($a_selected,
				ilPCParagraphGUI::_getCharacteristics($a_style_id), true));
			
			ilTooltipGUI::addTooltip("ilAdvSelListAnchorText_style_selection",
				$lng->txt("cont_paragraph_styles"), "iltinymenu_bd");

			$btpl->parseCurrentBlock();
		}

		if ($a_keywords)
		{
			$btpl->setCurrentBlock("bb_kw_button");
			$btpl->setVariable("CC_KW", "kw");
			$btpl->parseCurrentBlock();
			ilTooltipGUI::addTooltip("il_edm_kw", $lng->txt("cont_text_keyword"),
				"iltinymenu_bd");

		}

		if ($a_wiki_links)
		{
			$btpl->setCurrentBlock("bb_wikilink_button");
			$btpl->setVariable("TXT_WLN2", $lng->txt("obj_wiki"));
			$btpl->parseCurrentBlock();
			ilTooltipGUI::addTooltip("il_edm_wlink", $lng->txt("cont_link_to_wiki"),
				"iltinymenu_bd");
		}
//		$jsMathSetting = new ilSetting("jsMath");
//		$style = $this->getStyle();

		$aset = new ilSetting("adve");
		
		include_once("./Services/COPage/classes/class.ilPageContentGUI.php");
		foreach (ilPageContentGUI::_getCommonBBButtons() as $c => $st)
		{
			// these are handled via drop down now...
			if (in_array($c, array("com", "quot", "acc", "code")))
			{
				continue;
			}
			if (ilPageEditorSettings::lookupSettingByParentType(
				$a_par_type, "active_".$c, true))
			{
				$cc_code = $c;
				if ($aset->get("use_physical"))
				{
					$cc_code = str_replace(array("str", "emp", "imp"), array("B", "I", "U"), $cc_code);
				}
				
				if ($c != "tex" || $jsMathSetting->get("enable") || defined("URL_TO_LATEX"))
				{
					$btpl->setCurrentBlock("bb_".$c."_button");
					$btpl->setVariable("CC_".strtoupper($c), $cc_code);
					$btpl->parseCurrentBlock();
					ilTooltipGUI::addTooltip("il_edm_cc_".$c,
						$lng->txt("cont_cc_".$c),
						"iltinymenu_bd");

//					$btpl->setVariable("TXT_".strtoupper($c), $this->lng->txt("cont_text_".$c));
				}
			}
		}
		
		if ($jsMathSetting->get("enable") || defined("URL_TO_LATEX"))
		{
			ilTooltipGUI::addTooltip("il_edm_tex", $lng->txt("cont_tex"),
				"iltinymenu_bd");
		}
		ilTooltipGUI::addTooltip("il_edm_fn", $lng->txt("cont_fn"),
			"iltinymenu_bd");

		if ($a_save_return)
		{
			$btpl->setCurrentBlock("save_return");
			$btpl->setVariable("TXT_SAVE_RETURN", $lng->txt("save_return"));
			$btpl->parseCurrentBlock();
		}

		if ($a_anchors)
		{
			$btpl->setCurrentBlock("bb_anc_button");
			$btpl->setVariable("CC_ANC", "anc");
			$btpl->parseCurrentBlock();
			ilTooltipGUI::addTooltip("il_edm_anc", $lng->txt("cont_anchor"),
				"iltinymenu_bd");
		}

/*		// footnote
		$btpl->setVariable("TXT_ILN", $this->lng->txt("cont_text_iln"));
		$btpl->setVariable("TXT_BB_TIP", $this->lng->txt("cont_bb_tip"));
		$btpl->setVariable("TXT_WLN", $lng->txt("wiki_wiki_page"));
*/
//		$btpl->setVariable("PAR_TA_NAME", $a_ta_name);

		$btpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$btpl->setVariable("TXT_CANCEL", $lng->txt("cancel"));

		$btpl->setVariable("TXT_CHAR_FORMAT", $lng->txt("cont_char_format"));
		$btpl->setVariable("TXT_LISTS", $lng->txt("cont_lists"));
		$btpl->setVariable("TXT_LINKS", $lng->txt("cont_links"));
		$btpl->setVariable("TXT_MORE_FUNCTIONS", $lng->txt("cont_more_functions"));
		$btpl->setVariable("TXT_SAVING", $lng->txt("cont_saving"));
		
		$btpl->setVariable("CHAR_STYLE_SELECTOR", ilPCParagraphGUI::getCharStyleSelector($a_par_type));
		ilTooltipGUI::addTooltip("ilAdvSelListAnchorElement_char_style_selection",
			$lng->txt("cont_more_character_styles"), "iltinymenu_bd");

		return $btpl->get();
	}

	/**
	 * Set standard link xml
	 */
	function setDefaultLinkXml()
	{
		global $ilCtrl;

		$int_links = $this->getPageObject()->getInternalLinks();
//var_dump($int_links);
		$link_info = "<IntLinkInfos>";
		$targetframe = "None";
		foreach ($int_links as $int_link)
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

				$ltarget="_top";
				if ($targetframe != "None")
				{
					$ltarget="_blank";
				}

				// anchor
				$anc = $anc_add = "";
				if ($int_link["Anchor"] != "")
				{
					$anc = $int_link["Anchor"];
					$anc_add = "_".rawurlencode($int_link["Anchor"]);
				}

				$href = "";
				switch($type)
				{
					case "PageObject":
					case "StructureObject":
						$lm_id = ilLMObject::_lookupContObjID($target_id);
						if ($type == "PageObject")
						{
							$href = "./goto.php?target=pg_".$target_id.$anc_add;
						}
						else
						{
							$href = "./goto.php?target=st_".$target_id;
						}
						break;

					case "GlossaryItem":
						if ($targetframe == "None")
						{
							$targetframe = "Glossary";
						}
						$href = "./goto.php?target=git_".$target_id;
						break;

					case "MediaObject":
						$ilCtrl->setParameter($this, "mob_id", $target_id);
						//$ilCtrl->setParameter($this, "pg_id", $this->obj->getId());
						$href = $ilCtrl->getLinkTarget($this, "displayMedia");
						$ilCtrl->setParameter($this, "mob_id", "");
						break;

					case "RepositoryItem":
						$obj_type = ilObject::_lookupType($target_id, true);
						$obj_id = ilObject::_lookupObjId($target_id);
						$href = "./goto.php?target=".$obj_type."_".$target_id;
						break;


				}
				$anc_par = 'Anchor="'.$anc.'"';
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".$anc_par." ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" />";
			}
		}
		$link_info.= "</IntLinkInfos>";
		$this->setLinkXML($link_info);
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
		$this->displayMedia(true);
	}

	/**
	 * Display media
	 */
	function displayMedia($a_fullscreen = false)
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
		$pg_obj = $this->getPageObject();
		$pg_obj->buildDom();

		if (!empty ($_GET["pg_id"]))
		{
			$xml = "<dummy>";
			$xml.= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
			$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
			$xml.= $link_xml;
			$xml.="</dummy>";
		}
		else
		{
			$xml = "<dummy>";
			$xml.= $media_obj->getXML(IL_MODE_ALIAS);
			$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
			$xml.= $link_xml;
			$xml.="</dummy>";
		}

		$xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

		$mode = "media";
		if ($a_fullscreen)
		{
			$mode = "fullscreen";
		}

//echo "<b>XML:</b>".htmlentities($xml);
		// determine target frames for internal links
		$wb_path = ilUtil::getWebspaceDir("output")."/";
		$enlarge_path = ilUtil::getImagePath("enlarge.gif");
		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$_GET["ref_id"],'fullscreen_link' => "",
			'ref_id' => $_GET["ref_id"], 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
//echo "<br><br>".htmlentities($output);
	//echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
				ilObjStyleSheet::getContentStylePath(0));
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$tpl->setVariable("MEDIA_CONTENT", $output);
		echo $tpl->get();
		exit;
	}

	/**
	* download source code paragraph
	*/
	function download_paragraph()
	{
		$pg_obj = $this->getPageObject();
		$pg_obj->send_paragraph($_GET["par_id"], $_GET["downloadtitle"]);
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
	* Insert Maps
	*/
	function insertContentIncludes($a_html)
	{
		global $ilCtrl, $lng;
		
		$c_pos = 0;
		$start = strpos($a_html, "{{{{{ContentInclude;");
		if (is_int($start))
		{
			$end = strpos($a_html, "}}}}}", $start);
		}
		$i = 1;
		while ($end > 0)
		{
			$param = substr($a_html, $start + 20, $end - $start - 20);
			$param = explode(";", $param);

			if ($param[0] == "mep" && is_numeric($param[1]) && $param[2] <= 0)
			{
				include_once("./Modules/MediaPool/classes/class.ilMediaPoolPageGUI.php");

				if (ilMediaPoolPage::_exists($param[1]))
				{
					$page_gui = new ilMediaPoolPageGUI($param[1], 0, true);
					if ($this->getOutputMode() != "offline")
					{
						$page_gui->setFileDownloadLink($this->determineFileDownloadLink());
						$page_gui->setFullscreenLink($this->determineFullscreenLink());
						$page_gui->setSourceCodeDownloadScript($this->determineSourcecodeDownloadScript());
					}
					else
					{
						$page_gui->setOutputMode(IL_PAGE_OFFLINE);
					}
		
					$html = $page_gui->getRawContent();
				}
				else
				{
					if ($this->getOutputMode() == "edit")
					{
						$html = "// ".$lng->txt("cont_missing_snippet")." //";
					}
				}
				$h2 = substr($a_html, 0, $start).
					$html.
					substr($a_html, $end + 5);
				$a_html = $h2;
				$i++;
			}
			$start = strpos($a_html, "{{{{{ContentInclude;", $start + 5);
			$end = 0;
			if (is_int($start))
			{
				$end = strpos($a_html, "}}}}}", $start);
			}
		}
		return $a_html;
	}

	/**
	 * Insert page toc
	 *
	 * @param string output
	 * @return string output
	 */
	function insertPageToc($a_output)
	{
		global $lng;

		include_once("./Services/Utilities/classes/class.ilStr.php");

		// extract all headings
		$offsets = ilStr::strPosAll($a_output, "ilPageTocH");
		$page_heads = array();
		foreach ($offsets as $os)
		{
			$level = (int) substr($a_output, $os + 10, 1);
			if (in_array($level, array(1,2,3)))
			{
				$anchor = str_replace("TocH", "TocA",
					substr($a_output, $os, strpos($a_output, "<", $os) - $os - 3)
					);

				// get heading
				$tag_start = stripos($a_output, "<h".$level." ", $os);
				$tag_end = stripos($a_output, "</h".$level.">", $tag_start);
				$head = substr($a_output, $tag_start, $tag_end - $tag_start);

				// get headings text
				$text_start = stripos($head, ">") + 1;
				$text_end = strripos($head, "<!--", $text_start);
				$text = substr($head, $text_start, $text_end - $text_start);
				$page_heads[] = array("level" => $level, "text" => $text,
					"anchor" => $anchor);
			}
		}

		if (count($page_heads) > 1)
		{
			include_once("./Services/UIComponent/NestedList/classes/class.ilNestedList.php");
			$list = new ilNestedList();
			$list->setAutoNumbering(true);
			$list->setListClass("ilc_page_toc_PageTOCList");
			$list->setItemClass("ilc_page_toc_PageTOCItem");
			$i = 0;
			$c_depth = 1;
			$c_par[1] = 0;
			$c_par[2] = 0;
			$nr[1] = 1;
			$nr[2] = 1;
			$nr[3] = 1;
			foreach ($page_heads as $ind => $h)
			{
				$i++;
				$par = 0;

				// check if we have a parent for one level up
				$par = 0;
				if ($h["level"] == 2 && $c_par[1] > 0)
				{
					$par = $c_par[1];
				}
				if ($h["level"] == 3 && $c_par[2] > 0)
				{
					$par = $c_par[2];
				}

				$h["text"] = str_replace("<!--PageTocPH-->", "", $h["text"]);

				// add the list node
				$list->addListNode(
					"<a href='#".$h["anchor"]."' class='ilc_page_toc_PageTOCLink'>".$h["text"]."</a>",
					$i, $par);

				// set the node as current parent of the level
				if ($h["level"] == 1)
				{
					$c_par[1] = $i;
					$c_par[2] = 0;
				}
				if ($h["level"] == 2)
				{
					$c_par[2] = $i;
				}
			}

			$tpl = new ilTemplate("tpl.page_toc.html", true, true,
				"Services/COPage");
			$tpl->setVariable("PAGE_TOC", $list->getHTML());
			$tpl->setVariable("TXT_PAGE_TOC", $lng->txt("cont_page_toc"));
			$tpl->setVariable("TXT_HIDE", $lng->txt("hide"));
			$tpl->setVariable("TXT_SHOW", $lng->txt("show"));

			$a_output = str_replace("{{{{{PageTOC}}}}}",
				$tpl->get(), $a_output);
			$numbers = $list->getNumbers();

			if (count($numbers) > 0)
			{
				include_once("./Services/Utilities/classes/class.ilStr.php");
				foreach ($numbers as $n)
				{
					$a_output =
						ilStr::replaceFirsOccurence("<!--PageTocPH-->", $n." ", $a_output);
				}
			}
		}
		else
		{
			$a_output = str_replace("{{{{{PageTOC}}}}}",
				"", $a_output);
		}

		return $a_output;
	}
	
	/**
	 * Finalizing output processing. Maybe overwritten in derived
	 * classes, e.g. in wiki module.
	 */
	function postOutputProcessing($a_output)
	{
		return $a_output;
	}
	
	/**
	* Insert help texts
	*/
	function insertHelp($a_tpl)
	{
		global $lng;
		
		$a_tpl->setCurrentBlock("help_img");
		$a_tpl->setVariable("IMG_HELP", ilUtil::getImagePath("streaked_area.gif"));
		$a_tpl->parseCurrentBlock();
		$a_tpl->setCurrentBlock("help_item");
		$a_tpl->setVariable("TXT_HELP", $lng->txt("cont_add_elements"));
		$a_tpl->parseCurrentBlock();
		
		$a_tpl->setCurrentBlock("help_img");
		$a_tpl->setVariable("IMG_HELP", ilUtil::getImagePath("icon_cont_el_s.gif"));
		$a_tpl->parseCurrentBlock();
		$a_tpl->setCurrentBlock("help_img");
		$a_tpl->setVariable("IMG_HELP", ilUtil::getImagePath("drop_streaked_area.gif"));
		$a_tpl->parseCurrentBlock();
		$a_tpl->setCurrentBlock("help_item");
		$a_tpl->setVariable("TXT_HELP", $lng->txt("cont_drag_and_drop_elements"));
		$a_tpl->parseCurrentBlock();

		$a_tpl->setCurrentBlock("help_img");
		$a_tpl->setVariable("IMG_HELP", ilUtil::getImagePath("icon_cont_el_s.gif"));
		$a_tpl->parseCurrentBlock();
		$a_tpl->setCurrentBlock("help_item");
		$a_tpl->setVariable("TXT_HELP", $lng->txt("cont_double_click_to_delete"));
		$a_tpl->parseCurrentBlock();
		
		$a_tpl->setCurrentBlock("help");
		$a_tpl->parseCurrentBlock();
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

	/**
	 * edit ("view" before)
	 */
	function edit()
	{
		global $tree, $lng, $ilCtrl;
		
		if (!$this->getEnableEditing())
		{
			ilUtil::sendFailure($lng->txt("permission_denied"), true);
			$ilCtrl->redirect($this, "preview");
		}
		
		$this->setOutputMode(IL_PAGE_EDIT);

		$html = $this->showPage();
		
		if ($this->isEnabledNotes())
		{
			$html.= "<br /><br />".$this->getNotesHTML();
		}	
	
		return $html;
	}
	
	/**
	 * InsertJS at placeholder
	 *
	 * @param
	 * @return
	 */
	function insertJSAtPlaceholder()
	{
		global $tpl;
		
//		  'pl_hier_id' => string '2_1_1_1' (length=7)
//  'pl_pc_id' => string '1f77eb1d8a478497d69b99d938fda8f' (length=31)
		$html =  $this->edit();

		$tpl->addOnLoadCode("ilCOPage.insertJSAtPlaceholder('".
			$_GET["pl_hier_id"].":".$_GET["pl_pc_id"].
			"');", 3);

		return $html;
	}
	

	/*
	* presentation
	*/
	function presentation($a_mode = IL_PAGE_PRESENTATION)
	{
		global $tree;
		$this->setOutputMode($a_mode);

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
		$wb_path = ilUtil::getWebspaceDir("output")."/";
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
		
		$tpl->addJavaScript("./Services/COPage/js/page_history.js");
		
		include_once("./Services/COPage/classes/class.ilPageHistoryTableGUI.php");
		$table_gui = new ilPageHistoryTableGUI($this, "history");
		$table_gui->setId("hist_table");
		$entries = $this->getPageObject()->getHistoryEntries();
		$entries[] = array('page_id' => $this->getPageObject()->getId(),
			'parent_type' => $this->getPageObject()->getParentType(),
			'hdate' => $this->getPageObject()->getLastChange(),
			'parent_id' => $this->getPageObject()->getParentId(),
			'nr' => 0,
			'sortkey' => 999999,
			'user' => $this->getPageObject()->last_change_user);
		$table_gui->setData($entries);
		return $table_gui->getHTML();
	}

	/**
	* Rollback confirmation
	*/
	function rollbackConfirmation()
	{
		global $tpl, $lng, $ilAccess, $ilCtrl;
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$ilCtrl->setParameter($this, "rollback_nr", $_GET["old_nr"]); 
		$c_gui->setFormAction($ilCtrl->getFormAction($this, "rollback"));
		$c_gui->setHeaderText($lng->txt("cont_rollback_confirmation"));
		$c_gui->setCancel($lng->txt("cancel"), "history");
		$c_gui->setConfirm($lng->txt("confirm"), "rollback");

		$hentry = $this->obj->getHistoryEntry($_GET["old_nr"]);
			
		$c_gui->addItem("id[]", $_GET["old_nr"], 
			ilDatePresentation::formatDate(new ilDateTime($hentry["hdate"], IL_CAL_DATETIME)));
		
		$tpl->setContent($c_gui->getHTML());
	}
	
	/**
	* Rollback to a previous version
	*/
	function rollback()
	{
		global $ilCtrl;
		
		$hentry = $this->obj->getHistoryEntry($_GET["rollback_nr"]);

		if ($hentry["content"] != "")
		{
			$this->obj->setXMLContent($hentry["content"]);
			$this->obj->buildDom(true);
			if ($this->obj->update())
			{
				$ilCtrl->redirect($this, "history");
			}
		}
		$ilCtrl->redirect($this, "history");
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
	
			if ($this->getEnableEditing())
			{
				$ilTabs->addTarget("edit", $ilCtrl->getLinkTarget($this, "edit")
					, array("", "edit"));
			}
		}
		else
		{
			if ($this->getEnableEditing())
			{
				$ilTabs->addTarget("edit", $ilCtrl->getLinkTarget($this, "edit")
					, array("", "edit"));
			}
	
			$ilTabs->addTarget("cont_preview", $ilCtrl->getLinkTarget($this, "preview")
				, array("", "preview"));
		}
			
		//$tabs_gui->addTarget("properties", $this->ctrl->getLinkTarget($this, "properties")
		//	, "properties", get_class($this));

		if ($this->use_meta_data && !$this->layout_mode)
		{
			$ilTabs->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
				 "", "ilmdeditorgui");
		}

		$lm_set = new ilSetting("lm");
		
		if ($this->getEnableEditing() && !$this->layout_mode &&
			$lm_set->get("page_history", 1))
		{
			$ilTabs->addTarget("history", $this->ctrl->getLinkTarget($this, "history")
				, "history", get_class($this));
		}

/*		$tabs = $this->ctrl->getTabs();
		foreach ($tabs as $tab)
		{
			$tabs_gui->addTarget($tab["lang_var"], $tab["link"]
				, $tab["cmd"], $tab["class"]);
		}
*/
		if ($this->getEnableEditing())
		{
			$ilTabs->addTarget("clipboard", $this->ctrl->getLinkTargetByClass("ilEditClipboardGUI", "view")
				, "view", "ilEditClipboardGUI");
		}

		if ($this->getEnabledScheduledActivation())
		{
			$ilTabs->addTarget("cont_activation", $this->ctrl->getLinkTarget($this, "editActivation"),
				"editActivation", get_class($this));
		}

		if ($this->getEnabledNews())
		{
			$ilTabs->addTarget("news",
				$this->ctrl->getLinkTargetByClass("ilnewsitemgui", "editNews"),
				"", "ilnewsitemgui");
		}

		// external hook to add tabs
		if (is_array($this->tab_hook))
		{
			$func = $this->tab_hook["func"];
			$this->tab_hook["obj"]->$func();
		}
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
		$lpage_gui->setPresentationTitle($this->getPresentationTitle());
		$lpage_gui->setCompareMode(true);
		$lhtml = $lpage_gui->showPage();
		$lhtml = $this->replaceDiffTags($lhtml);
		$lhtml = str_replace("&lt;br /&gt;", "<br />", $lhtml);
		$tpl->setVariable("LEFT", $lhtml);
		
		// right page
		$rpage = $compare["r_page"];
		$rpage_gui = new ilPageObjectGUI("wpg");
		$rpage_gui->setOutputMode(IL_PAGE_PREVIEW);
		$rpage_gui->setPageObject($rpage);
		$rpage_gui->setPreventHTMLUnmasking(true);
		$rpage_gui->setPresentationTitle($this->getPresentationTitle());
		$rpage_gui->setCompareMode(true);
		$rhtml = $rpage_gui->showPage();
		$rhtml = $this->replaceDiffTags($rhtml);
		$rhtml = str_replace("&lt;br /&gt;", "<br />", $rhtml);
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
	
	/**
	* Edit activation (only, if scheduled page activation is activated in administration)
	*/
	function editActivation()
	{
		global $ilCtrl, $lng, $tpl;
		
		$atpl = new ilTemplate("tpl.page_activation.php", true, true, "Services/COPage");
		$this->initActivationForm();
		$this->getActivationFormValues();
		$atpl->setVariable("FORM", $this->form->getHTML());
		$atpl->setCurrentBlock("updater");
		$atpl->setVariable("UPDATER_FRAME", $this->exp_frame);
		$atpl->setVariable("EXP_ID_UPDATER", $this->exp_id);
		$atpl->setVariable("HREF_UPDATER", $this->exp_target_script);
		$atpl->parseCurrentBlock();
		$tpl->setContent($atpl->get());
	}
	
	/**
	* Init activation form
	*/
	function initActivationForm()
	{
		global $ilCtrl, $lng;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->setTitle($lng->txt("cont_page_activation"));
		
		// activation type radio
		$rad = new ilRadioGroupInputGUI($lng->txt("cont_activation"), "activation");
		$rad_op1 = new ilRadioOption($lng->txt("cont_activated"), "activated");

		$rad->addOption($rad_op1);
		$rad_op2 = new ilRadioOption($lng->txt("cont_deactivated"), "deactivated");
		$rad->addOption($rad_op2);
		$rad_op3 = new ilRadioOption($lng->txt("cont_scheduled_activation"), "scheduled");
		
			$dt_prop = new ilDateTimeInputGUI($lng->txt("cont_start"), "start");
			$dt_prop->setShowTime(true);
			$rad_op3->addSubItem($dt_prop);
			$dt_prop2 = new ilDateTimeInputGUI($lng->txt("cont_end"), "end");
			$dt_prop2->setShowTime(true);
			$rad_op3->addSubItem($dt_prop2);
		
		$rad->addOption($rad_op3);

		$this->form->addCommandButton("saveActivation", $lng->txt("save"));
		
		$this->form->addItem($rad);
	}
	
	/**
	* Get values for activation form
	*/
	function getActivationFormValues()
	{
		$values = array();
		$values["activation"] = "deactivated";
		if ($this->getPageObject()->getActive())
		{
			$values["activation"] = "activated";
		}
		
		$dt_prop = $this->form->getItemByPostVar("start");
		if ($this->getPageObject()->getActivationStart() != "")
		{
			$values["activation"] = "scheduled";
			$dt_prop->setDate(new ilDateTime($this->getPageObject()->getActivationStart(),
				IL_CAL_DATETIME));
		}
		$dt_prop = $this->form->getItemByPostVar("end");
		if ($this->getPageObject()->getActivationEnd() != "")
		{
			$values["activation"] = "scheduled";
			$dt_prop->setDate(new ilDateTime($this->getPageObject()->getActivationEnd(),
				IL_CAL_DATETIME));
		}
		
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Save Activation
	*/
	function saveActivation()
	{
		global $tpl, $lng, $ilCtrl;
		
		$this->initActivationForm();
		
		if ($this->form->checkInput())
		{
			$this->getPageObject()->setActive(true);
			$this->getPageObject()->setActivationStart(null);
			$this->getPageObject()->setActivationEnd(null);
			if ($_POST["activation"] == "deactivated")
			{
				$this->getPageObject()->setActive(false);
			}
			if ($_POST["activation"] == "scheduled")
			{
				$this->getPageObject()->setActive(false);
				$this->getPageObject()->setActivationStart(
					$this->form->getItemByPostVar("start")->getDate()->get(IL_CAL_DATETIME));
				$this->getPageObject()->setActivationEnd(
					$this->form->getItemByPostVar("end")->getDate()->get(IL_CAL_DATETIME));
			}
			$this->getPageObject()->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editActivation");
		}
		$this->form->getValuesByPost();
		$tpl->setContent($this->form->getHTML());
	}

	/**
	 * Get html for public and/or private notes
	 *
	 * @param bool $a_content_object
	 * @param bool $a_enable_private_notes
	 * @param bool $a_enable_public_notes
	 * @param bool $a_enable_notes_deletion
	 * @return string
	 */
	function getNotesHTML($a_content_object = null, $a_enable_private_notes = true, $a_enable_public_notes = false, $a_enable_notes_deletion = false, $a_callback = null)
	{
		global $ilCtrl;

		include_once("Services/Notes/classes/class.ilNoteGUI.php");

		// scorm 2004 page gui
		if(!$a_content_object)
		{
			$notes_gui = new ilNoteGUI($this->notes_parent_id,
				(int)$this->obj->getId(), "pg");

			$a_enable_private_notes = true;
			$a_enable_public_notes = true;
			$a_enable_notes_deletion = false;
		}
		// wiki page gui, blog posting gui
		else
		{
			$notes_gui = new ilNoteGUI($a_content_object->getParentId(),
				$a_content_object->getId(), $a_content_object->getParentType());
		}
	
		if($a_enable_private_notes)
		{
			$notes_gui->enablePrivateNotes();
		}
		if ($a_enable_public_notes)
		{
			$notes_gui->enablePublicNotes();
			if ((bool)$a_enable_notes_deletion)
			{
				$notes_gui->enablePublicNotesDeletion(true);
			}
		}
		
		if($a_callback)
		{
			$notes_gui->addObserver($a_callback);
		}

		$next_class = $this->ctrl->getNextClass($this);
		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{
			$html = $notes_gui->getNotesHTML();
		}
		return $html;
	}

	/**
	 * Get question js
	 */
	function getQuestionJsOfPage($a_no_interaction = false)
	{
		require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
		$q_ids = $this->getPageObject()->getQuestionIds();
		$js = array();
		if (count($q_ids) > 0)
		{
			foreach ($q_ids as $q_id)
			{
				$q_exporter = new ilQuestionExporter($a_no_interaction);
				$js[$q_id] = $q_exporter->exportQuestion($q_id);
			}
		}
		return $js;
	}
	
	/**
	 * Init question handling
	 */
	function initSelfAssessmentRendering($a_force_no_form = false)
	{
		global $tpl, $ilCtrl, $lng;

		if ($this->getEnabledSelfAssessment())
		{
			$qhtml = $this->getQuestionJsOfPage(($this->getOutputMode()=="edit" || $a_force_no_form)
				? true
				: false);
//			$qhtml = $this->getQuestionJsOfPage(true);
			$this->setQuestionHTML($qhtml);
			//include JQuery Libraries before Prototpye
//			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery.js");
//			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/jquery-ui-min.js");
			include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
			iljQueryUtil::initjQuery();
			iljQueryUtil::initjQueryUI();
			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/pure.js");
			$tpl->addJavaScript("./Modules/Scorm2004/scripts/questions/question_handling.js");
			$tpl->addCss("./Modules/Scorm2004/templates/default/question_handling.css");
			if (!$this->getEnabledSelfAssessmentScorm() && $this->getOutputMode() != IL_PAGE_PREVIEW
				&& $this->getOutputMode() != "offline")
			{
				$tpl->addJavaScript("./Services/COPage/js/ilCOPageQuestionHandler.js");
				$url = $ilCtrl->getLinkTarget($this, "processAnswer", "", true, false);
				$tpl->addOnloadCode("ilCOPageQuestionHandler.initCallback('".$url."');");
			}

			$lk = $this->getPageConfig()->getLocalizationLanguage();
			self::addPreparationJavascript($tpl, $lk);
		}
	}

	/**
	 * Add standard texts
	 *
	 * @param
	 * @return
	 */
	static function addPreparationJavascript($a_tpl, $a_lang)
	{
		global $lng;
		
		$a_tpl->addOnloadCode(self::getJSTextInitCode($a_lang).
			'ilCOPagePres.updateQuestionOverviews();
			');
	}
	
	/**
	 * Get js txt init code
	 *
	 * @param
	 * @return
	 */
	static function getJSTextInitCode($a_lang)
	{
		global $lng, $ilUser;
		
		if ($a_lang == "")
		{
			$a_lang = $ilUser->getLanguage();
		}
		
		return 
			'
			ilias.questions.txt.wrong_answers = "'.$lng->txtlng("content", "cont_wrong_answers", $a_lang).'";
			ilias.questions.txt.wrong_answers_single = "'.$lng->txtlng("content", "cont_wrong_answers_single", $a_lang).'";
			ilias.questions.txt.tries_remaining = "'.$lng->txtlng("content", "cont_tries_remaining", $a_lang).'";
			ilias.questions.txt.please_try_again = "'.$lng->txtlng("content", "cont_please_try_again", $a_lang).'";
			ilias.questions.txt.all_answers_correct = "'.$lng->txtlng("content", "cont_all_answers_correct", $a_lang).'";
			ilias.questions.txt.nr_of_tries_exceeded = "'.$lng->txtlng("content", "cont_nr_of_tries_exceeded", $a_lang).'";
			ilias.questions.txt.correct_answers_shown = "'.$lng->txtlng("content", "cont_correct_answers_shown", $a_lang).'";
			ilias.questions.txt.correct_answers_also = "'.$lng->txtlng("content", "cont_correct_answers_also", $a_lang).'";
			ilias.questions.txt.correct_answer_also = "'.$lng->txtlng("content", "cont_correct_answer_also", $a_lang).'";
			ilias.questions.txt.ov_all_correct = "'.$lng->txtlng("content", "cont_ov_all_correct", $a_lang).'";
			ilias.questions.txt.ov_some_correct = "'.$lng->txtlng("content", "cont_ov_some_correct", $a_lang).'";
			ilias.questions.txt.ov_wrong_answered = "'.$lng->txtlng("content", "cont_ov_wrong_answered", $a_lang).'";
			';

	}
	
	
	/**
	 * Process answer
	 */
	function processAnswer()
	{
		global $ilLog;

		/*$ilLog->write($_POST);
		$ilLog->write($_POST["id"]);
		$ilLog->write($_POST["type"]);
		$ilLog->write($_POST["answer"]);
		$ilLog->write($_GET);*/

		include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
		ilPageQuestionProcessor::saveQuestionAnswer(
			ilUtil::stripSlashes($_POST["type"]),
			ilUtil::stripSlashes($_POST["id"]),
			ilUtil::stripSlashes($_POST["answer"]));
	}

	/**
	 * Self assessment question rendering
	 *
	 * @param
	 * @return
	 */
	function selfAssessmentRendering($a_output)
	{
		if ($this->getEnabledSelfAssessment())
		{
			require_once './Modules/Scorm2004/classes/class.ilQuestionExporter.php';
			$a_output = "<script>var ScormApi=null;".ilQuestionExporter::questionsJS()."</script>".$a_output;
		}
		return $a_output;
	}


}
?>
