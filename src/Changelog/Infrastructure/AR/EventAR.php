<?php

namespace ILIAS\Changelog\Infrastructure\AR;

use ActiveRecord;
use Exception;

/**
 * Class EventAR
 *
 * @package ILIAS\Changelog\Infrastructure\AR
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EventAR extends ActiveRecord
{

    const TABLE_NAME = 'changelog_events';


    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @var int
     *
     * @con_is_primary  true
     * @con_sequence    true
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $id;
    /**
     * @var EventID
     *
     * @con_is_unique   true
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      128
     */
    protected $event_id;
    /**
     * @var String
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      128
     * @con_is_notnull  true
     */
    protected $event_name;
    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $actor_user_id;
    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $subject_user_id;
    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   integer
     * @con_length      8
     */
    protected $subject_obj_id;
    /**
     * @var String
     *
     * @con_has_field   true
     * @con_fieldtype   text
     * @con_length      128
     * @con_is_notnull  true
     */
    protected $ilias_component;
    /**
     * @var array
     *
     * @con_has_field   true
     * @con_fieldtype   clob
     */
    protected $additional_data;
    /**
     * @var int
     *
     * @con_has_field   true
     * @con_fieldtype   timestamp
     * @con_is_notnull  true
     */
    protected $timestamp;


    /**
     * @return EventID
     */
    public function getEventId() : EventID
    {
        return $this->event_id;
    }


    /**
     * @param EventID $event_id
     */
    public function setEventId(EventID $event_id)
    {
        $this->event_id = $event_id;
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
    public function getEventName() : String
    {
        return $this->event_name;
    }


    /**
     * @param String $event_name
     */
    public function setEventName(String $event_name)
    {
        $this->event_name = $event_name;
    }


    /**
     * @return int
     */
    public function getTimestamp() : int
    {
        return $this->timestamp;
    }


    /**
     * @param int $timestamp
     */
    public function setTimestamp(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }


    /**
     * @return int
     */
    public function getActorUserId() : int
    {
        return $this->actor_user_id;
    }


    /**
     * @param int $actor_user_id
     */
    public function setActorUserId(int $actor_user_id)
    {
        $this->actor_user_id = $actor_user_id;
    }


    /**
     * @return int
     */
    public function getSubjectUserId() : int
    {
        return $this->subject_user_id;
    }


    /**
     * @param int $subject_user_id
     */
    public function setSubjectUserId(int $subject_user_id)
    {
        $this->subject_user_id = $subject_user_id;
    }


    /**
     * @return int
     */
    public function getSubjectObjId() : int
    {
        return $this->subject_obj_id;
    }


    /**
     * @param int $subject_obj_id
     */
    public function setSubjectObjId(int $subject_obj_id)
    {
        $this->subject_obj_id = $subject_obj_id;
    }


    /**
     * @return String
     */
    public function getILIASComponent() : String
    {
        return $this->ilias_component;
    }


    /**
     * @param String $ilias_component
     */
    public function setILIASComponent(String $ilias_component)
    {
        $this->ilias_component = $ilias_component;
    }


    /**
     * @return array
     */
    public function getAdditionalData() : array
    {
        return $this->additional_data;
    }


    /**
     * @param array $additional_data
     */
    public function setAdditionalData(array $additional_data)
    {
        $this->additional_data = $additional_data;
    }


    /**
     * @param $field_name
     *
     * @return string|null
     */
    public function sleep($field_name)
    {
        switch ($field_name) {
            case 'event_id':
                return $this->event_id->getId();
            case 'timestamp':
                return date('Y-m-d H:i:s', $this->getTimestamp());
            case 'additional_data':
                return json_encode($this->getAdditionalData());
            default:
                return null;
        }
    }


    /**
     * @param $field_name
     * @param $field_value
     *
     * @return EventID|null
     * @throws Exception
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case 'event_id':
                return new EventID($field_value);
            case 'timestamp':
                return strtotime($field_value);
            case 'additional_data':
                return json_decode($field_value, true);
            default:
                return null;
        }
    }
}