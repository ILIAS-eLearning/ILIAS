<?php declare(strict_types=1);
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjMail
* contains all functions to manage mail settings of ILIAS3
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version	$Id$
*
* @extends	ilObject
*/
class ilObjMail extends ilObject
{
    /**
    * @param	int	reference_id or object_id
    * @param	bool	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id, bool $a_call_by_reference = true)
    {
        $this->type = "mail";
        parent::__construct($a_id, $a_call_by_reference);
    }
} // END class.ilObjMail
