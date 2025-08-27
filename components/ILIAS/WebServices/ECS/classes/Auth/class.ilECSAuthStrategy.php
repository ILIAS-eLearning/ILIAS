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
 */

declare(strict_types=1);

abstract class ilECSAuthStrategy
{
    abstract public function isActive(): bool;

    abstract public function getName(): string;

    abstract public function handleLogin(string $redirection_target): void;


    public static function build(int $auth_type): ?ilECSAuthStrategy
    {
        global $DIC;

        return match ($auth_type) {
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE =>
                new ilLoginPageAuthStrategy($DIC->ctrl(), $DIC->logger()->auth(), $DIC->language()),
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH =>
                new ilShibbolethAuthStrategy($DIC->ctrl(), $DIC->logger()->auth()),
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_OIDC =>
                new ilOIDCAuthStrategy($DIC->ctrl(), $DIC->logger()->auth()),
            default => null,
        };
    }

    public static function getAuthTypes(): array
    {
        return [
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE,
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH,
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_OIDC,
        ];
    }

    public static function getAvailableStrategies(): array
    {
        $strategies = [];
        foreach (self::getAuthTypes() as $auth_type) {
            $strategy = self::build($auth_type);
            if($strategy->isActive()) {
                $strategies[$auth_type] = $strategy;
            }
        }
        return $strategies;
    }
}
