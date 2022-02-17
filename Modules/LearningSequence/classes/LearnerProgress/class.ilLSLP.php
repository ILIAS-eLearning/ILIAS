<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * lp connector
 */
class ilLSLP extends ilObjectLP
{
    /**
     * @ineritdoc
     */
    public static function getDefaultModes(bool $a_lp_active) : array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED
        );
    }

    /**
     * @ineritdoc
     */
    public function getDefaultMode() : int
    {
        return ilLPObjSettings::LP_MODE_DEACTIVATED;
    }

    /**
     * @ineritdoc
     */
    public function getValidModes() : array
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_COLLECTION
        );
    }
}
