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

namespace ILIAS\Tracking\Status;

use Countable;
use Iterator;

interface CollectionInterface extends Iterator, Countable
{
    public function getElementsByStatusIds(
        string ...$lp_status_ids
    ): CollectionInterface;

    public function getElementByStatusId(
        string $lp_status_id
    ): LPStatusInterface|null;

    public function current(): LPStatusInterface;

    public function key(): int;
}
