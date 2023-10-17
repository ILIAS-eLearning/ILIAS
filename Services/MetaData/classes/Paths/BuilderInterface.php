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

namespace ILIAS\MetaData\Paths;

use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Structure\Definitions\DefinitionInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;

interface BuilderInterface
{
    /**
     * Relative paths start at some otherwise determined element,
     * absolute paths start at root. Default is false.
     */
    public function withRelative(bool $is_relative): BuilderInterface;

    /**
     * Building a path that is flagged to lead to exactly one element,
     * but does not actually do so can throw errors later on. If you
     * set this flag, be sure to set filters correctly.
     * Default is false.
     */
    public function withLeadsToExactlyOneElement(
        bool $leads_to_one
    ): BuilderInterface;

    /**
     * Add the next step to the path. If add_as_first is set true,
     * the step is added as the first and not the last step.
     */
    public function withNextStep(
        string $name,
        bool $add_as_first = false
    ): BuilderInterface;

    /**
     * Add going to the super element as the next step to the path.
     * If add_to_front is set true, the step is added as the first
     * and not the last step.
     */
    public function withNextStepToSuperElement(
        bool $add_as_first = false
    ): BuilderInterface;

    /**
     * Adds a filter to the current step, restricting what
     * elements are included in it:
     *
     * * mdid: Only elements with the corresponding ID.
     * * data: Only elements that carry data which matches the filter's value.
     * * index: The n-th element, beginning with 0. Non-numeric values are
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

    public function get(): PathInterface;

    public function withNextStepFromStep(
        StepInterface $next_step,
        bool $add_as_first = false
    ): BuilderInterface;
}
