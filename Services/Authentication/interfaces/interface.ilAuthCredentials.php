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
 * Interface of auth credentials
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
interface ilAuthCredentials
{
    /**
     * Set username
     */
    public function setUsername(string $a_name): void;

    /**
     * Get username
     */
    public function getUsername(): string;

    /**
     * Set password
     */
    public function setPassword(string $a_password): void;

    /**
     * Get password
     */
    public function getPassword(): string;

    /**
     * Set auth mode.
     * Used - for instance - for manual selection on login screen.
     * @param string $a_auth_mode
     */
    public function setAuthMode(string $a_auth_mode): void;

    /**
     * Get auth mode
     */
    public function getAuthMode(): string;
}
