<?php

namespace ILIAS\Changelog\DBUpdate;

use ActiveRecord;
use ILIAS\Changelog\Infrastructure\AR\EventID;

/**
 * Class EventAR_V1
 *
 * @package ILIAS\Changelog\DBUpdate
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class EventAR_V1 extends ActiveRecord
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
}