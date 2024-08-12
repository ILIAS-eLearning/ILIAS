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

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Input\Container\Form\FormInput;

/**
 * Interface Mustachable
 *
 * Describes an Input/Field that can contain Mustache placeholders which can
 * be displayed to the user in some form
 *
 * @package ILIAS\UI\Component
 */
interface Mustachable extends FormInput
{
    /**
     * Enable use of Mustache placeholders and
     * add placeholder defintions as an array
     *
     * [ 'PLACEHOLDER_NAME' => 'Descriptive text' ]
     */
    public function withMustachable(?array $placeholders): self;

    public function isMustachable(): bool;

    /**
     * get placeholder entries
     */
    public function getPlaceholderEntries(): array;

    /**
     * add an advice text for the placeholder definitions
     */
    public function withPlaceholderAdvice(string $text): self;

    /**
     * get placeholder advice text
     */
    public function getPlaceholderAdvice(): string;
}
