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

namespace ILIAS\LegalDocuments;

/**
 * @template A
 */
class SelectionMap
{
    private readonly ?string $default_selection;

    /**
     * @param array<string, A> $conditions
     */
    public function __construct(
        private readonly array $conditions = [],
        ?string $default_selection = null
    ) {
        $this->default_selection = $default_selection ?? key($this->conditions);
    }

    /**
     * @return array<string, A>
     */
    public function choices(): array
    {
        return $this->conditions;
    }

    public function defaultSelection(): ?string
    {
        return $this->default_selection;
    }
}
