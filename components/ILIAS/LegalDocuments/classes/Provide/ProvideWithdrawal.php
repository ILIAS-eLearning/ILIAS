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

namespace ILIAS\LegalDocuments\Provide;

use ilAuthSession;
use ilCtrl;
use ilSession;
use ilStartUpGUI;
use ilPersonalProfileGUI;
use Closure;

class ProvideWithdrawal
{
    /** @var Closure(int): void */
    private readonly Closure $close_session_context;

    /** @var Closure(array<string, string>): string */
    private readonly Closure $logout_url;

    /**
     * @param null|Closure(array): string $logout_url
     * @param null|Closure(int): void $close_session_context
     */
    public function __construct(
        private readonly string $id,
        private readonly ilCtrl $ctrl,
        private readonly ilAuthSession $auth_session,
        ?Closure $logout_url = null,
        ?Closure $close_session_context = null
    ) {
        $this->logout_url = $logout_url ?? ilStartUpGUI::logoutUrl(...);
        $this->close_session_context = $close_session_context ?? ilSession::setClosingContext(...);
    }

    public function beginProcessURL(): string
    {
        return ($this->logout_url)(['withdraw_consent' => $this->id]);
    }

    /**
     * @param array<string, string> $additional_url_parameters
     */
    public function finishAndLogout(array $additional_url_parameters = []): void
    {
        ($this->close_session_context)(ilSession::SESSION_CLOSE_USER);
        $this->auth_session->logout();

        $this->ctrl->redirectToURL('login.php?' . http_build_query(array_merge($additional_url_parameters, [
            'withdrawal_finished' => $this->id,
            'cmd' => 'force_login',
        ])));
    }
}
