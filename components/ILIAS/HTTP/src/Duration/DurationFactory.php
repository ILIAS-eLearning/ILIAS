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
 */

namespace ILIAS\HTTP\Duration;

use ILIAS\HTTP\Duration\Increment\IncrementFactory;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class DurationFactory
{
    protected IncrementFactory $incrementFactory;

    public function __construct(IncrementFactory $incrementFactory)
    {
        $this->incrementFactory = $incrementFactory;
    }

    public function callbackDuration(int $duration_in_ms): CallbackDuration
    {
        return new CallbackDuration($duration_in_ms);
    }

    public function increments(): IncrementFactory
    {
        return $this->incrementFactory;
    }
}
