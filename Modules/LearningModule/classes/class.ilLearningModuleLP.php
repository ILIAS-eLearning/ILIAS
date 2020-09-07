<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * LM to lp connector
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesLearningModule
 */
class ilLearningModuleLP extends ilObjectLP
{
    public static function getDefaultModes($a_lp_active)
    {
        if (!$a_lp_active) {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_QUESTIONS,
                ilLPObjSettings::LP_MODE_VISITED_PAGES
            );
        } else {
            return array(
                ilLPObjSettings::LP_MODE_DEACTIVATED,
                ilLPObjSettings::LP_MODE_MANUAL,
                ilLPObjSettings::LP_MODE_QUESTIONS,
                ilLPObjSettings::LP_MODE_VISITED_PAGES
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
            ilLPObjSettings::LP_MODE_MANUAL,
            ilLPObjSettings::LP_MODE_COLLECTION_MANUAL,
            ilLPObjSettings::LP_MODE_VISITS,
            ilLPObjSettings::LP_MODE_TLT,
            ilLPObjSettings::LP_MODE_COLLECTION_TLT,
            ilLPObjSettings::LP_MODE_QUESTIONS,
            ilLPObjSettings::LP_MODE_VISITED_PAGES
        );
    }
}
