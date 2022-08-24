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
 * Interface ilSamlAuth
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilSamlAuth
{
    /**
     * @return string
     */
    public function getAuthId(): string;

    /**
     * Protect a script resource with a SAML auth.
     */
    public function protectResource(): void;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function storeParam(string $key, $value): void;

    /**
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * @param string $key
     * @return mixed
     */
    public function popParam(string $key);

    /**
     * @param string $key
     * @return mixed
     */
    public function getParam(string $key);

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param string $returnUrl
     */
    public function logout(string $returnUrl = ''): void;

    /**
     * @return ilSamlIdpDiscovery
     */
    public function getIdpDiscovery(): ilSamlIdpDiscovery;

    /**
     * @return array
     */
    public function getAuthDataArray(): array;
}
