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

namespace ILIAS\Test\Participants;

use ILIAS\Language\Language;

class User
{
    public function __construct(
        private readonly int $user_id,
        private readonly string $login = '',
        private readonly string $firstname = '',
        private readonly string $lastname = '',
        private readonly string $matriculation = '',
        private readonly ?string $importname = null
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getImportname(): ?string
    {
        return $this->importname;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getMatriculation(): string
    {
        return $this->matriculation;
    }

    public function getDisplayName(Language $language, bool $anonymous_test = false): string
    {
        if ($this->user_id === ANONYMOUS_USER_ID && $this->importname !== null && $this->importname !== '') {
            return "{$this->importname} ({$language->txt('imported')})";
        }

        if ($anonymous_test) {
            return $language->txt('anonymous');
        }

        if ($this->login === '' && $this->firstname === '' && $this->lastname === '') {
            return $language->txt('user_deleted');
        }

        $display_name = '';

        if ($this->firstname !== '') {
            $display_name .= $this->firstname . ' ';
        }

        if ($this->lastname !== '') {
            $display_name .= $this->lastname;
        }

        return $display_name;
    }
}
