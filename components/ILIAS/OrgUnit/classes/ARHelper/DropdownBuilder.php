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

namespace ILIAS\components\OrgUnit\ARHelper;

/**
 * This is to construct/collect the entries (=Links) of a row's action-dropdown
 */
class DropdownBuilder
{
    protected array $items = [];

    public function __construct(
        private \ILIAS\UI\Factory $ui_f,
        private \ILIAS\UI\Renderer $ui_r,
        private \ilLanguage $lng
    ) {
    }

    public function withItem(
        string $label_lang_var,
        string $url,
        bool $condition = true
    ) {
        if (! $condition) {
            return $this;
        }
        $clone = clone $this;
        $clone->items[] = $this->ui_f->button()->shy(
            $items[] = $this->lng->txt($label_lang_var),
            $url
        );
        return $clone;
    }

    public function get(): string
    {
        return $this->ui_r->render(
            $this->ui_f->dropdown()->standard($this->items)
                ->withLabel($this->lng->txt('actions'))
        );
    }
}
