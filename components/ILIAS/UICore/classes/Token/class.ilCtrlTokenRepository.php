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

/**
 * Class ilCtrlTokenRepository
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTokenRepository implements ilCtrlTokenRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getToken(): ilCtrlTokenInterface
    {
        $token = $this->fetchToken() ?? $this->generateToken();

        $this->storeToken($token);

        return $token;
    }

    /**
     * Returns the currently stored token from the session.
     *
     * @return ilCtrlTokenInterface|null
     */
    protected function fetchToken(): ?ilCtrlTokenInterface
    {
        if (ilSession::has(ilCtrlInterface::PARAM_CSRF_TOKEN)) {
            return unserialize(ilSession::get(ilCtrlInterface::PARAM_CSRF_TOKEN), [ilCtrlTokenInterface::class]);
        }

        return null;
    }

    /**
     * Stores the given token in the curren session.
     *
     * @param ilCtrlTokenInterface $token
     */
    protected function storeToken(ilCtrlTokenInterface $token): void
    {
        ilSession::set(ilCtrlInterface::PARAM_CSRF_TOKEN, serialize($token));
    }

    /**
     * Returns a cryptographically secure token.
     *
     * @return ilCtrlToken
     */
    protected function generateToken(): ilCtrlTokenInterface
    {
        // random_bytes() is cryptographically secure but
        // depends on the system it's running on. If the
        // generation fails, we use a less secure option
        // that is available for sure.

        try {
            $token = bin2hex(random_bytes(32));
        } catch (Throwable $t) {
            $token = md5(uniqid((string) time(), true));
        }

        return new ilCtrlToken($token);
    }
}
