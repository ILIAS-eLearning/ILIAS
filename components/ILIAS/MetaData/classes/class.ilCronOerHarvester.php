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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\MetaData\OERHarvester\Services\Services as PublishingServices;
use ILIAS\MetaData\OERHarvester\CronJob\Results\Wrapper as ResultWrapper;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;

/**
 * Cron job for definition for oer harvesting
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCronOerHarvester extends CronJob
{
    public const string CRON_JOB_IDENTIFIER = 'meta_oer_harvester';
    protected const int DEFAULT_SCHEDULE_VALUE = 1;

    private ilLogger $logger;
    private PresentationUtilities $presentation_utilities;
    private PublishingServices $publishing_services;

    public function __construct()
    {
        global $DIC;

        $internal_services = new InternalServices($DIC);

        $this->logger = $internal_services->dic()->logger()->meta();
        $this->presentation_utilities = $internal_services->presentation()->utilities();
        $this->publishing_services = $internal_services->OERHarvester();
    }

    public function usesLegacyForms(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return $this->presentation_utilities->txt('meta_oer_harvester');
    }

    public function getDescription(): string
    {
        return $this->presentation_utilities->txt('meta_oer_harvester_desc');
    }

    public function getId(): string
    {
        return self::CRON_JOB_IDENTIFIER;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return self::DEFAULT_SCHEDULE_VALUE;
    }

    public function run(): JobResult
    {
        $this->logger->info('Started cron oer harvester.');
        $automatic_publisher = $this->publishing_services->automaticPublisher();
        $res = $automatic_publisher->run(new ResultWrapper(new JobResult()));
        $this->logger->info('cron oer harvester finished');

        return $res->get();
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active): void
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_META_COPYRIGHT:

                $a_fields['meta_oer_harvester'] =
                    (
                        $a_is_active ?
                        $this->presentation_utilities->txt('enabled') :
                        $this->presentation_utilities->txt('disabled')
                    );
                break;
        }
    }
}
