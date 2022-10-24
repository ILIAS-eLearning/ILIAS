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

class ilStudyProgrammeLP extends ilObjectLP
{
    protected ?ilObjStudyProgramme $prg = null;

    /**
     * @param bool $lp_active
     * @return int[]
     */
    public static function getDefaultModes(bool $lp_active): array
    {
        return [ilLPObjSettings::LP_MODE_DEACTIVATED];
    }

    public function getDefaultMode(): int
    {
        return ilLPObjSettings::LP_MODE_STUDY_PROGRAMME;
    }

    /**
     * @return array
     */
    public function getValidModes(): array
    {
        return [
            ilLPObjSettings::LP_MODE_STUDY_PROGRAMME,
            ilLPObjSettings::LP_MODE_DEACTIVATED
        ];
    }

    /**
     * @param bool $search
     * @return array
     */
    public function getMembers(bool $search = true): array
    {
        if ($this->prg === null) {
            $this->prg = new ilObjStudyProgramme($this->obj_id, false);
        }
        return $this->prg->getIdsOfUsersWithRelevantProgress();
    }
}
