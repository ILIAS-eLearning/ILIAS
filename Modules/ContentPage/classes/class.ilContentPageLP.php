<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilContentPageLP
 */
class ilContentPageLP extends \ilObjectLP
{
    /**
     * @inheritdoc
     */
    public function getDefaultMode()
    {
        return \ilLPObjSettings::LP_MODE_MANUAL;
    }

    /**
     * @inheritdoc
     */
    public function getValidModes()
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL,
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
        ];
    }
}
