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
 * Authentication plugin
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
abstract class ilAuthPlugin extends ilPlugin implements ilAuthDefinition
{
    /**
     * Does your AuthProvider needs "ext_account"? return true, false otherwise.
     *
     * @param int $a_auth_id
     */
    abstract public function isExternalAccountNameRequired(int $a_auth_id): bool;

    /**
     * @return ilAuthProviderInterface Your special instance of
     *         ilAuthProviderInterface where all the magic
     *         happens. You get the ilAuthCredentials and
     *         the user-selected (Sub-)-Mode as well.
     */
    abstract public function getProvider(ilAuthCredentials $credentials, string $a_auth_id): ilAuthProviderInterface;

    /**
     *
     * @return string Text-Representation of your Auth-mode.
     */
    abstract public function getAuthName(int $a_auth_id): string;

    /**
     * @return array return an array with all your sub-modes (options) if you have some.
     *         The array comes as ['subid1' => 'Name of the Sub-Mode One', ...]
     *         you can return an empty array if you have just a "Main"-Mode.
     */
    abstract public function getMultipleAuthModeOptions(int $a_auth_id): array;

    /**
     *
     * @param int $id
     *            (can be your Mode or – if you have any – a Sub-mode.
     */
    abstract public function isAuthActive(int $a_auth_id): bool;

    /**
     *
     * @return array IDs of your Auth-Modes and Sub-Modes.
     */
    abstract public function getAuthIds(): array;
}
