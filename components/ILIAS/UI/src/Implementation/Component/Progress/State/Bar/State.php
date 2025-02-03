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
use ILIAS\UI\Component\Progress\State\Bar;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class State implements Bar\State
{
    use ComponentHelper;

    public function __construct(
        protected Status $status,
        protected ?int $visual_progress_value = null,
        protected ?string $message = null,
    ) {
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getVisualProgressValue(): ?int
    {
        return $this->visual_progress_value;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
