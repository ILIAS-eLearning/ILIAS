<?php

namespace ILIAS\Changelog\Query;

/**
 * Class EventDTO
 *
 * @package ILIAS\Changelog\Query
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EventDTO
{

    /**
     * @var int
     */
    protected $id;
    /**
     * @var String
     */
    protected $event_id;
    /**
     * @var String
     */
    protected $event_name;
    /**
     * @var int
     */
    protected $actor_user_id;
    /**
     * @var int
     */
    protected $subject_user_id;
    /**
     * @var int
     */
    protected $subject_obj_id;
    /**
     * @var String
     */
    protected $ilias_component;
    /**
     * @var array
     */
    protected $additional_data;
    /**
     * @var int
     */
    protected $timestamp;


    /**
     * EventDTO constructor.
     *
     * @param int    $id
     * @param String $event_id
     * @param String $event_name
     * @param int    $actor_user_id
     * @param int    $subject_user_id
     * @param int    $subject_obj_id
     * @param String $ilias_component
     * @param array  $additional_data
     * @param int    $timestamp
     */
    public function __construct(
        int $id,
        String $event_id,
        String $event_name,
        int $actor_user_id,
        int $subject_user_id,
        int $subject_obj_id,
        String $ilias_component,
        array $additional_data,
        int $timestamp
    ) {
        $this->id = $id;
        $this->event_id = $event_id;
        $this->event_name = $event_name;
        $this->actor_user_id = $actor_user_id;
        $this->subject_user_id = $subject_user_id;
        $this->subject_obj_id = $subject_obj_id;
        $this->ilias_component = $ilias_component;
        $this->additional_data = $additional_data;
        $this->timestamp = $timestamp;
    }


    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }


    /**
     * @return String
     */
    public function getEventId() : String
    {
        return $this->event_id;
    }


    /**
     * @return String
     */
    public function getEventName() : String
    {
        return $this->event_name;
    }


    /**
     * @return int
     */
    public function getActorUserId() : int
    {
        return $this->actor_user_id;
    }


    /**
     * @return int
     */
    public function getSubjectUserId() : int
    {
        return $this->subject_user_id;
    }


    /**
     * @return int
     */
    public function getSubjectObjId() : int
    {
        return $this->subject_obj_id;
    }


    /**
     * @return String
     */
    public function getIliasComponent() : String
    {
        return $this->ilias_component;
    }


    /**
     * @return array
     */
    public function getAdditionalData() : array
    {
        return $this->additional_data;
    }


    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->timestamp;
    }


    /**
     * @return array
     */
    public function __toArray() : array
    {
        return get_object_vars($this);
    }
}