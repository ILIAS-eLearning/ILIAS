<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Represents a condition target object
 * @author  killing@leifos.de
 * @ingroup ServicesCondition
 */
class ilConditionTarget
{
    protected int $ref_id;
    protected int $obj_id;
    protected string $type;

    public function __construct(int $ref_id, int $obj_id, string $obj_type)
    {
        $this->ref_id = $ref_id;
        $this->obj_id = $obj_id;
        $this->type = $obj_type;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getType() : string
    {
        return $this->type;
    }
}
