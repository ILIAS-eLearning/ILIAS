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

namespace ILIAS\Language\Activities;

use ILIAS\Data\Result;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\UI\Component\Input\Field\Checkbox;
use ILIAS\UI\Component\Input\Field\Text as TextField;
use ILIAS\UI\Component\Input\Group as GroupInput;
use ILIAS\UI\Component\Input\Input;

/**
 * getInputDescription() returns the public FormInput interface; the UI-internal classes grind()
 * needs are only touched through GrindingInputAccess.
 */
trait GrindsFormInput
{
    // private on purpose: subclasses influence perform()'s arguments via
    // LanguageActivity::additionalPerformParameters()
    /**
     * Result\Error only for invalid input; any other throwable is a defect and propagates to maybePerformAs().
     *
     * @param array<mixed> $raw_parameters
     */
    private function grind(FormInput $description, array $raw_parameters): Result
    {
        try {
            $named = GrindingInputAccess::named($description, static::class);

            $flat_input = [];
            // unknown keys are tolerated on the outermost group, but rejected in nested ones
            $this->collectRawValues($named, $raw_parameters, $flat_input);

            $with_input = GrindingInputAccess::withRawValues($named, $flat_input);
            $content = GrindingInputAccess::content($with_input);

            if ($content->isError()) {
                return new Result\Error($this->describeInputError($with_input));
            }

            return new Result\Ok($content->value());
        } catch (InvalidInputException $e) {
            return new Result\Error($e);
        } catch (\InvalidArgumentException $e) {
            // a malformed request value, never an internal misconfiguration: safe to show the user
            return new Result\Error(new InvalidInputException($e->getMessage(), previous: $e));
        }
    }

    /**
     * @param array<string, mixed> $flat
     * @param bool $enforce_known_keys false for the outermost group (unrelated top-level keys are
     *        ignored); nested group keys come from our own getInputDescription(), so unknown ones fail
     */
    private function collectRawValues(Input $named, mixed $raw_value, array &$flat, bool $enforce_known_keys = false): void
    {
        if ($named instanceof GroupInput) {
            // null = group omitted (treated as empty); any other non-array is a type mismatch
            if ($raw_value !== null && !is_array($raw_value)) {
                throw new InvalidInputException(
                    'Expected an array of values (or none at all) for '
                    . $this->fieldLabelForErrorMessage($named) . ', got: ' . get_debug_type($raw_value)
                );
            }
            $sub_raw = $raw_value ?? [];

            if ($enforce_known_keys) {
                $unknown_keys = array_diff(array_keys($sub_raw), array_keys($named->getInputs()));
                if ($unknown_keys !== []) {
                    throw new InvalidInputException(
                        'Unknown key(s) for ' . $this->fieldLabelForErrorMessage($named) . ': '
                        . implode(', ', array_map(static fn(int|string $key): string => (string) $key, $unknown_keys))
                    );
                }
            }

            foreach ($named->getInputs() as $key => $child) {
                $this->collectRawValues($child, $sub_raw[$key] ?? null, $flat, true);
            }
            return;
        }

        GrindingInputAccess::requireNameable(
            $named,
            'Every leaf field of a grindable FormInput must implement the UI framework\'s ' .
            'input-processing internals.'
        );

        $name = GrindingInputAccess::nameOf($named);
        if ($name === null) {
            throw new \LogicException('Every field of a grindable FormInput must have a name.');
        }

        $flat[$name] = match (true) {
            $named instanceof Checkbox => $this->normalizeCheckboxRawValue($raw_value),
            $named instanceof TextField && is_array($raw_value) => $this->joinListOfStringsRawValue($raw_value),
            default => $raw_value ?? '',
        };
    }

    /**
     * @param array<mixed> $raw_value
     * @return array<mixed>|string comma-joined if all items are strings, otherwise unchanged
     */
    private function joinListOfStringsRawValue(array $raw_value): array|string
    {
        foreach ($raw_value as $item) {
            if (!is_string($item)) {
                return $raw_value;
            }
        }

        return implode(',', $raw_value);
    }

    private function normalizeCheckboxRawValue(mixed $value): string
    {
        if (
            $value === true || $value === 1 || $value === '1' || $value === 'true'
            || $value === 'checked' || $value === 'on'
        ) {
            return 'checked';
        }
        if (
            $value === false || $value === 0 || $value === '0'
            || $value === '' || $value === 'false' || $value === null
        ) {
            return '';
        }

        throw new \InvalidArgumentException(
            'Expected a boolean or one of the common primitive representations of true/false ' .
            '(true/false, 1/0, "1"/"0", "true"/"false", "checked"/"on", "" or null), got: '
            . get_debug_type($value)
        );
    }

    private function describeInputError(FormInput $with_input): InvalidInputException
    {
        $field_errors = [];
        $this->collectFieldErrors($with_input, $field_errors);

        if ($field_errors === []) {
            $error = GrindingInputAccess::errorOf($with_input);
            $field_errors[] = $error ?? 'Invalid input.';
        }

        return new InvalidInputException(implode('; ', $field_errors));
    }

    /** @param list<string> $field_errors */
    private function collectFieldErrors(Input $named, array &$field_errors): void
    {
        if ($named instanceof GroupInput) {
            foreach ($named->getInputs() as $child) {
                $this->collectFieldErrors($child, $field_errors);
            }
            return;
        }

        if (!GrindingInputAccess::isNameable($named)) {
            return;
        }

        $error = GrindingInputAccess::errorOf($named);
        if ($error !== null) {
            $field_errors[] = $this->fieldLabelForErrorMessage($named) . ': ' . $error;
        }
    }

    private function fieldLabelForErrorMessage(Input $named): string
    {
        $dedicated_name = GrindingInputAccess::dedicatedNameOf($named);
        if ($dedicated_name !== null) {
            return $dedicated_name;
        }

        return GrindingInputAccess::nameOf($named) ?? '?';
    }
}
