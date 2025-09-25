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

class ilECSAuthFactory
{
    protected readonly ilLogger $logger;
    protected readonly ilCtrlInterface $ctrl;
    protected readonly ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
    }

    public function build(int $auth_type): ?ilECSAuthStrategy
    {
        return match ($auth_type) {
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE =>
                new ilLoginPageAuthStrategy($this->ctrl, $this->logger, $this->lng),
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH =>
                new ilShibbolethAuthStrategy($this->ctrl, $this->logger),
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_OIDC =>
                new ilOIDCAuthStrategy($this->ctrl, $this->logger),
            default => null,
        };
    }

    public function getAuthTypes(): array
    {
        return [
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_LOGIN_PAGE,
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_SHIBBOLETH,
            ilECSParticipantSetting::INCOMING_AUTH_TYPE_OIDC,
        ];
    }

    public function getAvailableStrategies(): array
    {
        $strategies = [];
        foreach ($this->getAuthTypes() as $auth_type) {
            $strategy = $this->build($auth_type);
            if ($strategy->isActive()) {
                $strategies[$auth_type] = $strategy;
            }
        }
        return $strategies;
    }
}
