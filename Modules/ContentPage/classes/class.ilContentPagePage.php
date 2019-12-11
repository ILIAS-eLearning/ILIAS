<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPagePage
 */
class ilContentPagePage extends \ilPageObject implements \ilContentPageObjectConstants
{
    /**
     * @inheritdoc
     */
    public function getParentType()
    {
        return self::OBJ_TYPE;
    }
}
