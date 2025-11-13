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

namespace ILIAS\ApacheAuth\Setup;

use ILIAS\Setup;
use ILIAS\ApacheAuth\UsernameProvider;

class ApacheAuthAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @param list<UsernameProvider\UsernameProvider> $username_provider_contributions
     */
    public function __construct(
        private readonly array $username_provider_contributions
    ) {
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): \ILIAS\Refinery\Transformation
    {
        throw new \LogicException(self::class . ' has no Config.');
    }

    public function getInstallObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Installing ApacheAuthentication',
            false,
            new UsernameProvider\CollectUsernameProvidersObjective($this->username_provider_contributions)
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Building ApacheAuthentication',
            false,
            new UsernameProvider\CollectUsernameProvidersObjective($this->username_provider_contributions)
        );
    }

    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Updating ApacheAuthentication',
            false,
            new UsernameProvider\CollectUsernameProvidersObjective($this->username_provider_contributions)
        );
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getMigrations(): array
    {
        return [];
    }
}
