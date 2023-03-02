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

abstract class ilCronJob
{
    final public const SCHEDULE_TYPE_DAILY = 1;
    final public const SCHEDULE_TYPE_IN_MINUTES = 2;
    final public const SCHEDULE_TYPE_IN_HOURS = 3;
    final public const SCHEDULE_TYPE_IN_DAYS = 4;
    final public const SCHEDULE_TYPE_WEEKLY = 5;
    final public const SCHEDULE_TYPE_MONTHLY = 6;
    final public const SCHEDULE_TYPE_QUARTERLY = 7;
    final public const SCHEDULE_TYPE_YEARLY = 8;

    protected ?int $schedule_type = null;
    protected ?int $schedule_value = null;
    protected ?Closure $date_time_provider = null;

    private function checkWeeklySchedule(DateTimeImmutable $last_run, DateTimeImmutable $now): bool
    {
        $last_year = (int) $last_run->format('Y');
        $now_year = (int) $now->format('Y');

        if ($last_year > $now_year) {
            // Should never happen, don't execute otherwise
            return false;
        }

        $last_week = $last_run->format('W');
        $now_week = $now->format('W');

        if ($last_week !== $now_week) {
            // Week differs, always execute the job
            return true;
        }

        // For all following cases, the week number is always identical

        $last_month = (int) $last_run->format('m');
        $now_month = (int) $now->format('m');

        $is_within_same_week_in_same_year = ($last_year . '-' . $last_week) === ($now_year . '-' . $now_week);
        if ($is_within_same_week_in_same_year) {
            // Same week in same year, only execute if the month differs (2022-52 is valid for January and December)
            return $last_month !== $now_month && $now->diff($last_run)->d > 7;
        }

        if ($now_year - $last_year > 1) {
            // Always execute if the difference of years is greater than 1
            return true;
        }

        // Execute for week number 52 in 2022 (last run) and week number 52 in December of 2022 (now), but not for week number 52 in January of 2022 (now)
        return $last_month === $now_month;
    }

    private function checkSchedule(?DateTimeImmutable $last_run, ?int $schedule_type, ?int $schedule_value): bool
    {
        if (null === $schedule_type) {
            return false;
        }

        if (null === $last_run) {
            return true;
        }

        $now = new DateTimeImmutable('@' . time());
        if ($this->date_time_provider !== null) {
            $now = ($this->date_time_provider)();
        }

        switch ($schedule_type) {
            case self::SCHEDULE_TYPE_DAILY:
                $last = $last_run->format('Y-m-d');
                $ref = $now->format('Y-m-d');
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_WEEKLY:
                return $this->checkWeeklySchedule($last_run, $now);

            case self::SCHEDULE_TYPE_MONTHLY:
                $last = $last_run->format('Y-n');
                $ref = $now->format('Y-n');
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_QUARTERLY:
                $last = $last_run->format('Y') . '-' . ceil(((int) $last_run->format('n')) / 3);
                $ref = $now->format('Y') . '-' . ceil(((int) $now->format('n')) / 3);
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_YEARLY:
                $last = $last_run->format('Y');
                $ref = $now->format('Y');
                return ($last !== $ref);

            case self::SCHEDULE_TYPE_IN_MINUTES:
                $diff = floor(($now->getTimestamp() - $last_run->getTimestamp()) / 60);
                return ($diff >= $schedule_value);

            case self::SCHEDULE_TYPE_IN_HOURS:
                $diff = floor(($now->getTimestamp() - $last_run->getTimestamp()) / (60 * 60));
                return ($diff >= $schedule_value);

            case self::SCHEDULE_TYPE_IN_DAYS:
                $diff = floor(($now->getTimestamp() - $last_run->getTimestamp()) / (60 * 60 * 24));
                return ($diff >= $schedule_value);
        }

        return false;
    }

    public function setDateTimeProvider(?Closure $date_time_provider): void
    {
        if ($date_time_provider !== null) {
            $r = new ReflectionFunction($date_time_provider);
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

            $r = new ReflectionFunction($date_time_provider);
            $parameters = $r->getParameters();
            if ($parameters !== []) {
                throw new InvalidArgumentException(
                    'The datetime provider must not define any parameters',
                );
            }
        }

        $this->date_time_provider = $date_time_provider;
    }

    public function isDue(
        ?DateTimeImmutable $last_run,
        ?int $schedule_type,
        ?int $schedule_value,
        bool $is_manually_executed = false
    ): bool {
        if ($is_manually_executed) {
            return true;
        }

        if (!$this->hasFlexibleSchedule()) {
            $schedule_type = $this->getDefaultScheduleType();
            $schedule_value = $this->getDefaultScheduleValue();
        }

        return $this->checkSchedule($last_run, $schedule_type, $schedule_value);
    }

    /**
     * Get current schedule type (if flexible)
     */
    public function getScheduleType(): ?int
    {
        if ($this->schedule_type && $this->hasFlexibleSchedule()) {
            return $this->schedule_type;
        }

        return null;
    }

    /**
     * Get current schedule value (if flexible)
     */
    public function getScheduleValue(): ?int
    {
        if ($this->schedule_value && $this->hasFlexibleSchedule()) {
            return $this->schedule_value;
        }

        return null;
    }

    /**
     * Update current schedule (if flexible)
     */
    public function setSchedule(?int $a_type, ?int $a_value): void
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
    public function getAllScheduleTypes(): array
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
    public function getScheduleTypesWithValues(): array
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
    public function getValidScheduleTypes(): array
    {
        return $this->getAllScheduleTypes();
    }

    public function isManuallyExecutable(): bool
    {
        return true;
    }

    public function hasCustomSettings(): bool
    {
        return false;
    }

    public function addCustomSettingsToForm(ilPropertyFormGUI $a_form): void
    {
    }

    public function saveCustomSettings(ilPropertyFormGUI $a_form): bool
    {
        return true;
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active): void
    {
    }

    /**
     * Important: This method is (also) called from the setup process, where the constructor of an ilCronJob ist NOT executed.
     * Furthermore only few dependencies may be available in the $DIC.
     */
    public function activationWasToggled(ilDBInterface $db, ilSetting $setting, bool $a_currently_active): void
    {
    }

    abstract public function getId(): string;

    abstract public function getTitle(): string;

    abstract public function getDescription(): string;

    /**
     * Is to be activated on "installation", does only work for ILIAS core cron jobs
     */
    abstract public function hasAutoActivation(): bool;

    abstract public function hasFlexibleSchedule(): bool;

    abstract public function getDefaultScheduleType(): int;

    abstract public function getDefaultScheduleValue(): ?int;

    abstract public function run(): ilCronJobResult;
}
