<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjSearchSettingsGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @extends ilObjectGUI
* @package ilias-core
*/

class ilSearchSettings
{
    const LIKE_SEARCH = 0;
    const INDEX_SEARCH = 1;
    const LUCENE_SEARCH = 2;
    
    const OPERATOR_AND = 1;
    const OPERATOR_OR = 2;
    
    protected static $instance = null;
    
    protected $default_operator = self::OPERATOR_AND;
    protected $fragmentSize = 30;
    protected $fragmentCount = 3;
    protected $numSubitems = 5;
    protected $showRelevance = true;
    protected $last_index_date = null;
    protected $lucene_item_filter_enabled = false;
    protected $lucene_item_filter = array();
    protected $lucene_offline_filter = true;
    protected $auto_complete_length = 10;
    protected $show_inactiv_user = true;
    protected $show_limited_user = true;
    
    protected $lucene_mime_filter_enabled = false;
    protected $lucene_mime_filter = array();
    protected $showSubRelevance = false;
    protected $prefix_wildcard = false;
    
    protected $user_search = false;
    
    protected $date_filter = false;

    public $ilias = null;
    public $max_hits = null;
    public $index = null;

    public function __construct()
    {
        global $DIC;

        $ilias = $DIC['ilias'];

        $this->ilias = $ilias;
        $this->__read();
    }
    
    /**
     *
     *
     * @static
     * @return ilSearchSettings
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            return self::$instance = new ilSearchSettings();
        }
        return self::$instance;
    }
    
    /**
     * Get lucene item filter definitions
     * @return
     * @todo This has to be defined in module.xml
     */
    public static function getLuceneItemFilterDefinitions()
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
    
    public static function getLuceneMimeFilterDefinitions()
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
     * @return
     * @todo This has to be defined in module.xml
     */
    public function getEnabledLuceneItemFilterDefinitions()
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
    public function getEnabledLuceneMimeFilterDefinitions()
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
    
    public function enablePrefixWildcardQuery($a_stat)
    {
        $this->prefix_wildcard = $a_stat;
    }
    
    public function isPrefixWildcardQueryEnabled()
    {
        return $this->prefix_wildcard;
    }
    
    // end-patch mime_filter

    /**
    * Read the ref_id of Search Settings object. normally used for rbacsystem->checkAccess()
    * @return int ref_id
    * @access	public
    */
    public static function _getSearchSettingRefId()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

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
        
