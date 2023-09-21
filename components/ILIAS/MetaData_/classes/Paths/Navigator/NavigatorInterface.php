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

namespace ILIAS\MetaData\Paths\Navigator;

use ILIAS\MetaData\Elements\ElementInterface;

interface NavigatorInterface extends BaseNavigatorInterface
{
    public function nextStep(): ?NavigatorInterface;

    /**
     * Returns the elements at the end of the path.
     * If the path should lead to exactly one element,
     * this returns exactly one element or throws an
     * error.
     * @return ElementInterface[]
     * @throws \ilMDPathException
     */
    public function elementsAtFinalStep(): \Generator;

    /**
     * Returns only the first element of those at the end of
     * the path, if one exists.
     * @throws \ilMDPathException
     */
    public function lastElementAtFinalStep(): ?ElementInterface;

    /**
     * Returns the elements at the current step in the path.
     * If the path should lead to exactly one element,
     * this returns exactly one element or throws an
     * error.
     * @return ElementInterface[]
     * @throws \ilMDPathException
     */
    public function elements(): \Generator;

    /**
     * Returns only the first element at the current step,
     * if one exists.
     * @throws \ilMDPathException
     */
    public function lastElement(): ?ElementInterface;
}
