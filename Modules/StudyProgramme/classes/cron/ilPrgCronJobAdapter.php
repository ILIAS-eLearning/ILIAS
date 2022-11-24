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


interface ilPrgCronJobAdapter
{
    /**
     * acts as a pre-filter to not touch all PRGs;
     * return obj_ids and config-setting, when a mail should be issued.
     *
     * @return array <$programme_obj_id => $days_offset_mail>
     */
    public function getRelevantProgrammeIds(): array;

    /**
     * actual (additional) payload; what to do with the found assignment(s)
     */
    public function actOnSingleAssignment(ilPRGAssignment $ass): void;
}
