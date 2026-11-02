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

namespace ILIAS\Data\Privacy;

use ILIAS\Data\Privacy\TechnicalRequirement\TechnicalRequirement;
use ILIAS\Data\Privacy\Source\Source;

/**
 * Base implementation for privacy data types.
 *
 * The generic types are created through {@see Factory}, types of a
 * component with `new`.
 *
 * @template T
 * @implements PrivacyDataType<T>
 */
abstract readonly class AbstractPrivacyDataType implements PrivacyDataType
{
    /**
     * Final, because {@see withValue()} creates instances with `new static`.
     *
     * @param T $value
     */
    final public function __construct(
        protected mixed $value,
        protected Source $source,
    ) {
    }

    public function resolve(TechnicalRequirement $requirement): mixed
    {
        return $this->value;
    }

    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * Derives a new instance around a transformed value without resolving.
     *
     * Transformation is not disclosure: the raw value never leaves the
     * type, therefore no technical requirement has to be stated. The
     * given source replaces the current one (last write wins).
     *
     * @param T $value
     */
    protected function withValue(mixed $value, Source $source): static
    {
        return new static($value, $source);
    }

    public function __toString(): string
    {
        return static::class . '(***) from ' . $this->source->describe();
    }
}
