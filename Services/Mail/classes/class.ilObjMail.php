<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjMail
* contains all functions to manage mail settings of ILIAS3
* @author	Stefan Meyer <meyer@leifos.com>
*/
class ilObjMail extends ilObject
{
    /**
    * @param int  $a_id                reference_id or object_id
    * @param bool $a_call_by_reference treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id, bool $a_call_by_reference = true)
    {
        $this->type = "mail";
        parent::__construct($a_id, $a_call_by_reference);
    }
}

