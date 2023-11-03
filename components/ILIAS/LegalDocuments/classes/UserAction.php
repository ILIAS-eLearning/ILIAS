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

namespace ILIAS\LegalDocuments;

use DateTimeImmutable;
use ILIAS\Data\Clock\ClockInterface as Clock;
use ILIAS\LegalDocuments\Value\Edit;
use ilObjUser;

class UserAction
{
    public function __construct(private readonly ilObjUser $user, private readonly Clock $clock)
    {
    }

    public function modifiedNow(): Edit
    {
        return new Edit($this->user->getId(), $this->clock->now());
    }
}
