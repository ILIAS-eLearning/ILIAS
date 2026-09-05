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

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Component\Dropdown\Dropdown;

trait ContextualizesDropdownActions
{
    protected function contextualizeDropdownActions(?Dropdown $actions, string $context_title): ?Dropdown
    {
        if ($actions === null || $context_title === '') {
            return $actions;
        }

        $label = $actions->getLabel();
        if ($label !== null && $label !== '') {
            return $actions;
        }

        $aria_label = $actions->getAriaLabel();
        if ($aria_label !== null && $aria_label !== '') {
            return $actions;
        }

        return $actions->withAriaLabel(sprintf($this->txt('actions_for'), $context_title));
    }
}
