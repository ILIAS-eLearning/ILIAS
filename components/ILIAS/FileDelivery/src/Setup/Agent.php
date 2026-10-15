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

namespace ILIAS\FileDelivery\Setup;

use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Config;
use ILIAS\FileDelivery\Isolation\IsolationConfig;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Agent implements Setup\NamedAgent
{
    /**
     * @param string|null $isolation_artefact path of the current isolation settings, for tests
     */
    public function __construct(
        private readonly Refinery $refinery,
        private readonly ?string $isolation_artefact = null,
    ) {
    }

    public function getAgentName(): string
    {
        return 'content_isolation';
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [];
    }

    public function hasConfig(): bool
    {
        return true;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(
            static function (?array $data): FileDeliverySetupConfig {
                if ($data === null) {
                    // no "content_isolation" section, e.g. a setup update with a
                    // config.json of other components only
                    return new FileDeliverySetupConfig(false, null, false);
                }

                return new FileDeliverySetupConfig(
                    (bool) ($data['activated'] ?? false),
                    isset($data['content_domain']) && $data['content_domain'] !== ''
                        ? (string) $data['content_domain']
                        : null,
                );
            }
        );
    }

    public function getInstallObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'FileDelivery',
            true,
            new KeyRotationObjective(),
            new DeliveryMethodObjective(),
            new BaseDirObjective(),
            $this->buildIsolationObjective($config),
        );
    }

    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'FileDelivery',
            true,
            new KeyRotationObjective(),
            new DeliveryMethodObjective(),
            new BaseDirObjective(),
            $this->buildIsolationObjective($config),
        );
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new NullObjective();
    }

    public function getMigrations(): array
    {
        return [];
    }

    private function buildIsolationObjective(?Config $config): Objective
    {
        // Without a config (e.g. "setup update" without config.json, as run by
        // automated updates) the current settings are kept; writing the defaults
        // would silently deactivate the isolation of an installation.
        if (!$config instanceof FileDeliverySetupConfig || !$config->isIsolationConfigured()) {
            $current = IsolationConfig::fromArtefact($this->isolation_artefact);
            return new IsolationObjective(
                $current->isActivated(),
                $current->getContentDomain(),
            );
        }

        return new IsolationObjective(
            $config->isIsolationActivated(),
            $config->getIsolationContentDomain(),
        );
    }
}
