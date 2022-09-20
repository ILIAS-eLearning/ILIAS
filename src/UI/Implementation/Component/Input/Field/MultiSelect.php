<?php

declare(strict_types=1);

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

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This implements the multi-select input.
 */
class MultiSelect extends Input implements C\Input\Field\MultiSelect
{
    /**
     * @var array <string,string> {$value => $label}
     */
    protected array $options = [];
    private bool $complex = true;

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
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        if (is_null($value)) {
            return true;
        }
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
        return $this->refinery->custom()->constraint(
            fn ($value) => is_array($value) && count($value) > 0,
            "Empty"
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode(): Closure
    {
        return fn ($id) => "var checkedBoxes = function() {
				var options = [];
				$('#$id').find('li').each(function() {
				    if ($(this).find('input').prop('checked')) {
					    options.push($(this).find('span').text());
                    }
				});
				return options.join(', ');
			}
			$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', checkedBoxes());
			});
			il.UI.input.onFieldUpdate(event, '$id', checkedBoxes());
			";
    }

    /**
     * @inheritdoc
     */
    public function isComplex(): bool
    {
        return $this->complex;
    }
}
