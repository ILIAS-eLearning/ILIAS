<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Stefan Meyer <meyer@leifos.com>
* @ingroup ServicesLogging
*/
class ilObjLoggingSettings extends ilObject
{
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "logs";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
