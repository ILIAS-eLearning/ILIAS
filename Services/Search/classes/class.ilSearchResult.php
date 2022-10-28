<?php

declare(strict_types=1);
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
* searchResult stores all result of a search query.
* Offers methods like mergeResults. To merge result sets of different queries.
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*/


class ilSearchResult
{
    private string $permission = 'visible';

    private int $user_id;
    private array $entries = array();
    private array $results = array();
    private array $observers = array();
    private int $max_hits = 0;

    protected ilUserSearchCache $search_cache;
    protected int $offset = 0;

    // OBJECT VARIABLES
    protected ilAccess $ilAccess;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilObjUser $user;
    protected ilSearchSettings $search_settings;

    // Stores info if MAX HITS is reached or not
    public bool $limit_reached = false;

    protected bool $preventOverwritingMaxhits = false;



    /**
    * Constructor
    * @access	public
    */
    public function __construct(int $a_user_id = 0)
    {
        global $DIC;

        $this->ilAccess = $DIC->access();
        $this->db = $DIC->database();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();

        if ($a_user_id) {
            $this->user_id = $a_user_id;
        } else {
            $this->user_id = $this->user->getId();
        }
        $this->__initSearchSettingsObject();
        $this->initUserSearchCache();
    }

    /**
    * Set the required permission for the rbac checks in function 'filter()'
    */
    public function setRequiredPermission(string $a_permission): void
    {
        $this->permission = $a_permission;
    }

    public function getRequiredPermission(): string
    {
        return $this->permission;
    }


    public function setUserId(int $a_user_id): void
    {
        $this->user_id = $a_user_id;
    }
    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    public function isLimitReached(): bool
    {
        return $this->limit_reached;
    }

    public function setMaxHits(int $a_max_hits): void
    {
        $this->max_hits = $a_max_hits;
    }
    public function getMaxHits(): int
    {
        return $this->max_hits;
    }

    /**
     * Check if offset is reached
     */
    public function isOffsetReached(int $a_counter): bool
    {
        return !($a_counter < $this->offset);
    }

    /**
     *
     * add search result entry
     * Entries are stored with 'obj_id'. This method is typically called to store db query results.
     * @param int object object_id
     * @param string obj_type 'lm' or 'crs' ...
     * @param array value position of query parser words in query string
     * @param int child id e.g id of page or chapter
     * @return void
     */
    public function addEntry(int $a_obj_id, string $a_type, array $found, int $a_child_id = 0): void
    {
        // Create new entry if it not exists
        if (!isset($this->entries[$a_obj_id])) {
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
            $counter = 0;
            foreach ($found as $position) {
                if ($position) {
                    $this->entries[$a_obj_id]['found'][$counter] = $position;
                }
                $counter++;
            }
        }
    }

    /**
     *
     * Check number of entries
     * @access	public
     */
    public function numEntries(): int
    {
        return count($this->getEntries());
    }

    /**
     *
     * merge entries of this instance and another result object
     * @param object result_obj
     * @access	public
     */
    public function mergeEntries(ilSearchResult $result_obj): void
    {
        foreach ($result_obj->getEntries() as $entry) {
            $this->addEntry($entry['obj_id'], $entry['type'], $entry['found']);
            $this->__updateEntryChilds($entry['obj_id'], $entry['child']);
        }
    }

    /**
     * diff entries of this instance and another result object
     * Used for search in results
     */
    public function diffEntriesFromResult(): void
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
     * Build intersection of entries (all entries that are present in both result sets)
     */
    public function intersectEntries(ilSearchResult $result_obj): void
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

