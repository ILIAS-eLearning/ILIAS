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

namespace ILIAS\MetaData\Elements\RessourceID;

class RessourceID implements RessourceIDInterface
{
    protected int $obj_id;
    protected int $sub_id;
    protected string $type;

    public function __construct(
        int $obj_id,
        int $sub_id,
        string $type
    ) {
        $this->obj_id = $obj_id;
        $this->sub_id =$sub_id;
        $this->type = $type;
    }

    public function type(): string
    {
        return $this->type;
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
