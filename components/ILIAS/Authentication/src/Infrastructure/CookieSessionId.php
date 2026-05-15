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

namespace ILIAS\Authentication\Infrastructure;

use ILIAS\Authentication\Domain\SubmittedSessionId;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Reads the submitted session id from the request cookies.
 */
final readonly class CookieSessionId implements SubmittedSessionId
{
    public function __construct(
        private RequestWrapper $cookies,
        private Refinery $refinery
    ) {
    }

    public function get(): string
    {
        return $this->cookies->retrieve(
            (string) session_name(),
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
    }
}
