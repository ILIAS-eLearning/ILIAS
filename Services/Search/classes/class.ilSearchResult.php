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

/**
* searchResult stores all result of a search query.
* Offers methods like mergeResults. To merge result sets of different queries.
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version Id$
*
* @package ilias-search
*/
include_once('Services/Search/classes/class.ilUserSearchCache.php');

define('DEFAULT_SEARCH', 0);
define('ADVANCED_SEARCH', 1);
define('ADVANCED_MD_SEARCH', 4);

class ilSearchResult
{
    public $permission = 'visible';

    public $user_id;
    public $entries = array();
    public $results = array();
    public $observers = array();

    protected $search_cache = null;
    protected $offset = 0;

    // OBJECT VARIABLES
    public $ilias;
    public $ilAccess;

    // Stores info if MAX HITS is reached or not
    public $limit_reached = false;
    public $result;
    
    protected $preventOverwritingMaxhits = false;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_user_id = 0)
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilAccess = $DIC['ilAccess'];
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $this->ilAccess = $ilAccess;
        if ($a_user_id) {
            $this->user_id = $a_user_id;
        } else {
            $this->user_id = $ilUser->getId();
        }
        $this->__initSearchSettingsObject();
        $this->initUserSearchCache();

        $this->db = $ilDB;
    }

    /**
    * Set the required permission for the rbac checks in function 'filter()'
    */
    public function setRequiredPermission($a_permission)
    {
        $this->permission = $a_permission;
    }
    
    public function getRequiredPermission()
    {
        return $this->permission;
    }


    public function setUserId($a_user_id)
    {
        $this->user_id = $a_user_id;
    }
    public function getUserId()
    {
        return $this->user_id;
    }

    public function getEntries()
    {
        return $this->entries ? $this->entries : array();
    }

    public function isLimitReached()
    {
        return $this->limit_reached ? true : false;
    }

    public function setMaxHits($a_max_hits)
    {
        $this->max_hits = $a_max_hits;
    }
    public function getMaxHits()
    {
        return $this->max_hits;
    }
    
    /**
     * Check if offset is reached
     *
     * @access public
     * @param int current counter of result
     * @return bool reached or not
     */
    public function isOffsetReached($a_counter)
    {
        return ($a_counter < $this->offset) ? false : true;
    }
    
    /**
     *
     * add search result entry
     * Entries are stored with 'obj_id'. This method is typically called to store db query results.
     * @param integer object object_id
     * @param string obj_type 'lm' or 'crs' ...
     * @param array value position of query parser words in query string
     * @param integer child id e.g id of page or chapter
     * @access	public
     */
    public function addEntry($a_obj_id, $a_type, $found, $a_child_id = 0)
    {
        // Create new entry if it not exists
        if (!$this->entries[$a_obj_id]) {
            $this->entries[$a_obj_id]['obj_id'] = $a_obj_id;
            $this->entries[$a_obj_id]['type'] = $a_type;
            $this->entries[$a_obj_id]['found'] = $found;
            $this->entries[$a_obj_id]['child'] = [];

            if ($a_child_id and $a_child_id != $a_obj_id) {
                $this->entries[$a_obj_id]['child'][$a_child_id] = $a_child_id;
            }
        } else {
            // replace or add child ('pg','st') id
            if ($a_child_id and $a_child_id != $a_obj_id) {
                $this->entries[$a_obj_id]['child'][$a_child_id] = $a_child_id;
            }

            // UPDATE FOUND
            $counter = 0;
            foreach ($found as $position) {
                if ($position) {
                    $this->entries[$a_obj_id]['found'][$counter] = $position;
                }
                $counter++;
            }
        }
        return true;
    }

    /**
     *
     * Check number of entries
     * @access	public
     */
    public function numEntries()
    {
        return count($this->getEntries());
    }

    /**
     *
     * merge entries of this instance and another result object
     * @param object result_obj
     * @access	public
     */
    public function mergeEntries(&$result_obj)
    {
        foreach ($result_obj->getEntries() as $entry) {
            $this->addEntry($entry['obj_id'], $entry['type'], $entry['found']);
            $this->__updateEntryChilds($entry['obj_id'], $entry['child']);
        }
        return true;
    }

    /**
     *
     * diff entries of this instance and another result object
     * Used for search in results
     * @param object result_obj
     * @access	public
     */
    public function diffEntriesFromResult(&$result_obj)
    {
        $new_entries = $this->getEntries();
        $this->entries = array();

        // Get all checked objects
        foreach ($this->search_cache->getCheckedItems() as $ref_id => $obj_id) {
            if (isset($new_entries[$obj_id])) {
                $this->addEntry(
                    $new_entries[$obj_id]['obj_id'],
                    $new_entries[$obj_id]['type'],
                    $new_entries[$obj_id]['found']
                );
                $this->__updateEntryChilds(
                    $new_entries[$obj_id]['obj_id'],
                    $new_entries[$obj_id]['child']
                );
            }
        }
    }

    /**
     *
     * Build intersection of entries (all entries that are present in both result sets)
     * @param object result_obj
     * @access	public
     */
    public function intersectEntries(&$result_obj)
    {
        $new_entries = $this->getEntries();
        $this->entries = array();

        foreach ($result_obj->getEntries() as $entry) {
            $obj_id = $entry['obj_id'];
            if (isset($new_entries[$obj_id])) {
                $this->addEntry(
                    $new_entries[$obj_id]['obj_id'],
                    $new_entries[$obj_id]['type'],
                    $new_entries[$obj_id]['found']
                );

                $this->__updateEntryChilds(
                    $new_entries[$obj_id]['obj_id'],
                    $new_entries[$obj_id]['child']
                );
            }
        }
    }


    /**
     *
     * add search result
     * Results are stored with 'ref_id'. This method is typically called after checking access of entries.
     * @param integer ref_id
     * @param integer obj_id
     * @param string obj_type 'lm' or 'crs' ...
     * @access	public
     */
    public function addResult($a_ref_id, $a_obj_id, $a_type)
    {
        $this->results[$a_ref_id]['ref_id'] = $a_ref_id;
        $this->results[$a_ref_id]['obj_id'] = $a_obj_id;
        $this->results[$a_ref_id]['type'] = $a_type;
    }

    public function getResults()
    {
        return $this->results ? $this->results : array();
    }
    
    /**
     * get result ids
     *
     * @access public
     * @return array result ids
     */
    public function getResultIds()
    {
        foreach ($this->getResults() as $id => $tmp) {
            $ids[] = $id;
        }
        return $ids ? $ids : array();
    }
    
    public function getResultsByObjId()
    {
        $tmp_res = array();
        foreach ($this->getResults() as $ref_id => $res_data) {
            $tmp_res[$res_data['obj_id']][] = $ref_id;
        }
        return $tmp_res ? $tmp_res : array();
    }


    /**
     *
     * Get unique results. Return an array of obj_id (No multiple results for references)
     * Results are stored with 'ref_id'. This method is typically called after checking access of entries.
     * @access	public
     */
    public function getUniqueResults()
    {
        $obj_ids = array();
        foreach ($this->results as $result) {
            if (in_array($result['obj_id'], $obj_ids)) {
                continue;
            }
            $obj_ids[] = $result['obj_id'];
            $objects[] = $result;
        }
        return $objects ? $objects : array();
    }

    public function getResultsForPresentation()
    {
        $res = array();
        
        foreach ($this->getResults() as $result) {
            $res[$result['ref_id']] = $result['obj_id'];
        }
        return $res;
    }

    public function getSubitemIds()
    {
        $res = array();
        foreach ($this->getResults() as $row) {
            $res[$row['obj_id']] = $row['child'];
        }
        return $res ? $res : array();
    }
    
    
    
    /**
     * Filter search result.
     * Do RBAC checks.
     *
     * Allows paging of results for referenced objects
     *
     * @access public
     * @param int root node id
     * @param bool check and boolean search
     * @return bool success status
     *
     */
    public function filter($a_root_node, $check_and)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        // get ref_ids and check access
        $counter = 0;
        $offset_counter = 0;
        foreach ($this->getEntries() as $entry) {
            // boolean and failed continue
            if ($check_and and in_array(0, $entry['found'])) {
                continue;
            }
            // Types like role, rolt, user do not need rbac checks
            $type = ilObject::_lookupType($entry['obj_id']);
            if ($type == 'rolt' or $type == 'usr' or $type == 'role') {
                if ($this->callListeners($entry['obj_id'], $entry)) {
                    $this->addResult($entry['obj_id'], $entry['obj_id'], $type);
                    if (is_array($entry['child'])) {
                        $counter += count($entry['child']);
                    }
                    // Stop if maximum of hits is reached
                    if (++$counter > $this->getMaxHits()) {
                        $this->limit_reached = true;
                        return true;
                    }
                }
                continue;
            }
            // Check referenced objects
            foreach (ilObject::_getAllReferences($entry['obj_id']) as $ref_id) {
                // Failed check: if ref id check is failed by previous search
                if ($this->search_cache->isFailed($ref_id)) {
                    continue;
                }
                // Offset check
                if ($this->search_cache->isChecked($ref_id) and !$this->isOffsetReached($offset_counter)) {
                    ++$offset_counter;
                    continue;
                }
                
                if (!$this->callListeners($ref_id, $entry)) {
                    continue;
                }
                
                
                
                // RBAC check
                $type = ilObject::_lookupType($ref_id, true);
                if ($this->ilAccess->checkAccessOfUser(
                    $this->getUserId(),
                    $this->getRequiredPermission(),
                    '',
                    $ref_id,
                    $type,
                    $entry['obj_id']
                )) {
                    if ($a_root_node == ROOT_FOLDER_ID or $tree->isGrandChild($a_root_node, $ref_id)) {
                        // Call listeners
                        #if($this->callListeners($ref_id,$entry))
                        if (1) {
                            $this->addResult($ref_id, $entry['obj_id'], $type);
                            $this->search_cache->appendToChecked($ref_id, $entry['obj_id']);
                            $this->__updateResultChilds($ref_id, $entry['child']);

                            $counter++;
                            $offset_counter++;
                            // Stop if maximum of hits is reached
                            
                            if ($counter >= $this->getMaxHits()) {
                                $this->limit_reached = true;
                                $this->search_cache->setResults($this->results);
                                return true;
                            }
                        }
                    }
                    continue;
                }
                $this->search_cache->appendToFailed($ref_id);
            }
        }
        $this->search_cache->setResults($this->results);
        return false;
    }
    
    /**
     *
     * Filter search area of result set
     * @access	public
     */
    public function filterResults($a_root_node)
    {
        global $DIC;

        $tree = $DIC['tree'];

        $tmp_results = $this->getResults();
        $this->results = array();
        foreach ($tmp_results as $result) {
            if ($tree->isGrandChild($a_root_node, $result['ref_id']) and $tree->isInTree($result['ref_id'])) {
                $this->addResult($result['ref_id'], $result['obj_id'], $result['type']);
                $this->__updateResultChilds($result['ref_id'], $result['child']);
            }
        }

        return true;
    }


    /**
     *
     * Save search results
     * @param integer DEFAULT_SEARCH or ADVANCED_SEARCH
     * @access	public
     */
    public function save($a_type = DEFAULT_SEARCH)
    {
        $this->search_cache->save();
        return false;
    }
    /**
     *
     * read search results
     * @param integer DEFAULT_SEARCH or ADVANCED_SEARCH
     * @access	public
     */
    public function read($a_type = DEFAULT_SEARCH)
    {
        $this->results = $this->search_cache->getResults();
    }

    // PRIVATE
    /**
     *
     * Update childs for a specific entry
     * @param integer object object_id
     * @param array array of child ids. E.g 'pg', 'st'
     * @access	private
     */
    public function __updateEntryChilds($a_obj_id, $a_childs)
    {
        if ($this->entries[$a_obj_id] and is_array($a_childs)) {
            foreach ($a_childs as $child_id) {
                if ($child_id) {
                    $this->entries[$a_obj_id]['child'][$child_id] = $child_id;
                }
            }
            return true;
        }
        return false;
    }
    /**
     *
     * Update childs for a specific result
     * @param integer  object ref_id
     * @param array array of child ids. E.g 'pg', 'st'
     * @access	private
     */
    public function __updateResultChilds($a_ref_id, $a_childs)
    {
        if ($this->results[$a_ref_id] and is_array($a_childs)) {
            foreach ($a_childs as $child_id) {
                $this->results[$a_ref_id]['child'][$child_id] = $child_id;
            }
            return true;
        }
        return false;
    }



    public function __initSearchSettingsObject()
    {
        include_once 'Services/Search/classes/class.ilSearchSettings.php';

        $this->search_settings = new ilSearchSettings();
        if (!$this->preventOverwritingMaxhits()) {
            $this->setMaxHits($this->search_settings->getMaxHits());
        }
    }
    
    /**
     * Init user search cache
     *
     * @access private
     *
     */
    protected function initUserSearchCache()
    {
        include_once('Services/Search/classes/class.ilUserSearchCache.php');
        $this->search_cache = ilUserSearchCache::_getInstance($this->getUserId());
        $this->offset = $this->getMaxHits() * ($this->search_cache->getResultPageNumber() - 1) ;
    }
    
    /**
     * If you call this function and pass "true" the maxhits setting will not be overwritten
     * in __initSearchSettingsObject()
     *
     * @access	public
     * @param	boolean	$a_flag	true or false to set the flag or leave blank to get the status of the flag
     * @returmn	boolean	if called without parameter the status of the flag will be returned, otherwise $this
     *
     */
    public function preventOverwritingMaxhits($a_flag = null)
    {
        if (null === $a_flag) {
            return $this->preventOverwritingMaxhits;
        }
        
        $this->preventOverwritingMaxhits = $a_flag;
        
        return $this;
    }

    /**
     * The observer is used to call functions for filtering result.
     * Every callback function should support the following parameters:
     * array of ids. E.g: ref_id = 5,array(obj_id = 1,type = 'crs'),
     * The function should return true or false.
     * @param object class of callback function
     * @param string name of callback method
     * @access public
     */
    public function addObserver(&$a_class, $a_method)
    {
        $this->observers[] = array('class' => $a_class,
                                   'method' => $a_method);
        return true;
    }
    public function callListeners($a_ref_id, &$a_data)
    {
        foreach ($this->observers as $observer) {
            $class = &$observer['class'];
            $method = $observer['method'];

            if (!$class->$method($a_ref_id, $a_data)) {
                return false;
            }
        }
        return true;
    }
} // END class.Search
