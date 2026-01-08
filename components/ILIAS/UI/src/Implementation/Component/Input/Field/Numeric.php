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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Refinery\ConstraintViolationException;

/**
 * This implements the numeric input.
 */
class Numeric extends FormInput implements C\Input\Field\Numeric
{
    private bool $integer_only = false;
    private bool $prevent_scientific_notation = false;
    private bool $prevent_signs = false;
    private bool $prevent_decimals = false;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);

        /**
         * @var $trafo_numericOrNull Transformation
         */
        $trafo_numericOrNull = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->int()
        ])
        ->withProblemBuilder(fn($txt) => $txt("numeric_only"));

        $this->setAdditionalTransformation($trafo_numericOrNull);
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        return is_numeric($value) || $value === "" || $value === null;
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->numeric()->isNumeric();
    }

    public function withIntegerOnly(): self
    {
        $clone = clone $this;
        $clone->integer_only = true;
        $clone->prevent_scientific_notation = true;
        $clone->prevent_signs = true;
        $clone->prevent_decimals = true;

        return $clone;
    }

    public function withPreventScientificNotation(bool $prevent = true): self
    {
        $clone = clone $this;
        $clone->prevent_scientific_notation = $prevent;

        return $clone;
    }

    public function withPreventSigns(bool $prevent = true): self
    {
        $clone = clone $this;
        $clone->prevent_signs = $prevent;

        return $clone;
    }

    public function withPreventDecimals(bool $prevent = true): self
    {
        $clone = clone $this;
        $clone->prevent_decimals = $prevent;

        return $clone;
    }

    public function isIntegerOnly(): bool
    {
        return $this->integer_only;
    }

    public function isScientificNotationPrevented(): bool
    {
        return $this->prevent_scientific_notation;
    }

    public function areSignsPrevented(): bool
    {
        return $this->prevent_signs;
    }

    public function areDecimalsPrevented(): bool
    {
        return $this->prevent_decimals;
    }

    public function getUpdateOnLoadCode(): Closure
    {
        $prevent_e = $this->prevent_scientific_notation ? 'true' : 'false';
        $prevent_signs = $this->prevent_signs ? 'true' : 'false';
        $prevent_decimals = $this->prevent_decimals ? 'true' : 'false';
        $integer_only = $this->integer_only ? 'true' : 'false';

        return static fn($id) => "(function() {
				const input = document.getElementById('$id');
				if (!input) return;
				
				const preventE = $prevent_e;
				const preventSigns = $prevent_signs;
				const preventDecimals = $prevent_decimals;
				const integerOnly = $integer_only;
				
				const blockedChars = [];
				const blockedKeyCodes = [];
				
				if (integerOnly || preventE) {
					blockedChars.push('e', 'E');
				}
				if (integerOnly || preventSigns) {
					blockedChars.push('+', '-');
					blockedKeyCodes.push(187, 189, 173, 107, 109);
				}
				if (integerOnly || preventDecimals) {
					blockedChars.push('.', ',');
					blockedKeyCodes.push(190, 188);
				}
				
				// Define allowed keycodes
				const allowedKeyCodes = [
					8,   // backspace
					9,   // tab
					13,  // enter
					27,  // escape
					35,  // end
					36,  // home
					37,  // left arrow
					38,  // up arrow
					39,  // right arrow
					46   // delete
				];
				
				const ctrlKeyCodes = [65, 67, 86, 88]; // Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
				
				const charsToRemove = [];
				if (integerOnly || preventE) {
					charsToRemove.push('e', 'E');
				}
				if (integerOnly || preventSigns) {
					charsToRemove.push('+', '-');
				}
				if (integerOnly || preventDecimals) {
					charsToRemove.push('.', ',');
				}
				
				const escapeChar = function(c) {
					var special = ['.', '*', '+', '?', '^', '$', '(', ')', '|', '[', ']', '\\\\'];
					if (special.indexOf(c) !== -1) {
						return '\\\\' + c;
					}
					return c;
				};
				const escapedChars = charsToRemove.length > 0 ? charsToRemove.map(escapeChar).join('') : '';
				const cleanRegex = escapedChars ? new RegExp('[' + escapedChars + ']', 'g') : null;
				
				input.addEventListener('input', function(event) {
					let value = input.value;
					if (value === null || value === undefined) {
						value = '';
					}
					
					if (cleanRegex) {
						let cleaned = String(value).replace(cleanRegex, '');
						if (value !== cleaned) {
							input.value = cleaned;
							value = cleaned;
						}
					}
					
					il.UI.input.onFieldUpdate(event, '$id', value);
				});
				
				il.UI.input.onFieldUpdate(null, '$id', input.value);
				
				input.addEventListener('keydown', function(event) {
					const key = event.key;
					const keyCode = event.which || event.keyCode;
					const isCtrl = event.ctrlKey || event.metaKey;
					
					if (blockedChars.includes(key)) {
						event.preventDefault();
						return false;
					}
					
					if (blockedKeyCodes.includes(keyCode)) {
						event.preventDefault();
						return false;
					}
					
					if (allowedKeyCodes.includes(keyCode)) {
						return true;
					}
					
					if (isCtrl && ctrlKeyCodes.includes(keyCode)) {
						return true;
					}
					
					if ((keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105)) {
						return true;
					}
					
					if (integerOnly) {
						event.preventDefault();
						return false;
					}
				});
				
				input.addEventListener('paste', function(event) {
					const paste = (event.clipboardData || window.clipboardData).getData('text');
					let testPattern = '^[0-9';
					
					if (integerOnly) {
						testPattern = '^[0-9]*$';
					} else {
						if (!preventSigns) {
							testPattern += '+-';
						}
						if (!preventDecimals) {
							testPattern += '.,';
						}
						if (!preventE) {
							testPattern += 'eE';
						}
						testPattern += ']*$';
					}
					
					const regex = new RegExp(testPattern);
					if (!regex.test(paste)) {
						event.preventDefault();
						return false;
					}
				});
			})();";
    }

    public function isComplex(): bool
    {
        return false;
    }
}
