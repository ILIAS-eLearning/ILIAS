<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

abstract class ilCronJob
{
    public const SCHEDULE_TYPE_DAILY = 1;
    public const SCHEDULE_TYPE_IN_MINUTES = 2;
    public const SCHEDULE_TYPE_IN_HOURS = 3;
    public const SCHEDULE_TYPE_IN_DAYS = 4;
    public const SCHEDULE_TYPE_WEEKLY = 5;
    public const SCHEDULE_TYPE_MONTHLY = 6;
    public const SCHEDULE_TYPE_QUARTERLY = 7;
    public const SCHEDULE_TYPE_YEARLY = 8;

    protected ?int $schedule_type = null;
    protected ?int $schedule_value = null;
    protected ?Closure $dateTimeProviver = null;

    private function checkSchedule(?int $a_ts_last_run, ?int $a_schedule_type, ?int $a_schedule_value) : bool
    {
        if (null === $a_schedule_type) {
            return false;
        }

        if (null === $a_ts_last_run) {
            return true;
        }

        $now = time();
        if ($this->dateTimeProviver !== null) {
            $get_datetime = $this->dateTimeProviver;
            $now = $get_datetime()->getTimestamp();
        }

        switch ($a_schedule_type) {
            case self::SCHEDULE_TYPE_DAILY:
                $last = date('Y-m-d', $a_ts_last_run);
                $ref = date('Y-m-d', $now);
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_WEEKLY:
                $d = date('Y-m-d H:i:s', $a_ts_last_run);
                $last = date('Y-W', $a_ts_last_run);
                $ref = date('Y-W', $now);
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_MONTHLY:
                $d = date('Y-m-d H:i:s', $a_ts_last_run);
                $last = date('Y-n', $a_ts_last_run);
                $ref = date('Y-n', $now);
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_QUARTERLY:
                $last = date('Y', $a_ts_last_run) . '-' . ceil(((int) date('n', $a_ts_last_run)) / 3);
                $ref = date('Y', $now) . '-' . ceil(((int) date('n', $now)) / 3);
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_YEARLY:
                $d = date('Y-m-d H:i:s', $a_ts_last_run);
                $last = date('Y', $a_ts_last_run);
                $ref = date('Y', $now);
                return ($last !== $ref);

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

        return false;
    }

    /**
     * @param Closure|null $dateTimeProviver
     */
    public function setDateTimeProviver(?Closure $dateTimeProviver) : void
    {
        if ($dateTimeProviver !== null) {
            $r = new ReflectionFunction($dateTimeProviver);
            $return_type = $r->getReturnType();
            if ($return_type !== null) {
                $return_type = $return_type->getName();
            }
            $expected_type = DateTimeInterface::class;
            if (!is_subclass_of($return_type, $expected_type)) {
                throw new InvalidArgumentException(sprintf(
                    'The return type of the datetime provider must be of type %s',
                    $expected_type
                ));
            }

            $r = new ReflectionFunction($dateTimeProviver);
            $parameters = $r->getParameters();
            if ($parameters !== []) {
                throw new InvalidArgumentException(
                    'The datetime provider must not define any parameters',
                );
            }
        }

        $this->dateTimeProviver = $dateTimeProviver;
    }

    public function isDue(
        ?int $a_ts_last_run,
        ?int $a_schedule_type,
        ?int $a_schedule_value,
        bool $a_manual = false
    ) : bool {
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
     * @return int|null
     */
    public function getScheduleType() : ?int
    {
        if ($this->schedule_type && $this->hasFlexibleSchedule()) {
            return $this->schedule_type;
        }

        return null;
    }

    /**
     * Get current schedule value (if flexible)
     * @return int|null
     */
    public function getScheduleValue() : ?int
    {
        if ($this->schedule_value && $this->hasFlexibleSchedule()) {
            return $this->schedule_value;
        }

        return null;
    }

    /**
     * Update current schedule (if flexible)
     * @param int|null $a_type
     * @param int|null $a_value
     */
    public function setSchedule(?int $a_type, ?int $a_value) : void
    {
        if (
            $a_value &&
            $this->hasFlexibleSchedule() &&
            in_array($a_type, $this->getValidScheduleTypes(), true)
        ) {
            $this->schedule_type = $a_type;
            $this->schedule_value = $a_value;
        }
    }

    /**
     * Get all available schedule types
     * @return int[]
     */
    public function getAllScheduleTypes() : array
    {
        return [
            self::SCHEDULE_TYPE_DAILY,
            self::SCHEDULE_TYPE_WEEKLY,
            self::SCHEDULE_TYPE_MONTHLY,
            self::SCHEDULE_TYPE_QUARTERLY,
            self::SCHEDULE_TYPE_YEARLY,
            self::SCHEDULE_TYPE_IN_MINUTES,
            self::SCHEDULE_TYPE_IN_HOURS,
            self::SCHEDULE_TYPE_IN_DAYS,
        ];
    }

    /**
     * @return int[]
     */
    public function getScheduleTypesWithValues() : array
    {
        return [
            self::SCHEDULE_TYPE_IN_MINUTES,
            self::SCHEDULE_TYPE_IN_HOURS,
            self::SCHEDULE_TYPE_IN_DAYS,
        ];
    }

    /**
     * Returns a collection of all valid schedule types for a specific job
     * @return int[]
     */
    public function getValidScheduleTypes() : array
    {
        return $this->getAllScheduleTypes();
    }

    public function isManuallyExecutable() : bool
    {
        return true;
    }

    public function hasCustomSettings() : bool
    {
        return false;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form) : void
    {
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form) : bool
    {
        return true;
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active) : void
    {
    }

    /**
     * Important: This method is (also) called from the setup process, where the constructor of an ilCronJob ist NOT executed.
     * Furthermore only few dependencies may be available in the $DIC.
     * @param ilDBInterface $db
     * @param ilSetting $setting
     * @param bool $a_currently_active
     * @return void
     */
    public function activationWasToggled(ilDBInterface $db, ilSetting $setting, bool $a_currently_active) : void
    {
    }

    abstract public function getId() : string;

    abstract public function getTitle() : string;

    abstract public function getDescription() : string;

    /**
     * Is to be activated on "installation", does only work for ILIAS core cron jobs
     * @return bool
     */
    abstract public function hasAutoActivation() : bool;

    abstract public function hasFlexibleSchedule() : bool;

    abstract public function getDefaultScheduleType() : int;

    abstract public function getDefaultScheduleValue() : ?int;

    abstract public function run() : ilCronJobResult;
}
