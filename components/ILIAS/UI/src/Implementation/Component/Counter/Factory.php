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

namespace ILIAS\UI\Implementation\Component\Counter;

use ILIAS\UI\Component as C;

class Factory implements C\Counter\Factory
{
    /**
     * @inheritdoc
     */
    public function status(int $number): C\Counter\Counter
    {
        return new Counter(C\Counter\Counter::STATUS, $number);
    }

    /**
     * @inheritdoc
     */
    public function novelty(int $number): C\Counter\Counter
    {
        return new Counter(C\Counter\Counter::NOVELTY, $number);
    }
}
