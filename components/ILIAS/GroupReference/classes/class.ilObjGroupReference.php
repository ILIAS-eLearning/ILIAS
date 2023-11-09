<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Fabian Wolf <wolf@leifos.com>
 * @extends ilContainerReference
 *
 * @ingroup components\ILIASGroupReference
*/
class ilObjGroupReference extends ilContainerReference
{
    /**
     * Constructor
     * @param int $a_id reference id
     * @param bool $a_call_by_reference
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = 'grpr';
        parent::__construct($a_id, $a_call_by_reference);
    }
}
