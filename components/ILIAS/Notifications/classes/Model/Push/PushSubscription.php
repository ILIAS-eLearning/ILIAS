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

namespace ILIAS\Notifications\Model\Push;

readonly class PushSubscription
{
    public function __construct(
        protected string $endpoint,
        protected string $auth,
        protected string $p256dh,
    ) {
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getAuth(): string
    {
        return $this->auth;
    }

    public function getP256dh(): string
    {
        return $this->p256dh;
    }
}
