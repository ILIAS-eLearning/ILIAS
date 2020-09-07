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

/**
* Search result implementing iterator interface.
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup
*/
class ilLuceneSearchResult implements Iterator
{
    private $listener;
    private $position = 0;
    
    private $limit = 0;
    private $total_hits = 0;
    private $max_score = 0;

    private $objects = [];
    private $relevance;
    

    /**
     * Constructor
     * @param string search result
     * @return
     */
    public function __construct()
    {
    }
    
    /**
     * set search callback
     * @param
     * @return
     */
    public function setCallback($a_callback)
    {
        $this->listener = $a_callback;
    }
    
    /**
     * Iterator rewind
     * @return
     */
    public function rewind()
    {
        $this->position = 0;
    }
    
    /**
     * Iterator valid
     * @param
     * @return
     */
    public function valid()
    {
        if ($this->position < count($this->objects)) {
            return true;
        }
        // if the number of candidates is smaller than the total number of hits
        // get next result page
        if (count($this->objects) < $this->getTotalHits()) {
            ilLoggerFactory::getLogger('src')->debug("Trying to get next result page...");
            @call_user_func($this->listener);
        }
        // Check again
        if ($this->position < count($this->objects)) {
            return true;
        }
        return false;
    }
    
    /**
     * Iterator key
     * @return
     */
    public function key()
    {
        return $this->position;
    }
    
    /**
     * Iterator current
     * @return
     */
    public function current()
    {
        return $this->objects[$this->position];
    }
    
    /**
     * Iterator next
     * @return
     */
    public function next()
    {
        $this->position++;
    }
    
    
    
    /**
     * get candidates
     * @param
     * @return
     */
    public function getCandidates()
    {
        return $this->objects;
    }
    
    /**
     * Add object entry
     * @param int key
     * @param int value
     *
     * @return
     */
    public function addObject($a_value, $a_relevance = 0)
    {
        $this->objects[] = $a_value;
        $this->relevance[$a_value] = $a_relevance;
    }
    
    /**
     * get relevance
     * @param int obj_id
     * @return int	relevance in percent
     */
    public function getRelevance($a_obj_id)
    {
        if (!$this->getMaxScore()) {
            return 0;
        }
        return isset($this->relevance[$a_obj_id]) ? $this->relevance[$a_obj_id] / $this->getMaxScore() * 100 : 0;
    }
    
    
    /**
     *
     * @param
     * @return
     */
    public function setLimit($a_limit)
    {
        $this->limit = $a_limit;
    }
    
    /**
     *
     * @param
     * @return
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    
    /**
     *
     * @param
     * @return
     */
    public function setMaxScore($a_score)
    {
        $this->max_score = $a_score;
    }
    
    /**
     *
     * @param
     * @return
     */
    public function getMaxScore()
    {
        return $this->max_score;
    }
    
    /**
     * set total hits
     * @return
     */
    public function setTotalHits($a_hits)
    {
        $this->total_hits = $a_hits;
    }
    
    /**
     * get total hits
     * @param
     * @return
     */
    public function getTotalHits()
    {
        return $this->total_hits;
    }
}
