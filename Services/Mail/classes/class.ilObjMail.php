<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjMail
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjMail extends ilObject
{
    public function __construct(int $a_id, bool $a_call_by_reference = true)
    {
        $this->type = "mail";
        parent::__construct($a_id, $a_call_by_reference);
    }
}
