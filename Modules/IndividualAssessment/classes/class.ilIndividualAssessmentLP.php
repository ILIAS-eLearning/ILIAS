<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilIndividualAssessmentLP extends ilObjectLP
{
    /**
     * @var int[]|string[]
     */
    protected ?array $members_ids = null;

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_INDIVIDUAL_ASSESSMENT;
    }

    public function getValidModes(): array
    {
        return [
            ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_INDIVIDUAL_ASSESSMENT
        ];
    }

    /**
     * Get an array of member ids participating in the object corresponding to this.
     */
    public function getMembers(bool $a_search = true): array
    {
        if ($this->members_ids === null) {
            $iass = new ilObjIndividualAssessment($this->obj_id, false);
            $this->members_ids = $iass->loadMembers()->membersIds();
        }
        return $this->members_ids;
    }
}
