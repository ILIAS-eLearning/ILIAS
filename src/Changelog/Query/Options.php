<?php

namespace ILIAS\Changelog\Query;

/**
 * Class Options
 *
 * @package ILIAS\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Options
{

    const ORDER_ASCENDING = 'ASC';
    const ORDER_DESCENDING = 'DESC';

    /**
     * @var int
     */
    protected $limit = 0;
    /**
     * @var int
     */
    protected $offset = 0;
    /**
     * @var string
     */
    protected $orderBy = '';

    /**
     * @var string
     */
    protected $orderDirection = self::ORDER_ASCENDING;

    /**
     * @param int $limit
     *
     * @return Options
     */
    public function withLimit(int $limit) {
        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }
    /**
     * @param int $offset
     *
     * @return Options
     */
    public function withOffset(int $offset) {
        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderByEventName() {
        $clone = clone $this;
        $clone->orderBy = 'event_name';

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderByTimestamp() {
        $clone = clone $this;
        $clone->orderBy = 'timestamp';

        return $clone;
    }

    /**
     * @return Options
     */
    public function withOrderDirectionAscending() {
        $clone = clone $this;
        $clone->orderDirection = self::ORDER_ASCENDING;

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderDirectionDescending() {
        $clone = clone $this;
        $clone->orderDirection = self::ORDER_DESCENDING;

        return $clone;
    }

    /**
     * @return int
     */
    public function getLimit(): int {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int {
        return $this->offset;
    }


    /**
     * @return string
     */
    public function getOrderBy(): string {
        return $this->orderBy;
    }


    /**
     * @return string
     */
    public function getOrderDirection(): string {
        return $this->orderDirection;
    }
}