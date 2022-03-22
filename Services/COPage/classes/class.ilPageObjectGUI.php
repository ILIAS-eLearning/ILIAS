<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPageObjectGUI
 *
 * User Interface for Page Objects Editing
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilPageObjectGUI: ilPageEditorGUI, ilEditClipboardGUI, ilObjectMetaDataGUI
 * @ilCtrl_Calls ilPageObjectGUI: ilPublicUserProfileGUI, ilNoteGUI, ilNewsItemGUI
 * @ilCtrl_Calls ilPageObjectGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI, ilLearningHistoryGUI
 */
class ilPageObjectGUI
{
    const PRESENTATION = "presentation";
    const EDIT = "edit";
    const PREVIEW = "preview";
    const OFFLINE = "offline";
    const PRINTING = "print";

    protected $enabled_href = true;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilPageObject
     */
    public $obj;

    /**
     * @var string
     */
    protected $output_mode;

    public $presentation_title;
    public $target_script;
    public $return_location;
    public $target_var;
    public $template_output_var;
    public $output2template;
    public $link_params;
    public $sourcecode_download_script;
    public $change_comments;
    public $question_html;
    public $activation = false;
    public $activated = true;
    public $editpreview = false;
    public $use_meta_data = false;
    public $link_xml_set = false;
    public $enableediting = true;
    public $rawpagecontent = false;
    public $enabledcontentincludes = false;
    public $compare_mode = false;
    public $page_config = null;
    public $tabs_enabled = true;
    public $render_page_container = false;
    private $abstract_only = false;
    protected $parent_type = "";

    /**
     * @var \ILIAS\GlobalScreen\ScreenContext\ContextServices
     */
    protected $tool_context;

    //var $pl_start = "&#123;&#123;&#123;&#123;&#123;";
    //var $pl_end = "&#125;&#125;&#125;&#125;&#125;";
    public $pl_start = "{{{{{";
    public $pl_end = "}}}}}";

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    protected $page_linker;

    /**
     * @var string pcid of single paragraph
     */
    protected $abstract_pcid = "";

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var string
     */
    protected $open_place_holder;

    /**
     * Constructor
     *
     * @param string $a_parent_type type of parent object
     * @param int $a_id page id
     * @param int $a_old_nr history number (current version 0)
     * @param bool $a_prevent_get_id prevent getting id automatically from $_GET (e.g. set when concentInclude are included)
     * @param string $a_lang language ("" reads also $_GET["transl"], "-" forces master lang)
     */
    public function __construct(
        $a_parent_type,
        $a_id,
        $a_old_nr = 0,
        $a_prevent_get_id = false,
        $a_lang = ""
    ) {
        global $DIC;

        $this->log = ilLoggerFactory::getLogger('copg');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs_gui = $DIC->tabs();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->help = $DIC["ilHelp"];
        $this->ui = $DIC->ui();
        $this->toolbar = $DIC->toolbar();

        $this->setParentType($a_parent_type);
        $this->setId($a_id);
        if ($a_old_nr == 0 && !$a_prevent_get_id && $_GET["old_nr"] > 0) {
            $a_old_nr = $_GET["old_nr"];
        }
        $this->setOldNr($a_old_nr);
        
        if ($a_lang == "" && $_GET["transl"] != "") {
            $this->setLanguage($_GET["transl"]);
        } else {
            if ($a_lang == "") {
                $a_lang = "-";
            }
            $this->setLanguage($a_lang);
        }
        

        $this->setOutputMode(self::PRESENTATION);
        $this->setEnabledPageFocus(true);
        $this->initPageObject();
        $this->setPageConfig($this->getPageObject()->getPageConfig());

        $this->page_linker = new ilPageLinker(get_class($this));

        $this->output2template = true;
        $this->question_xml = "";
        $this->question_html = "";

        $this->template_output_var = "PAGE_CONTENT";
        $this->change_comments = false;
        $this->page_back_title = $this->lng->txt("page");
        $this->lng->loadLanguageModule("content");
        $this->lng->loadLanguageModule("copg");

        $this->tool_context = $DIC->globalScreen()->tool()->context();
        
        $this->setTemplateOutput(false);

        $this->ctrl->saveParameter($this, "transl");
        
        $this->afterConstructor();
    }
    
    /**
     * After constructor
     */
    public function afterConstructor()
    {
    }
    

    /**
     * Init page object
     */
    final protected function initPageObject()
    {
        include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
        $page = ilPageObjectFactory::getInstance(
            $this->getParentType(),
            $this->getId(),
            $this->getOldNr(),
            $this->getLanguage()
        );
        $this->setPageObject($page);
    }
    
    /**
     * Set parent type
     *
     * @param string $a_val parent type
     */
    public function setParentType($a_val)
    {
        $this->parent_type = $a_val;
    }
    
    /**
     * Get parent type
     *
     * @return string parent type
     */
    public function getParentType()
    {
        return $this->parent_type;
    }
    
    /**
     * Set ID
     *
     * @param integer $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get ID
     *
     * @return integer id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set old nr (historic page)
     *
     * @param int $a_val old nr
     */
    public function setOldNr($a_val)
    {
        $this->old_nr = $a_val;
    }
    
    /**
     * Get old nr (historic page)
     *
     * @return int old nr
     */
    public function getOldNr()
    {
        return $this->old_nr;
    }
    
    /**
     * Set language
     *
     * @param string $a_val language
     */
    public function setLanguage($a_val)
    {
        $this->language = $a_val;
    }
    
    /**
     * Get language
     *
     * @return string language
     */
    public function getLanguage()
    {
        if ($this->language == "") {
            return "-";
        }
        
        return $this->language;
    }
    
    /**
     * Set enable pc type
     *
     * @param boolean $a_val enable pc type true/false
     */
    public function setEnablePCType($a_pc_type, $a_val)
    {
        $this->getPageConfig()->setEnablePCType($a_pc_type, $a_val);
    }
    
    /**
     * Get enable pc type
     *
     * @return boolean enable pc type true/false
     */
    public function getEnablePCType($a_pc_type)
    {
        return $this->getPageConfig()->getEnablePCType($a_pc_type);
    }
    
    /**
     * Set page config object
     *
     * @param	object	config object
     */
    public function setPageConfig($a_val)
    {
        $this->page_config = $a_val;
    }
    
    /**
     * Get page config object
     *
     * @return	object	config object
     */
    public function getPageConfig()
    {
        return $this->page_config;
    }
    
    /**
     * Set Page Object
     *
     * @param ilPageObject $a_pg_obj
     */
    public function setPageObject(ilPageObject $a_pg_obj)
    {
        $this->obj = $a_pg_obj;
    }

    /**
     * Get Page Object
     *
     * @return ilPageObject
     */
    public function getPageObject()
    {
        return $this->obj;
    }

    /**
    * Set Output Mode
    *
    * @param	string		Mode self::PRESENTATION | self::EDIT | self::PREVIEW
    */
    public function setOutputMode($a_mode = self::PRESENTATION)
    {
        $this->output_mode = $a_mode;
    }

    public function getOutputMode()
    {
        return $this->output_mode;
    }

    public function setTemplateOutput($a_output = true)
    {
        $this->output2template = $a_output;
    }

    public function outputToTemplate()
    {
        return $this->output2template;
    }

    public function setPresentationTitle($a_title = "")
    {
        $this->presentation_title = $a_title;
    }

    public function getPresentationTitle()
    {
        return $this->presentation_title;
    }

