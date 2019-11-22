<?php

namespace ILIAS\Membership\Changelog\Query;

/**
 * Class Filter
 *
 * @package ILIAS\Membership\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Filter
{

    /**
     * @var int
     */
    protected $timestamp_from = 0;
    /**
     * @var int
     */
    protected $timestamp_to = 0;
    /**
     * @var string[]
     */
    protected $event_ids = [];
    /**
     * @var string[]
     */
    protected $event_names = [];
    /**
     * @var int[]
     */
    protected $actor_user_ids = [];
    /**
     * @var int[]
     */
    protected $subject_user_ids = [];
    /**
     * @var int[]
     */
    protected $subject_obj_ids = [];


    /**
     * @param int $timestamp_from
     *
     * @return Filter
     */
    public function withTimestampFrom(int $timestamp_from) : self
    {
        $clone = clone $this;
        $clone->timestamp_from = $timestamp_from;

        return $clone;
    }


    /**
     * @param int $timestamp_to
     *
     * @return Filter
     */
    public function withTimestampTo(int $timestamp_to) : self
    {
        $clone = clone $this;
        $clone->timestamp_to = $timestamp_to;

        return $clone;
    }


    /**
     * @param string[] $event_names
     *
     * @return Filter
     */
    public function withEventNames(array $event_names) : self
    {
        $clone = clone $this;
        $clone->event_names = $event_names;

        return $clone;
    }


    /**
     * @param int[] $actor_user_ids
     *
     * @return Filter
     */
    public function withActorUserIds(array $actor_user_ids) : self
    {
        $clone = clone $this;
        $clone->actor_user_ids = $actor_user_ids;

        return $clone;
    }


    /**
     * @param int[] $subject_user_ids
     *
     * @return Filter
     */
    public function withSubjectUserIds(array $subject_user_ids) : self
    {
        $clone = clone $this;
        $clone->subject_user_ids = $subject_user_ids;

        return $clone;
    }


    /**
     * @param int[] $subject_obj_ids
     *
     * @return Filter
     */
    public function withSubjectObjIds(array $subject_obj_ids) : self
    {
        $clone = clone $this;
        $clone->subject_obj_ids = $subject_obj_ids;

        return $clone;
    }


    /**
     * @param string[] $event_ids
     *
     * @return Filter
     */
    public function withEventIds(array $event_ids) : self
    {
        $clone = clone $this;
        $clone->event_ids = $event_ids;

        return $clone;
    }


    /**
     * @return int
     */
    public function getTimestampFrom() : int
    {
        return $this->timestamp_from;
    }


    /**
     * @return string[]
     */
    public function getEventIds() : array
    {
        return $this->event_ids;
    }


    /**
     * @return string[]
     */
    public function getEventNames() : array
    {
        return $this->event_names;
    }


    /**
     * @return int[]
     */
    public function getSubjectUserIds() : array
    {
        return $this->subject_user_ids;
    }


    /**
     * @return int[]
     */
    public function getActorUserIds() : array
    {
        return $this->actor_user_ids;
    }


    /**
     * @return int
     */
    public function getTimestampTo() : int
    {
        return $this->timestamp_to;
    }


    /**
     * @return int[]
     */
    public function getSubjectObjIds() : array
    {
        return $this->subject_obj_ids;
    }
}