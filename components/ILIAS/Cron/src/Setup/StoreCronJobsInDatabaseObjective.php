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

namespace ILIAS\Cron\Setup;

use ILIAS\Cron\CronJobRegistry;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Cron\Job\Repository\JobRepositoryImpl;

/**
 * Persists cron job metadata from the {@see CronJobRegistry} into `cron_job`.
 */
final class StoreCronJobsInDatabaseObjective implements Objective
{
    /**
     * @param list<\ILIAS\Cron\CronJob> $jobs
     */
    public function __construct(private readonly array $jobs)
    {
    }

    public function getHash(): string
    {
        return hash('sha256', self::class);
    }

    public function getLabel(): string
    {
        return 'Cron jobs from component contributions are stored in the database.';
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new \ilDatabaseUpdatedObjective(),
            new \ilSettingsFactoryExistsObjective(),
            new \ilComponentRepositoryExistsObjective(),
            new \ilComponentFactoryExistsObjective(),
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        /** @var \ilSettingsFactory $settings_factory */
        $settings_factory = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY);
        /** @var \ilComponentRepository $component_repository */
        $component_repository = $environment->getResource(Environment::RESOURCE_COMPONENT_REPOSITORY);
        /** @var \ilComponentFactory $component_factory */
        $component_factory = $environment->getResource(Environment::RESOURCE_COMPONENT_FACTORY);

        $registry = new \ILIAS\Cron\InMemoryCronJobRegistry($this->jobs);

        $language = new \ilSetupLanguage('en');
        $logger_factory = new class () implements \ILIAS\Logging\LoggerFactory {
            public function getComponentLogger($a_component_id): \ilLogger
            {
            }
        };

        $job_repository = new JobRepositoryImpl(
            $registry,
            $db,
            $settings_factory->settingsFor(),
            new \ILIAS\components\Logging\NullLogger(),
            $component_repository,
            $component_factory,
            $language,
            $logger_factory
        );

        $job_repository->syncJobsFromRegistry();

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        return true;
    }
}