        return $seas_ref_id = $row->ref_id;
    }

    public function enabledIndex()
    {
        return $this->index ? true : false;
    }
    public function enableIndex($a_status)
    {
        $this->index = $a_status;
    }
    public function enabledLucene()
    {
        return $this->lucene ? true : false;
    }
    public function enableLucene($a_status)
    {
        $this->lucene = $a_status ? true : false;
    }

    public function getMaxHits()
    {
        return $this->max_hits;
    }
    public function setMaxHits($a_max_hits)
    {
        $this->max_hits = $a_max_hits;
    }
    
    public function getDefaultOperator()
    {
        return $this->default_operator;
    }
    
    public function setDefaultOperator($a_op)
    {
        $this->default_operator = $a_op;
    }
    
    public function setFragmentSize($a_size)
    {
        $this->fragmentSize = $a_size;
    }
    
    public function getFragmentSize()
    {
        return $this->fragmentSize;
    }
    
    public function setFragmentCount($a_count)
    {
        $this->fragmentCount = $a_count;
    }

    public function getHideAdvancedSearch()
    {
        return $this->hide_adv_search ? true : false;
    }
    public function setHideAdvancedSearch($a_status)
    {
        $this->hide_adv_search = $a_status;
    }
    public function getAutoCompleteLength()
    {
        return $this->auto_complete_length;
    }
    public function setAutoCompleteLength($auto_complete_length)
    {
        $this->auto_complete_length = $auto_complete_length;
    }

    public function getFragmentCount()
    {
        return $this->fragmentCount;
    }
    
    public function setMaxSubitems($a_max)
    {
        $this->numSubitems = $a_max;
    }
    
    public function getMaxSubitems()
    {
        return $this->numSubitems;
    }
    
    public function isRelevanceVisible()
    {
        return $this->showRelevance;
    }
    
    public function showRelevance($a_status)
    {
        $this->showRelevance = (bool) $a_status;
    }
    
    public function getLastIndexTime()
    {
        return $this->last_index_date instanceof ilDateTime  ?
            $this->last_index_date :
            new ilDateTime('2009-01-01 12:00:00', IL_CAL_DATETIME);
    }
    
    public function enableLuceneItemFilter($a_status)
    {
        $this->lucene_item_filter_enabled = $a_status;
    }
    
    public function isLuceneItemFilterEnabled()
    {
        return $this->lucene_item_filter_enabled;
    }

    public function getLuceneItemFilter()
    {
        return $this->lucene_item_filter;
    }
    
    
    public function setLuceneItemFilter($a_filter)
    {
        $this->lucene_item_filter = $a_filter;
    }
    
    public function enableLuceneOfflineFilter($a_stat)
    {
        $this->lucene_offline_filter = $a_stat;
    }
    
    public function isLuceneOfflineFilterEnabled()
    {
        return $this->lucene_offline_filter;
    }
    
    public function showSubRelevance($a_stat)
    {
        $this->showSubRelevance = $a_stat;
    }
    
    public function isSubRelevanceVisible()
    {
        return $this->showSubRelevance;
    }
    
    
    public function setLuceneMimeFilter($a_filter)
    {
        $this->lucene_mime_filter = $a_filter;
    }
    
    public function getLuceneMimeFilter()
    {
        return $this->lucene_mime_filter;
    }

    /**
     * Check if lucene mime filter is enabled
     */
    public function isLuceneMimeFilterEnabled()
    {
        return $this->lucene_mime_filter_enabled;
    }
    
    /**
     * Enable lucene mime filter
     * @param type $a_stat
     */
    public function enableLuceneMimeFilter($a_stat)
    {
        $this->lucene_mime_filter_enabled = $a_stat;
    }
    
    
    /**
     * @param object instance of ilDateTime
     */
    public function setLastIndexTime($time)
    {
        $this->last_index_date = $time;
    }
    
    /**
     * Check if user search is enabled
     * @return type
     */
    public function isLuceneUserSearchEnabled()
    {
        return $this->user_search;
    }
    
    /**
     * Enable lucene user search
     * @param type $a_status
     */
    public function enableLuceneUserSearch($a_status)
    {
        $this->user_search = $a_status;
    }

    /**
     * show inactive user in user search
     *
     * @param bool $a_visible
     */
    public function showInactiveUser($a_visible)
    {
        $this->show_inactiv_user = (bool) $a_visible;
    }

    /**
     * are inactive user visible in user search
     *
     * @return bool
     */
    public function isInactiveUserVisible()
    {
        return $this->show_inactiv_user;
    }

    /**
     * show user with limited access in user search
     *
     * @param bool $a_visible
     */
    public function showLimitedUser($a_visible)
    {
        $this->show_limited_user = (bool) $a_visible;
    }

    /**
     * are user with limited access visible in user search
     *
     * @return bool
     */
    public function isLimitedUserVisible()
    {
        return $this->show_limited_user;
    }
    
    public function isDateFilterEnabled()
    {
        return $this->date_filter;
    }
    
    public function enableDateFilter($a_filter)
    {
        $this->date_filter = $a_filter;
    }
    
    public function update()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->ilias->setSetting('search_max_hits', $this->getMaxHits());
        $this->ilias->setSetting('search_index', (int) $this->enabledIndex());
        $this->ilias->setSetting('search_lucene', (int) $this->enabledLucene());
        
        $this->ilias->setSetting('lucene_default_operator', $this->getDefaultOperator());
        $this->ilias->setSetting('lucene_fragment_size', $this->getFragmentSize());
        $this->ilias->setSetting('lucene_fragment_count', $this->getFragmentCount());
        $this->ilias->setSetting('lucene_max_subitems', $this->getMaxSubitems());
        $this->ilias->setSetting('lucene_show_relevance', $this->isRelevanceVisible());
        $this->ilias->setSetting('lucene_last_index_time', $this->getLastIndexTime()->get(IL_CAL_UNIX));
        $this->ilias->setSetting('hide_adv_search', (int) $this->getHideAdvancedSearch());
        $this->ilias->setSetting('auto_complete_length', (int) $this->getAutoCompleteLength());
        $this->ilias->setSetting('lucene_item_filter_enabled', (int) $this->isLuceneItemFilterEnabled());
        $this->ilias->setSetting('lucene_item_filter', serialize($this->getLuceneItemFilter()));
        $this->ilias->setSetting('lucene_offline_filter', (int) $this->isLuceneOfflineFilterEnabled());
        $this->ilias->setSetting('lucene_mime_filter', serialize($this->getLuceneMimeFilter()));
        $this->ilias->setSetting('lucene_sub_relevance', $this->isSubRelevanceVisible());
        $ilSetting->set('lucene_mime_filter_enabled', $this->isLuceneMimeFilterEnabled());
        $this->ilias->setSetting('lucene_prefix_wildcard', $this->isPrefixWildcardQueryEnabled());
        $ilSetting->set('lucene_user_search', $this->isLuceneUserSearchEnabled());
        $ilSetting->set('search_show_inactiv_user', $this->isInactiveUserVisible());
        $ilSetting->set('search_show_limited_user', $this->isLimitedUserVisible());
        
        $ilSetting->set('search_date_filter', $this->isDateFilterEnabled());

        return true;
    }

    // PRIVATE
    public function __read()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->setMaxHits($this->ilias->getSetting('search_max_hits', 10));
        $this->enableIndex($this->ilias->getSetting('search_index', 0));
        $this->enableLucene($this->ilias->getSetting('search_lucene', 0));
        
        $this->setDefaultOperator($this->ilias->getSetting('lucene_default_operator', self::OPERATOR_AND));
        $this->setFragmentSize($this->ilias->getSetting('lucene_fragment_size', 50));
        $this->setFragmentCount($this->ilias->getSetting('lucene_fragment_count', 3));
        $this->setMaxSubitems($this->ilias->getSetting('lucene_max_subitems', 5));
        $this->showRelevance($this->ilias->getSetting('lucene_show_relevance', true));

        if ($time = $this->ilias->getSetting('lucene_last_index_time', false)) {
            $this->setLastIndexTime(new ilDateTime($time, IL_CAL_UNIX));
        } else {
            $this->setLastIndexTime(null);
        }
        
        $this->setHideAdvancedSearch($this->ilias->getSetting('hide_adv_search', 0));
        $this->setAutoCompleteLength($this->ilias->getSetting('auto_complete_length', $this->getAutoCompleteLength()));
        
        $this->enableLuceneItemFilter($this->ilias->getSetting('lucene_item_filter_enabled', (int) $this->isLuceneItemFilterEnabled()));
        
        $filter = $this->ilias->getSetting('lucene_item_filter', serialize($this->getLuceneItemFilter()));
        $this->setLuceneItemFilter(unserialize($filter));
        $this->enableLuceneOfflineFilter($this->ilias->getSetting('lucene_offline_filter'), $this->isLuceneOfflineFilterEnabled());
        
        $this->enableLuceneMimeFilter($ilSetting->get('lucene_mime_filter_enabled', $this->lucene_item_filter_enabled));
        $filter = $this->ilias->getSetting('lucene_mime_filter', serialize($this->getLuceneMimeFilter()));
        $this->setLuceneMimeFilter(unserialize($filter));
        $this->showSubRelevance($this->ilias->getSetting('lucene_sub_relevance', $this->showSubRelevance));
        $this->enablePrefixWildcardQuery($this->ilias->getSetting('lucene_prefix_wildcard', $this->prefix_wildcard));
        $this->enableLuceneUserSearch($ilSetting->get('lucene_user_search', $this->user_search));

        $this->showInactiveUser($ilSetting->get('search_show_inactiv_user', $this->show_inactiv_user));
        $this->showLimitedUser($ilSetting->get('search_show_limited_user', $this->show_limited_user));
        
        $this->enableDateFilter($ilSetting->get('search_date_filter', $this->date_filter));
    }
}
