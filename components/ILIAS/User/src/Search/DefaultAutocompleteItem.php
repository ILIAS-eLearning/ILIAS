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

namespace ILIAS\User\Search;

class DefaultAutocompleteItem implements AutocompleteItem
{
    public function __construct(
        private readonly string $login,
        private readonly string $lastname,
        private readonly string $firstname,
        private readonly string $unprocessed_search_term
    ) {
    }

    public function getTagArray(): array
    {
        $return = [
            'value' => rawurlencode($this->login),
            'searchBy' => $this->unprocessed_search_term
        ];

        if ($this->lastname === '' && $this->firstname === '') {
            return $return + [
                'display' => $this->login
            ];
        }

        return $return + [
            'display' => "{$this->login} [{$this->buildNameComponent()}]"
        ];
    }

    private function buildNameComponent(): string
    {
        if ($this->lastname !== '') {
            return "{$this->lastname}, {$this->firstname}";
        }

        return $this->firstname;
    }
}
