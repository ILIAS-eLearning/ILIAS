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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;

class Boolean extends Column implements C\Boolean
{
    public function __construct(
        string $title,
        protected string|Icon|Glyph $true_option,
        protected string|Icon|Glyph $false_option
    ) {
        parent::__construct($title);

        if (
            ($true_option instanceof Glyph && $true_option->getAction() !== null)
            || ($false_option instanceof Glyph && $false_option->getAction() !== null)
        ) {
            throw new \LogicException(
                "If Glyps are used to indicate the state, they MUST NOT have an attached action."
            );
        }
    }

    public function format($value): string|Icon|Glyph
    {
        $this->checkBoolArg('value', $value);
        return $value ? $this->true_option : $this->false_option;
    }
}
