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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * This is what a radio-input looks like.
 */
interface Radio extends FormInput
{
    /**
     * Add an option-entry to the radio-input.
     */
    public function withOption(string $value, string $label, string $byline = null): Radio;

    /**
     * Get all options as value=>label.
     *
     * @return array <string,string>
     */
    public function getOptions(): array;

    /**
     * Get byline for a single option.
     * Returns null, if none present.
     */
    public function getBylineFor(string $value): ?string;
}