    public function setHeader($a_title = "")
    {
        $this->header = $a_title;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setLinkParams($l_params = "")
    {
        $this->link_params = $l_params;
    }

    public function getLinkParams()
    {
        return $this->link_params;
    }

    public function setLinkFrame($l_frame = "")
    {
        $this->link_frame = $l_frame;
    }

    public function getLinkFrame()
    {
        return $this->link_frame;
    }

    public function setPageLinker($page_linker)
    {
        $this->page_linker = $page_linker;
    }

    public function getLinkXML()
    {
        return $this->link_xml;
    }

    public function setQuestionXML($question_xml)
    {
        $this->question_xml = $question_xml;
    }

    public function setQuestionHTML($question_html)
    {
        $this->getPageConfig()->setQuestionHTML($question_html);
    }

    public function getQuestionXML()
    {
        return $this->question_xml;
    }

    public function getQuestionHTML()
    {
        return $this->getPageConfig()->getQuestionHTML();
    }

    public function setTemplateTargetVar($a_variable)
    {
        $this->target_var = $a_variable;
    }

    public function getTemplateTargetVar()
    {
        return $this->target_var;
    }

    public function setTemplateOutputVar($a_value)
    {
        $this->template_output_var = $a_value;
    }

    public function getTemplateOutputVar()
    {
        return $this->template_output_var;
    }

    /**
     * Set sourcecode download script
     *
     * @param string $script_name
     */
    public function setSourcecodeDownloadScript($script_name)
    {
        $this->sourcecode_download_script = $script_name;
    }

    /**
     * Get sourcecode download script
     *
     * @return string
     */
    public function getSourcecodeDownloadScript()
    {
        return $this->sourcecode_download_script;
    }

    public function setLocator(&$a_locator)
    {
        $this->locator = $a_locator;
    }

    public function setTabs($a_tabs)
    {
        $this->tabs_gui = $a_tabs;
    }

    public function setPageBackTitle($a_title)
    {
        $this->page_back_title = $a_title;
    }

    /**
     * Set file download link
     *
     * @param string $a_download_link download link
     */
    public function setFileDownloadLink($a_download_link)
    {
        $this->file_download_link = $a_download_link;
    }

    /**
     * Get file download link
     *
     * @return string
     */
    public function getFileDownloadLink()
    {
        return $this->file_download_link;
    }

    /**
     * Set fullscreen link
     *
     * @param string $a_download_link download link
     */
    public function setFullscreenLink($a_fullscreen_link)
    {
        $this->fullscreen_link = $a_fullscreen_link;
    }

    /**
     * Get fullscreen link
     *
     * @return string
     */
    public function getFullscreenLink()
    {
        return $this->fullscreen_link;
    }

    public function setIntLinkReturn($a_return)
    {
        $this->int_link_return = $a_return;
    }

    public function enableChangeComments($a_enabled)
    {
        $this->change_comments = $a_enabled;
    }

    public function isEnabledChangeComments()
    {
        return $this->change_comments;
    }

    public function enableNotes($a_enabled, $a_parent_id)
    {
        $this->notes_enabled = $a_enabled;
        $this->notes_parent_id = $a_parent_id;
    }

    public function isEnabledNotes()
    {
        return $this->notes_enabled;
    }

    /**
     * set offline directory to offdir
     *
     * @param offdir contains diretory where to store files
     */
    public function setOfflineDirectory($offdir)
    {
        $this->offline_directory = $offdir;
    }


    /**
     * get offline directory
     * @return directory where to store offline files
     */
    public function getOfflineDirectory()
    {
        return $this->offline_directory;
    }


    /**
    * set link for "view page" button
    *
    * @param	string		link target
    * @param	string		target frame
    */
    public function setViewPageLink($a_link, $a_target = "")
    {
        $this->view_page_link = $a_link;
        $this->view_page_target = $a_target;
    }

    /**
    * get view page link
    */
    public function getViewPageLink()
    {
        return $this->view_page_link;
    }

    /**
    * get view page target frame
    */
    public function getViewPageTarget()
    {
        return $this->view_page_target;
    }

    /**
     * get view page text
     *
     * @return string
     */
    public function getViewPageText()
    {
        return $this->lng->txt("cont_presentation_view");
    }

    public function setActivationListener(&$a_obj, $a_meth)
    {
        $this->act_obj = $a_obj;
        $this->act_meth = $a_meth;
    }

    /**
     * Set enabled news
     *
     * @param	boolean	enabled news
     */
    public function setEnabledNews($a_enabled, $a_news_obj_id = 0, $a_news_obj_type = 0)
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
    public function getEnabledNews()
    {
        return $this->enabled_news;
    }

    /**
    * Set tab hook
    */
    public function setTabHook($a_object, $a_function)
    {
        $this->tab_hook = array("obj" => $a_object, "func" => $a_function);
    }
        
    /**
    * Set Display first Edit tab, then Preview tab, instead of Page and Edit.
    *
    * @param	boolean	$a_editpreview		Edit/preview mode
    */
    public function setEditPreview($a_editpreview)
    {
        $this->editpreview = $a_editpreview;
    }

    /**
    * Get Display first Edit tab, then Preview tab, instead of Page and Edit.
    *
    * @return	boolean		Edit/Preview mode
    */
    public function getEditPreview()
    {
        return $this->editpreview;
    }

    /**
    * Set Output tabs.
    *
    * @param	boolean	$a_enabledtabs	Output tabs
    */
    public function setEnabledTabs($a_enabledtabs)
    {
        $this->tabs_enabled = $a_enabledtabs;
    }

    /**
    * Get Output tabs.
    *
    * @return	boolean	Output tabs
    */
    public function getEnabledTabs()
    {
        return $this->tabs_enabled;
    }

    /**
    * Set Enable page focus.
    *
    * @param	boolean	$a_enabledpagefocus	Enable page focus
    */
    public function setEnabledPageFocus($a_enabledpagefocus)
    {
        $this->enabledpagefocus = $a_enabledpagefocus;
    }

    /**
     * Set open placeholder
     * @param string $a_val open placeholder pc id
     */
    function setOpenPlaceHolder($a_val)
    {
        $this->open_place_holder = $a_val;
    }

    /**
     * Get open placeholder
     * @return string open placeholder pc id
     */
    function getOpenPlaceHolder()
    {
        return $this->open_place_holder;
    }
    

    /**
    * Get Enable page focus.
    *
    * @return	boolean	Enable page focus
    */
    public function getEnabledPageFocus()
    {
        return $this->enabledpagefocus;
    }

    /**
    * Set Explorer Updater
    *
    * @param	object	$a_tree	Tree Object
    */
    public function setExplorerUpdater($a_exp_frame, $a_exp_id, $a_exp_target_script)
    {
        return;
        $this->exp_frame = $a_exp_frame;
        $this->exp_id = $a_exp_id;
        $this->exp_target_script = $a_exp_target_script;
    }

    /**
    * Set Prepending HTML.
    *
    * @param	string	$a_prependinghtml	Prepending HTML
    */
    public function setPrependingHtml($a_prependinghtml)
    {
        $this->prependinghtml = $a_prependinghtml;
    }

    /**
    * Get Prepending HTML.
    *
    * @return	string	Prepending HTML
    */
    public function getPrependingHtml()
    {
        return $this->prependinghtml;
    }

    /**
    * Set Enable Editing.
    *
    * @param	boolean	$a_enableediting	Enable Editing
    */
    public function setEnableEditing($a_enableediting)
    {
        $this->enableediting = $a_enableediting;
    }

    /**
    * Get Enable Editing.
    *
    * @return	boolean	Enable Editing
    */
    public function getEnableEditing()
    {
        return $this->enableediting;
    }

    /**
    * Set Get raw page content only.
    *
    * @param	boolean	$a_rawpagecontent	Get raw page content only
    */
    public function setRawPageContent($a_rawpagecontent)
    {
        $this->rawpagecontent = $a_rawpagecontent;
    }

    /**
    * Get Get raw page content only.
    *
    * @return	boolean	Get raw page content only
    */
    public function getRawPageContent()
    {
        return $this->rawpagecontent;
    }

    /**
    * Set Style Id.
    *
    * @param	int	$a_styleid	Style Id
    */
    public function setStyleId($a_styleid)
    {
        $this->styleid = $a_styleid;
    }

    /**
    * Get Style Id.
    *
    * @return	int	Style Id
    */
    public function getStyleId()
    {
        return $this->styleid;
    }

    /**
    * Set compare mode
    *
    * @param	boolean		compare_mode
    */
    public function setCompareMode($a_val)
    {
        $this->compare_mode = $a_val;
    }
    
    /**
    * Get compare mode
    *
    * @return	boolean		compare_mode
    */
    public function getCompareMode()
    {
        return $this->compare_mode;
    }
    
    /**
     * Set abstract only
     *
     * @param boolean $a_val get only abstract (first text paragraph)
     */
    public function setAbstractOnly($a_val, $pcid = "")
    {
        $this->abstract_only = $a_val;
        $this->abstract_pcid = $pcid;
    }
    
    /**
     * Get abstract only
     *
     * @return boolean get only abstract (first text paragraph)
     */
    public function getAbstractOnly()
    {
        return $this->abstract_only;
    }
    
    /**
     * Set render page container
     *
     * @param bool $a_val render page container
     */
    public function setRenderPageContainer($a_val)
    {
        $this->render_page_container = $a_val;
    }
    
    /**
     * Get render page container
     *
     * @return bool render page container
     */
    public function getRenderPageContainer()
    {
        return $this->render_page_container;
    }

    /**
     * Get disabled text
     *
     * @param
     * @return
     */
    public function getDisabledText()
    {
        return $this->lng->txt("inactive");
    }

    public function getEnabledHref() : bool
    {
        return $this->enabled_href;
    }

    public function setEnabledHref(bool $enable) : void
    {
        $this->enabled_href = $enable;
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
    public function activateMetaDataEditor(
        $a_rep_obj,
        $a_type,
        $a_sub_obj_id,
        $a_observer_obj = null,
        $a_observer_func = ""
    ) {
        $this->use_meta_data = true;
        $this->meta_data_rep_obj = $a_rep_obj;
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
    public function determineFileDownloadLink()
    {
        $file_download_link = $this->getFileDownloadLink();
        if ($this->getFileDownloadLink() == "" && $this->getOutputMode() != "offline") {
            $file_download_link = $this->ctrl->getLinkTarget($this, "downloadFile");
        }
        return $file_download_link;
    }

    /**
     * Determine fullscreen link
     *
     * @return	string	fullscreen link
     */
    public function determineFullscreenLink()
    {
        $fullscreen_link = $this->getFullscreenLink();
        if ($this->getFullscreenLink() == "" && $this->getOutputMode() != "offline") {
            $fullscreen_link = $this->ctrl->getLinkTarget($this, "displayMediaFullscreen", "", false, false);
        }
        return $fullscreen_link;
    }

    /**
     * Determine source code download script
     *
     * @return	string	sourcecode download script
     */
    public function determineSourcecodeDownloadScript()
    {
        $l = $this->sourcecode_download_script;
        if ($this->sourcecode_download_script == "" && $this->getOutputMode() != "offline") {
            $l = $this->ctrl->getLinkTarget($this, "");
        }
        return $l;
    }

    /**
    * Put information about activated plugins into XML
    */
    public function getComponentPluginsXML()
    {
        $xml = "";
        if ($this->getOutputMode() == "edit") {
            $pl_names = $this->plugin_admin->getActivePluginsForSlot(
                IL_COMP_SERVICE,
                "COPage",
                "pgcp"
            );
            foreach ($pl_names as $pl_name) {
                $plugin = $this->plugin_admin->getPluginObject(
                    IL_COMP_SERVICE,
                    "COPage",
                    "pgcp",
                    $pl_name
                );
                if ($plugin->isValidParentType($this->getPageObject()->getParentType())) {
                    $xml .= '<ComponentPlugin Name="' . $plugin->getPluginName() .
                        '" InsertText="' . $plugin->txt(ilPageComponentPlugin::TXT_CMD_INSERT) . '" />';
                }
            }
        }
        if ($xml != "") {
            $xml = "<ComponentPlugins>" . $xml . "</ComponentPlugins>";
        }
        return $xml;
    }
    
    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $this->ctrl->setReturn($this, "edit");

        $next_class = $this->ctrl->getNextClass($this);
        $this->log->debug("next_class: " . $next_class);

        if ($next_class == "" && $this->ctrl->getCmd() == "edit") {
            $this->tabs_gui->clearTargets();
        } else {
            $this->getTabs();
        }


        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $this->tabs_gui->activateTab("meta_data");
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI($this->meta_data_rep_obj, $this->meta_data_type, $this->meta_data_sub_obj_id);
                if (is_object($this->meta_data_observer_obj)) {
                    $md_gui->addMDObserver(
                        $this->meta_data_observer_obj,
                        $this->meta_data_observer_func,
                        "General"
                    );
                }
                $this->ctrl->forwardCommand($md_gui);
                break;
            
            case "ileditclipboardgui":
                $clip_gui = new ilEditClipboardGUI();
                $clip_gui->setPageBackTitle($this->page_back_title);
                $ret = $this->ctrl->forwardCommand($clip_gui);
                break;
                
            // notes
            case "ilnotegui":
                switch ($_GET["notes_mode"]) {
                    default:
                        $html = $this->edit();
                        $this->tabs_gui->setTabActive("edit");
                        return $html;
                }
                break;
                
            case 'ilpublicuserprofilegui':
                require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
                $profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
                $ret = $this->ctrl->forwardCommand($profile_gui);
                break;

            case "ilpageeditorgui":
                $this->setEditorToolContext();

                if (!$this->getEnableEditing()) {
                    ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
                    $this->ctrl->redirect($this, "preview");
                }
                $page_editor = new ilPageEditorGUI($this->getPageObject(), $this);
                $page_editor->setLocator($this->locator);
                $page_editor->setHeader($this->getHeader());
                $page_editor->setPageBackTitle($this->page_back_title);
                $page_editor->setIntLinkReturn($this->int_link_return);
                //$page_editor->executeCommand();
                $ret = $this->ctrl->forwardCommand($page_editor);
                break;

            case 'ilnewsitemgui':
                include_once("./Services/News/classes/class.ilNewsItemGUI.php");
                $news_item_gui = new ilNewsItemGUI();
                $news_item_gui->setEnableEdit(true);
                $news_item_gui->setContextObjId($this->news_obj_id);
                $news_item_gui->setContextObjType($this->news_obj_type);
                $news_item_gui->setContextSubObjId($this->obj->getId());
                $news_item_gui->setContextSubObjType("pg");

                $ret = $this->ctrl->forwardCommand($news_item_gui);
                break;

                $profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
                $ret = $this->ctrl->forwardCommand($profile_gui);
                break;

            case "ilpropertyformgui":
                include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
                $form = $this->initOpenedContentForm();
                $this->ctrl->forwardCommand($form);
                break;
                
            case "ilinternallinkgui":
                $this->lng->loadLanguageModule("content");
                require_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
                $link_gui = new ilInternalLinkGUI("Media_Media", 0);

                $link_gui->filterLinkType("PageObject_FAQ");
                $link_gui->filterLinkType("GlossaryItem");
                $link_gui->filterLinkType("Media_Media");
                $link_gui->filterLinkType("Media_FAQ");
                
                $link_gui->setFilterWhiteList(true);
                $this->ctrl->forwardCommand($link_gui);
                break;

            case "ilquestioneditgui":
                $this->setQEditTabs("question");
                include_once("./Modules/TestQuestionPool/classes/class.ilQuestionEditGUI.php");
                $edit_gui = new ilQuestionEditGUI();
                $edit_gui->setPageConfig($this->getPageConfig());
//			    $edit_gui->addNewIdListener($this, "setNewQuestionId");
                $edit_gui->setSelfAssessmentEditingMode(true);
                $ret = $this->ctrl->forwardCommand($edit_gui);
                $this->tpl->setContent($ret);
                break;

            case 'ilassquestionfeedbackeditinggui':

                $this->onFeedbackEditingForwarding();

                // set tabs
                $this->setQEditTabs("feedback");
                
                // load required lang mods
                $this->lng->loadLanguageModule("assessment");

                // set context tabs
                require_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
                require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
                $questionGUI = assQuestionGUI::_getQuestionGUI(assQuestion::_getQuestionType((int) $_GET['q_id']), (int) $_GET['q_id']);
                $questionGUI->object->setObjId(0);
                $questionGUI->object->setSelfAssessmentEditingMode(true);
                $questionGUI->object->setPreventRteUsage($this->getPageConfig()->getPreventRteUsage());

                // forward to ilAssQuestionFeedbackGUI
                require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
                $gui = new ilAssQuestionFeedbackEditingGUI($questionGUI, $this->ctrl, $this->access, $this->tpl, $this->tabs_gui, $this->lng);
                $this->ctrl->forwardCommand($gui);
                break;

/*			case "ilpagemultilanggui":
                $this->ctrl->setReturn($this, "edit");
                include_once("./Services/COPage/classes/class.ilPageMultiLangGUI.php");
                $ml_gui = new ilPageMultiLangGUI($this->getPageObject()->getParentType(), $this->getPageObject()->getParentId(),
                    $this->getPageConfig()->getSinglePageMode());
                //$this->setTabs("settings");
                //$this->setSubTabs("cont_multilinguality");
                $ret = $this->ctrl->forwardCommand($ml_gui);
                break;*/


            case 'ilLearninghistorygui':
                $user_id = null;
                if ($this->getPageObject()->getParentType() == "prtf") {
                    $user_id = ilObject::_lookupOwner($this->getPageObject()->getPortfolioId());
                }
                $hist_gui = new ilLearningHistoryGUI();
                $hist_gui->setUserId($user_id);
                $this->ctrl->forwardCommand($hist_gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("preview");
                // presentation view
                if ($this->getViewPageLink() != "" && $cmd != "edit") {
                    $this->tabs_gui->addNonTabbedLink(
                        "pres_view",
                        $this->getViewPageText(),
                        $this->getViewPageLink(),
                        $this->getViewPageTarget()
                    );
                }
                $ret = $this->$cmd();
                if ($this->getOutputMode() == self::PREVIEW && $cmd == "preview") {
                    $this->showEditToolbar();
                }
                break;
        }
        //echo "+$ret+";

        return $ret;
    }

    /**
     * Set question editing tabs
     *
     * @param
     * @return
     */
    public function setQEditTabs($a_active)
    {
        include_once("./Modules/TestQuestionPool/classes/class.assQuestion.php");
        
        $this->tabs_gui->clearTargets();

        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "edit")
        );

        $this->ctrl->setParameterByClass("ilquestioneditgui", "q_id", $_GET["q_id"]);
        $this->tabs_gui->addTab(
            "question",
            $this->lng->txt("question"),
            $this->ctrl->getLinkTargetByClass("ilquestioneditgui", "editQuestion")
        );

        require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionFeedbackEditingGUI.php';
        $this->ctrl->setParameterByClass("ilAssQuestionFeedbackEditingGUI", "q_id", $_GET["q_id"]);
        $this->tabs_gui->addTab(
            "feedback",
            $this->lng->txt("feedback"),
            $this->ctrl->getLinkTargetByClass("ilAssQuestionFeedbackEditingGUI", ilAssQuestionFeedbackEditingGUI::CMD_SHOW)
        );

        $this->tabs_gui->activateTab($a_active);
    }
    
