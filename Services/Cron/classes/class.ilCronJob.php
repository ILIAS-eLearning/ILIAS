<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job application base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
abstract class ilCronJob
{
    const SCHEDULE_TYPE_DAILY = 1;
    const SCHEDULE_TYPE_IN_MINUTES = 2;
    const SCHEDULE_TYPE_IN_HOURS = 3;
    const SCHEDULE_TYPE_IN_DAYS = 4;
    const SCHEDULE_TYPE_WEEKLY = 5;
    const SCHEDULE_TYPE_MONTHLY = 6;
    const SCHEDULE_TYPE_QUARTERLY = 7;
    const SCHEDULE_TYPE_YEARLY = 8;
        
    //
    // SCHEDULE
    //
    
    /**
     * Is job currently active?
     *
     * @param timestamp $a_ts_last_run
     * @param integer $a_ts_last_run
     * @param integer $a_ts_last_run
     * @param bool $a_ts_last_run
     * @return boolean
     */
    public function isActive($a_ts_last_run, $a_schedule_type, $a_schedule_value, $a_manual = false)
    {
        if ($a_manual) {
            return true;
        }
        if (!$this->hasFlexibleSchedule()) {
            $a_schedule_type = $this->getDefaultScheduleType();
            $a_schedule_value = $this->getDefaultScheduleValue();
        }
        return $this->checkSchedule($a_ts_last_run, $a_schedule_type, $a_schedule_value);
    }
    
    /**
     * Get current schedule type (if flexible)
     *
     * @return int
     */
    public function getScheduleType()
    {
        if ($this->hasFlexibleSchedule() && $this->schedule_type) {
            return $this->schedule_type;
        }
    }
    
    /**
     * Get current schedule value (if flexible)
     *
     * @return int
     */
    public function getScheduleValue()
    {
        if ($this->hasFlexibleSchedule() && $this->schedule_value) {
            return $this->schedule_value;
        }
    }
    
    /**
     * Update current schedule (if flexible)
     *
     * @param integer $a_type
     * @param integer $a_value
     */
    public function setSchedule($a_type, $a_value)
    {
        if ($this->hasFlexibleSchedule() &&
            in_array($a_type, $this->getValidScheduleTypes()) &&
            $a_value) {
            $this->schedule_type = $a_type;
            $this->schedule_value = $a_value;
        }
    }

    /**
     * Get all available schedule types
     * @return int[]
     */
    public function getAllScheduleTypes()
    {
        return array(
            self::SCHEDULE_TYPE_DAILY,
            self::SCHEDULE_TYPE_WEEKLY,
            self::SCHEDULE_TYPE_MONTHLY,
            self::SCHEDULE_TYPE_QUARTERLY,
            self::SCHEDULE_TYPE_YEARLY,
            self::SCHEDULE_TYPE_IN_MINUTES,
            self::SCHEDULE_TYPE_IN_HOURS,
            self::SCHEDULE_TYPE_IN_DAYS
        );
    }

    /**
     * @return int[]
     */
    public function getScheduleTypesWithValues()
    {
        return [
            ilCronJob::SCHEDULE_TYPE_IN_MINUTES,
            ilCronJob::SCHEDULE_TYPE_IN_HOURS,
            ilCronJob::SCHEDULE_TYPE_IN_DAYS
        ];
    }

    /**
     * Returns a collection of all valid schedule types for a specific job
     * @return int[]
     */
    public function getValidScheduleTypes()
    {
        return $this->getAllScheduleTypes();
    }

    /*
     * Check if next run is due
     *
     * @param timestamp $a_ts_last_run
     * @param integer $a_schedule_type
     * @param integer $a_schedule_value
     * @return boolean
     */
    protected function checkSchedule($a_ts_last_run, $a_schedule_type, $a_schedule_value)
    {
        if (!$a_schedule_type) {
            return false;
        }
        if (!$a_ts_last_run) {
            return true;
        }
        
        $now = time();
        
        switch ($a_schedule_type) {
            case self::SCHEDULE_TYPE_DAILY:
                $last = date("Y-m-d", $a_ts_last_run);
                $ref = date("Y-m-d", $now);
                return ($last != $ref);
                
            case self::SCHEDULE_TYPE_WEEKLY:
                $last = date("Y-W", $a_ts_last_run);
                $ref = date("Y-W", $now);
                return ($last != $ref);
                
            case self::SCHEDULE_TYPE_MONTHLY:
                $last = date("Y-n", $a_ts_last_run);
                $ref = date("Y-n", $now);
                return ($last != $ref);
                
            case self::SCHEDULE_TYPE_QUARTERLY:
                $last = date("Y", $a_ts_last_run) . "-" . ceil(date("n", $a_ts_last_run) / 3);
                $ref = date("Y", $now) . "-" . ceil(date("n", $now) / 3);
                return ($last != $ref);
                
            case self::SCHEDULE_TYPE_YEARLY:
                $last = date("Y", $a_ts_last_run);
                $ref = date("Y", $now);
                return ($last != $ref);
            
            case self::SCHEDULE_TYPE_IN_MINUTES:
                $diff = floor(($now - $a_ts_last_run) / 60);
                return ($diff >= $a_schedule_value);
                
            case self::SCHEDULE_TYPE_IN_HOURS:
                $diff = floor(($now - $a_ts_last_run) / (60 * 60));
                return ($diff >= $a_schedule_value);
                
            case self::SCHEDULE_TYPE_IN_DAYS:
                $diff = floor(($now - $a_ts_last_run) / (60 * 60 * 24));
                return ($diff >= $a_schedule_value);
        }
    }
    
    
    //
    // TITLE / DESCRIPTION
    //
    
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
    }
    
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
    }

    /**
     * Defines whether or not a cron job can be started manually
     * @return bool
     */
    public function isManuallyExecutable()
    {
        return true;
    }
    
    //
    // SETTINGS
    //
    
    /**
     * Has cron job any custom setting which can be edited?
     *
     * @return boolean
     */
    public function hasCustomSettings()
    {
        return false;
    }
    
    /**
     * Add custom settings to form
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
    {
    }
    
    /**
     * Save custom settings
     *
     * @param ilPropertyFormGUI $a_form
     * @return boolean
     */
    public function saveCustomSettings(ilPropertyFormGUI $a_form)
    {
        return true;
    }

    /**
     * Add external settings to form
     *
     * @param int $a_form_id
     * @param array $a_fields
     * @param bool $a_is_active
     */
    public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
    {
    }
    
    
    //
    // HOOKS
    //
    
    /**
     * Cron job status was changed
     *
     * @param bool $a_currently_active
     */
    public function activationWasToggled($a_currently_active)
    {
        // we cannot use ilObject or any higher level construct here
        // this may be called from setup, so it is limited to handling ilSetting/ilDB mostly
    }
    
    
    //
    // ABSTRACT
    //
    
    /**
     * Get id
     *
     * @return string
     */
    abstract public function getId();
    
    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    abstract public function hasAutoActivation();

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    abstract public function hasFlexibleSchedule();
    
    /**
     * Get schedule type
     *
     * @return int
     */
    abstract public function getDefaultScheduleType();
    
    /**
     * Get schedule value
     *
     * @return int|array
     */
    abstract public function getDefaultScheduleValue();
        
    /**
     * Run job
     *
     * @return ilCronJobResult
     */
    abstract public function run();
}
