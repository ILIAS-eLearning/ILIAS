<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @extends ilObjectGUI
* @package ilias-core
*/

class ilSearchSettings
{
    public const LIKE_SEARCH = 0;
    public const LUCENE_SEARCH = 2;
    
    public const OPERATOR_AND = 1;
    public const OPERATOR_OR = 2;
    
    protected static ?ilSearchSettings $instance = null;
    
    protected int $default_operator = self::OPERATOR_AND;
    protected int $fragmentSize = 30;
    protected int $fragmentCount = 3;
    protected int $numSubitems = 5;
    protected bool $showRelevance = true;
    protected ?ilDateTime $last_index_date = null;
    protected bool $lucene_item_filter_enabled = false;
    protected array $lucene_item_filter = array();
    protected bool $lucene_offline_filter = true;
    protected int $auto_complete_length = 10;
    protected bool $show_inactiv_user = true;
    protected bool $show_limited_user = true;

    protected bool $lucene = false;
    protected bool $hide_adv_search = false;
    protected bool $lucene_mime_filter_enabled = false;
    protected array $lucene_mime_filter = array();
    protected bool $showSubRelevance = false;
    protected bool $prefix_wildcard = false;
    protected bool $user_search = false;
    protected bool $date_filter = false;

    protected ?ILIAS $ilias = null;
    protected ilSetting $setting;
    private int $max_hits = 10;

    public function __construct()
    {
        global $DIC;

        $this->ilias = $DIC['ilias'];
        $this->setting = $DIC->settings();
        $this->__read();
    }
    
    public static function getInstance() : ilSearchSettings
    {
        if (self::$instance instanceof ilSearchSettings) {
            return self::$instance;
        }
        return self::$instance = new ilSearchSettings();
    }
    
    /**
     * Get lucene item filter definitions
     * @todo This has to be defined in module.xml
     */
    public static function getLuceneItemFilterDefinitions() : array
    {
        return array(
            'crs' => array('filter' => 'type:crs','trans' => 'objs_crs'),
            'grp' => array('filter' => 'type:grp', 'trans' => 'objs_grp'),
            'lms' => array('filter' => 'type:lm OR type:htlm','trans' => 'obj_lrss'),
            'glo' => array('filter' => 'type:glo','trans' => 'objs_glo'),
            'mep' => array('filter' => 'type:mep', 'trans' => 'objs_mep'),
            'tst' => array('filter' => 'type:tst OR type:svy OR type:qpl OR type:spl','trans' => 'search_tst_svy'),
            'frm' => array('filter' => 'type:frm','trans' => 'objs_frm'),
            'exc' => array('filter' => 'type:exc','trans' => 'objs_exc'),
            'file' => array('filter' => 'type:file','trans' => 'objs_file'),
            'mcst' => array('filter' => 'type:mcst','trans' => 'objs_mcst'),
            'wiki' => array('filter' => 'type:wiki','trans' => 'objs_wiki'),
            'copa' => array('filter' => 'type:copa','trans' => 'objs_copa'),
        );
    }
    
    public static function getLuceneMimeFilterDefinitions() : array
    {
        return array(
            'pdf' => array('filter' => 'mimeType:pdf','trans' => 'search_mime_pdf'),
            'word' => array('filter' => 'mimeType:word','trans' => 'search_mime_word'),
            'excel' => array('filter' => 'mimeType:excel','trans' => 'search_mime_excel'),
            'powerpoint' => array('filter' => 'mimeType:powerpoint','trans' => 'search_mime_powerpoint'),
            'image' => array('filter' => 'mimeType:image','trans' => 'search_mime_image')
        );
    }
    
    /**
     * Get lucene item filter definitions
     * @todo This has to be defined in module.xml
     */
    public function getEnabledLuceneItemFilterDefinitions() : array
    {
        if (!$this->isLuceneItemFilterEnabled()) {
            return array();
        }
        
        $filter = $this->getLuceneItemFilter();
        $enabled = array();
        foreach (self::getLuceneItemFilterDefinitions() as $obj => $def) {
            if (isset($filter[$obj]) and $filter[$obj]) {
                $enabled[$obj] = $def;
            }
        }
        return $enabled;
    }
    
    // begin-patch mime_filter
    public function getEnabledLuceneMimeFilterDefinitions() : array
    {
        if (!$this->isLuceneItemFilterEnabled()) {
            return array();
        }
        
        $filter = $this->getLuceneMimeFilter();
        $enabled = array();
        foreach (self::getLuceneMimeFilterDefinitions() as $mime => $def) {
            if (isset($filter[$mime]) and $filter[$mime]) {
                $enabled[$mime] = $def;
            }
        }
        return $enabled;
    }
    
    public function enablePrefixWildcardQuery(bool $a_stat) : void
    {
        $this->prefix_wildcard = $a_stat;
    }
    
    public function isPrefixWildcardQueryEnabled() : bool
    {
        return $this->prefix_wildcard;
    }
    
    /**
    * Read the ref_id of Search Settings object. normally used for rbacsystem->checkAccess()
    */
    public static function _getSearchSettingRefId() : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        static $seas_ref_id = 0;

