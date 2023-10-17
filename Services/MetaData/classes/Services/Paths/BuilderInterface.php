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

namespace ILIAS\MetaData\Services\Paths;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;

interface BuilderInterface
{
    /**
     * Add the next step to the path, identified by the name of a LOM element.
     */
    public function withNextStep(string $name): BuilderInterface;

    /**
     * Add going to the super element as the next step to the path.
     */
    public function withNextStepToSuperElement(): BuilderInterface;

    /**
     * Adds a filter to the latest added step, restricting what
     * elements are included in it:
     *
     * * **mdid:** Only elements with the corresponding ID.
     * * **data:** Only elements that carry data which matches the filter's value.
     * * **index:** The n-th element, beginning with 0. Non-numeric values are
     *   interpreted as referring to the last index.
     *   (Note that filters are applied in the order they are added,
     *   so the index applies to already filtered elements.)
     *
     * Multiple values in the same filter are treated as OR,
     * multiple filters at the same step are treated as AND.
     */
    public function withAdditionalFilterAtCurrentStep(
        FilterType $type,
        string ...$values
    ): BuilderInterface;

    /**
     * Get the path as constructed. Throws an error if the path
     * is invalid, e.g. because the name of a step was misspelled.
     */
    public function get(): PathInterface;
}
