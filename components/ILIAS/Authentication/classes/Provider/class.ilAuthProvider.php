<?php

declare(strict_types=1);

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

/**
 * Base class for authentication providers (ldap, apache, ...)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
abstract class ilAuthProvider implements ilAuthProviderInterface
{
    private const STATUS_UNDEFINED = 0;
    private const STATUS_AUTHENTICATION_SUCCESS = 1;
    private const STATUS_AUTHENTICATION_FAILED = 2;
    private const STATUS_MIGRATION = 3;


    private ilLogger $logger;

    private ilAuthCredentials $credentials;

    private int $status = self::STATUS_UNDEFINED;
    private int $user_id = 0;
    /**
     * Constructor
     */
    public function __construct(ilAuthCredentials $credentials)
    {
        global $DIC;
        $this->logger = $DIC->logger()->auth();
        $this->credentials = $credentials;
    }

    /**
     * Get logger
     * @return \ilLogger $logger
     */
    public function getLogger(): ilLogger
    {
        return $this->logger;
    }

    /**
     * @return \ilAuthCredentials $credentials
     */
    public function getCredentials(): ilAuthCredentials
    {
        return $this->credentials;
    }

    /**
     * Handle failed authentication
     */
    protected function handleAuthenticationFail(ilAuthStatus $status, string $a_reason): bool
    {
        $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
        $status->setReason($a_reason);
        return false;
    }
}