        if ($seas_ref_id) {
            return $seas_ref_id;
        }
        $query = "SELECT object_reference.ref_id as ref_id FROM object_reference,tree,object_data " .
            "WHERE tree.parent = " . $ilDB->quote(SYSTEM_FOLDER_ID, 'integer') . " " .
            "AND object_data.type = 'seas' " .
            "AND object_reference.ref_id = tree.child " .
            "AND object_reference.obj_id = object_data.obj_id";
            
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        
        return $seas_ref_id = (int) $row->ref_id;
    }

    public function enabledLucene() : bool
    {
        return $this->lucene;
    }
    public function enableLucene(bool $a_status) : void
    {
        $this->lucene = $a_status;
    }

    public function getMaxHits() : int
    {
        return $this->max_hits;
    }
    public function setMaxHits(int $a_max_hits) : void
    {
        $this->max_hits = $a_max_hits;
    }
    
    public function getDefaultOperator() : int
    {
        return $this->default_operator;
    }
    
    public function setDefaultOperator(int $a_op) : void
    {
        $this->default_operator = $a_op;
    }
    
    public function setFragmentSize(int $a_size) : void
    {
        $this->fragmentSize = $a_size;
    }
    
    public function getFragmentSize() : int
    {
        return $this->fragmentSize;
    }
    
    public function setFragmentCount(int $a_count) : void
    {
        $this->fragmentCount = $a_count;
    }

    public function getHideAdvancedSearch() : bool
    {
        return $this->hide_adv_search;
    }
    public function setHideAdvancedSearch(bool $a_status) : void
    {
        $this->hide_adv_search = $a_status;
    }
    public function getAutoCompleteLength() : int
    {
        return $this->auto_complete_length;
    }
    public function setAutoCompleteLength(int $auto_complete_length) : void
    {
        $this->auto_complete_length = $auto_complete_length;
    }

    public function getFragmentCount() : int
    {
        return $this->fragmentCount;
    }
    
    public function setMaxSubitems(int $a_max) : void
    {
        $this->numSubitems = $a_max;
    }
    
    public function getMaxSubitems() : int
    {
        return $this->numSubitems;
    }
    
    public function isRelevanceVisible() : bool
    {
        return $this->showRelevance;
    }
    
    public function showRelevance(bool $a_status) : void
    {
        $this->showRelevance = $a_status;
    }
    
    public function getLastIndexTime() : ilDateTime
    {
        return $this->last_index_date instanceof ilDateTime  ?
            $this->last_index_date :
            new ilDateTime('2009-01-01 12:00:00', IL_CAL_DATETIME);
    }
    
    public function enableLuceneItemFilter(bool $a_status) : void
    {
        $this->lucene_item_filter_enabled = $a_status;
    }
    
    public function isLuceneItemFilterEnabled() : bool
    {
        return $this->lucene_item_filter_enabled;
    }

    public function getLuceneItemFilter() : array
    {
        return $this->lucene_item_filter;
    }
    
    
    public function setLuceneItemFilter(array $a_filter) : void
    {
        $this->lucene_item_filter = $a_filter;
    }
    
    public function enableLuceneOfflineFilter(bool $a_stat) : void
    {
        $this->lucene_offline_filter = $a_stat;
    }
    
    public function isLuceneOfflineFilterEnabled() : bool
    {
        return $this->lucene_offline_filter;
    }
    
    public function showSubRelevance(bool $a_stat) : void
    {
        $this->showSubRelevance = $a_stat;
    }
    
    public function isSubRelevanceVisible() : bool
    {
        return $this->showSubRelevance;
    }
    
    
    public function setLuceneMimeFilter(array $a_filter) : void
    {
        $this->lucene_mime_filter = $a_filter;
    }
    
    public function getLuceneMimeFilter() : array
    {
        return $this->lucene_mime_filter;
    }

    /**
     * Check if lucene mime filter is enabled
     */
    public function isLuceneMimeFilterEnabled() : bool
    {
        return $this->lucene_mime_filter_enabled;
    }
    
    public function enableLuceneMimeFilter(bool $a_stat) : void
    {
        $this->lucene_mime_filter_enabled = $a_stat;
    }
    
    
    public function setLastIndexTime(ilDateTime $time) : void
    {
        $this->last_index_date = $time;
    }
    
    /**
     * Check if user search is enabled
     */
    public function isLuceneUserSearchEnabled() : bool
    {
        return $this->user_search;
    }
    
    /**
     * Enable lucene user search
     * @param bool $a_status
     */
    public function enableLuceneUserSearch(bool $a_status) : void
    {
        $this->user_search = $a_status;
    }

    /**
     * show inactive user in user search
     */
    public function showInactiveUser(bool $a_visible) : void
    {
        $this->show_inactiv_user = $a_visible;
    }

    /**
     * are inactive user visible in user search
     * @return bool
     */
    public function isInactiveUserVisible() : bool
    {
        return $this->show_inactiv_user;
    }

    /**
     * show user with limited access in user search
     */
    public function showLimitedUser(bool $a_visible) : void
    {
        $this->show_limited_user = $a_visible;
    }


    public function isLimitedUserVisible() : bool
    {
        return $this->show_limited_user;
    }
    
    public function isDateFilterEnabled() : bool
    {
        return $this->date_filter;
    }
    
    public function enableDateFilter(bool $a_filter) : void
    {
        $this->date_filter = $a_filter;
    }
    
    public function update() : void
    {
        $this->setting->set('search_max_hits', (string) $this->getMaxHits());
        $this->setting->set('search_lucene', (string) $this->enabledLucene());
        
        $this->setting->set('lucene_default_operator', (string) $this->getDefaultOperator());
        $this->setting->set('lucene_fragment_size', (string) $this->getFragmentSize());
        $this->setting->set('lucene_fragment_count', (string) $this->getFragmentCount());
        $this->setting->set('lucene_max_subitems', (string) $this->getMaxSubitems());
        $this->setting->set('lucene_show_relevance', (string) $this->isRelevanceVisible());
        $this->setting->set('lucene_last_index_time', (string) $this->getLastIndexTime()->get(IL_CAL_UNIX));
        $this->setting->set('hide_adv_search', (string) $this->getHideAdvancedSearch());
        $this->setting->set('auto_complete_length', (string) $this->getAutoCompleteLength());
        $this->setting->set('lucene_item_filter_enabled', (string) $this->isLuceneItemFilterEnabled());
        $this->setting->set('lucene_item_filter', serialize($this->getLuceneItemFilter()));
        $this->setting->set('lucene_offline_filter', (string) $this->isLuceneOfflineFilterEnabled());
        $this->setting->set('lucene_mime_filter', serialize($this->getLuceneMimeFilter()));
        $this->setting->set('lucene_sub_relevance', (string) $this->isSubRelevanceVisible());
        $this->setting->set('lucene_mime_filter_enabled', (string) $this->isLuceneMimeFilterEnabled());
        $this->setting->set('lucene_prefix_wildcard', (string) $this->isPrefixWildcardQueryEnabled());
        $this->setting->set('lucene_user_search', (string) $this->isLuceneUserSearchEnabled());
        $this->setting->set('search_show_inactiv_user', (string) $this->isInactiveUserVisible());
        $this->setting->set('search_show_limited_user', (string) $this->isLimitedUserVisible());
        $this->setting->set('search_date_filter', (string) $this->isDateFilterEnabled());
    }

    // PRIVATE
    protected function __read()
    {
        $this->setMaxHits((int) $this->setting->get('search_max_hits', '10'));
        $this->enableLucene((bool) $this->setting->get('search_lucene', '0'));
        $this->setDefaultOperator((int) $this->setting->get('lucene_default_operator', (string) self::OPERATOR_AND));
        $this->setFragmentSize((int) $this->setting->get('lucene_fragment_size', "50"));
        $this->setFragmentCount((int) $this->setting->get('lucene_fragment_count', "3"));
        $this->setMaxSubitems((int) $this->setting->get('lucene_max_subitems', "5"));
        $this->showRelevance((bool) $this->setting->get('lucene_show_relevance', "1"));
        if ($time = $this->setting->get('lucene_last_index_time', '0')) {
            $this->setLastIndexTime(new ilDateTime($time, IL_CAL_UNIX));
        } else {
            $this->setLastIndexTime(null);
        }
        $this->setHideAdvancedSearch((bool) $this->setting->get('hide_adv_search', '0'));
        $this->setAutoCompleteLength((int) $this->setting->get('auto_complete_length', (string) $this->getAutoCompleteLength()));
        $this->enableLuceneItemFilter((bool) $this->setting->get('lucene_item_filter_enabled', (string) $this->isLuceneItemFilterEnabled()));
        $filter = (string) $this->setting->get('lucene_item_filter', serialize($this->getLuceneItemFilter()));
        $this->setLuceneItemFilter(unserialize($filter));
        $this->enableLuceneOfflineFilter((bool) $this->setting->get('lucene_offline_filter', (string) $this->isLuceneOfflineFilterEnabled()));
        $this->enableLuceneMimeFilter((bool) $this->setting->get('lucene_mime_filter_enabled', (string) $this->lucene_item_filter_enabled));
        $filter = (string) $this->setting->get('lucene_mime_filter', serialize($this->getLuceneMimeFilter()));
        $this->setLuceneMimeFilter(unserialize($filter));
        $this->showSubRelevance((bool) $this->setting->get('lucene_sub_relevance', (string) $this->showSubRelevance));
        $this->enablePrefixWildcardQuery((bool) $this->setting->get('lucene_prefix_wildcard', (string) $this->prefix_wildcard));
        $this->enableLuceneUserSearch((bool) $this->setting->get('lucene_user_search', (string) $this->user_search));
        $this->showInactiveUser((bool) $this->setting->get('search_show_inactiv_user', (string) $this->show_inactiv_user));
        $this->showLimitedUser((bool) $this->setting->get('search_show_limited_user', (string) $this->show_limited_user));
        $this->enableDateFilter((bool) $this->setting->get('search_date_filter', (string) $this->date_filter));
    }
}
