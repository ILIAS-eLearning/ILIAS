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

class PRGProgressId
{
    public const DELIMITER = '_';

    protected int $ass_id;
    protected int $usr_id;
    protected int $node_obj_id;

    public function __construct(int $ass_id, int $usr_id, int $node_obj_id)
    {
        $this->ass_id = $ass_id;
        $this->usr_id = $usr_id;
        $this->node_obj_id = $node_obj_id;
    }

    public static function createFromString(string $id): self
    {
        $id = array_map('intval', explode(self::DELIMITER, $id));
        return new self(...$id);
    }

    public function getAssignmentId(): int
    {
        return $this->ass_id;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function getNodeId(): int
    {
        return $this->node_obj_id;
    }

    public function __toString(): string
    {
        return implode(self::DELIMITER, [$this->ass_id, $this->usr_id, $this->node_obj_id]);
    }
}
