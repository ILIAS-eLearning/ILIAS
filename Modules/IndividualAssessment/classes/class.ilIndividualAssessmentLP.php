<?php

/* Copyright (c) 2016 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("./Services/Object/classes/class.ilObjectLP.php");

class ilIndividualAssessmentLP extends ilObjectLP
{
    protected $members_ids = null;
    
    /**
     * @inheritdoc
     */
    public function getDefaultMode()
    {
        return ilLPObjSettings::LP_MODE_INDIVIDUAL_ASSESSMENT;
    }
    
    /**
     * @inheritdoc
     */
    public function getValidModes()
    {
        return array(ilLPObjSettings::LP_MODE_INDIVIDUAL_ASSESSMENT
                    ,ilLPObjSettings::LP_MODE_DEACTIVATED);
    }
    
    /**
     * Get an array of member ids participating in the obnject coresponding to this.
     *
     * @return int|string[]
     */
    public function getMembers($a_search = true)
    {
        if ($this->members_ids === null) {
            global $DIC;
            require_once("Modules/IndividualAssessment/classes/class.ilObjIndividualAssessment.php");
            $iass = new ilObjIndividualAssessment($this->obj_id, false);
            $this->members_ids = $iass->loadMembers()->membersIds();
        }
        return $this->members_ids;
    }
}
