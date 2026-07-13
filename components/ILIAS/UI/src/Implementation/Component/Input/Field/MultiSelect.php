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
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This implements the multi-select input.
 */
class MultiSelect extends FormInput implements C\Input\Field\MultiSelect, HasOptionFilterInternal
{
    use HasOptionFilter;

    /**
     * @param array<string, string> $options
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        array $options,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                if (!array_key_exists($v, $this->options)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->custom()->constraint(
            fn($value) => is_array($value) && count($value) > 0,
            "Empty"
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return static fn($id) => <<<JS
          (function () {
            function reduceMultiSelectCheckboxInputs(inputs) {
              return Array
                .from(inputs)
                .filter((input) => input.checked)
                .map((input) => input.parentElement.querySelector('.c-field-multiselect__label-text')?.textContent ?? '')
                .join(', ');
            }
            const multiSelectField = document.getElementById('$id');
            const multiSelectCheckboxInputs = multiSelectField.querySelectorAll('.c-field-multiselect input[type="checkbox"]');
            multiSelectCheckboxInputs.forEach((input) => {
              input.addEventListener('input', (event) => {
                il.UI.input.onFieldUpdate(event, '$id', reduceMultiSelectCheckboxInputs(multiSelectCheckboxInputs));
              });
            });
            il.UI.input.onFieldUpdate(undefined, '$id', reduceMultiSelectCheckboxInputs(multiSelectCheckboxInputs));
          })();
JS;
    }

    /**
     * @inheritdoc
     */
    public function isComplex(): bool
    {
        return true;
    }
}
