<?php declare(strict_types=1);

/**
 * Class ilForumLP
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION,
        ];
    }

    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes()
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTRIBUTION_TO_DISCUSSION,
        ];
    }
}
