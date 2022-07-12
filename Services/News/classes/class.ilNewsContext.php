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

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsContext
{
    protected int $obj_id;
    protected string $obj_type;
    protected int $sub_id;
    protected string $sub_type;

    public function __construct(int $obj_id, string $obj_type, int $sub_id, string $sub_type)
    {
        $this->obj_id = $obj_id;
        $this->obj_type = $obj_type;
        $this->sub_id = $sub_id;
        $this->sub_type = $sub_type;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getObjType() : string
    {
        return $this->obj_type;
    }

    public function getSubId() : int
    {
        return $this->sub_id;
    }

    public function getSubType() : string
    {
        return $this->sub_type;
    }
}
