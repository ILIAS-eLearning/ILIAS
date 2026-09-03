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

namespace ILIAS\Tracking\DB\LPSettings\Element;

class LPSettings implements LPSettingsInterface
{
    protected string $obj_type;
    protected int $obj_id;
    protected int $u_mode;
    protected int $visits;

    public function __construct()
    {
    }

    public function getObjectId(): int
    {
        return $this->obj_id;
    }

    public function getUMode(): int
    {
        return $this->u_mode;
    }

    public function getVisits(): int
    {
        return $this->visits;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function withObjectId(
        int $obj_id
    ): LPSettingsInterface {
        $clone = clone $this;
        $clone->obj_id = $obj_id;
        return $clone;
    }

    public function withUMode(
        int $u_mode
    ): LPSettingsInterface {
        $clone = clone $this;
        $clone->u_mode = $u_mode;
        return $clone;
    }

    public function withVisits(
        int $visits
    ): LPSettingsInterface {
        $clone = clone $this;
        $clone->visits = $visits;
        return $clone;
    }

    public function withObjType(
        string $obj_type
    ): LPSettingsInterface {
        $clone = clone $this;
        $clone->obj_type = $obj_type;
        return $clone;
    }
}
