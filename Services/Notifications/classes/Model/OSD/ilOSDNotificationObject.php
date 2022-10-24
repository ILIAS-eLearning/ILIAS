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

namespace ILIAS\Notifications\Model\OSD;

use ILIAS\Notifications\Model\ilNotificationObject;

/**
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class ilOSDNotificationObject
{
    protected int $id;
    protected int $user;
    protected ilNotificationObject $object;
    protected int $time_added = 0;
    protected int $valid_until = 0;
    protected int $visible_for = 0;
    protected string $type;

    public function __construct(
        int $id,
        int $user,
        ilNotificationObject $object,
        ?int $time_added = 0,
        ?int $valid_until = 0,
        ?int $visible_for = 0,
        ?string $type = ''
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->object = $object;
        $this->time_added = $time_added;
        $this->valid_until = $valid_until;
        $this->visible_for = $visible_for;
        $this->type = $type;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): int
    {
        return $this->user;
    }

    public function getObject(): ilNotificationObject
    {
        return $this->object;
    }

    public function getValidUntil(): int
    {
        return $this->valid_until;
    }

    public function setValidUntil(int $valid_until): void
    {
        $this->valid_until = $valid_until;
    }

    public function getVisibleFor(): int
    {
        return $this->visible_for;
    }

    public function setVisibleFor(int $visible_for): void
    {
        $this->visible_for = $visible_for;
    }

    public function getTimeAdded(): int
    {
        return $this->time_added;
    }

    public function setTimeAdded(int $time_added): void
    {
        $this->time_added = $time_added;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
