<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObjectLP.php';

/**
 * File to lp connector
 *
 * @author  Michael Jansen <mjansen@databay.de>
 * @package ModulesFile
 */
class ilFileLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
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
            ilLPObjSettings::LP_MODE_CONTENT_VISITED,
        );
    }
}
