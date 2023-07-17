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

namespace ILIAS\MetaData\Paths\Steps;

use ILIAS\MetaData\Paths\Filters\FilterInterface;

interface StepInterface
{
    /**
     * Steps are identified by the names of LOM elements,
     * or a token to specify a step to the super-element.
     */
    public function name(): string|StepToken;

    /**
     * Filters restrict the elements a step leads to.
     * Multiple filters at the same step are evaluated
     * in order.
     * @return FilterInterface[]
     */
    public function filters(): \Generator;
}
