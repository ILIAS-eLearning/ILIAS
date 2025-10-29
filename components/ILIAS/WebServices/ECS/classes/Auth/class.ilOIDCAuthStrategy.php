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

class ilOIDCAuthStrategy extends ilECSAuthStrategy
{
    public function __construct(
        private readonly ilCtrlInterface $ctrl,
        private readonly ilLogger $logger
    ) {
    }

    public function isActive(): bool
    {
        return ilOpenIdConnectSettings::getInstance()->getActive();
    }

    public function getName(): string
    {
        return 'oidc';
    }

    public function handleLogin(string $redirection_target): void
    {
        // the target may be '/goto.php/crs/123' which is not supported by oidc (see `LegacyGotoHandler::handle`)
        // instead it should be 'crs_123' and will be built to '/goto.php/?target=crs_123' in oidc login page
        if (str_contains($redirection_target, 'goto.php/')) {
            $redirection_target = ltrim(str_replace('goto.php', '', $redirection_target), '/');
            $redirection_target = str_replace('/', '_', $redirection_target);
        }

        $this->logger->info('Redirect to oidc authentication');
        $this->ctrl->redirectToURL('openidconnect.php?target=' . $redirection_target);
    }
}
