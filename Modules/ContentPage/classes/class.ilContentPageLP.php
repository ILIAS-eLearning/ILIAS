<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilContentPageLP extends ilObjectLP
{
    public static function getDefaultModes(bool $lp_active) : array
    {
        if (true === $lp_active) {
            return [
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_MANUAL,
                ilLPObjSettings::LP_MODE_CONTENT_VISITED,
            ];
        }

        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
        ];
    }
    
    public function getDefaultMode() : int
    {
        return ilLPObjSettings::LP_MODE_MANUAL;
    }

    public function getValidModes() : array
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL,
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
        ];
    }
}
