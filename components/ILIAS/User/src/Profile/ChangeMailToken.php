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

namespace ILIAS\User\Profile;

use ILIAS\Data\URI;
use ILIAS\StaticURL\Builder\URIBuilder;
use ILIAS\User\StaticURLHandler;

class ChangeMailToken
{
    public function __construct(
        private readonly int $user_id,
        private readonly string $current_email,
        private readonly string $new_email,
        private readonly int $created_timestamp,
        private readonly ChangeMailStatus $status = ChangeMailStatus::Login,
        private ?string $token = null
    ) {
        $this->token ??= $this->buildValidToken();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getCurrentEmail(): string
    {
        return $this->current_email;
    }

    public function getNewEmail(): string
    {
        return $this->new_email;
    }

    public function getCreatedTimestamp(): int
    {
        return $this->created_timestamp;
    }

    public function getStatus(): ChangeMailStatus
    {
        return $this->status;
    }

    public function isTokenValidForCurrentStatus(\ilSetting $settings): bool
    {
        if ($this->buildValidToken() === $this->token
            && time() < $this->created_timestamp + $this->status->getValidity($settings)) {
            return true;
        }
        return false;
    }

    public function getUriForStatus(URIBuilder $uri_builder): URI
    {
        return $uri_builder->build(
            StaticURLHandler::NAMESPACE,
            null,
            [StaticUrlHandler::CHANGE_EMAIL_OPERATIONS, $this->token]
        );
    }

    private function buildValidToken(): string
    {
        return hash('md5', "{$this->created_timestamp}-{$this->user_id}-{$this->current_email}-{$this->status->value}");
    }
}
