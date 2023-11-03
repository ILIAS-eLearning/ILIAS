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

class ilAuthProviderDatabase extends ilAuthProvider
{
    private bool $verify_password = true;

    public function withoutPasswordVerification(): self
    {
        $clone = clone $this;
        $clone->verify_password = false;

        return $clone;
    }

    public function doAuthentication(ilAuthStatus $status): bool
    {
        /** @var ilObjUser|null $user */
        $user = ilObjectFactory::getInstanceByObjId(ilObjUser::_loginExists($this->getCredentials()->getUsername()), false);

        $this->getLogger()->debug('Trying to authenticate user: ' . $this->getCredentials()->getUsername());
        if ($user instanceof ilObjUser) {
            if ($user->getId() === ANONYMOUS_USER_ID) {
                $this->getLogger()->notice('Failed authentication for anonymous user id. ');
                $this->handleAuthenticationFail($status, 'err_wrong_login');

                return false;
            }

            if (!ilAuthUtils::isLocalPasswordEnabledForAuthMode($user->getAuthMode(true))) {
                $this->getLogger()->debug('DB authentication failed: current user auth mode does not allow local validation.');
                $this->getLogger()->debug('User auth mode: ' . $user->getAuthMode(true));
                $this->handleAuthenticationFail($status, 'err_wrong_login');

                return false;
            }

            if (!$this->verify_password || ilUserPasswordManager::getInstance()->verifyPassword($user, $this->getCredentials()->getPassword())) {
                $this->getLogger()->debug('Successfully authenticated user: ' . $this->getCredentials()->getUsername());
                $status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
                $status->setAuthenticatedUserId($user->getId());

                return true;
            }
        }

        $this->handleAuthenticationFail($status, 'err_wrong_login');

        return false;
    }
}
