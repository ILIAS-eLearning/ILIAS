<?php declare(strict_types=1);
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

/**
* Validate Lucene search results
* Do access checks, create ref_ids from obj_ids...
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilLuceneSearchResultFilter
{
    protected static ?ilLuceneSearchResultFilter $instance = null;
    
    protected int $user_id;
    protected ?ilLuceneSearchResult $result = null;
    protected array $checked = [];
    protected ilSearchSettings $settings;
    protected ilUserSearchCache $cache;
    protected string $required_permission = 'visible';
    protected bool $limit_reached = false;
    protected int $offset = 0;
    
    protected array $filters = array();

    protected ilAccess $access;


    /**
     * Singleton constructor
     */
    protected function __construct(int $a_user_id)
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->user_id = $a_user_id;
        $this->settings = ilSearchSettings::getInstance();
        $this->cache = ilUserSearchCache::_getInstance($this->getUserId());
        $this->offset = $this->settings->getMaxHits() * ($this->cache->getResultPageNumber() - 1);
    }
    
    public static function getInstance(int $a_user_id) : ilLuceneSearchResultFilter
    {
        if (self::$instance == null) {
            return self::$instance = new ilLuceneSearchResultFilter($a_user_id);
        }
        return self::$instance;
    }
    
    public function addFilter(ilLuceneResultFilter $filter) : void
    {
        $this->filters[] = $filter;
    }
    
    /**
     * Set result ids
     */
    public function setCandidates(ilLuceneSearchResult $a_ids) : void
    {
        $this->result = $a_ids;
    }
    
    /**
     * get result ids
     */
    public function getCandidates() : ?ilLuceneSearchResult
    {
        return $this->result;
    }
    
    /**
     * Get user id
     */
    public function getUserId() : int
    {
        return $this->user_id;
    }
    
    /**
     * Get required permission
     */
    public function getRequiredPermission() : string
    {
        return $this->required_permission;
    }
    
    /**
     * Check if search max hits is reached
     * @return bool max hits reached
     */
    public function isLimitReached() : bool
    {
        return $this->limit_reached;
    }
    
    /**
     * get filtered ids
     * @return int[] array of filtered ref_ids
     */
    public function getResultIds() : array
    {
        return $this->checked;
    }
    
    /**
     * get filtered ids
     * @return int[] array of filtered obj_ids
     */
    public function getResultObjIds() : array
    {
        $obj_ids = [];
        foreach ($this->checked as $obj_id) {
            $obj_ids[] = $obj_id;
        }
        return $obj_ids;
    }
    
    /**
     * get results
     * @return int[] array of ids
     */
    public function getResults()
    {
        return $this->checked;
    }
    
    /**
     * get max hits
     */
    public function getMaxHits() : int
    {
        return $this->settings->getMaxHits();
    }
    
    /**
     * Load results from db
     */
    public function loadFromDb() : void
    {
        $this->checked = $this->cache->getResults();
    }
    
    /**
     * Filter search results.
     * Do RBAC checks.
     */
    public function filter() : bool
    {
        // get ref_ids and check access
        $counter = 0;
        $offset_counter = 0;
        
        foreach ($this->getCandidates() as $obj_id) {
            // Check referenced objects
            foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                // Check filter
                if (!$this->checkFilter($ref_id)) {
                    $this->cache->appendToFailed($ref_id);
                    continue;
                }

                // Access failed by prior check
                if ($this->cache->isFailed($ref_id)) {
                    continue;
                }
                // Offset check
                if ($this->cache->isChecked($ref_id) and !$this->isOffsetReached($offset_counter)) {
                    ilLoggerFactory::getLogger('src')->debug('Result was checked');
                    $offset_counter++;
                    break;
                }
                
                // RBAC check
                if ($this->access->checkAccessOfUser(
                    $this->getUserId(),
                    $this->getRequiredPermission(),
                    '',
                    $ref_id,
                    '',
                    $obj_id
                )) {
                    ++$counter;
                    $offset_counter++;
                    $this->append($ref_id, $obj_id);
                    $this->cache->appendToChecked($ref_id, $obj_id);
                    break;
                } else {
                    $this->cache->appendToFailed($ref_id);
                }
            }
            if ($counter >= $this->settings->getMaxHits()) {
                $this->limit_reached = true;
                $this->cache->setResults($this->getResultIds());
                $this->cache->save();
                return false;
            }
        }
        $this->cache->setResults($this->getResultIds());
        $this->cache->save();
        return true;
    }
    
    /**
     * check appended filter
     */
    protected function checkFilter(int $a_ref_id) : bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($a_ref_id)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Append to filtered results
     */
    protected function append(int $a_ref_id, int $a_obj_id) : void
    {
        $this->checked[$a_ref_id] = $a_obj_id;
    }
    
    /**
     * Check if offset is reached
     * @param int $a_current_nr Current result number
     * @return bool
     */
    protected function isOffsetReached(int $a_current_nr) : bool
    {
        return !($a_current_nr < $this->offset);
    }
}
