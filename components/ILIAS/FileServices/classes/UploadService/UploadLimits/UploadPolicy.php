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
 */

declare(strict_types=1);

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class UploadPolicy
{
    public const POLICY_ID = 'policy_id';
    public const AUDIENCE_TYPE_ALL_USERS = 0;
    public const AUDIENCE_TYPE_GLOBAL_ROLE = 1;
    public const SCOPE_DEFINITION_GLOBAL = "Global";

    public function __construct(
        protected ?int $policy_id,
        protected string $title,
        protected int $upload_limit_in_mb,
        protected array $audience,
        protected int $audience_type,
        protected string $scope_definition,
        protected bool $active,
        protected ?DateTimeImmutable $valid_from,
        protected ?DateTimeImmutable $valid_until,
        protected int $owner,
        protected DateTimeImmutable $create_date,
        protected DateTimeImmutable $last_update
    ) {
    }

    public function getPolicyId(): ?int
    {
        return $this->policy_id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUploadLimitInMB(): int
    {
        return $this->upload_limit_in_mb;
    }

    public function getAudience(): array
    {
        return $this->audience;
    }

    public function getAudienceType(): int
    {
        return $this->audience_type;
    }

    public function getScopeDefinition(): string
    {
        return $this->scope_definition;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->valid_from;
    }

    public function getValidUntil(): ?DateTimeImmutable
    {
        return $this->valid_until;
    }

    public function getOwnerId(): int
    {
        return $this->owner;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->create_date;
    }

    public function getLastUpdate(): DateTimeImmutable
    {
        return $this->last_update;
    }
}