    /**
     * On feedback editing forwarding
     */
    public function onFeedbackEditingForwarding()
    {
    }
    
    
    public function deactivatePage()
    {
        $this->getPageObject()->setActivationStart(null);
        $this->getPageObject()->setActivationEnd(null);
        $this->getPageObject()->setActive(false);
        $this->getPageObject()->update();
        $this->ctrl->redirect($this, "edit");
    }

    public function activatePage()
    {
        $this->getPageObject()->setActivationStart(null);
        $this->getPageObject()->setActivationEnd(null);
        $this->getPageObject()->setActive(true);
        $this->getPageObject()->update();
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Show edit toolbar
     */
    protected function showEditToolbar()
    {
        $ui = $this->ui;
        $lng = $this->lng;
        if ($this->getEnableEditing()) {
            $b = $ui->factory()->button()->standard(
                $lng->txt("edit_page"),
                $this->ctrl->getLinkTarget($this, "edit")
            );
            $this->toolbar->addComponent($b);
        }
    }

    /**
     * display content of page
     */
    public function showPage()
    {
        $main_tpl = $this->tpl;

        // jquery and jquery ui are always provided for components
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();

        //		$this->initSelfAssessmentRendering();
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
        ilObjMediaObjectGUI::includePresentationJS($main_tpl);

        $main_tpl->addJavaScript("./Services/COPage/js/ilCOPagePres.js");

        // needed for overlays in iim
        include_once("./Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php");
        ilOverlayGUI::initJavascript();
        
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");
        ilPlayerUtil::initMediaElementJs($main_tpl);
        
        // init template
        //if($this->outputToTemplate())
        //{
        if ($this->getOutputMode() == "edit") {
            $this->initEditing();
            if (!$this->getPageObject()->getEditLock()) {
                return;
            }

            $this->getPageObject()->buildDom();

            $this->log->debug("ilPageObjectGUI, showPage() in edit mode.");

            //echo ":".$this->getTemplateTargetVar().":";
            $tpl = new ilTemplate("tpl.page_edit_wysiwyg.html", true, true, "Services/COPage");
            //$this->tpl->addBlockFile($this->getTemplateTargetVar(), "adm_content", "tpl.page_edit_wysiwyg.html", "Services/COPage");

            // to do: status dependent class
            $tpl->setVariable("CLASS_PAGE_TD", "ilc_Page");

            // user comment
            if ($this->isEnabledChangeComments()) {
                $tpl->setCurrentBlock("change_comment");
                $tpl->setVariable("TXT_ADD_COMMENT", $this->lng->txt("cont_add_change_comment"));
                $tpl->parseCurrentBlock();
            }

            if ($this->getPageConfig()->getUsePageContainer()) {
                $tpl->setVariable("PAGE_CONTAINER_CLASS", "ilc_page_cont_PageContainer");
            }

            $tpl->setVariable(
                "WYSIWYG_ACTION",
                $this->ctrl->getFormActionByClass("ilpageeditorgui", "", "", true)
            );

            // determine media, html and javascript mode
            $sel_js_mode = (ilPageEditorGUI::_doJSEditing())
                        ? "enable"
                        : "disable";
            $sel_js_mode = "enable";

            // show prepending html
            $tpl->setVariable("PREPENDING_HTML", $this->getPrependingHtml());
            $tpl->setVariable("TXT_CONFIRM_DELETE", $this->lng->txt("cont_confirm_delete"));


            // get js files for JS enabled editing
            if ($sel_js_mode == "enable") {

                // add int link parts
                include_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
                $tpl->setCurrentBlock("int_link_prep");
                $tpl->setVariable("INT_LINK_PREP", ilInternalLinkGUI::getInitHTML(
                    $this->ctrl->getLinkTargetByClass(
                        array("ilpageeditorgui", "ilinternallinkgui"),
                        "",
                        false,
                        true,
                        false
                    )
                ));
                $tpl->parseCurrentBlock();

                $editor_init = new \ILIAS\COPage\Editor\UI\Init();
                $editor_init->initUI($main_tpl, (string) $this->getOpenPlaceHolder());
            }
        } else {
            // presentation or preview here

            $tpl = new ilTemplate("tpl.page.html", true, true, "Services/COPage");
            if ($this->getEnabledPageFocus()) {
                $tpl->touchBlock("page_focus");
            }
                
            include_once("./Services/User/classes/class.ilUserUtil.php");
                
            // presentation
            if ($this->isPageContainerToBeRendered()) {
                $tpl->touchBlock("page_container_1");
                $tpl->touchBlock("page_container_2");
                $tpl->touchBlock("page_container_3");
            }

            // history
            $c_old_nr = $this->getPageObject()->old_nr;
            if ($c_old_nr > 0 || $this->getCompareMode() || $_GET["history_mode"] == 1) {
                $hist_info =
                        $this->getPageObject()->getHistoryInfo($c_old_nr);

                if (!$this->getCompareMode()) {
                    $this->ctrl->setParameter($this, "history_mode", "1");

                    // previous revision
                    if (is_array($hist_info["previous"])) {
                        $tpl->setCurrentBlock("previous_rev");
                        $tpl->setVariable("TXT_PREV_REV", $this->lng->txt("cont_previous_rev"));
                        $this->ctrl->setParameter($this, "old_nr", $hist_info["previous"]["nr"]);
                        $tpl->setVariable(
                            "HREF_PREV",
                            $this->ctrl->getLinkTarget($this, "preview")
                        );
                        $tpl->parseCurrentBlock();
                    } else {
                        $tpl->setCurrentBlock("previous_rev_disabled");
                        $tpl->setVariable("TXT_PREV_REV", $this->lng->txt("cont_previous_rev"));
                        $tpl->parseCurrentBlock();
                    }
                        
                    // next revision
                    if ($c_old_nr > 0) {
                        $tpl->setCurrentBlock("next_rev");
                        $tpl->setVariable("TXT_NEXT_REV", $this->lng->txt("cont_next_rev"));
                        $this->ctrl->setParameter($this, "old_nr", $hist_info["next"]["nr"]);
                        $tpl->setVariable(
                            "HREF_NEXT",
                            $this->ctrl->getLinkTarget($this, "preview")
                        );
                        $tpl->parseCurrentBlock();

                        // latest revision
                        $tpl->setCurrentBlock("latest_rev");
                        $tpl->setVariable("TXT_LATEST_REV", $this->lng->txt("cont_latest_rev"));
                        $this->ctrl->setParameter($this, "old_nr", "");
                        $tpl->setVariable(
                            "HREF_LATEST",
                            $this->ctrl->getLinkTarget($this, "preview")
                        );
                        $tpl->parseCurrentBlock();
                    }

                    $this->ctrl->setParameter($this, "history_mode", "");

                    // rollback
                    if ($c_old_nr > 0 && $this->user->getId() != ANONYMOUS_USER_ID) {
                        $tpl->setCurrentBlock("rollback");
                        $this->ctrl->setParameter($this, "old_nr", $c_old_nr);
                        $tpl->setVariable(
                            "HREF_ROLLBACK",
                            $this->ctrl->getLinkTarget($this, "rollbackConfirmation")
                        );
                        $this->ctrl->setParameter($this, "old_nr", "");
                        $tpl->setVariable(
                            "TXT_ROLLBACK",
                            $this->lng->txt("cont_rollback")
                        );
                        $tpl->parseCurrentBlock();
                    }
                }
                    
                $tpl->setCurrentBlock("hist_nav");
                $tpl->setVariable("TXT_REVISION", $this->lng->txt("cont_revision"));
                $tpl->setVariable(
                    "VAL_REVISION_DATE",
                    ilDatePresentation::formatDate(new ilDateTime($hist_info["current"]["hdate"], IL_CAL_DATETIME))
                );
                $tpl->setVariable(
                    "VAL_REV_USER",
                    ilUserUtil::getNamePresentation($hist_info["current"]["user_id"])
                );
                $tpl->parseCurrentBlock();
            }
        }
        if ($this->getOutputMode() != self::PRESENTATION &&
                $this->getOutputMode() != self::OFFLINE &&
                $this->getOutputMode() != self::PREVIEW &&
                $this->getOutputMode() != self::PRINTING) {
            $tpl->setVariable("FORMACTION", $this->ctrl->getFormActionByClass("ilpageeditorgui"));
        }

        // output media object edit list (of media links)
        if ($this->getOutputMode() == "edit") {
            $links = ilInternalLink::_getTargetsOfSource(
                $this->obj->getParentType() . ":pg",
                $this->obj->getId(),
                $this->obj->getLanguage()
            );
            $mob_links = array();
            foreach ($links as $link) {
                if ($link["type"] == "mob") {
                    if (ilObject::_exists($link["id"]) && ilObject::_lookupType($link["id"]) == "mob") {
                        $mob_links[$link["id"]] = ilObject::_lookupTitle($link["id"]) . " [" . $link["id"] . "]";
                    }
                }
            }

            // linked media objects
            if (count($mob_links) > 0) {
                $tpl->setCurrentBlock("med_link");
                $tpl->setVariable("TXT_LINKED_MOBS", $this->lng->txt("cont_linked_mobs"));
                $tpl->setVariable(
                    "SEL_MED_LINKS",
                    ilUtil::formSelect(0, "mob_id", $mob_links, false, true)
                );
                $tpl->setVariable("TXT_EDIT_MEDIA", $this->lng->txt("cont_edit_mob"));
                $tpl->setVariable("TXT_COPY_TO_CLIPBOARD", $this->lng->txt("cont_copy_to_clipboard"));
                //$this->tpl->setVariable("TXT_COPY_TO_POOL", $this->lng->txt("cont_copy_to_mediapool"));
                $tpl->parseCurrentBlock();
            }
                
            // content snippets used
            include_once("./Services/COPage/classes/class.ilPCContentInclude.php");
            $this->getPageObject()->buildDom();
            $snippets = ilPCContentInclude::collectContentIncludes(
                $this->getPageObject(),
                $this->getPageObject()->getDomDoc()
            );
            if (count($snippets) > 0) {
                foreach ($snippets as $s) {
                    include_once("./Modules/MediaPool/classes/class.ilMediaPoolPage.php");
                    $sn_arr[$s["id"]] = ilMediaPoolPage::lookupTitle($s["id"]);
                }
                $tpl->setCurrentBlock("med_link");
                $tpl->setVariable("TXT_CONTENT_SNIPPETS_USED", $this->lng->txt("cont_snippets_used"));
                $tpl->setVariable(
                    "SEL_SNIPPETS",
                    ilUtil::formSelect(0, "ci_id", $sn_arr, false, true)
                );
                $tpl->setVariable("TXT_SHOW_INFO", $this->lng->txt("cont_show_info"));
                $tpl->parseCurrentBlock();
            }
                
            // scheduled activation?
            if (!$this->getPageObject()->getActive() &&
                    $this->getPageObject()->getActivationStart() != "" &&
                    $this->getPageConfig()->getEnableScheduledActivation()) {
                $tpl->setCurrentBlock("activation_txt");
                $tpl->setVariable("TXT_SCHEDULED_ACTIVATION", $this->lng->txt("cont_scheduled_activation"));
                $tpl->setVariable(
                    "SA_FROM",
                    ilDatePresentation::formatDate(
                        new ilDateTime(
                            $this->getPageObject()->getActivationStart(),
                            IL_CAL_DATETIME
                        )
                    )
                );
                $tpl->setVariable(
                    "SA_TO",
                    ilDatePresentation::formatDate(
                        new ilDateTime(
                            $this->getPageObject()->getActivationEnd(),
                            IL_CAL_DATETIME
                        )
                    )
                );
                $tpl->parseCurrentBlock();
            }
        }

        if ($_GET["reloadTree"] == "y") {
            $tpl->setCurrentBlock("reload_tree");
            $tpl->setVariable(
                "LINK_TREE",
                $this->ctrl->getLinkTargetByClass("ilobjlearningmodulegui", "explorer", "", false, false)
            );
            $tpl->parseCurrentBlock();
        }
        //		}
        // get content
        $builded = $this->obj->buildDom();

        // manage hierarchical ids
        if ($this->getOutputMode() == "edit") {
            
            // add pc ids, if necessary
            if (!$this->obj->checkPCIds()) {
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
            foreach ($hids as $hid) {
                $tpl->setCurrentBlock("add_dhtml");
                $tpl->setVariable("CONTEXTMENU", "contextmenu_" . $hid);
                $tpl->parseCurrentBlock();
            }

            // column menues for tables
            foreach ($col1_ids as $hid) {
                $tpl->setCurrentBlock("add_dhtml");
                $tpl->setVariable("CONTEXTMENU", "contextmenu_r" . $hid);
                $tpl->parseCurrentBlock();
            }

            // row menues for tables
            foreach ($row1_ids as $hid) {
                $tpl->setCurrentBlock("add_dhtml");
                $tpl->setVariable("CONTEXTMENU", "contextmenu_c" . $hid);
                $tpl->parseCurrentBlock();
            }

            // list item menues
            foreach ($litem_ids as $hid) {
                $tpl->setCurrentBlock("add_dhtml");
                $tpl->setVariable("CONTEXTMENU", "contextmenu_i" . $hid);
                $tpl->parseCurrentBlock();
            }

            // file item menues
            foreach ($fitem_ids as $hid) {
                $tpl->setCurrentBlock("add_dhtml");
                $tpl->setVariable("CONTEXTMENU", "contextmenu_i" . $hid);
                $tpl->parseCurrentBlock();
            }
        } else {
            $this->obj->addFileSizes();
        }

        //echo "<br>-".htmlentities($this->obj->getXMLContent())."-<br><br>"; exit;
        //echo "<br>-".htmlentities($this->getLinkXML())."-"; exit;

        // set default link xml, if nothing was set yet
        if (!$this->link_xml_set) {
            $this->setDefaultLinkXml();
        }

        //$content = $this->obj->getXMLFromDom(false, true, true,
        //	$this->getLinkXML().$this->getQuestionXML().$this->getComponentPluginsXML());
        $link_xml = $this->getLinkXML();
        //echo "<br>-".htmlentities($link_xml)."-"; exit;
        // disable/enable auto margins
        if ($this->getStyleId() > 0) {
            if (ilObject::_lookupType($this->getStyleId()) == "sty") {
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                $style = new ilObjStyleSheet($this->getStyleId());
                $template_xml = $style->getTemplateXML();
                $disable_auto_margins = "n";
                if ($style->lookupStyleSetting("disable_auto_margins")) {
                    $disable_auto_margins = "y";
                }
            }
        }

        $append_footnotes = "y";
        if ($this->getAbstractOnly()) {
            if (!$this->abstract_pcid) {
                $content = "<dummy><PageObject><PageContent><Paragraph>" .
                    $this->obj->getFirstParagraphText() . $link_xml .
                    "</Paragraph></PageContent></PageObject></dummy>";
            } else {
                $append_footnotes = "n";
                $par = $this->obj->getParagraphForPCID($this->abstract_pcid);
                $content = "<dummy><PageObject><PageContent><Paragraph Characteristic='" . $par->getCharacteristic() . "'>" .
                    $par->getText() . $link_xml .
                    "</Paragraph></PageContent></PageObject></dummy>";
            }
        } else {
            $content = $this->obj->getXMLFromDom(
                false,
                true,
                true,
                $link_xml . $this->getQuestionXML() . $template_xml . $this->getComponentPluginsXML()
            );
        }

        // check validation errors
        if ($builded !== true) {
            $this->displayValidationError($builded);
        } else {
            $this->displayValidationError($_SESSION["il_pg_error"]);
        }
        unset($_SESSION["il_pg_error"]);

        // get title
        $pg_title = $this->getPresentationTitle();

        if ($this->getOutputMode() == "edit") {
            $col_path = ilUtil::getImagePath("col.svg");
            $row_path = ilUtil::getImagePath("row.svg");
            $item_path = ilUtil::getImagePath("item.svg");
            $cell_path = ilUtil::getImagePath("cell.svg");
        }

        if ($this->getOutputMode() != "offline") {
            $enlarge_path = ilUtil::getImagePath("enlarge.svg");
            $wb_path = ilUtil::getWebspaceDir("output") . "/";
        } else {
            $enlarge_path = "images/enlarge.svg";
            $wb_path = "";
        }
        $pg_title_class = ($this->getOutputMode() == "print")
            ? "ilc_PrintPageTitle"
            : "";

        // page splitting only for learning modules and
        // digital books
        $enable_split_new = ($this->obj->getParentType() == "lm")
            ? "y"
            : "n";

        // page splitting to next page only for learning modules and
        // digital books if next page exists in tree
        if (($this->obj->getParentType() == "lm") &&
            ilObjContentObject::hasSuccessorPage(
                $this->obj->getParentId(),
                $this->obj->getId()
            )) {
            $enable_split_next = "y";
        } else {
            $enable_split_next = "n";
        }

        $img_path = ilUtil::getImagePath("", false, $this->getOutputMode(), $this->getOutputMode() == "offline");

        
        if ($this->getPageConfig()->getEnablePCType("Tabs")) {
            //include_once("./Services/YUI/classes/class.ilYuiUtil.php");
            //ilYuiUtil::initTabView();
            include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
            ilAccordionGUI::addJavaScript();
            ilAccordionGUI::addCss();
        }

        // needed for placeholders
        $this->tpl->addCss(ilObjStyleSheet::getPlaceHolderStylePath());

        $file_download_link = $this->determineFileDownloadLink();
        $fullscreen_link = $this->determineFullscreenLink();
        $this->sourcecode_download_script = $this->determineSourcecodeDownloadScript();
        
        // default values for various parameters (should be used by
        // all instances in the future)
        $media_mode = ($this->getOutputMode() == "edit")
            ? $this->user->getPref("ilPageEditor_MediaMode")
            : "enable";

        include_once("./Modules/LearningModule/classes/class.ilEditClipboard.php");
        $paste = (ilEditClipboard::getAction() == "copy" &&
            $this->getOutputMode() == "edit");
        
        include_once("./Services/MediaObjects/classes/class.ilPlayerUtil.php");

        $flv_video_player = ($this->getOutputMode() != "offline")
            ? ilPlayerUtil::getFlashVideoPlayerFilename(true)
            : ilPlayerUtil::getFlashVideoPlayerFilename(true);
            
        $cfg = $this->getPageConfig();

        $current_ts = time();
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

        $enable_href = $this->getEnabledHref();
        if ($this->getOutputMode() == self::EDIT) {
            $enable_href = false;
        }

        // added UTF-8 encoding otherwise umlaute are converted too
        include_once("./Services/Maps/classes/class.ilMapUtil.php");
        $params = array('mode' => $this->getOutputMode(), 'pg_title' => htmlentities($pg_title, ENT_QUOTES, "UTF-8"),
                         'enable_placeholder' => $cfg->getEnablePCType("PlaceHolder") ? "y" : "n",
                         'pg_id' => $this->obj->getId(), 'pg_title_class' => $pg_title_class,
                         'webspace_path' => $wb_path, 'enlarge_path' => $enlarge_path,
                         'img_col' => $col_path,
                         'img_row' => $row_path,
                         'img_cell' => $cell_path,
                         'img_item' => $item_path,
                         'append_footnotes' => $append_footnotes,
                         'compare_mode' => $this->getCompareMode() ? "y" : "n",
                         'enable_split_new' => $enable_split_new,
                         'enable_split_next' => $enable_split_next,
                         'link_params' => $this->link_params,
                         'file_download_link' => $file_download_link,
                         'fullscreen_link' => $fullscreen_link,
                         'img_path' => $img_path,
                         'parent_id' => $this->obj->getParentId(),
                         'download_script' => $this->sourcecode_download_script,
                         'encoded_download_script' => urlencode($this->sourcecode_download_script),
                         'enable_rep_objects' => $cfg->getEnablePCType("Resources") ? "y" : "n",
                         'enable_login_page' => $cfg->getEnablePCType("LoginPageElement") ? "y" : "n",
                         'enable_map' => ($cfg->getEnablePCType("Map") && ilMapUtil::isActivated()) ? "y" : "n",
                         'enable_tabs' => $cfg->getEnablePCType("Tabs") ? "y" : "n",
                         'enable_sa_qst' => $cfg->getEnableSelfAssessment() ? "y" : "n",
                         'enable_file_list' => $cfg->getEnablePCType("FileList") ? "y" : "n",
                         'enable_content_includes' => $cfg->getEnablePCType("ContentInclude") ? "y" : "n",
                         'enable_content_templates' => (count($this->getPageObject()->getContentTemplates()) > 0) ? "y" : "n",
                         'paste' => $paste ? "y" : "n",
                         'media_mode' => $media_mode,
                         'javascript' => $sel_js_mode,
                         'paragraph_plugins' => $paragraph_plugin_string,
                         'disable_auto_margins' => $disable_auto_margins,
                         'page_toc' => $cfg->getEnablePageToc() ? "y" : "n",
                         'enable_profile' => $cfg->getEnablePCType("Profile") ? "y" : "n",
                         'enable_verification' => $cfg->getEnablePCType("Verification") ? "y" : "n",
                         'enable_blog' => $cfg->getEnablePCType("Blog") ? "y" : "n",
                         'enable_skills' => $cfg->getEnablePCType("Skills") ? "y" : "n",
                         'enable_learning_history' => $cfg->getEnablePCType("LearningHistory") ? "y" : "n",
                         'enable_qover' => $cfg->getEnablePCType("QuestionOverview") ? "y" : "n",
                         'enable_consultation_hours' => $cfg->getEnablePCType("ConsultationHours") ? "y" : "n",
                         'enable_my_courses' => $cfg->getEnablePCType("MyCourses") ? "y" : "n",
                         'enable_amd_page_list' => $cfg->getEnablePCType("AMDPageList") ? "y" : "n",
                         'current_ts' => $current_ts,
                         'enable_html_mob' => ilObjMediaObject::isTypeAllowed("html") ? "y" : "n",
                         'flv_video_player' => $flv_video_player,
                         'page_perma_link' => $this->getPagePermaLink(),
                          'enable_href' => $enable_href
                        );
        if ($this->link_frame != "") {		// todo other link types
            $params["pg_frame"] = $this->link_frame;
        }

        //$content = str_replace("&nbsp;", "", $content);
        
        // this ensures that cache is emptied with every update
        $params["version"] = ILIAS_VERSION;
        // ensure no cache hit, if included files/media objects have been changed
        $params["incl_elements_date"] = $this->obj->getLastUpdateOfIncludedElements();


        // should be modularized
        include_once("./Services/COPage/classes/class.ilPCSection.php");
        $md5_adds = ilPCSection::getCacheTriggerString($this->getPageObject());
        // run xslt
        $md5 = md5(serialize($params) . $link_xml . $template_xml . $md5_adds);
        
        //$a = microtime();
        
        // check cache (same parameters, non-edit mode and rendered time
        // > last change
        $is_error = false;
        if (($this->getOutputMode() == "preview" || $this->getOutputMode() == "presentation") &&
            !$this->getCompareMode() &&
            !$this->getAbstractOnly() &&
            $md5 == $this->obj->getRenderMd5() &&
            ($this->obj->getLastChange() < $this->obj->getRenderedTime()) &&
            $this->obj->getRenderedTime() != "" &&
            $this->obj->old_nr == 0) {
            // cache hit
            $output = $this->obj->getRenderedContent();
        } else {
            $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
            $this->log->debug("Calling XSLT, content: " . substr($content, 0, 100));
            try {
                $args = array( '/_xml' => $content, '/_xsl' => $xsl );
                $xh = xslt_create();
                $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
            } catch (Exception $e) {
                $output = "";
                if ($this->getOutputMode() == "edit") {
                    $output = "<pre>" . $e->getMessage() . "<br>" . htmlentities($content) . "</pre>";
                    $is_error = true;
                }
            }
            if (($this->getOutputMode() == "presentation" || $this->getOutputMode() == "preview")
                && !$this->getAbstractOnly()
                && $this->obj->old_nr == 0) {
                $this->obj->writeRenderedContent($output, $md5);
            }
            xslt_free($xh);
        }

        if (!$is_error) {
            // unmask user html
            if (($this->getOutputMode() != "edit" ||
                    $this->user->getPref("ilPageEditor_HTMLMode") != "disable")
                && !$this->getPageConfig()->getPreventHTMLUnmasking()) {
                $output = str_replace("&lt;", "<", $output);
                $output = str_replace("&gt;", ">", $output);
            }
            $output = str_replace("&amp;", "&", $output);

            include_once './Services/MathJax/classes/class.ilMathJax.php';
            $output = ilMathJax::getInstance()->insertLatexImages($output);

            // insert page snippets
            //$output = $this->insertContentIncludes($output);

            // insert resource blocks
            $output = $this->insertResources($output);

            // insert page toc
            if ($this->getPageConfig()->getEnablePageToc()) {
                $output = $this->insertPageToc($output);
            }

            // insert advanced output trigger
            $output = $this->insertAdvTrigger($output);

            // workaround for preventing template engine
            // from hiding paragraph text that is enclosed
            // in curly brackets (e.g. "{a}", see ilLMEditorGUI::executeCommand())
            $output = $this->replaceCurlyBrackets($output);

            // remove all newlines (important for code / pre output)
            $output = str_replace("\n", "", $output);

            //echo htmlentities($output);
            $output = $this->postOutputProcessing($output);
            //echo htmlentities($output);
            if ($this->getOutputMode() == "edit" &&
                !$this->getPageObject()->getActive($this->getPageConfig()->getEnableScheduledActivation())) {
                $output = '<div class="il_editarea_disabled"><div class="ilCopgDisabledText">' . $this->getDisabledText() . '</div>' . $output . '</div>';
            }

            // for all page components...
            include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
            $defs = ilCOPagePCDef::getPCDefinitions();
            foreach ($defs as $def) {
                ilCOPagePCDef::requirePCClassByName($def["name"]);
                $pc_class = $def["pc_class"];
                $pc_obj = new $pc_class($this->getPageObject());
                $pc_obj->setSourcecodeDownloadScript($this->determineSourcecodeDownloadScript());
                $pc_obj->setFileDownloadLink($this->determineFileDownloadLink());
                $pc_obj->setFullscreenLink($this->determineFullscreenLink());

                // post xsl page content modification by pc elements
                $output = $pc_obj->modifyPageContentPostXsl($output, $this->getOutputMode(), $this->getAbstractOnly());
            }
        }

        $this->addResourcesToTemplate($main_tpl);
        
        //		$output = $this->selfAssessmentRendering($output);

        // output
        if ($this->ctrl->isAsynch() && !$this->getRawPageContent() &&
            $this->getOutputMode() == "edit") {
            // e.g. ###3:110dad8bad6df8620071a0a693a2d328###
            if ($_GET["updated_pc_id_str"] != "") {
                echo $_GET["updated_pc_id_str"];
            }
            $tpl->setVariable($this->getTemplateOutputVar(), $output);
            $tpl->setCurrentBlock("edit_page");
            $tpl->parseCurrentBlock();
            echo $tpl->get("edit_page");
            exit;
        }
        if ($this->outputToTemplate()) {
            $tpl->setVariable($this->getTemplateOutputVar(), $output);
            $this->tpl->setVariable($this->getTemplateTargetVar(), $tpl->get());
            return $output;
        } else {
            if ($this->getRawPageContent()) {		// e.g. needed in glossaries
                return $output;
            } else {
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
    public function replaceCurlyBrackets($output)
    {
        //echo "<br><br>".htmlentities($output);
        
        while (is_int($start = strpos($output, "<!--ParStart-->")) &&
            is_int($end = strpos($output, "<!--ParEnd-->", $start))) {
            $output = substr($output, 0, $start) .
                str_replace(
                    array("{","}"),
                    array("&#123;","&#125;"),
                    substr($output, $start + 15, $end - ($start + 15))
                ) .
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
    public function getActivationCaptions()
    {
        return array("deactivatePage" => $this->lng->txt("cont_deactivate_page"),
                "activatePage" => $this->lng->txt("cont_activate_page"));
    }

    /**
     * Set edit mode
     */
    public function setEditMode()
    {
        if ($_GET["media_mode"] != "") {
            if ($_GET["media_mode"] == "disable") {
                $this->user->writePref("ilPageEditor_MediaMode", "disable");
            } else {
                $this->user->writePref("ilPageEditor_MediaMode", "");
            }
        }
        if ($_GET["html_mode"] != "") {
            if ($_GET["html_mode"] == "disable") {
                $this->user->writePref("ilPageEditor_HTMLMode", "disable");
            } else {
                $this->user->writePref("ilPageEditor_HTMLMode", "");
            }
        }
        if ($_GET["js_mode"] != "") {
            if ($_GET["js_mode"] == "disable") {
                $this->user->writePref("ilPageEditor_JavaScript", "disable");
            } else {
                $this->user->writePref("ilPageEditor_JavaScript", "");
            }
        }

        $this->ctrl->redirect($this, "edit");
    }


    /**
     * Get Tiny Menu
     */
    public static function getTinyMenu(
        $a_par_type,
        $a_int_links = false,
        $a_wiki_links = false,
        $a_keywords = false,
        $a_style_id = 0,
        $a_paragraph_styles = true,
        $a_save_return = true,
        $a_anchors = false,
        $a_save_new = true,
        $a_user_links = false,
        \ILIAS\COPage\Editor\Server\UIWrapper $ui_wrapper = null
    ) {
        global $DIC;

        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();
        $ui = $DIC->ui();

        $aset = new ilSetting("adve");

        // character styles
        $chars = array(
            "Comment" => array("code" => "com", "txt" => $lng->txt("cont_char_style_com")),
            "Quotation" => array("code" => "quot", "txt" => $lng->txt("cont_char_style_quot")),
            "Accent" => array("code" => "acc", "txt" => $lng->txt("cont_char_style_acc")),
            "Code" => array("code" => "code", "txt" => $lng->txt("cont_char_style_code"))
        );
        foreach (ilPCParagraphGUI::_getTextCharacteristics($a_style_id) as $c) {
            if (!isset($chars[$c])) {
                $chars[$c] = array("code" => "", "txt" => $c);
            }
        }
        $char_formats = [];
        foreach ($chars as $key => $char) {
            if (ilPageEditorSettings::lookupSettingByParentType(
                $a_par_type,
                "active_" . $char["code"],
                true
            )) {
                $t = "text_inline";
                $tag = "span";
                switch ($key) {
                    case "Code": $tag = "code"; break;
                }
                $html = '<' . $tag . ' class="ilc_' . $t . '_' . $key . '" style="font-size:90%; margin-top:2px; margin-bottom:2px; position:static;">' . $char["txt"] . "</" . $tag . ">";
                $char_formats[] = ["text" => $html, "action" => "selection.format", "data" => ["format" => $key]];
            }
        }


        $numbered_list = '<svg width="24" height="24"><path d="M10 17h8c.6 0 1 .4 1 1s-.4 1-1 1h-8a1 1 0 010-2zm0-6h8c.6 0 1 .4 1 1s-.4 1-1 1h-8a1 1 0 010-2zm0-6h8c.6 0 1 .4 1 1s-.4 1-1 1h-8a1 1 0 110-2zM6 4v3.5c0 .3-.2.5-.5.5a.5.5 0 01-.5-.5V5h-.5a.5.5 0 010-1H6zm-1 8.8l.2.2h1.3c.3 0 .5.2.5.5s-.2.5-.5.5H4.9a1 1 0 01-.9-1V13c0-.4.3-.8.6-1l1.2-.4.2-.3a.2.2 0 00-.2-.2H4.5a.5.5 0 01-.5-.5c0-.3.2-.5.5-.5h1.6c.5 0 .9.4.9 1v.1c0 .4-.3.8-.6 1l-1.2.4-.2.3zM7 17v2c0 .6-.4 1-1 1H4.5a.5.5 0 010-1h1.2c.2 0 .3-.1.3-.3 0-.2-.1-.3-.3-.3H4.4a.4.4 0 110-.8h1.3c.2 0 .3-.1.3-.3 0-.2-.1-.3-.3-.3H4.5a.5.5 0 110-1H6c.6 0 1 .4 1 1z" fill-rule="evenodd"></path></svg>';

        $bullet_list = '<svg width="24" height="24"><path d="M11 5h8c.6 0 1 .4 1 1s-.4 1-1 1h-8a1 1 0 010-2zm0 6h8c.6 0 1 .4 1 1s-.4 1-1 1h-8a1 1 0 010-2zm0 6h8c.6 0 1 .4 1 1s-.4 1-1 1h-8a1 1 0 010-2zM4.5 6c0-.4.1-.8.4-1 .3-.4.7-.5 1.1-.5.4 0 .8.1 1 .4.4.3.5.7.5 1.1 0 .4-.1.8-.4 1-.3.4-.7.5-1.1.5-.4 0-.8-.1-1-.4-.4-.3-.5-.7-.5-1.1zm0 6c0-.4.1-.8.4-1 .3-.4.7-.5 1.1-.5.4 0 .8.1 1 .4.4.3.5.7.5 1.1 0 .4-.1.8-.4 1-.3.4-.7.5-1.1.5-.4 0-.8-.1-1-.4-.4-.3-.5-.7-.5-1.1zm0 6c0-.4.1-.8.4-1 .3-.4.7-.5 1.1-.5.4 0 .8.1 1 .4.4.3.5.7.5 1.1 0 .4-.1.8-.4 1-.3.4-.7.5-1.1.5-.4 0-.8-.1-1-.4-.4-.3-.5-.7-.5-1.1z" fill-rule="evenodd"></path></svg>';

        $indent = '<svg width="24" height="24"><path d="M7 5h12c.6 0 1 .4 1 1s-.4 1-1 1H7a1 1 0 110-2zm5 4h7c.6 0 1 .4 1 1s-.4 1-1 1h-7a1 1 0 010-2zm0 4h7c.6 0 1 .4 1 1s-.4 1-1 1h-7a1 1 0 010-2zm-5 4h12a1 1 0 010 2H7a1 1 0 010-2zm-2.6-3.8L6.2 12l-1.8-1.2a1 1 0 011.2-1.6l3 2a1 1 0 010 1.6l-3 2a1 1 0 11-1.2-1.6z" fill-rule="evenodd"></path></svg>';

        $outdent = '<svg width="24" height="24"><path d="M7 5h12c.6 0 1 .4 1 1s-.4 1-1 1H7a1 1 0 110-2zm5 4h7c.6 0 1 .4 1 1s-.4 1-1 1h-7a1 1 0 010-2zm0 4h7c.6 0 1 .4 1 1s-.4 1-1 1h-7a1 1 0 010-2zm-5 4h12a1 1 0 010 2H7a1 1 0 010-2zm1.6-3.8a1 1 0 01-1.2 1.6l-3-2a1 1 0 010-1.6l3-2a1 1 0 011.2 1.6L6.8 12l1.8 1.2z" fill-rule="evenodd"></path></svg>';

        // menu
        $str = "str";
        $emp = "emp";
        $imp = "imp";
        if ($aset->get("use_physical")) {
            $str = "B";
            $emp = "I";
            $imp = "U";
        }
        $c_formats = [];
        foreach (["str", "emp", "imp", "sup", "sub"] as $c) {
            if (ilPageEditorSettings::lookupSettingByParentType(
                $a_par_type,
                "active_" . $c,
                true
            )) {
                switch ($c) {
                    case "str":
                        $c_formats[] = ["text" => '<span class="ilc_text_inline_Strong">' . $str . '</span>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Strong"]
                        ];
                        break;
                    case "emp":
                        $c_formats[] = ["text" => '<span class="ilc_text_inline_Emph">' . $emp . '</span>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Emph"]
                        ];
                        break;
                    case "imp":
                        $c_formats[] = ["text" => '<span class="ilc_text_inline_Important">' . $imp . '</span>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Important"]
                        ];
                        break;
                    case "sup":
                        $c_formats[] = ["text" => 'x<sup>2</sup>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Sup"]
                        ];
                        break;
                    case "sub":
                        $c_formats[] = ["text" => 'x<sub>2</sub>',
                                        "action" => "selection.format",
                                        "data" => ["format" => "Sub"]
                        ];
                        break;
                }
            }
        }
        $c_formats[] = ["text" => "<i>A</i>",
                        "action" => $char_formats
        ];
        $c_formats[] = ["text" => '<i><b><u>T</u></b><sub>x</sub></i>',
                        "action" => "selection.removeFormat",
                        "data" => []
        ];
        $menu = [
            "cont_char_format" => $c_formats,
            "cont_lists" => [
                ["text" => $bullet_list, "action" => "list.bullet", "data" => []],
                ["text" => $numbered_list, "action" => "list.number", "data" => []],
                ["text" => $outdent, "action" => "list.outdent", "data" => []],
                ["text" => $indent, "action" => "list.indent", "data" => []]
            ]
        ];

        // more...

        // links
        $links = [];
        if ($a_wiki_links) {
            $links[] = ["text" => $lng->txt("cont_wiki_link_dialog"), "action" => "link.wikiSelection", "data" => [
                "url" => $ctrl->getLinkTargetByClass("ilwikipagegui", "")]];
            $links[] = ["text" => "[[" . $lng->txt("cont_wiki_page") . "]]", "action" => "link.wiki", "data" => []];
        }
        if ($a_int_links) {
            $links[] = ["text" => $lng->txt("cont_text_iln_link"), "action" => "link.internal", "data" => []];
        }
        if (ilPageEditorSettings::lookupSettingByParentType(
            $a_par_type,
            "active_xln",
            true
        )) {
            $links[] = ["text" => $lng->txt("cont_text_xln"), "action" => "link.external", "data" => []];
        }
        if ($a_user_links) {
            $links[] = ["text" => $lng->txt("cont_link_user"), "action" => "link.user", "data" => []];
        }


        // more
        $menu["cont_more_functions"] = [];
        $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_link") . '<i class="mce-ico mce-i-link"></i>', "action" => $links];

        if ($a_keywords) {
            $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_keyword"), "action" => "selection.keyword", "data" => []];
        }
        $mathJaxSetting = new ilSetting("MathJax");
        if (ilPageEditorSettings::lookupSettingByParentType(
            $a_par_type,
            "active_tex",
            true
        )) {
            if ($mathJaxSetting->get("enable") || defined("URL_TO_LATEX")) {
                $menu["cont_more_functions"][] = ["text" => 'Tex', "action" => "selection.tex", "data" => []];
            }
        }
        if (ilPageEditorSettings::lookupSettingByParentType(
            $a_par_type,
            "active_fn",
            true
        )) {
            $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_footnote"), "action" => "selection.fn", "data" => []];
        }
        if ($a_anchors) {
            $menu["cont_more_functions"][] = ["text" => $lng->txt("cont_anchor"), "action" => "selection.anchor", "data" => []];
        }

        $btpl = new ilTemplate("tpl.tiny_menu.html", true, true, "Services/COPage");

        foreach ($menu as $section_title => $section) {
            foreach ($section as $item) {
                if (is_array($item["action"])) {
                    $buttons = [];
                    foreach ($item["action"] as $i) {
                        $buttons[] = $ui_wrapper->getButton($i["text"], "par-action", $i["action"], $i["data"]);
                    }
                    $dd = $ui->factory()->dropdown()->standard($buttons)->withLabel($item["text"]);
                    $btpl->setCurrentBlock("button");
                    $btpl->setVariable("BUTTON", $ui->renderer()->renderAsync($dd));
                    $btpl->parseCurrentBlock();
                } else {
                    $b = $ui_wrapper->getRenderedButton($item["text"], "par-action", $item["action"], $item["data"]);
                    $btpl->setCurrentBlock("button");
                    $btpl->setVariable("BUTTON", $b);
                    $btpl->parseCurrentBlock();
                }
            }
            $btpl->setCurrentBlock("section");
            $btpl->setVariable("TXT_SECTION", $lng->txt($section_title));
            $btpl->parseCurrentBlock();
        }


        if ($a_paragraph_styles) {
            $sel = new \ILIAS\COPage\Editor\Components\Paragraph\ParagraphStyleSelector($ui_wrapper, $a_style_id);
            $dd = $sel->getStyleSelector("");
            $btpl->setCurrentBlock("par_edit");
            $btpl->setVariable("TXT_PAR_FORMAT", $lng->txt("cont_par_format"));

            $btpl->setVariable("STYLE_SELECTOR", $ui->renderer()->render($dd));

            $btpl->parseCurrentBlock();
        }

        // block styles
        $sel = new \ILIAS\COPage\Editor\Components\Section\SectionStyleSelector($ui_wrapper, $a_style_id);
        $dd = $sel->getStyleSelector("", $type = "par-action", $action = "sec.class", $attr = "class", true);
        $btpl->setVariable("TXT_BLOCK", $lng->txt("cont_sur_block_format"));
        $btpl->setVariable("BLOCK_STYLE_SELECTOR", $ui->renderer()->render($dd));


        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

        $btpl->setVariable(
            "SPLIT_BUTTON",
            $ui_wrapper->getRenderedButton($lng->txt("save_return"), "par-action", "save.return")
        );

        $btpl->setVariable(
            "CANCEL_BUTTON",
            $ui_wrapper->getRenderedButton($lng->txt("cancel"), "par-action", "component.cancel")
        );

        $btpl->setVariable("TXT_SAVING", $lng->txt("cont_saving"));
        
        include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");

        $btpl->setVariable("CHAR_STYLE_SELECTOR", ilPCParagraphGUI::getCharStyleSelector($a_par_type, true, $a_style_id));
        ilTooltipGUI::addTooltip(
            "ilAdvSelListAnchorElement_char_style_selection",
            $lng->txt("cont_more_character_styles"),
            "iltinymenu_bd"
        );

        return $btpl->get();
    }

    /**
     * Set standard link xml
     */
    public function setDefaultLinkXml()
    {
        $this->page_linker->setOffline($this->getOutputMode() == self::OFFLINE);
        $this->setLinkXML($this->page_linker->getLinkXml($this->getPageObject()->getInternalLinks()));
    }

    /**
     * Set linkXML
     *
     * @param string
     */
    public function setLinkXml($xml)
    {
        $this->link_xml = $xml;
        $this->link_xml_set = true;
    }


    /**
     * Get profile back url
     */
    public function getProfileBackUrl()
    {
        return $this->ctrl->getLinkTargetByClass(strtolower(get_class($this)), "preview");
    }

    
    /**
     * Download file of file lists
     */
    public function downloadFile()
    {
        $download_ok = false;

        require_once("./Modules/File/classes/class.ilObjFile.php");
        $pg_obj = $this->getPageObject();
        $pg_obj->buildDom();
        $int_links = $pg_obj->getInternalLinks();
        foreach ($int_links as $il) {
            if ($il["Target"] == str_replace("_file_", "_dfile_", $_GET["file_id"])) {
                $file = explode("_", $_GET["file_id"]);
                $file_id = (int) $file[count($file) - 1];
                $download_ok = true;
            }
        }
        if (in_array($_GET["file_id"], $pg_obj->getAllFileObjIds())) {
            $file = explode("_", $_GET["file_id"]);
            $file_id = (int) $file[count($file) - 1];
            $download_ok = true;
        }

        $pcs = ilPageContentUsage::getUsagesOfPage($pg_obj->getId(), $pg_obj->getParentType() . ":pg", 0, false);
        foreach ($pcs as $pc) {
            $files = ilObjFile::_getFilesOfObject("mep:pg", $pc["id"], 0);
            $file = explode("_", $_GET["file_id"]);
            $file_id = (int) $file[count($file) - 1];
            if (in_array($file_id, $files)) {
                $download_ok = true;
            }
        }

        if ($download_ok) {
            $fileObj = new ilObjFile($file_id, false);
            $fileObj->sendFile();
            exit;
        }
    }
    
    /**
     * Show media in fullscreen mode
     */
    public function displayMediaFullscreen()
    {
        $this->displayMedia(true);
    }

    /**
     * Display media
     */
    public function displayMedia($a_fullscreen = false)
    {
        $tpl = $this->tpl;

        $tpl = new ilCOPageGlobalTemplate("tpl.fullscreen.html", true, true, "Modules/LearningModule");
        $tpl->setCurrentBlock("ilMedia");

        //$int_links = $page_object->getInternalLinks();
        $med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);
        
        // @todo
        $link_xml = $this->page_linker->getLinkXML($med_links);
        
        require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $media_obj = new ilObjMediaObject($_GET["mob_id"]);
        require_once("./Services/COPage/classes/class.ilPageObject.php");
        $pg_obj = $this->getPageObject();
        $pg_obj->buildDom();

        if (!empty($_GET["pg_id"])) {
            $xml = "<dummy>";
            $xml .= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
            $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
            $xml .= $link_xml;
            $xml .= "</dummy>";
        } else {
            $xml = "<dummy>";
            $xml .= $media_obj->getXML(IL_MODE_ALIAS);
            $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
            $xml .= $link_xml;
            $xml .= "</dummy>";
        }

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        $mode = "media";
        if ($a_fullscreen) {
            $mode = "fullscreen";
        }

        //echo "<b>XML:</b>".htmlentities($xml);
        // determine target frames for internal links
        $wb_path = ilUtil::getWebspaceDir("output") . "/";
        $enlarge_path = ilUtil::getImagePath("enlarge.svg");
        $params = array('mode' => $mode, 'enlarge_path' => $enlarge_path,
            'link_params' => "ref_id=" . $_GET["ref_id"],'fullscreen_link' => "",
            'ref_id' => $_GET["ref_id"], 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
        //echo "<br><br>".htmlentities($output);
        //echo xslt_error($xh);
        xslt_free($xh);

        // unmask user html
        require_once('./Services/Style/Content/classes/class.ilObjStyleSheet.php');
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $tpl->setVariable("MEDIA_CONTENT", $output);

        // add js
        include_once("./Services/MediaObjects/classes/class.ilObjMediaObjectGUI.php");
        ilObjMediaObjectGUI::includePresentationJS($tpl);
        $tpl->fillJavaScriptFiles();
        $tpl->fillCssFiles();

        echo $tpl->get();
        exit;
    }

    /**
    * download source code paragraph
    */
    public function download_paragraph()
    {
        $pg_obj = $this->getPageObject();
        $pg_obj->send_paragraph($_GET["par_id"], $_GET["downloadtitle"]);
    }

    /**
     * Insert page toc
     *
     * @param string output
     * @return string output
     */
    public function insertPageToc($a_output)
    {
        include_once("./Services/Utilities/classes/class.ilStr.php");

        // extract all headings
        $offsets = ilStr::strPosAll($a_output, "ilPageTocH");
        $page_heads = array();
        foreach ($offsets as $os) {
            $level = (int) substr($a_output, $os + 10, 1);
            if (in_array($level, array(1,2,3))) {
                $anchor = str_replace(
                    "TocH",
                    "TocA",
                    substr($a_output, $os, strpos($a_output, "<", $os) - $os - 4)
                );

                // get heading
                $tag_start = stripos($a_output, "<h" . $level . " ", $os);
                $tag_end = stripos($a_output, "</h" . $level . ">", $tag_start);
                $head = substr($a_output, $tag_start, $tag_end - $tag_start);

                // get headings text
                $text_start = stripos($head, ">") + 1;
                $text_end = strripos($head, "<!--", $text_start);
                $text = substr($head, $text_start, $text_end - $text_start);
                $page_heads[] = array("level" => $level, "text" => $text,
                    "anchor" => $anchor);
            }
        }

        if (count($page_heads) > 1) {
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
            foreach ($page_heads as $ind => $h) {
                $i++;
                $par = 0;

                // check if we have a parent for one level up
                $par = 0;
                if ($h["level"] == 2 && $c_par[1] > 0) {
                    $par = $c_par[1];
                }
                if ($h["level"] == 3 && $c_par[2] > 0) {
                    $par = $c_par[2];
                }

                $h["text"] = str_replace("<!--PageTocPH-->", "", $h["text"]);

                // add the list node
                $list->addListNode(
                    "<a href='#" . $h["anchor"] . "' class='ilc_page_toc_PageTOCLink'>" . $h["text"] . "</a>",
                    $i,
                    $par
                );

                // set the node as current parent of the level
                if ($h["level"] == 1) {
                    $c_par[1] = $i;
                    $c_par[2] = 0;
                }
                if ($h["level"] == 2) {
                    $c_par[2] = $i;
                }
            }

            $tpl = new ilTemplate(
                "tpl.page_toc.html",
                true,
                true,
                "Services/COPage"
            );
            $tpl->setVariable("PAGE_TOC", $list->getHTML());
            $tpl->setVariable("TXT_PAGE_TOC", $this->lng->txt("cont_page_toc"));
            $tpl->setVariable("TXT_HIDE", $this->lng->txt("hide"));
            $tpl->setVariable("TXT_SHOW", $this->lng->txt("show"));

            $a_output = str_replace(
                "{{{{{PageTOC}}}}}",
                $tpl->get(),
                $a_output
            );
            $numbers = $list->getNumbers();

            if (count($numbers) > 0) {
                include_once("./Services/Utilities/classes/class.ilStr.php");
                foreach ($numbers as $n) {
                    $a_output =
                        ilStr::replaceFirsOccurence("<!--PageTocPH-->", $n . " ", $a_output);
                }
            }
        } else {
            $a_output = str_replace(
                "{{{{{PageTOC}}}}}",
                "",
                $a_output
            );
        }

        return $a_output;
    }
    
    /**
     * Insert resources
     *
     * @param
     * @return
     */
    public function insertResources($a_output)
    {
        // this is edit mode only
        
        if ($this->getEnablePCType("Resources") &&
            ($this->getOutputMode() == "edit" || $this->getOutputMode() == "preview")) {
            include_once("./Services/COPage/classes/class.ilPCResourcesGUI.php");
            $a_output = ilPCResourcesGUI::insertResourcesIntoPageContent($a_output, $this->getOutputMode());
        }
        return $a_output;
    }
    
    
    
    /**
     * Insert adv content trigger
     *
     * @param string $a_output output
     * @return string modified output
     */
    public function insertAdvTrigger($a_output)
    {
        if (!$this->getAbstractOnly()) {
            $a_output = str_replace(
                "{{{{{LV_show_adv}}}}}",
                $this->lng->txt("cont_show_adv"),
                $a_output
            );
            $a_output = str_replace(
                "{{{{{LV_hide_adv}}}}}",
                $this->lng->txt("cont_hide_adv"),
                $a_output
            );
        } else {
            $a_output = str_replace(
                "{{{{{LV_show_adv}}}}}",
                "",
                $a_output
            );
            $a_output = str_replace(
                "{{{{{LV_hide_adv}}}}}",
                "",
                $a_output
            );
        }
        
        return $a_output;
    }
    
    
    /**
     * Finalizing output processing. Maybe overwritten in derived
     * classes, e.g. in wiki module.
     */
    public function postOutputProcessing($a_output)
    {
        return $a_output;
    }
    

    /**
     * Preview history
     */
    public function previewHistory()
    {
        $this->preview();
    }

    /**
     * preview
     */
    public function preview()
    {
        $this->setOutputMode(self::PREVIEW);
        return $this->showPage();
    }

    /**
     * Set editor tool context
     */
    protected function setEditorToolContext()
    {
        $collection = $this->tool_context->current()->getAdditionalData();
        if ($collection->exists(ilCOPageEditGSToolProvider::SHOW_EDITOR)) {
            $collection->replace(ilCOPageEditGSToolProvider::SHOW_EDITOR, true);
        } else {
            $collection->add(ilCOPageEditGSToolProvider::SHOW_EDITOR, true);
        }
    }

    /**
     * Init editing
     * @param
     * @return
     */
    protected function initEditing()
    {
        // editing allowed?
        if (!$this->getEnableEditing()) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "preview");
        }

        // not so nive workaround for container pages, bug #0015831
        $ptype = $this->getParentType();
        if ($ptype == "cont" && $_GET["ref_id"] > 0) {
            $ptype = ilObject::_lookupType((int) $_GET["ref_id"], true);
        }
        $this->setScreenIdComponent();
        $this->help->setScreenId("edit_" . $ptype);

        require_once 'Services/Captcha/classes/class.ilCaptchaUtil.php';
        if (
            $this->user->isAnonymous() &&
            !$this->user->isCaptchaVerified() &&
            ilCaptchaUtil::isActiveForWiki()
        ) {
            $form = $this->initCaptchaForm();
            if ($_POST['captcha_code'] && $form->checkInput()) {
                $this->user->setCaptchaVerified(true);
            } else {
                return $form->getHTML();
            }
        }

        // edit lock
        if (!$this->getPageObject()->getEditLock()) {
            $this->showEditLockInfo();
            return;
        } else {
            $this->setEditorToolContext();
        }

        $this->lng->toJS("paste");
        $this->lng->toJS("delete");
        $this->lng->toJS("cont_delete_content");
        $this->lng->toJS("copg_confirm_el_deletion");
        $this->lng->toJS("cont_saving");
        $this->lng->toJS("cont_ed_par");
        $this->lng->toJS("cont_no_block");
        $this->lng->toJS("copg_error");
        $this->lng->toJS("cont_ed_click_to_add_pg");
        // workaroun: we need this js for the new editor version, e.g. for new section form to work
        // @todo: solve this in a smarter way
        $this->tpl->addJavascript("./Services/UIComponent/AdvancedSelectionList/js/AdvancedSelectionList.js");
        \ilCalendarUtil::initDateTimePicker();
    }

    protected function showEditLockInfo()
    {
        $info = $this->lng->txt("content_no_edit_lock");
        $lock = $this->getPageObject()->getEditLockInfo();
        $info .= "</br>" . $this->lng->txt("content_until") . ": " .
            ilDatePresentation::formatDate(new ilDateTime($lock["edit_lock_until"], IL_CAL_UNIX));
        $info .= "</br>" . $this->lng->txt("obj_usr") . ": " .
            ilUserUtil::getNamePresentation($lock["edit_lock_user"]);

        $back_link = $this->ui->factory()->link()->standard(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "finishEditing")
        );

        $mbox = $this->ui->factory()->messageBox()->info($info)
            ->withLinks([$back_link]);
        $rendered_mbox = $this->ui->renderer()->render($mbox);

        if (!$this->ctrl->isAsynch()) {
            $this->tpl->setContent($rendered_mbox);
        } else {
            echo $rendered_mbox;
            exit;
        }
    }

    /**
     * edit ("view" before)
     */
    public function edit()
    {
        $this->setOutputMode(self::EDIT);
        $html = $this->showPage();
        
        if ($this->isEnabledNotes()) {
            $html .= "<br /><br />" . $this->getNotesHTML();
        }
    
        return $html;
    }

    /**
     * Get block info message
     * @return string
     */
    public function getBlockingInfoMessage() : string
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $ui = $this->ui;

        $lock = $this->getPageObject()->getEditLockInfo();
        $info = $this->lng->txt("cont_got_lock_release");
        $info = str_replace("%1", ilDatePresentation::formatDate(new ilDateTime($lock["edit_lock_until"], IL_CAL_UNIX)), $info);

        $mbox = $ui->factory()->messageBox()->info($info);

        return $ui->renderer()->render($mbox);
    }

    
    /**
     * InsertJS at placeholder
     *
     * @param
     * @return
     */
    public function insertJSAtPlaceholder()
    {
        $tpl = $this->tpl;
        
        if ($_GET["pl_hier_id"] == "") {
            $this->obj->buildDom();
            $this->obj->addHierIDs();
            $hid = $this->obj->getHierIdsForPCIds(array($_GET["pl_pc_id"]));
            $_GET["pl_hier_id"] = $hid[$_GET["pl_pc_id"]];
        }
        
        //		  'pl_hier_id' => string '2_1_1_1' (length=7)
        //  'pl_pc_id' => string '1f77eb1d8a478497d69b99d938fda8f' (length=31)
        $this->setOpenPlaceHolder($_GET["pl_pc_id"]);

        $html = $this->edit();
        return $html;
    }
    
    /**
     * Init captcha form.
     */
    public function initCaptchaForm()
    {
        require_once  'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        
        require_once 'Services/Captcha/classes/class.ilCaptchaInputGUI.php';
        $ci = new ilCaptchaInputGUI($this->lng->txt('cont_captcha_code'), 'captcha_code');
        $ci->setRequired(true);
        $form->addItem($ci);

        $form->addCommandButton('edit', $this->lng->txt('ok'));
        
        $form->setTitle($this->lng->txt('cont_captcha_verification'));
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        return $form;
    }

    /*
    * presentation
    */
    public function presentation($a_mode = self::PRESENTATION)
    {
        $this->setOutputMode($a_mode);

        return $this->showPage();
    }

    public function getHTML()
    {
        $this->getTabs("preview");
        return $this->showPage();
    }
    
    /**
    * show fullscreen view of media object
    */
    public function showMediaFullscreen($a_style_id = 0)
    {
        $this->tpl = new ilGlobalTemplate("tpl.fullscreen.html", true, true, "Services/COPage");
        $this->tpl->setCurrentBlock("ContentStyle");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", 0);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable("PAGETITLE", " - " . ilObject::_lookupTitle($_GET["mob_id"]));
        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
        $this->tpl->setCurrentBlock("ilMedia");

        require_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
        $media_obj = new ilObjMediaObject($_GET["mob_id"]);
        if (!empty($_GET["pg_id"])) {
            include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
            $pg_obj = ilPageObjectFactory::getInstance($this->obj->getParentType(), $_GET["pg_id"]);
            $pg_obj->buildDom();

            $xml = "<dummy>";
            // todo: we get always the first alias now (problem if mob is used multiple
            // times in page)
            $xml .= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
            $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
            $xml .= "</dummy>";
        } else {
            $xml = "<dummy>";
            $xml .= $media_obj->getXML(IL_MODE_ALIAS);
            $xml .= $media_obj->getXML(IL_MODE_OUTPUT);
            $xml .= "</dummy>";
        }

        //echo htmlentities($xml); exit;

        $xsl = file_get_contents("./Services/COPage/xsl/page.xsl");
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        $xh = xslt_create();

        //echo "<b>XML:</b>".htmlentities($xml);
        // determine target frames for internal links
        //$pg_frame = $_GET["frame"];
        $wb_path = ilUtil::getWebspaceDir("output") . "/";
        $mode = "fullscreen";
        $params = array('mode' => $mode, 'webspace_path' => $wb_path);
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, $params);
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
    public function displayValidationError($a_error)
    {
        if (is_array($a_error)) {
            $error_str = "<b>Error(s):</b><br>";
            foreach ($a_error as $error) {
                $err_mess = implode(" - ", $error);
                if (!is_int(strpos($err_mess, ":0:"))) {
                    $error_str .= htmlentities($err_mess) . "<br />";
                }
            }
            $this->tpl->setVariable("MESSAGE", $error_str);
        }
    }

    /**
    * Get history table as HTML.
    */
    public function history()
    {
        if (!$this->getEnableEditing()) {
            return;
        }
        
        $this->tpl->addJavaScript("./Services/COPage/js/page_history.js");
        
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
    public function rollbackConfirmation()
    {
        if (!$this->getEnableEditing()) {
            return;
        }
        
        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();
        
        // set confirm/cancel commands
        $this->ctrl->setParameter($this, "rollback_nr", $_GET["old_nr"]);
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "rollback"));
        $c_gui->setHeaderText($this->lng->txt("cont_rollback_confirmation"));
        $c_gui->setCancel($this->lng->txt("cancel"), "history");
        $c_gui->setConfirm($this->lng->txt("confirm"), "rollback");

        $hentry = $this->obj->getHistoryEntry($_GET["old_nr"]);
            
        $c_gui->addItem(
            "id[]",
            $_GET["old_nr"],
            ilDatePresentation::formatDate(new ilDateTime($hentry["hdate"], IL_CAL_DATETIME))
        );
        
        $this->tpl->setContent($c_gui->getHTML());
    }
    
    /**
    * Rollback to a previous version
    */
    public function rollback()
    {
        if (!$this->getEnableEditing()) {
            return;
        }

        $hentry = $this->obj->getHistoryEntry($_GET["rollback_nr"]);

        if ($hentry["content"] != "") {
            $this->obj->setXMLContent($hentry["content"]);
            $this->obj->buildDom(true);
            if ($this->obj->update()) {
                $this->ctrl->redirect($this, "history");
            }
        }
        $this->ctrl->redirect($this, "history");
    }
    
    /**
     * Set screen id component
     *
     * @param
     * @return
     */
    public function setScreenIdComponent()
    {
        $this->help->setScreenIdComponent("copg");
    }

    /**
    * adds tabs to tab gui object
    *
    * @param	object		$tabs_gui		ilTabsGUI object
    */
    public function getTabs($a_activate = "")
    {
        $this->setScreenIdComponent();

        if (!$this->getEnabledTabs()) {
            return;
        }

        // back to upper context
        if (!$this->getEditPreview()) {
            $this->tabs_gui->addTarget("pg", $this->ctrl->getLinkTarget($this, "preview"), array("", "preview"));
        } else {
            $this->tabs_gui->addTarget("cont_preview", $this->ctrl->getLinkTarget($this, "preview"), array("", "preview"));
        }
            
        //$tabs_gui->addTarget("properties", $this->ctrl->getLinkTarget($this, "properties")
        //	, "properties", get_class($this));

        if ($this->use_meta_data) {
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI(
                $this->meta_data_rep_obj,
                $this->meta_data_type,
                $this->meta_data_sub_obj_id
            );
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilobjectmetadatagui"
                );
            }
        }

        $lm_set = new ilSetting("lm");
        
        if ($this->getEnableEditing() && $lm_set->get("page_history", 1)) {
            $this->tabs_gui->addTarget("history", $this->ctrl->getLinkTarget($this, "history"), "history", get_class($this));
            if ($_GET["history_mode"] == "1" || $this->ctrl->getCmd() == "compareVersion") {
                $this->tabs_gui->activateTab("history");
            }
        }

        /*		$tabs = $this->ctrl->getTabs();
                foreach ($tabs as $tab)
                {
                    $tabs_gui->addTarget($tab["lang_var"], $tab["link"]
                        , $tab["cmd"], $tab["class"]);
                }
        */
        if ($this->getEnableEditing() && $this->user->getId() != ANONYMOUS_USER_ID) {
            $this->tabs_gui->addTarget("clipboard", $this->ctrl->getLinkTargetByClass(array(get_class($this), "ilEditClipboardGUI"), "view"), "view", "ilEditClipboardGUI");
        }

        if ($this->getPageConfig()->getEnableScheduledActivation()) {
            $this->tabs_gui->addTarget(
                "cont_activation",
                $this->ctrl->getLinkTarget($this, "editActivation"),
                "editActivation",
                get_class($this)
            );
        }

        if ($this->getEnabledNews()) {
            $this->tabs_gui->addTarget(
                "news",
                $this->ctrl->getLinkTargetByClass("ilnewsitemgui", "editNews"),
                "",
                "ilnewsitemgui"
            );
        }

        // external hook to add tabs
        if (is_array($this->tab_hook)) {
            $func = $this->tab_hook["func"];
            $this->tab_hook["obj"]->$func();
        }
        //$this->tabs_gui->setTabActive("pg");
    }

