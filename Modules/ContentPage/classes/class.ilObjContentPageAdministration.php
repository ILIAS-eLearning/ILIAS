<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjContentPageAdministration
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjContentPageAdministration extends ilObject2
{
    /**
     * @ineritdoc
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @ineritdoc
     */
    protected function initType()
    {
        $this->type = 'cpad';
    }
}
