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

use ILIAS\Notifications\Interfaces\PushProviderInterface;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Setup\Config;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective;
use ILIAS\Setup\NullConfig;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\ObjectiveCollection;

class ilNotificationsSetupAgent implements Agent
{
    use HasNoNamedObjective;

    /**
     * @param PushProviderInterface[] $provider
     */
    public function __construct(protected readonly Factory $refinery, protected readonly array $provider)
    {
    }

    public function hasConfig(): bool
    {
        return true;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(static function ($data): Config {
            if ($data !== null) {
                if (($data['private_key_path'] ?? '') === '') {
                    throw new InvalidArgumentException(
                        'No valid private key path given for push notifications. Please check your config file.'
                    );
                }
                return new PushNotificationConfig($data['private_key_path']);
            } else {
                return new NullConfig();
            }
        });
    }

    public function getInstallObjective(?Config $config = null): Objective
    {
        return new PushNotificationObjective($this->provider, $config ?? new NullConfig());
    }

    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Notification Objectives',
            true,
            new ilTreeAdminNodeAddedObjective('nota', 'Notification Service Administration Object'),
            new ilDatabaseUpdateStepsExecutedObjective(new ilNotificationUpdateSteps()),
            new ilDatabaseUpdateStepsExecutedObjective(new ilNotificationUpdateSteps11()),
            new PushNotificationObjective($this->provider, $config ?? new NullConfig())
        );
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilNotificationUpdateSteps());
    }

    public function getMigrations(): array
    {
        return [];
    }
}
