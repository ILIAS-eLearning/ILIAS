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

namespace ILIAS\LTI\LTI1p1\Consumer;

/**
 * OAuth1 key and secret set on a single LTI consumer object, used instead of the provider
 * credentials when the provider allows customizing them. Stored in lti_consumer_settings.
 */
final class ObjectCredentials
{
    private string $key = '';
    private string $secret = '';

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function assignFromDbRow(array $row): void
    {
        if (array_key_exists('launch_key', $row)) {
            $this->setKey((string) $row['launch_key']);
        }
        if (array_key_exists('launch_secret', $row)) {
            $this->setSecret((string) $row['launch_secret']);
        }
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public function getDbFields(): array
    {
        return [
            'launch_key' => ['text', $this->getKey()],
            'launch_secret' => ['text', $this->getSecret()],
        ];
    }
}
