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

namespace ILIAS\Search\Presentation\Result\User;

use ILIAS\Data\URI;

class PropertiesImpl implements Properties
{
    /**
     * @param array<string, string> $other_fields
     */
    public function __construct(
        protected int $user_id,
        protected string $presentable_name,
        protected string $login,
        protected ?URI $link_to_profile,
        protected string $avatar_path,
        protected array $other_fields
    ) {
    }

    public function userID(): int
    {
        return $this->user_id;
    }

    public function presentableName(): string
    {
        return $this->presentable_name;
    }

    public function login(): string
    {
        return $this->login;
    }

    public function linkToProfile(): ?URI
    {
        return $this->link_to_profile;
    }

    public function avatarPath(): string
    {
        return $this->avatar_path;
    }

    /**
     * As they should appear as properties in the search result.
     * @return array<string, string>
     */
    public function otherFields(): array
    {
        return $this->other_fields;
    }
}
