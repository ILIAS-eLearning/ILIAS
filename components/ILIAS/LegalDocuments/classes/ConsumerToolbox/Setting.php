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

namespace ILIAS\LegalDocuments\ConsumerToolbox;

use ilSetting;
use Closure;

/**
 * @template A
 */
class Setting
{
    /**
     * @param Closure(): A $value
     * @param Closure(A): void $update
     */
    public function __construct(
        private readonly Closure $value,
        private readonly Closure $update
    ) {
    }

    /**
     * @return A
     */
    public function value()
    {
        return ($this->value)();
    }

    /**
     * @param A $value
     */
    public function update($value): void
    {
        ($this->update)($value);
    }
}
