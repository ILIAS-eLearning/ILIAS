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

namespace ILIAS\TestQuestionPool\ExportImport\Foundation\Contracts;

use ILIAS\Refinery\Transformation;

/**
 * Provides a contract for objects capable of self-contained normalization and denormalization.
 *
 * This interface allows an object to define its own transformation into a language-neutral,
 * intermediate array structure. This array serves as a stable representation for further
 * processing, such as serialization into transport formats like XML or JSON during
 * export/import operations.
 *
 * A normalized array must only contain null, scalar values (string, int, float, bool) and nested
 * normalized arrays.
 */
interface Normalizable
{
    /**
     * Create a transformation that produces the normalized representation of the object.
     *
     * The returned transformation is expected to convert the internal state of the implementing
     * object into a language-neutral array structure. The resulting array must contain only
     * null, scalar values (string, int, float, bool) and nested arrays following the same rules.
     * It is typically used by {@see Transformations::normalize()} and higher-level exporters
     * before serializing the data to XML, JSON or other formats.
     */
    public function toNormalized(Transformations $tt): Transformation;

    /**
     * Create a transformation that restores the object from its normalized representation.
     *
     * The returned transformation is expected to take a normalized array as input (such as the
     * one produced by {@see Normalizable::toNormalized()}) and use it to set or update the
     * internal state of the implementing object. It is typically used by
     * {@see Transformations::fromNormalized()} and higher-level importers when reconstructing
     * objects from exported data.
     *
     * The transformation should validate and cast incoming values using the helper methods
     * provided by {@see Transformations} and return a copy of the object with the new state.
     */
    public function fromNormalized(Transformations $tt): Transformation;
}
