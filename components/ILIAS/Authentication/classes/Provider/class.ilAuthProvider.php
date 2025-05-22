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

abstract class ilAuthProvider implements ilAuthProviderInterface
{
    private ilLogger $logger;
    private ilAuthCredentials $credentials;

    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
        $this->credentials = $credentials;
    }

    public function getLogger(): ilLogger
    {
        return $this->logger;
    }

    public function getCredentials(): ilAuthCredentials
    {
        return $this->credentials;
    }

    protected function handleAuthenticationFail(ilAuthStatus $status, string $a_reason): bool
    {
        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
        $status->setReason($a_reason);
        return false;
    }
}
