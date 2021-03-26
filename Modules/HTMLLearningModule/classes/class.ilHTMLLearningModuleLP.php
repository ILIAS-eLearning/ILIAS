<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * HTML lm to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilHTMLLearningModuleLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        if (!$a_lp_active) {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED
            );
        } else {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_MANUAL
            );
        }
    }
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_MANUAL;
    }
    
    public function getValidModes()
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_MANUAL
        );
    }
}
