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

class ilShibbolethAuthStrategy extends ilECSAuthStrategy
{
    public function __construct(
        private ilCtrlInterface $ctrl,
        private ilLogger $logger
    ) {
    }

    public function getName(): string
    {
        return 'shib';
    }

    public function isActive(): bool
    {
        return (new ilShibbolethSettings())->isActive();
    }

    public function handleLogin(string $redirection_target): void
    {
        $this->logger->info('Redirect to shibboleth authentication');
        $this->ctrl->redirectToURL('shib_login.php?target=' . $redirection_target);
    }
}
