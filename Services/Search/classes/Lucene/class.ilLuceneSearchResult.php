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
 * Search result implementing iterator interface.
 *
 * @author Stefan Meyer <meyer@leifos.com>
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
    private float $max_score = 0;

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


    public function setMaxScore(float $a_score): void
    {
        $this->max_score = $a_score;
    }

    public function getMaxScore(): float
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
