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

namespace ILIAS\UI\Implementation\Component\Table\Column;

use ILIAS\UI\Component\Table\Column as C;

class TimeSpan extends Date implements C\TimeSpan
{
    public function format($value): string
    {
        assert(is_array($value));
        assert(is_a($value[0], \DateTimeImmutable::class) && is_a($value[1], \DateTimeImmutable::class));

        return
            $value[0]->format($this->getFormat()->toString())
            . ' - ' .
            $value[1]->format($this->getFormat()->toString());
    }
}
