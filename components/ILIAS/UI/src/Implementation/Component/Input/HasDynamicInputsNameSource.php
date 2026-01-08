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

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Component\Input\Field\HasDynamicInputs;

/**
 * Generates input names that are stacked when submitted to the server.
 *
 * This is achieved by adding an array postfix ('[]') with and without
 * index to the input name. Names like 'parent_input[template_input][]'
 * or 'parent_input[template_input][0]' can be generated.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class HasDynamicInputsNameSource implements NameSource
{
    /** @var array<string, int> (name => index) */
    protected array $used_input_name_index_map = [];
    protected bool $should_generate_indices = false;
    protected ?string $parent_name = null;

    public function __construct(
        protected NameSource $default_name_source,
    ) {
    }

    public function getNextName(?string $dedicated_name = null): string
    {
        if (null === $this->parent_name) {
            throw new \LogicException('Cannot generate input-names without parent-name.');
        }
        $new_name_without_array_postfix = $this->parent_name . "[{$this->default_name_source->getNextName($dedicated_name)}]";
        if ($this->should_generate_indices) {
            return $this->addArrayPostfixWithIndex($new_name_without_array_postfix);
        }
        return $this->addArrayPostfix($new_name_without_array_postfix);
    }

    public function withParentName(string $parent_name): static
    {
        $clone = clone $this;
        $clone->parent_name = $parent_name;
        return $clone;
    }

    public function withReset(): static
    {
        $clone = clone $this;
        $clone->default_name_source = $clone->default_name_source->withReset();
        $clone->used_input_name_index_map = [];
        $clone->should_generate_indices = false;
        $clone->parent_name = null;
        return $clone;
    }

    /**
     * Get a NameSource like this, but reset its default NameSource.
     *
     * This MUST be called before a new generated input (group) is named, otherwise
     * these inputs will not have the same name(s).
     */
    public function withResetDefaultNameSource(): static
    {
        $clone = clone $this;
        $clone->default_name_source = $clone->default_name_source->withReset();
        return $clone;
    }

    /**
     * Get a NameSource like this, but change the way the array postfix is treated.
     *
     * Indices MUST be used for input names that are ultimately rendered, otherwise
     * some inputs like e.g. Radio will not operate properly on client.
     *
     * Indices MUST NOT be used for input names of templates, otherwise mapping them
     * inside @see HasDynamicInputs::withInput() is not possible.
     */
    public function withIndices(bool $with_indices): static
    {
        $clone = clone $this;
        $clone->should_generate_indices = $with_indices;
        return $clone;
    }

    protected function addArrayPostfixWithIndex(string $new_name_without_array_postfix): string
    {
        $index = $this->used_input_name_index_map[$new_name_without_array_postfix] ?? 0;
        $this->used_input_name_index_map[$new_name_without_array_postfix] = $index + 1;

        return "{$new_name_without_array_postfix}[$index]";
    }

    protected function addArrayPostfix(string $new_name_without_array_postfix): string
    {
        return "{$new_name_without_array_postfix}[]";
    }
}
