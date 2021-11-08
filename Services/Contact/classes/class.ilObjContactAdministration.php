<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjContactAdministration
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilObjContactAdministration extends ilObject2
{
    protected function initType() : void
    {
        $this->type = 'cadm';
    }
}
