<?php declare(strict_types=1);

/* Copyright (c) 2016 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */

class ilIndividualAssessmentLP extends ilObjectLP
{
    /**
     * @var int[]|string[]
     */
    protected ?array $members_ids = null;

    public function getDefaultMode() : int
    {
        return ilLPObjSettings::LP_MODE_INDIVIDUAL_ASSESSMENT;
    }

    public function getValidModes() : array
    {
        return [
            ilLPObjSettings::LP_MODE_INDIVIDUAL_ASSESSMENT,
            ilLPObjSettings::LP_MODE_DEACTIVATED
        ];
    }
    
    /**
     * Get an array of member ids participating in the object corresponding to this.
     */
    public function getMembers(bool $a_search = true) : array
    {
        if ($this->members_ids === null) {
            $iass = new ilObjIndividualAssessment($this->obj_id, false);
            $this->members_ids = $iass->loadMembers()->membersIds();
        }
        return $this->members_ids;
    }
}
