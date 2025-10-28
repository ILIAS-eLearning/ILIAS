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
 * Describes a Form Input that can contain Mustache variables, which will
 * be made available for users to insert on the client.
 *
 * @see https://mustache.github.io/mustache.5.html
 */
interface HasMustacheVariables extends FormInput
{
    /**
     * Get an input like this, but provide it with Mustache variables which will
     * be made available for users to insert on the client. Optionally provide some
     * context information text which will be displayed along the variable definitions.
     *
     * The definitions consists of variable-name and description pairs, whereas the
     * variable-name COULD be dotted as well (e.g. company or recipient.company).
     *
     * @param array<string, string> $variable_definitions (variable-name => description)
     */
    public function withMustacheVariables(
        array $variable_definitions,
        ?string $context_information = null
    ): static;
}
