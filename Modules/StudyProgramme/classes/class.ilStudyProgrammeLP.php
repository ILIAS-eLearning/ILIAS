<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilStudyProgrammeLP extends ilObjectLP
{
    protected ?ilObjStudyProgramme $prg = null;

    /**
     * @param bool $lp_active
     * @return int[]
     */
    public static function getDefaultModes($lp_active) : array
    {
        return [ilLPObjSettings::LP_MODE_DEACTIVATED];
    }
    
    public function getDefaultMode() : int
    {
        return ilLPObjSettings::LP_MODE_STUDY_PROGRAMME;
    }

    /**
     * @return int[]
     */
    public function getValidModes() : array
    {
        return [
            ilLPObjSettings::LP_MODE_STUDY_PROGRAMME,
            ilLPObjSettings::LP_MODE_DEACTIVATED
        ];
    }

    /**
     * @param bool $search
     * @return int[]
     */
    public function getMembers($search = true) : array
    {
        if ($this->prg === null) {
            $this->prg = new ilObjStudyProgramme($this->obj_id, false);
        }
        return $this->prg->getIdsOfUsersWithRelevantProgress();
    }
}
