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

namespace ILIAS\FileUpload\Activities;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
 * Reads the raw parameters of an activity strictly, with the Refinery.
 *
 * Raw parameters come from outside - a query string, a JSON body - and a cast
 * makes something of everything: `(int) ['5']` is 1, the root of the
 * repository, `(int) '102abc'` is 102. `kindlyTo()` is the right tool for
 * values that arrive as text, but it is kind to more than text: it makes 1 of
 * `true` and rounds `1.7`. So every parameter first has to be of a type that
 * can carry it (`to()`), and only then is it converted (`kindlyTo()`). A value
 * that is not what the parameter takes is refused with a 400, so a client never
 * gets an answer to a question it did not ask.
 *
 * Once `maybePerformAs()` reads raw parameters through the input description of
 * an activity, this goes away.
 */
class Parameter
{
    public function __construct(
        private readonly Refinery $refinery,
    ) {
    }

    /**
     * A positive whole number, e.g. a reference id.
     *
     * @param array<string, mixed> $raw_parameters
     * @param string $missing what to answer when the parameter is not there
     */
    public function id(array $raw_parameters, string $name, string $missing): int
    {
        $value = $raw_parameters[$name] ?? null;

        if ($value === null || $value === '') {
            throw new \InvalidArgumentException($missing, 400);
        }

        return $this->apply(
            $this->refinery->in()->series([
                $this->refinery->byTrying([
                    $this->refinery->to()->int(),
                    $this->refinery->to()->string(),
                ]),
                $this->refinery->kindlyTo()->int(),
                $this->refinery->int()->isGreaterThan(0),
            ]),
            $value,
            "'{$name}' has to be a positive whole number."
        );
    }

    /**
     * A plain value as it was sent, trimmed; null if it was not sent.
     *
     * @param array<string, mixed> $raw_parameters
     */
    public function string(array $raw_parameters, string $name): ?string
    {
        $value = $raw_parameters[$name] ?? null;

        if ($value === null) {
            return null;
        }

        return $this->apply($this->toString(), $value, "'{$name}' has to be a text.");
    }

    /**
     * Text a user wrote, e.g. a title: tags are stripped, as the fields of the
     * UI framework do. Null if it was not sent.
     *
     * @param array<string, mixed> $raw_parameters
     */
    public function text(array $raw_parameters, string $name): ?string
    {
        $value = $raw_parameters[$name] ?? null;

        if ($value === null) {
            return null;
        }

        return $this->apply(
            $this->refinery->in()->series([
                $this->toString(),
                $this->refinery->string()->stripTags(),
                $this->trim(),
            ]),
            $value,
            "'{$name}' has to be a text."
        );
    }

    /**
     * A yes or a no: `true`, `false`, `1`, `0`, also as text. Anything else is
     * refused rather than read as "no" - for `keep_previous_version` a typo
     * would be "drop the history". Only a missing value falls back to the
     * default.
     *
     * @param array<string, mixed> $raw_parameters
     */
    public function flag(array $raw_parameters, string $name, bool $default): bool
    {
        $value = $raw_parameters[$name] ?? null;

        if ($value === null) {
            return $default;
        }

        return $this->apply(
            $this->refinery->kindlyTo()->bool(),
            $value,
            "'{$name}' has to be true or false."
        );
    }

    /**
     * A list of plain values, sent as a list or comma separated; empty entries
     * are dropped.
     *
     * @param array<string, mixed> $raw_parameters
     *
     * @return list<string>
     */
    public function list(array $raw_parameters, string $name): array
    {
        $value = $raw_parameters[$name] ?? [];

        $list = $this->apply(
            $this->refinery->in()->series([
                $this->refinery->byTrying([
                    $this->refinery->in()->series([
                        $this->refinery->to()->string(),
                        $this->refinery->string()->splitString(','),
                    ]),
                    $this->refinery->identity(),
                ]),
                $this->refinery->to()->listOf(
                    $this->refinery->in()->series([
                        $this->refinery->to()->string(),
                        $this->trim(),
                    ])
                ),
            ]),
            $value,
            "'{$name}' has to be a list of texts."
        );

        return array_values(array_filter($list, static fn(string $entry): bool => $entry !== ''));
    }

    /**
     * Text, or a number sent as one - a JSON body may well carry a title 42.
     */
    private function toString(): Transformation
    {
        return $this->refinery->in()->series([
            $this->refinery->byTrying([
                $this->refinery->to()->string(),
                $this->refinery->to()->int(),
                $this->refinery->to()->float(),
            ]),
            $this->refinery->kindlyTo()->string(),
            $this->trim(),
        ]);
    }

    private function trim(): Transformation
    {
        return $this->refinery->custom()->transformation(
            static fn(string $value): string => trim($value)
        );
    }

    /**
     * The Refinery says why in its own words and in its own exceptions; the
     * caller is told which parameter was wrong, as a 400.
     */
    private function apply(Transformation $transformation, mixed $value, string $refusal): mixed
    {
        try {
            return $transformation->transform($value);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($refusal, 400, $e);
        }
    }
}
