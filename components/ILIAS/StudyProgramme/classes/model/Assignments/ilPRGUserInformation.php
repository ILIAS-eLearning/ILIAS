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
 * Additional information about a user, used in context of assignments
 */
class ilPRGUserInformation
{
    public const MANDATORY_FIELDS = [
        'firstname',
        'lastname',
        'login',
        'active',
        'email',
        'gender',
        'title',
        'org_units',
    ];

    public function __construct(
        protected array $user_data
    ) {
    }

    public function getAvailableUserFields(): array
    {
        return array_keys($this->user_data);
    }

    public function getUserData(string $field)
    {
        return $this->user_data[$field];
    }

    public function getFirstname(): string
    {
        return $this->user_data['firstname'];
    }
    public function getLastname(): string
    {
        return $this->user_data['lastname'];
    }
    public function isActive(): bool
    {
        return (bool)$this->user_data['active'];
    }
    public function getEmail(): string
    {
        return $this->user_data['email'];
    }
    public function getLogin(): string
    {
        return $this->user_data['login'];
    }
    public function getOrguRepresentation(): string
    {
        return $this->user_data['org_units'];
    }
    public function getFullname(): string
    {
        return $this->user_data['lastname'] . ', ' . $this->user_data['firstname'];
    }
    public function getGender(): string
    {
        return $this->user_data['gender'];
    }
    public function getTitle(): string
    {
        return $this->user_data['title'];
    }
}
