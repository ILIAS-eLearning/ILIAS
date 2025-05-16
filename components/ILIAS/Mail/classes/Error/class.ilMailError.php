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

class ilMailError
{
    /**
     * @param list<string> $place_holder_values
     */
    public function __construct(protected string $language_variable, protected array $place_holder_values = [])
    {
    }

    public function getLanguageVariable(): string
    {
        return $this->language_variable;
    }

    /**
     * @return list<string>
     */
    public function getPlaceHolderValues(): array
    {
        return $this->place_holder_values;
    }
}
