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

namespace ILIAS\LegalDocuments;

use ILIAS\Data\URI;
use ilCtrlInterface;
use ILIAS\Authentication\Logout\LogoutDestinations;
use ILIAS\components\Authentication\Logout\LogoutTarget;

readonly class WithdrawalAcknowledgementLogoutTarget implements LogoutTarget
{
    public function __construct(
        private LogoutTarget $origin,
        private bool $user_withdrew_legal_docs,
        private ilCtrlInterface $ctrl,
        private string $http_path = ILIAS_HTTP_PATH
    ) {
    }

    public function asURI(): URI
    {
        if ($this->user_withdrew_legal_docs) {
            return LogoutDestinations::LOGIN_SCREEN->asURI($this->ctrl, $this->http_path);
        }

        return $this->origin->asURI();
    }
}