    /**
    * Compares two revisions of the page
    */
    public function compareVersion()
    {
        if (!$this->getEnableEditing()) {
            return;
        }

        $tpl = new ilTemplate("tpl.page_compare.html", true, true, "Services/COPage");
        $compare = $this->obj->compareVersion((int) $_POST["left"], (int) $_POST["right"]);
        
        // left page
        $lpage = $compare["l_page"];
        $cfg = $this->getPageConfig();
        $cfg->setPreventHTMLUnmasking(true);

        $this->setOutputMode(self::PREVIEW);
        $this->setPageObject($lpage);
        $this->setPresentationTitle($this->getPresentationTitle());
        $this->setCompareMode(true);

        $lhtml = $this->showPage();
        $lhtml = $this->replaceDiffTags($lhtml);
        $lhtml = str_replace("&lt;br /&gt;", "<br />", $lhtml);
        $tpl->setVariable("LEFT", $lhtml);
        
        // right page
        $rpage = $compare["r_page"];
        $this->setPageObject($rpage);
        $this->setPresentationTitle($this->getPresentationTitle());
        $this->setCompareMode(true);
        $this->setOutputMode(self::PREVIEW);

        $rhtml = $this->showPage();
        $rhtml = $this->replaceDiffTags($rhtml);
        $rhtml = str_replace("&lt;br /&gt;", "<br />", $rhtml);
        $tpl->setVariable("RIGHT", $rhtml);
        
        $tpl->setVariable("TXT_NEW", $this->lng->txt("cont_pc_new"));
        $tpl->setVariable("TXT_MODIFIED", $this->lng->txt("cont_pc_modified"));
        $tpl->setVariable("TXT_DELETED", $this->lng->txt("cont_pc_deleted"));

        //var_dump($left);
        //var_dump($right);

        return $tpl->get();
    }
    
