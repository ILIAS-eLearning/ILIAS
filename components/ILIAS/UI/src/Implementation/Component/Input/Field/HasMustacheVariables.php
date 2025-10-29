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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * @see HasMustacheVariablesInternal for interface definition.
 */
trait HasMustacheVariables
{
    use ComponentHelper;

    /** @var array<string, string> (variable-name => description) */
    protected array $mustache_variable_definitions = [];

    protected ?string $mustache_variable_context_info = null;

    public function withMustacheVariables(
        array $variable_definitions,
        ?string $context_information = null
    ): static {
        $variable_names = array_keys($variable_definitions);
        $this->checkArgListElements('$variable_definitions', $variable_names, ['string']);
        $this->checkArgListElements('$variable_definitions', $variable_definitions, ['string']);

        $clone = clone $this;
        $clone->mustache_variable_definitions = $variable_definitions;
        $clone->mustache_variable_context_info = $context_information;
        return $clone;
    }

    public function getMustacheVariables(): array
    {
        return $this->mustache_variable_definitions;
    }

    public function getMustacheVariableContextInfo(): ?string
    {
        return $this->mustache_variable_context_info;
    }
}
