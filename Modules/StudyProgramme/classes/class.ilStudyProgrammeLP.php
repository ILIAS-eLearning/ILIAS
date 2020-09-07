<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectLP.php");

/**
 * Class ilObjStudyProgramme
 *
 * @author : Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilStudyProgrammeLP extends ilObjectLP
{
    /**
     * @var ilObjStudyProgramme|null
     */
    protected $prg = null;
    
    public static function getDefaultModes($a_lp_active)
    {
        return array(
            ilLPObjSettings::LP_MODE_DEACTIVATED
        );
    }
    
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_STUDY_PROGRAMME;
    }
    
    public function getValidModes()
    {
        return array( ilLPObjSettings::LP_MODE_STUDY_PROGRAMME
            , ilLPObjSettings::LP_MODE_DEACTIVATED
            );
    }
    
    public function getMembers($a_search = true)
    {
        if ($this->prg === null) {
            require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
            $this->prg = new ilObjStudyProgramme($this->obj_id, false);
        }
        return $this->prg->getIdsOfUsersWithRelevantProgress();
    }
}
