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
    public function getAuthId(): string;

    /**
     * Protect a script resource with a SAML auth.
     */
    public function protectResource(): void;

    /**
     * @param mixed $value
     */
    public function storeParam(string $key, $value): void;

    public function isAuthenticated(): bool;

    /**
     * @return mixed
     */
    public function popParam(string $key);

    /**
     * @return mixed
     */
    public function getParam(string $key);

    public function getAttributes(): array;

    public function logout(string $returnUrl = ''): void;

    public function getIdpDiscovery(): ilSamlIdpDiscovery;

    public function getAuthDataArray(): array;
}
