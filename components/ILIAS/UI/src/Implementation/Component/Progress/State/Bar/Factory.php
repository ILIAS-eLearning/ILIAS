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

namespace ILIAS\UI\Implementation\Component\Progress\State\Bar;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Progress;
use ILIAS\UI\Component\Progress\State\Bar as C;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory implements C\Factory
{
    use ComponentHelper;

    public function indeterminate(?string $message = null): State
    {
        return new State(Status::INDETERMINATE, null, $message);
    }

    public function determinate(int $visual_progress_value, ?string $message = null): State
    {
        $this->checkArg(
            'visual_progress_value',
            ($visual_progress_value >= 0 && $visual_progress_value < Progress\Bar::MAX_VALUE),
            'must be a whole number between 0 and' . Progress\Bar::MAX_VALUE
        );

        return new State(Status::DETERMINATE, $visual_progress_value, $message);
    }

    public function success(string $message): State
    {
        return new State(Status::SUCCESS, Progress\Bar::MAX_VALUE, $message);
    }

    public function failure(string $message): State
    {
        return new State(Status::FAILURE, Progress\Bar::MAX_VALUE, $message);
    }
}
