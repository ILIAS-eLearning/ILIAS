<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjSystemCheck extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = 'sysc';
        parent::__construct($a_id, $a_call_by_reference);
    }
}
