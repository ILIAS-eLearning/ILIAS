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

namespace ILIAS\TestQuestionPool\Scoring;

use RuntimeException;

final class DecimalSum
{
    public function __construct(private int $scale = 50)
    {
        if (!extension_loaded('bcmath')) {
            throw new RuntimeException('The bcmath extension is required to sum decimal values reliably.');
        }
    }

    /**
     * @param array<int|float|string> $values
     */
    public function sum(array $values): float
    {
        if ($values === []) {
            return 0.0;
        }

        $total = '0';
        foreach ($values as $value) {
            $total = bcadd($total, sprintf('%.14F', (float) $value), $this->scale);
        }

        return (float) $total;
    }
}
