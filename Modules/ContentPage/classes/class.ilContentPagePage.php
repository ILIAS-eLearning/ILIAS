<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilContentPagePage extends ilPageObject implements ilContentPageObjectConstants
{
    public function getParentType() : string
    {
        return self::OBJ_TYPE;
    }
}
