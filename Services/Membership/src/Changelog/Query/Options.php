<?php

namespace ILIAS\Membership\Changelog\Query;

/**
 * Class Options
 *
 * @package ILIAS\Membership\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Options
{

    const DEFAULT_ORDER_FIELD = 'timestamp';
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
    protected $order_direction = self::ORDER_ASCENDING;
    /**
     * @var bool
     */
    protected $fetch_object_title = false;

    /**
     * @param int $limit
     *
     * @return Options
     */
    public function withLimit(int $limit) : self
    {
        $clone = clone $this;
        $clone->limit = $limit;

        return $clone;
    }


    /**
     * @param int $offset
     *
     * @return Options
     */
    public function withOffset(int $offset) : self
    {
        $clone = clone $this;
        $clone->offset = $offset;

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderByEventName() : self
    {
        $clone = clone $this;
        $clone->orderBy = 'event_name';

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderByTimestamp() : self
    {
        $clone = clone $this;
        $clone->orderBy = 'timestamp';

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderDirectionAscending() : self
    {
        $clone = clone $this;
        $clone->order_direction = self::ORDER_ASCENDING;

        return $clone;
    }


    /**
     * @return Options
     */
    public function withOrderDirectionDescending() : self
    {
        $clone = clone $this;
        $clone->order_direction = self::ORDER_DESCENDING;

        return $clone;
    }


    /**
     * @param bool $fetch_object_title
     *
     * @return $this
     */
    public function withFetchObjectTitle(bool $fetch_object_title) : self
    {
        $clone = clone $this;
        $clone->fetch_object_title = $fetch_object_title;

        return $clone;
    }

    /**
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }


    /**
     * @return int
     */
    public function getOffset() : int
    {
        return $this->offset;
    }


    /**
     * @return string
     */
    public function getOrderBy() : string
    {
        return $this->orderBy ?: self::DEFAULT_ORDER_FIELD;
    }


    /**
     * @return string
     */
    public function getOrderDirection() : string
    {
        return $this->order_direction;
    }


    /**
     * @return bool
     */
    public function getFetchObjectTitle() : bool
    {
        return $this->fetch_object_title;
    }
}