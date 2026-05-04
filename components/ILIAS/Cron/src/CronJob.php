<?php

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

declare(strict_types=1);

namespace ILIAS\Cron;

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;

/**
 * A scheduled background job executed by the ILIAS cron subsystem.
 *
 * Implementations typically extend {@see AbstractCronJob}.
 */
interface CronJob
{
    /**
     * ILIAS component root class name contributed from `<Component>.php`.
     */
    public function getComponent(): string;

    /**
     * Component-specific initialization (former constructor body). Invoked when a job is prepared
     * for execution. Once the jobs are properly constructed via "<Component>.php", this is obsolete.
     */
    public function init(): void;

    /**
     * @param \Closure():\DateTimeInterface|null $date_time_provider
     */
    public function setDateTimeProvider(?\Closure $date_time_provider): void;

    public function isDue(
        ?\DateTimeImmutable $last_run,
        ?JobScheduleType $schedule_type,
        ?int $schedule_value,
        bool $is_manually_executed = false
    ): bool;

    /**
     * Get current schedule type (if flexible)
     */
    public function getScheduleType(): ?JobScheduleType;

    /**
     * Get current schedule value (if flexible)
     */
    public function getScheduleValue(): ?int;

    /**
     * Update current schedule (if flexible)
     */
    public function setSchedule(?JobScheduleType $a_type, ?int $a_value): void;

    /**
     * Get all available schedule types
     *
     * @return list<JobScheduleType>
     */
    public function getAllScheduleTypes(): array;

    /**
     * @return list<JobScheduleType>
     */
    public function getScheduleTypesWithValues(): array;

    /**
     * Returns a collection of all valid schedule types for a specific job
     *
     * @return list<JobScheduleType>
     */
    public function getValidScheduleTypes(): array;

    public function isManuallyExecutable(): bool;

    public function hasCustomSettings(): bool;

    /**
     * @deprecated
     */
    #[\Deprecated('Will be removed without any alternative, KS/UI forms will be expected', since: '11.0')]
    public function usesLegacyForms(): bool;

    public function getCustomConfigurationInput(
        \ILIAS\UI\Factory $ui_factory,
        \ILIAS\Refinery\Factory $factory,
        \ilLanguage $lng
    ): \ILIAS\UI\Component\Input\Container\Form\FormInput;

    /**
     * @deprecated
     */
    #[\Deprecated('Will be removed without any alternative, KS/UI forms will be expected', since: '11.0')]
    public function addCustomSettingsToForm(\ilPropertyFormGUI $a_form): void;

    /**
     * @param mixed $form_data The form data provided by the KS (\ILIAS\UI\Component\Input\Container\Container::getData)).
     *                         The types and structure depend on the structure provided by `getCustomConfigurationInput`.
     *                         It might be a single value or a `array<string, mixed>`-like structure.
     */
    public function saveCustomConfiguration(mixed $form_data): void;

    /**
     * @deprecated
     */
    #[\Deprecated('Will be removed without any alternative, KS/UI forms will be expected', since: '11.0')]
    public function saveCustomSettings(\ilPropertyFormGUI $a_form): bool;

    /**
     * @param array<string, mixed> $a_fields
     */
    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active): void;

    /**
     * Important: This method is (also) called from the setup process, where {@see init()} may not have run.
     * Furthermore only few dependencies may be available in the $DIC.
     */
    public function activationWasToggled(\ilDBInterface $db, \ilSetting $setting, bool $a_currently_active): void;

    public function getId(): string;

    public function getTitle(): string;

    public function getDescription(): string;

    /**
     * Is to be activated on "installation", does only work for ILIAS core cron jobs
     */
    public function hasAutoActivation(): bool;

    public function hasFlexibleSchedule(): bool;

    public function getDefaultScheduleType(): JobScheduleType;

    public function getDefaultScheduleValue(): ?int;

    public function run(): JobResult;
}
