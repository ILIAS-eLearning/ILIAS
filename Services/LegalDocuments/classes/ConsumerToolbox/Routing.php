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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ilCtrl;
use Closure;

class Routing
{
    /**
     * @param Closure(): void $redirect_to_starting_page
     * @param Closure(): string $logout_url
     */
    public function __construct(
        private readonly ilCtrl $ctrl,
        private readonly SelectSetting $session,
        private readonly Closure $redirect_to_starting_page,
        private readonly Closure $logout_url
    ) {
    }

    public function ctrl(): ilCtrl
    {
        return $this->ctrl;
    }

    public function logoutUrl(): string
    {
        return ($this->logout_url)();
    }

    public function redirectToOriginalTarget(): void
    {
        if ($this->originalTarget()->value() === null) {
            ($this->redirect_to_starting_page)();
            return;
        }

        $target = $this->originalTarget()->value();
        $this->originalTarget()->update(null);
        $this->ctrl()->redirectToURL($target);
    }

    private function originalTarget(): Setting
    {
        return $this->session->typed('orig_request_target', fn(Marshal $m) => $m->nullable($m->string()));
    }
}
