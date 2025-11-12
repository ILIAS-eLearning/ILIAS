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
use ILIAS\Setup\Artifact;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\Config;
use ILIAS\Setup\Environment;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\NullConfig;

class PushNotificationObjective extends BuildArtifactObjective
{
    /**
     * @param PushProviderInterface[] $provider
     */
    public function __construct(protected readonly array $provider, protected readonly Config $config)
    {
    }

    public function build(): Artifact
    {
        return new ArrayArtifact(array_map(fn($p) => $p::class, $this->provider));
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilSettingsFactoryExistsObjective(),
        ];
    }

    public function getArtifactName(): string
    {
        return 'push_providers';
    }

    public function achieve(Environment $environment): Environment
    {
        if (!($this->config instanceof NullConfig)) {
            $settings = $environment->getResource(Environment::RESOURCE_SETTINGS_FACTORY)->settingsFor('notifications');

            $private_key_path = $this->config->getPrivateKeyPath();
            if (file_exists($private_key_path)) {
                $settings->set('private_key_path', $private_key_path);

                $details = openssl_pkey_get_details(openssl_pkey_get_private('file://' . $private_key_path))['ec'];
                $settings->set(
                    'application_server_key',
                    str_replace(
                        ['+', '/', '='],
                        ['-', '_', ''],
                        base64_encode(hex2bin('04') . $details['x'] . $details['y'])
                    )
                );
            } else {
                throw new InvalidArgumentException('Notification key path is invalid!');
            }
        }

        return parent::achieve($environment);
    }
}
