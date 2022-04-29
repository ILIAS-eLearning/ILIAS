<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Config class for page editing
 *
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilPageConfig
{
    // section protection
    public const SEC_PROTECT_NONE = 0;          // page does not support section protection
    public const SEC_PROTECT_EDITABLE = 1;      // current use can edit protected sections
    public const SEC_PROTECT_PROTECTED = 2;     // current use cannot edit protected sections

    protected bool $int_link_def_id_is_ref = false;
    protected ilLanguage $lng;
    protected array $int_link_filter = array("File", "PortfolioPage", "PortfolioTemplatePage");
    protected bool $prevent_rte_usage = false;
    protected bool $use_attached_content = false;
    protected array $pc_defs = array();
    protected array $pc_enabled = array();
    protected bool $enabledinternallinks = false;
    protected bool $enable_keywords = false;
    protected bool $enable_anchors = false;
    protected bool $enablewikilinks = false;
    protected bool $page_toc = false;
    protected bool $activation = false;
    protected bool $scheduled_activation = false;
    protected bool $preventhtmlunmasking = false;
    protected bool $enabledselfassessment = false;
    protected bool $enabledselfassessment_scorm = false;
    protected string $int_link_def_type = "";
    protected int $int_link_def_id = 0;
    protected bool $multi_lang_support = false;
    protected bool $single_page_mode = false;	// currently only used by multi-lang support
    // single page means: only one page per parent_id
    protected bool $disable_default_qfeedback = false;
    protected array $question_html = array();
    protected bool $use_stored_tries = false;
    protected bool $enable_user_links = false;
    protected bool $edit_lock_support = true;
    protected bool $use_page_container = true;
    protected bool $enable_permission_checks = false;
    protected \ilSetting $adve_set;
    /**
     * Key as returned by ilCOPageObjDef->getDefinitions()
     * @var string
     */
    protected string $page_obj_key = "";
    protected bool $link_filter_white_list = false;
    protected string $localization_lang = "";
    protected int $section_protection = self::SEC_PROTECT_NONE;
    protected string $section_protection_info = "";

    final public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        // load pc_defs
        $this->pc_defs = ilCOPagePCDef::getPCDefinitions();
        foreach ($this->pc_defs as $def) {
            $this->setEnablePCType($def["name"], (bool) $def["def_enabled"]);
        }

        $this->adve_set = new ilSetting("adve");
        $def = new ilCOPageObjDef();
        foreach ($def->getDefinitions() as $key => $def) {
            if (strtolower(get_class($this)) == strtolower($def["class_name"] . "Config")) {
                $this->page_obj_key = $key;
            }
        }
        $this->init();
    }
    
    public function init() : void
    {
    }
    
    public function setEnablePCType(string $a_pc_type, bool $a_val) : void
    {
        $this->pc_enabled[$a_pc_type] = $a_val;
    }
    
    public function getEnablePCType(string $a_pc_type) : bool
    {
        return $this->pc_enabled[$a_pc_type];
    }

    public function getEnabledTopPCTypes() : array
    {
        $types = [];
        foreach ($this->pc_defs as $def) {
            if ($def["top_item"] && $this->getEnablePCType($def["name"])) {
                $types[] = $def;
            }
        }
        return $types;
    }

    public function setEnableKeywords(bool $a_val) : void
    {
        $this->enable_keywords = $a_val;
    }
    
    public function getEnableKeywords() : bool
    {
        return $this->enable_keywords;
    }

    public function setEnableAnchors(bool $a_val) : void
    {
        $this->enable_anchors = $a_val;
    }

    public function getEnableAnchors() : bool
    {
        return $this->enable_anchors;
    }

    public function setEnableInternalLinks(bool $a_enabledinternallinks) : void
    {
        $this->enabledinternallinks = $a_enabledinternallinks;
    }

    public function getEnableInternalLinks() : bool
    {
        return $this->enabledinternallinks;
    }

    public function getEnableUserLinks() : bool
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
    
    public function setEnableWikiLinks(bool $a_enablewikilinks) : void
    {
        $this->enablewikilinks = $a_enablewikilinks;
    }

    public function getEnableWikiLinks() : bool
    {
        return $this->enablewikilinks;
    }

    /**
     * Add internal links filter
     * @param	string	internal links filter
     */
    public function addIntLinkFilter(string $a_val) : void
    {
        $lng = $this->lng;
        
        $this->setLocalizationLanguage($lng->getLangKey());
        $this->int_link_filter[] = $a_val;
    }
    
    public function removeIntLinkFilter(string $a_val) : void
    {
        foreach ($this->int_link_filter as $k => $v) {
            if ($v == $a_val) {
                unset($this->int_link_filter[$k]);
            }
        }
    }
    
    public function getIntLinkFilters() : array
    {
        return $this->int_link_filter;
    }

    /**
     * Set internal links filter type list to white list
     */
    public function setIntLinkFilterWhiteList(bool $a_white_list) : void
    {
        $this->link_filter_white_list = $a_white_list;
        if ($a_white_list) {
            $this->int_link_filter = array();
        }
    }

    public function getIntLinkFilterWhiteList() : bool
    {
        return $this->link_filter_white_list;
    }

    public function setPreventRteUsage(bool $a_val) : void
    {
        $this->prevent_rte_usage = $a_val;
    }

    public function getPreventRteUsage() : bool
    {
        return $this->prevent_rte_usage;
    }
    
    /**
     * @param string $a_val lang key
     */
    public function setLocalizationLanguage(string $a_val) : void
    {
        $this->localization_lang = $a_val;
    }
    
    public function getLocalizationLanguage() : string
    {
        return $this->localization_lang;
    }
    
    public function setUseAttachedContent(bool $a_val) : void
    {
        $this->use_attached_content = $a_val;
    }
    
    public function getUseAttachedContent() : bool
    {
        return $this->use_attached_content;
    }
    
    public function setIntLinkHelpDefaultType(string $a_val) : void
    {
        $this->int_link_def_type = $a_val;
    }
    
    public function getIntLinkHelpDefaultType() : string
    {
        return $this->int_link_def_type;
    }
    
    /**
     * Set internal link default id
     * @param int $a_val default object id
     */
    public function setIntLinkHelpDefaultId(
        int $a_val,
        bool $a_is_ref = true
    ) : void {
        $this->int_link_def_id = $a_val;
        $this->int_link_def_id_is_ref = $a_is_ref;
    }
    
    public function getIntLinkHelpDefaultId() : int
    {
        return $this->int_link_def_id;
    }

    public function getIntLinkHelpDefaultIdIsRef() : bool
    {
        return $this->int_link_def_id_is_ref;
    }

    /**
     * Set enabled page activation
     */
    public function setEnableActivation(bool $a_val) : void
    {
        $this->activation = $a_val;
    }
    
    public function getEnableActivation() : bool
    {
        return $this->activation;
    }
    
    public function setEnableScheduledActivation(bool $a_val) : void
    {
        $this->scheduled_activation = $a_val;
    }
    
    public function getEnableScheduledActivation() : bool
    {
        return $this->scheduled_activation;
    }
    
    public function setEnablePageToc(bool $a_val) : void
    {
        $this->page_toc = $a_val;
    }
    
    public function getEnablePageToc() : bool
    {
        return $this->page_toc;
    }
    
    public function setPreventHTMLUnmasking(
        bool $a_preventhtmlunmasking
    ) : void {
        $this->preventhtmlunmasking = $a_preventhtmlunmasking;
    }

    public function getPreventHTMLUnmasking() : bool
    {
        return true;
    }

    public function setEnableSelfAssessment(
        bool $a_enabledselfassessment,
        bool $a_scorm = true
    ) : void {
        $this->setEnablePCType("Question", $a_enabledselfassessment);
        $this->enabledselfassessment = $a_enabledselfassessment;
        $this->enabledselfassessment_scorm = $a_scorm;
    }

    public function getEnableSelfAssessment() : bool
    {
        return $this->enabledselfassessment;
    }

    /**
     * Is self assessment used in SCORM mode?
     */
    public function getEnableSelfAssessmentScorm() : bool
    {
        return $this->enabledselfassessment_scorm;
    }
    
    /**
     * Set disable default question feedback
     */
    public function setDisableDefaultQuestionFeedback(bool $a_val) : void
    {
        $this->disable_default_qfeedback = $a_val;
    }
    
    public function getDisableDefaultQuestionFeedback() : bool
    {
        return $this->disable_default_qfeedback;
    }
    
    public function setMultiLangSupport(bool $a_val) : void
    {
        $this->multi_lang_support = $a_val;
    }
    
    public function getMultiLangSupport() : bool
    {
        return $this->multi_lang_support;
    }
    
    /**
     * Set single page mode
     * @param bool $a_val single page mode (only one page per parent_id)
     */
    public function setSinglePageMode(bool $a_val) : void
    {
        $this->single_page_mode = $a_val;
    }
    
    public function getSinglePageMode() : bool
    {
        return $this->single_page_mode;
    }

    public function setQuestionHTML(array $question_html) : void
    {
        $this->question_html = $question_html;
    }

    public function getQuestionHTML() : array
    {
        return $this->question_html;
    }
    
    /**
     * Set use stored answers/tries
     * @param bool $a_val use stored number of tries and given (correct) answers
     */
    public function setUseStoredQuestionTries(bool $a_val) : void
    {
        $this->use_stored_tries = $a_val;
    }
    
    public function getUseStoredQuestionTries() : bool
    {
        return $this->use_stored_tries;
    }

    public function setEnablePermissionChecks(bool $a_val) : void
    {
        $this->enable_permission_checks = $a_val;
    }

    public function getEnablePermissionChecks() : bool
    {
        return $this->enable_permission_checks;
    }

    /**
     * @param $a_val  bool set edit lock support for pages
     */
    public function setEditLockSupport(bool $a_val) : void
    {
        $this->edit_lock_support = $a_val;
    }

    public function getEditLockSupport() : bool
    {
        return $this->edit_lock_support;
    }

    /**
     * Set if page container css class should be used
     */
    public function setUsePageContainer(bool $a_val) : void
    {
        $this->use_page_container = $a_val;
    }

    public function getUsePageContainer() : bool
    {
        return $this->use_page_container;
    }

    public function setSectionProtection(int $a_val) : void
    {
        $this->section_protection = $a_val;
    }

    public function getSectionProtection() : int
    {
        return $this->section_protection;
    }

    public function setSectionProtectionInfo(string $a_val) : void
    {
        $this->section_protection_info = $a_val;
    }

    public function getSectionProtectionInfo() : string
    {
        return $this->section_protection_info;
    }
}
