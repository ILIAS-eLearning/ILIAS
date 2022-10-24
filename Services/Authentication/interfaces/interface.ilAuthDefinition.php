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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
interface ilAuthDefinition
{
    /**
     * Get auth provider instance
     */
    public function getProvider(ilAuthCredentials $credentials, string $a_auth_id): ilAuthProviderInterface;


    /**
     * Get authentication id.
     * For plugins the auth must be greater than 1000 and unique
     *
     * @see constants like in ilAuthUtils::AUTH_LDAP
     * @return int[]
     */
    public function getAuthIds(): array;


    /**
     * Get the auth id by an auth mode name.
     * the auth mode name is stored for each user in table usr_data -> auth_mode
     *
     * @see ilAuthUtils::_getAuthMode()
     */
    public function getAuthIdByName(string $a_auth_name): int;

    /**
     * Get auth name by auth id
     * @param int $a_auth_id
     */
    public function getAuthName(int $a_auth_id): string;

    /**
     * Check if auth mode is active
     */
    public function isAuthActive(int $a_auth_id): bool;

    /**
     * Check whther authentication supports sequenced authentication
     * @see ilAuthContainerMultiple
     */
    public function supportsMultiCheck(int $a_auth_id): bool;

    /**
     * Check if an external account name is required for this authentication method
     * Normally this should return true
     */
    public function isExternalAccountNameRequired(int $a_auth_id): bool;

    /**
     * Check if authentication method allows password modifications
     */
    public function isPasswordModificationAllowed(int $a_auth_id): bool;

    /**
     * Get local password validation type
     * One of
     * ilAuthUtils::LOCAL_PWV_FULL
     * ilAuthUtils::LOCAL_PWV_NO
     * ilAuthUtils::LOCAL_PWV_USER
     */
    public function getLocalPasswordValidationType(int $a_auth_id): int;

    /**
     * Get an array of options for "multiple auth mode" selection
     * array(
     *	AUTH_ID => array( 'txt' => NAME)
     * )
     */
    public function getMultipleAuthModeOptions(int $a_auth_id): array;
}
