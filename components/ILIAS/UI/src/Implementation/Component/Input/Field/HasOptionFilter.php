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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;

abstract class HasOptionFilter extends FormInput implements C\Input\Field\HasOptionFilter
{
    protected bool $has_option_filter = false;

    /**
     * @return array<string, string> (value => label)
     */
    abstract public function getOptions(): array;

    public function withHasOptionFilter(bool $has_option_filter = true): static
    {
        $clone = clone $this;
        $clone->has_option_filter = $has_option_filter;
        return $clone;
    }

    public function hasOptionFilter(): bool
    {
        return $this->has_option_filter;
    }
}
