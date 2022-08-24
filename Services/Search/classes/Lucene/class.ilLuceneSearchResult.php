<?php

declare(strict_types=1);
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
*
*
* @ingroup
*/
class ilLuceneSearchResult implements Iterator
{
    /**
     * @var Closure[]
     */
    private array $listener = [];
    private int $position = 0;

    private int $limit = 0;
    private int $total_hits = 0;
    private int $max_score = 0;

    private array $objects = [];
    private array $relevance = [];



    /**
     * set search callback
     * @param Closure[]
     */
    public function setCallback(array $a_callback): void
    {
        $this->listener = $a_callback;
    }

    /**
     * Iterator rewind
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
     */
    public function next()
    {
        $this->position++;
    }



    public function getCandidates(): array
    {
        return $this->objects;
    }

    public function addObject(int $a_value, float $a_relevance = 0): void
    {
        $this->objects[] = $a_value;
        $this->relevance[$a_value] = $a_relevance;
    }

    public function getRelevance(int $a_obj_id): float
    {
        if (!$this->getMaxScore()) {
            return 0;
        }
        return isset($this->relevance[$a_obj_id]) ? $this->relevance[$a_obj_id] / $this->getMaxScore() * 100 : 0;
    }


    public function setLimit(int $a_limit): void
    {
        $this->limit = $a_limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }


    public function setMaxScore(int $a_score): void
    {
        $this->max_score = $a_score;
    }

    public function getMaxScore(): int
    {
        return $this->max_score;
    }

    public function setTotalHits(int $a_hits): void
    {
        $this->total_hits = $a_hits;
    }

    public function getTotalHits(): int
    {
        return $this->total_hits;
    }
}