    public function replaceDiffTags($a_html)
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
    public function editActivation()
    {
        $atpl = new ilTemplate("tpl.page_activation.php", true, true, "Services/COPage");
        $this->initActivationForm();
        $this->getActivationFormValues();
        $atpl->setVariable("FORM", $this->form->getHTML());
        $atpl->setCurrentBlock("updater");
        $atpl->setVariable("UPDATER_FRAME", $this->exp_frame);
        $atpl->setVariable("EXP_ID_UPDATER", $this->exp_id);
        $atpl->setVariable("HREF_UPDATER", $this->exp_target_script);
        $atpl->parseCurrentBlock();
        $this->tpl->setContent($atpl->get());
    }
    
    /**
    * Init activation form
    */
    public function initActivationForm()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt("cont_page_activation"));
        
        // activation type radio
        $rad = new ilRadioGroupInputGUI($this->lng->txt("cont_activation"), "activation");
        $rad_op1 = new ilRadioOption($this->lng->txt("cont_activated"), "activated");

        $rad->addOption($rad_op1);
        $rad_op2 = new ilRadioOption($this->lng->txt("cont_deactivated"), "deactivated");
        $rad->addOption($rad_op2);
        $rad_op3 = new ilRadioOption($this->lng->txt("cont_scheduled_activation"), "scheduled");
        
        $dt_prop = new ilDateTimeInputGUI($this->lng->txt("cont_start"), "start");
        $dt_prop->setRequired(true);
        $dt_prop->setShowTime(true);
        $rad_op3->addSubItem($dt_prop);
        $dt_prop2 = new ilDateTimeInputGUI($this->lng->txt("cont_end"), "end");
        $dt_prop2->setRequired(true);
        $dt_prop2->setShowTime(true);
        $rad_op3->addSubItem($dt_prop2);
            
        // show activation information
        $cb = new ilCheckboxInputGUI($this->lng->txt("cont_show_activation_info"), "show_activation_info");
        $cb->setInfo($this->lng->txt("cont_show_activation_info_info"));
        $rad_op3->addSubItem($cb);
            
        
        $rad->addOption($rad_op3);

        $this->form->addCommandButton("saveActivation", $this->lng->txt("save"));
        
        $this->form->addItem($rad);
    }
    
    /**
    * Get values for activation form
    */
    public function getActivationFormValues()
    {
        $activation = "deactivated";
        if ($this->getPageObject()->getActive()) {
            $activation = "activated";
        }
        
        $dt_prop = $this->form->getItemByPostVar("start");
        if ($this->getPageObject()->getActivationStart() != "") {
            $activation = "scheduled";
            $dt_prop->setDate(new ilDateTime(
                $this->getPageObject()->getActivationStart(),
                IL_CAL_DATETIME
            ));
        }
        $dt_prop = $this->form->getItemByPostVar("end");
        if ($this->getPageObject()->getActivationEnd() != "") {
            $activation = "scheduled";
            $dt_prop->setDate(new ilDateTime(
                $this->getPageObject()->getActivationEnd(),
                IL_CAL_DATETIME
            ));
        }
        
        $this->form->getItemByPostVar("activation")->setValue($activation);
        $this->form->getItemByPostVar("show_activation_info")->setChecked($this->getPageObject()->getShowActivationInfo());
    }
    
    /**
    * Save Activation
    */
    public function saveActivation()
    {
        $this->initActivationForm();
        
        if ($this->form->checkInput()) {
            $this->getPageObject()->setActive(true);
            $this->getPageObject()->setActivationStart(null);
            $this->getPageObject()->setActivationEnd(null);
            $this->getPageObject()->setShowActivationInfo($_POST["show_activation_info"]);
            if ($_POST["activation"] == "deactivated") {
                $this->getPageObject()->setActive(false);
            }
            if ($_POST["activation"] == "scheduled") {
                $this->getPageObject()->setActive(false);
                $this->getPageObject()->setActivationStart(
                    $this->form->getItemByPostVar("start")->getDate()->get(IL_CAL_DATETIME)
                );
                $this->getPageObject()->setActivationEnd(
                    $this->form->getItemByPostVar("end")->getDate()->get(IL_CAL_DATETIME)
                );
            }
            $this->getPageObject()->update();
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "editActivation");
        }
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
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
    public function getNotesHTML($a_content_object = null, $a_enable_private_notes = true, $a_enable_public_notes = false, $a_enable_notes_deletion = false, $a_callback = null, $export = false)
    {
        include_once("Services/Notes/classes/class.ilNoteGUI.php");

        // scorm 2004 page gui
        if (!$a_content_object) {
            $notes_gui = new ilNoteGUI(
                $this->notes_parent_id,
                (int) $this->obj->getId(),
                "pg"
            );

            $a_enable_private_notes = true;
            $a_enable_public_notes = true;
            $a_enable_notes_deletion = false;
        }
        // wiki page gui, blog posting gui
        else {
            $notes_gui = new ilNoteGUI(
                $a_content_object->getParentId(),
                $a_content_object->getId(),
                $a_content_object->getParentType()
            );
        }

        if ($a_enable_private_notes) {
            $notes_gui->enablePrivateNotes();
        }
        if ($a_enable_public_notes) {
            $notes_gui->enablePublicNotes();
            if ((bool) $a_enable_notes_deletion) {
                $notes_gui->enablePublicNotesDeletion(true);
            }
        }
        if ($export) {
            $notes_gui->setExportMode();
        }
        
        if ($a_callback) {
            $notes_gui->addObserver($a_callback);
        }

        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "ilnotegui") {
            $html = $this->ctrl->forwardCommand($notes_gui);
        } else {
            $html = $notes_gui->getNotesHTML();
        }
        return $html;
    }

    /**
     * Process answer
     */
    public function processAnswer()
    {
        include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
        ilPageQuestionProcessor::saveQuestionAnswer(
            ilUtil::stripSlashes($_POST["type"]),
            ilUtil::stripSlashes($_POST["id"]),
            ilUtil::stripSlashes($_POST["answer"])
        );
    }


    //
    // Initially opened content (e.g. used in learning modules), that
    // is presented in another than the main content area (e.g. a picture in
    // the bottom left area)
    //

    /**
     * Initially opened content
     *
     * @param
     * @return
     */
    public function initialOpenedContent()
    {
        $this->tabs_gui->activateTab("edit");
        $form = $this->initOpenedContentForm();
        
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * Init form for initially opened content
     *
     * @param
     * @return
     */
    public function initOpenedContentForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        
        // link input
        include_once 'Services/Form/classes/class.ilLinkInputGUI.php';
        $ac = new ilLinkInputGUI($this->lng->txt('cont_resource'), 'opened_content');
        $ac->setAllowedLinkTypes(ilLinkInputGUI::INT);
        $ac->setInternalLinkDefault("Media_Media", 0);
        $ac->setInternalLinkFilterTypes(array("PageObject_FAQ", "GlossaryItem", "Media_Media", "Media_FAQ"));
        $val = $this->obj->getInitialOpenedContent();
        if ($val["id"] != "" && $val["type"] != "") {
            $ac->setValue($val["type"] . "|" . $val["id"] . "|" . $val["target"]);
        }
        
        $form->addItem($ac);
        
        $form->addCommandButton("saveInitialOpenedContent", $this->lng->txt("save"));
        $form->addCommandButton("edit", $this->lng->txt("cancel"));
        $form->setTitle($this->lng->txt("cont_initial_attached_content"));
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        return $form;
    }
    
    /**
     * Save initial opened content
     *
     * @param
     * @return
     */
    public function saveInitialOpenedContent()
    {
        $this->obj->saveInitialOpenedContent(
            ilUtil::stripSlashes($_POST["opened_content_ajax_type"]),
            ilUtil::stripSlashes($_POST["opened_content_ajax_id"]),
            ilUtil::stripSlashes($_POST["opened_content_ajax_target"])
        );
        
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
        $this->ctrl->redirect($this, "edit");
    }
    
    ////
    //// Multilinguality functions
    ////
        
    
    /**
     * Switch to language
     */
    public function switchToLanguage()
    {
        $l = ilUtil::stripSlashes($_GET["totransl"]);
        $p = $this->getPageObject();
        if (!ilPageObject::_exists($p->getParentType(), $p->getId(), $l)) {
            $this->confirmPageTranslationCreation();
            return;
        }
        $this->ctrl->setParameter($this, "transl", $_GET["totransl"]);
        $this->ctrl->redirect($this, "edit");
    }
    
    /**
     * Confirm page translation creation
     */
    public function confirmPageTranslationCreation()
    {
        $l = ilUtil::stripSlashes($_GET["totransl"]);
        $this->ctrl->setParameter($this, "totransl", $l);
        $this->lng->loadLanguageModule("meta");
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("cont_page_translation_does_not_exist") . ": " .
            $this->lng->txt("meta_l_" . $l));
        $cgui->setCancel($this->lng->txt("cancel"), "editMasterLanguage");
        $cgui->setConfirm($this->lng->txt("confirm"), "createPageTranslation");
        $this->tpl->setContent($cgui->getHTML());
    }
    
    /**
     * Edit master language
     */
    public function editMasterLanguage()
    {
        $this->ctrl->setParameter($this, "transl", "");
        $this->ctrl->redirect($this, "edit");
    }
    
    /**
     * Create page translation
     */
    public function createPageTranslation()
    {
        $l = ilUtil::stripSlashes($_GET["totransl"]);

        include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
        $p = ilPageObjectFactory::getInstance(
            $this->getPageObject()->getParentType(),
            $this->getPageObject()->getId(),
            0,
            "-"
        );
        $p->copyPageToTranslation($l);
        $this->ctrl->setParameter($this, "transl", $l);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Release page lock
     */
    public function releasePageLock()
    {
        $this->getPageObject()->releasePageLock();
        ilUtil::sendSuccess($this->lng->txt("cont_page_lock_released"), true);
        $this->finishEditing();
    }

    public function finishEditing()
    {
        $this->ctrl->redirect($this, "preview");
    }
    
    protected function isPageContainerToBeRendered()
    {
        return (
            $this->getRenderPageContainer() || ($this->getOutputMode() == self::PREVIEW && $this->getPageConfig()->getUsePageContainer())
        );
    }

    /**
     * Get page perma link
     *
     * @param
     * @return
     */
    public function getPagePermaLink()
    {
        return "";
    }

    /**
     * Add resources to template
     * @param ilGlobalTemplateInterface $tpl
     */
    protected function addResourcesToTemplate(ilGlobalTemplateInterface $tpl)
    {
        $collector = new \ILIAS\COPage\ResourcesCollector($this->getOutputMode(), $this->getPageObject());

        foreach ($collector->getJavascriptFiles() as $js) {
            $tpl->addJavascript($js);
        }

        foreach ($collector->getCssFiles() as $css) {
            $tpl->addCss($css);
        }

        foreach ($collector->getOnloadCode() as $code) {
            $tpl->addOnloadCode($code);
        }
    }

    /**
     * Get additional page actions
     * @return array
     */
    public function getAdditionalPageActions() : array
    {
        return [];
    }

}
