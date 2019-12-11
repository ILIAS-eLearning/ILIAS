<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Config class for page editing
 *
 * @author Alex Killing <alex.killing.gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
abstract class ilPageConfig
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $int_link_filter = array("File", "PortfolioPage", "PortfolioTemplatePage");
    protected $prevent_rte_usage = false;
    protected $use_attached_content = false;
    protected $pc_defs = array();
    protected $pc_enabled = array();
    protected $enabledinternallinks = false;
    protected $enable_keywords = false;
    protected $enable_anchors = false;
    protected $enablewikilinks = false;
    protected $page_toc = false;
    protected $activation = false;
    protected $scheduled_activation = false;
    protected $preventhtmlunmasking = false;
    protected $enabledselfassessment = false;
    protected $enabledselfassessment_scorm = false;
    protected $int_link_def_type = "";
    protected $int_link_def_id = 0;
    protected $multi_lang_support = false;
    protected $single_page_mode = false;	// currently only used by multi-lang support
    // single page means: only one page per parent_id
    protected $disable_default_qfeedback = false;
    protected $question_html = array();
    protected $use_stored_tries = false;
    protected $enable_user_links = false;

    protected $edit_lock_support = true;

    /**
     * @var bool
     */
    protected $enable_permission_checks = false;
    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    final public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        // load pc_defs
        include_once("./Services/COPage/classes/class.ilCOPagePCDef.php");
        $this->pc_defs = ilCOPagePCDef::getPCDefinitions();
        foreach ($this->pc_defs as $def) {
            $this->setEnablePCType($def["name"], (bool) $def["def_enabled"]);
        }
        
        $this->init();
    }
    
    /**
     * Init
     *
     * @param
     * @return
     */
    public function init()
    {
    }
    
    
    /**
     * Set enable pc type
     *
     * @param boolean $a_val enable pc type true/false
     */
    public function setEnablePCType($a_pc_type, $a_val)
    {
        $this->pc_enabled[$a_pc_type] = $a_val;
    }
    
    /**
     * Get enable pc type
     *
     * @return boolean enable pc type true/false
     */
    public function getEnablePCType($a_pc_type)
    {
        return $this->pc_enabled[$a_pc_type];
    }

    /**
     * Set enable keywords handling
     *
     * @param	boolean	keywords handling
     */
    public function setEnableKeywords($a_val)
    {
        $this->enable_keywords = $a_val;
    }
    
    /**
     * Get enable keywords handling
     *
     * @return	boolean	keywords handling
     */
    public function getEnableKeywords()
    {
        return $this->enable_keywords;
    }

    /**
     * Set enable anchors
     *
     * @param	boolean	anchors
     */
    public function setEnableAnchors($a_val)
    {
        $this->enable_anchors = $a_val;
    }
    
    /**
     * Get enable anchors
     *
     * @return	boolean	anchors
     */
    public function getEnableAnchors()
    {
        return $this->enable_anchors;
    }

    /**
     * Set Enable internal links.
     *
     * @param	boolean	$a_enabledinternallinks	Enable internal links
     */
    public function setEnableInternalLinks($a_enabledinternallinks)
    {
        $this->enabledinternallinks = $a_enabledinternallinks;
    }

    /**
     * Get Enable internal links.
     *
     * @return	boolean	Enable internal links
     */
    public function getEnableInternalLinks()
    {
        return $this->enabledinternallinks;
    }

    /**
     * Get enable user links
     *
     * @return boolean enable user links
     */
    public function getEnableUserLinks()
    {
        if (!$this->getEnableInternalLinks()) {
            return false;
        }
        if ($this->getIntLinkFilterWhiteList() && in_array("User", $this->int_link_filter)) {
            return true;
        }
        if (!$this->getIntLinkFilterWhiteList() && !in_array("User", $this->int_link_filter)) {
            return true;
        }

        return false;
    }
    
    /**
     * Set Enable Wiki Links.
     *
     * @param	boolean	$a_enablewikilinks	Enable Wiki Links
     */
    public function setEnableWikiLinks($a_enablewikilinks)
    {
        $this->enablewikilinks = $a_enablewikilinks;
    }

    /**
     * Get Enable Wiki Links.
     *
     * @return	boolean	Enable Wiki Links
     */
    public function getEnableWikiLinks()
    {
        return $this->enablewikilinks;
    }

    /**
     * Add internal links filter
     *
     * @param	string	internal links filter
     */
    public function addIntLinkFilter($a_val)
    {
        $lng = $this->lng;
        
        $this->setLocalizationLanguage($lng->getLangKey());
        if (is_array($a_val)) {
            $this->int_link_filter =
                array_merge($a_val, $this->int_link_filter);
        } else {
            $this->int_link_filter[] = $a_val;
        }
    }
    
    /**
     * Remove int link filter
     *
     * @param string $a_val internal link filter
     */
    public function removeIntLinkFilter($a_val)
    {
        foreach ($this->int_link_filter as $k => $v) {
            if ($v == $a_val) {
                unset($this->int_link_filter[$k]);
            }
        }
    }
    
    
    /**
     * Get internal links filter
     *
     * @return	string	internal links filter
     */
    public function getIntLinkFilters()
    {
        return $this->int_link_filter;
    }

    /**
     * Set internal links filter type list to white list
     *
     * @param	boolean white list
     */
    public function setIntLinkFilterWhiteList($a_white_list)
    {
        $this->link_filter_white_list = $a_white_list;
        if ($a_white_list) {
            $this->int_link_filter = array();
        }
    }

    /**
     * Get internal links filter type list to white list
     *
     * @return	boolean white list
     */
    public function getIntLinkFilterWhiteList()
    {
        return $this->link_filter_white_list;
    }

    /**
     * Set prevent rte usage
     *
     * @param	boolean	prevent rte usage
     */
    public function setPreventRteUsage($a_val)
    {
        $this->prevent_rte_usage = $a_val;
    }

    /**
     * Get prevent rte usage
     *
     * @return	boolean	prevent rte usage
     */
    public function getPreventRteUsage()
    {
        return $this->prevent_rte_usage;
    }
    
    /**
     * Set localizazion language
     *
     * @param string $a_val lang key
     */
    public function setLocalizationLanguage($a_val)
    {
        $this->localization_lang = $a_val;
    }
    
    /**
     * Get localizazion language
     *
     * @return string lang key
     */
    public function getLocalizationLanguage()
    {
        return $this->localization_lang;
    }
    
    /**
     * Set use attached content
     *
     * @param string $a_val use initial attached content
     */
    public function setUseAttachedContent($a_val)
    {
        $this->use_attached_content = $a_val;
    }
    
    /**
     * Get use attached content
     *
     * @return string use initial attached content
     */
    public function getUseAttachedContent()
    {
        return $this->use_attached_content;
    }
    
    /**
     * Set internal link default type
     *
     * @param string $a_val type
     */
    public function setIntLinkHelpDefaultType($a_val)
    {
        $this->int_link_def_type = $a_val;
    }
    
    /**
     * Get internal link default type
     *
     * @return string type
     */
    public function getIntLinkHelpDefaultType()
    {
        return $this->int_link_def_type;
    }
    
    /**
     * Set internal link default id
     *
     * @param int $a_val default object if
     */
    public function setIntLinkHelpDefaultId($a_val, $a_is_ref = true)
    {
        $this->int_link_def_id = $a_val;
        $this->int_link_def_id_is_ref = $a_is_ref;
    }
    
    /**
     * Get internal link default id
     *
     * @return int default object if
     */
    public function getIntLinkHelpDefaultId()
    {
        return $this->int_link_def_id;
    }

    /**
     * Get internal link default id
     *
     * @return int default object if
     */
    public function getIntLinkHelpDefaultIdIsRef()
    {
        return $this->int_link_def_id_is_ref;
    }

    /**
     * Set enabled actication
     *
     * @param bool $a_val page activation enabled?
     */
    public function setEnableActivation($a_val)
    {
        $this->activation = $a_val;
    }
    
    /**
     * Get enabled actication
     *
     * @return bool page activation enabled?
     */
    public function getEnableActivation()
    {
        return $this->activation;
    }
    
    /**
     * Set enable scheduled page activation
     *
     * @param bool $a_val scheduled activated enabled?
     */
    public function setEnableScheduledActivation($a_val)
    {
        $this->scheduled_activation = $a_val;
    }
    
    /**
     * Get enable scheduled page activation
     *
     * @return bool scheduled activated enabled?
     */
    public function getEnableScheduledActivation()
    {
        return $this->scheduled_activation;
    }
    
    /**
     * Set enable page toc
     *
     * @param bool $a_val enable page toc?
     */
    public function setEnablePageToc($a_val)
    {
        $this->page_toc = $a_val;
    }
    
    /**
     * Get enable page toc
     *
     * @return bool enable page toc?
     */
    public function getEnablePageToc()
    {
        return $this->page_toc;
    }
    
    /**
     * Set Prevent HTML Unmasking (true/false).
     *
     * @param	boolean	$a_preventhtmlunmasking	Prevent HTML Unmasking (true/false)
     */
    public function setPreventHTMLUnmasking($a_preventhtmlunmasking)
    {
        $this->preventhtmlunmasking = $a_preventhtmlunmasking;
    }

    /**
    * Get Prevent HTML Unmasking (true/false).
    *
    * @return	boolean	Prevent HTML Unmasking (true/false)
    */
    public function getPreventHTMLUnmasking()
    {
        return $this->preventhtmlunmasking;
    }

    /**
     * Set Enable Self Assessment Questions.
     *
     * @param	boolean	$a_enabledselfassessment	Enable Self Assessment Questions
     */
    public function setEnableSelfAssessment($a_enabledselfassessment, $a_scorm = true)
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
    public function getEnableSelfAssessment()
    {
        return $this->enabledselfassessment;
    }

    /**
     * Is self assessment used in SCORM mode?
     *
     * @return	boolean	Enable Self Assessment Questions
     */
    public function getEnableSelfAssessmentScorm()
    {
        return $this->enabledselfassessment_scorm;
    }
    
    /**
     * Set disable default question feedback
     *
     * @param bool $a_val disable feedback
     */
    public function setDisableDefaultQuestionFeedback($a_val)
    {
        $this->disable_default_qfeedback = $a_val;
    }
    
    /**
     * Get disable default question feedback
     *
     * @return bool disable feedback
     */
    public function getDisableDefaultQuestionFeedback()
    {
        return $this->disable_default_qfeedback;
    }
    
    /**
     * Set multi language support
     *
     * @param bool $a_val general multi language support?
     */
    public function setMultiLangSupport($a_val)
    {
        $this->multi_lang_support = $a_val;
    }
    
    /**
     * Get multi language support
     *
     * @return bool general multi language support?
     */
    public function getMultiLangSupport()
    {
        return $this->multi_lang_support;
    }
    
    /**
     * Set single page mode
     *
     * @param bool $a_val single page mode (only one page per parent_id)
     */
    public function setSinglePageMode($a_val)
    {
        $this->single_page_mode = $a_val;
    }
    
    /**
     * Get single page mode
     *
     * @return bool single page mode (only one page per parent_id)
     */
    public function getSinglePageMode()
    {
        return $this->single_page_mode;
    }

    public function setQuestionHTML($question_html)
    {
        $this->question_html = $question_html;
    }

    public function getQuestionHTML()
    {
        return $this->question_html;
    }
    
    /**
     * Set use stored answers/tries
     *
     * @param bool $a_val use stored number of tries and given (correct) answers
     */
    public function setUseStoredQuestionTries($a_val)
    {
        $this->use_stored_tries = $a_val;
    }
    
    /**
     * Get use stored answers/tries
     *
     * @return bool use stored number of tries and given (correct) answers
     */
    public function getUseStoredQuestionTries()
    {
        return $this->use_stored_tries;
    }

    /**
     * Set enable permission checks
     *
     * @param bool $a_val enable permission checks
     */
    public function setEnablePermissionChecks($a_val)
    {
        $this->enable_permission_checks = $a_val;
    }

    /**
     * Get enable permission checks
     *
     * @return bool enable permission checks
     */
    public function getEnablePermissionChecks()
    {
        return $this->enable_permission_checks;
    }

    /**
     * @param $a_val  bool set edit lock support for blogs
     */
    public function setEditLockSupport($a_val)
    {
        $this->edit_lock_support = $a_val;
    }

    /**
     * @return bool get edit lock support for blogs
     */
    public function getEditLockSupport()
    {
        return $this->edit_lock_support;
    }
}
