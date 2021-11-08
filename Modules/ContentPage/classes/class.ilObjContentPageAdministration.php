<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjContentPageAdministration extends ilObject2
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }

    protected function initType() : void
    {
        $this->type = 'cpad';
    }
}
