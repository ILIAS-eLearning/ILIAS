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
use ILIAS\UI\Component\Input\Input;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use ILIAS\UI\Implementation\Component\Input\InputInternal;

/**
 * Sole point of coupling to `ILIAS\UI\Implementation\Component\Input\*`: the public `FormInput`
 * interfaces expose no way to collect raw request data (`withNameFrom()`, `withInput()`,
 * `getContent()`, `getDedicatedName()`), and there is no public alternative. If the UI internals
 * change, the methods here fail loudly instead of silently misbehaving.
 */
final class GrindingInputAccess
{
    private function __construct()
    {
    }

    /**
     * Assigns HTML input names throughout $description's tree; required before collecting raw data.
     */
    public static function named(FormInput $description, string $activity_class): FormInput
    {
        if (!$description instanceof InputInternal) {
            throw new \LogicException(
                $activity_class . '::getInputDescription() must return a FormInput built from the ' .
                'UI framework (i.e. one that also implements ' . InputInternal::class . ') to be ' .
                'grindable by maybePerformAs() - see the GrindsFormInput trait for why.'
            );
        }

        return $description->withNameFrom(new FormInputNameSource());
    }

    /** @param array<string, mixed> $flat_raw_values keyed by the HTML input names assigned by named() */
    public static function withRawValues(FormInput $named, array $flat_raw_values): FormInput
    {
        self::assertInternal($named, 'withInput()');

        /** @var InputInternal&FormInput $named */
        return $named->withInput(new ArrayInputData($flat_raw_values));
    }

    public static function content(FormInput $with_input): Result
    {
        self::assertInternal($with_input, 'getContent()');

        /** @var InputInternal $with_input */
        return $with_input->getContent();
    }

    public static function nameOf(Input $input): ?string
    {
        return $input instanceof InputInternal ? $input->getName() : null;
    }

    public static function errorOf(Input $input): ?string
    {
        return $input instanceof InputInternal ? $input->getError() : null;
    }

    public static function isNameable(Input $input): bool
    {
        return $input instanceof InputInternal;
    }

    public static function requireNameable(Input $input, string $exception_message): void
    {
        if (!$input instanceof InputInternal) {
            throw new \LogicException($exception_message);
        }
    }

    /** Only available on the concrete implementation base class, not on the public `Input` interface. */
    public static function dedicatedNameOf(Input $input): ?string
    {
        return $input instanceof \ILIAS\UI\Implementation\Component\Input\Input
            ? $input->getDedicatedName()
            : null;
    }

    private static function assertInternal(FormInput $input, string $needed_for): void
    {
        if (!$input instanceof InputInternal) {
            throw new \LogicException(
                'Expected a FormInput also implementing ' . InputInternal::class . ' (needed for ' .
                $needed_for . '), got: ' . get_debug_type($input)
            );
        }
    }
}
