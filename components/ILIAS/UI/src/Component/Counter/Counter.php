<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Counter;

use ILIAS\UI\Component\Component;

/**
 * This tags a counter object.
 */
interface Counter extends Component
{
    // Types of counters:
    public const NOVELTY = "novelty";
    public const STATUS = "status";

    /**
     * Get the type of the counter.
     *
     * @return	string	One of the counter types.
     */
    public function getType(): string;

    /**
     * Get the number on the counter.
     */
    public function getNumber(): int;
}
