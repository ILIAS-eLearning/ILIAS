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

namespace ILIAS\AdvancedMetaData\Services;

class SubObjectID implements SubObjectIDInterface
{
    protected int $obj_id;
    protected int $sub_id;
    protected string $sub_type;

    public function __construct(
        int $obj_id,
        int $sub_id,
        string $sub_type
    ) {
        $this->obj_id = $obj_id;
        $this->sub_id = $sub_id;
        $this->sub_type = $sub_type;
    }

    public function subtype(): string
    {
        return $this->sub_type;
    }

    public function objID(): int
    {
        return $this->obj_id;
    }

    public function subID(): int
    {
        return $this->sub_id;
    }
}