    public function addResult(int $a_ref_id, int $a_obj_id, string $a_type): void
    {
        $this->results[$a_ref_id]['ref_id'] = $a_ref_id;
        $this->results[$a_ref_id]['obj_id'] = $a_obj_id;
        $this->results[$a_ref_id]['type'] = $a_type;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * get result ids
     * @return int[] result ids
     */
    public function getResultIds(): array
    {
        $ids = [];
        foreach ($this->getResults() as $id => $tmp) {
            $ids[] = $id;
        }
        return $ids;
    }

    public function getResultsByObjId(): array
    {
        $tmp_res = [];
        foreach ($this->getResults() as $ref_id => $res_data) {
            $tmp_res[$res_data['obj_id']][] = $ref_id;
        }
        return $tmp_res;
    }


    /**
     * Get unique results. Return an array of obj_id (No multiple results for references)
     * Results are stored with 'ref_id'. This method is typically called after checking access of entries.
     */
    public function getUniqueResults(): array
    {
        $obj_ids = [];
        $objects = [];
        foreach ($this->results as $result) {
            if (in_array($result['obj_id'], $obj_ids)) {
                continue;
            }
            $obj_ids[] = $result['obj_id'];
            $objects[] = $result;
        }
        return $objects;
    }

    public function getResultsForPresentation(): array
    {
        $res = [];
        foreach ($this->getResults() as $result) {
            if (!is_array($result)) {
                continue;
            }

            $res[(int) $result['ref_id']] = (int) $result['obj_id'];
        }
        return $res;
    }

    public function getSubitemIds(): array
    {
        $res = array();
        foreach ($this->getResults() as $row) {
            $res[$row['obj_id']] = $row['child'] ?? [];
        }
        return $res;
    }



    /**
     * Filter search result.
     * Do RBAC checks.
     * Allows paging of results for referenced objects
     */
    public function filter(int $a_root_node, bool $check_and): bool
    {


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
            foreach (ilObject::_getAllReferences((int) $entry['obj_id']) as $ref_id) {
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
                    if ($a_root_node == ROOT_FOLDER_ID or $this->tree->isGrandChild($a_root_node, $ref_id)) {
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
    public function filterResults(int $a_root_node): void
    {
        $tmp_results = $this->getResults();
        $this->results = array();
        foreach ($tmp_results as $result) {
            if ($this->tree->isGrandChild($a_root_node, $result['ref_id']) && $this->tree->isInTree($result['ref_id'])) {
                $this->addResult($result['ref_id'], $result['obj_id'], $result['type']);
                $this->__updateResultChilds($result['ref_id'], $result['child'] ?? []);
            }
        }
    }


    /**
     *
     * Save search results
     * @param int DEFAULT_SEARCH or ADVANCED_SEARCH
     */
    public function save(int $a_type = ilUserSearchCache::DEFAULT_SEARCH): void
    {
        $this->search_cache->save();
    }
    /**
     *
     * read search results
     * @param int DEFAULT_SEARCH or ADVANCED_SEARCH
     * @access	public
     */
    public function read(int $a_type = ilUserSearchCache::DEFAULT_SEARCH): void
    {
        $this->results = $this->search_cache->getResults();
    }

    // PRIVATE
    /**
     *
     * Update childs for a specific entry
     * @param int object object_id
     * @param array array of child ids. E.g 'pg', 'st'
     * @access	private
     */
    public function __updateEntryChilds(int $a_obj_id, array $a_childs): bool
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
     * Update child ids for a specific result
     */
    public function __updateResultChilds(int $a_ref_id, array $a_childs): bool
    {
        if ($this->results[$a_ref_id] and is_array($a_childs)) {
            foreach ($a_childs as $child_id) {
                $this->results[$a_ref_id]['child'][$child_id] = $child_id;
            }
            return true;
        }
        return false;
    }



    public function __initSearchSettingsObject(): void
    {
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
    protected function initUserSearchCache(): void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->getUserId());
        $this->offset = $this->getMaxHits() * ($this->search_cache->getResultPageNumber() - 1) ;
    }

    /**
     * If you call this function and pass "true" the maxhits setting will not be overwritten
     * in __initSearchSettingsObject()
     * @access	public
     * @param	bool|null $a_flag true or false to set the flag or leave blank to get the status of the flag
     * @return	bool|ilSearchResult	if called without parameter the status of the flag will be returned, otherwise $this
     */
    public function preventOverwritingMaxhits(?bool $a_flag = null)
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
     */
    public function addObserver(object $a_class, string $a_method): bool
    {
        $this->observers[] = array('class' => $a_class,
                                   'method' => $a_method);
        return true;
    }


    public function callListeners(int $a_ref_id, array $a_data): bool
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
