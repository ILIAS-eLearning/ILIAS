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
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Component\Component;

class Boolean extends Column implements C\Boolean
{
    /**
     * @var string|Icon|Glyph
     */
    protected $true_option;
    /**
     * @var string|Icon|Glyph
     */
    protected $false_option;

    public function __construct(
        \ilLanguage $lng,
        string $title,
        $true_option,
        $false_option
    ) {
        parent::__construct($lng, $title);

        if (
            ($true_option instanceof Glyph && $true_option->getAction() !== null)
            || ($false_option instanceof Glyph && $false_option->getAction() !== null)
        ) {
            throw new \LogicException(
                "If Glyps are used to indicate the state, they MUST NOT have an attached action."
            );
        }
        $this->true_option = $true_option;
        $this->false_option = $false_option;
    }

    public function format($value)
    {
        $this->checkBoolArg('value', $value);
        return $value ? $this->true_option : $this->false_option;
    }

    /**
     * @return string[]
     */
    public function getOrderingLabels(): array
    {
        $column_value_true = $this->format(true);
        $column_value_false = $this->format(false);
        if($column_value_true instanceof Symbol) {
            $column_value_true = $column_value_true->getLabel();
        }
        if($column_value_false instanceof Symbol) {
            $column_value_false = $column_value_false->getLabel();
        }
        return [
            $this->asc_label ?? $column_value_true . ' ' . $this->lng->txt('order_option_first'),
            $this->desc_label ?? $column_value_false . ' ' . $this->lng->txt('order_option_first')
        ];
    }
}
