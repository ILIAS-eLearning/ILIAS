<?php

declare(strict_types=1);

/**
 * lp connector
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED
        );
    }

    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    public function getValidModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_COLLECTION
        );
    }
}
