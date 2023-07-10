<?php

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

/**
 * Handles exercise repository object assignments. Main entry point for consumers.
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcRepoObjAssignment implements ilExcRepoObjAssignmentInterface
{
    /**
     * Constructor
     *
     */
    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        return new self();
    }

    /**
     * @return \ilExcRepoObjAssignmentInfo[]
     */
    public function getAssignmentInfoOfObj(int $a_ref_id, int $a_user_id): array
    {
        return ilExcRepoObjAssignmentInfo::getInfo($a_ref_id, $a_user_id);
    }

    public function getAccessInfo(
        int $a_ref_id,
        int $a_user_id
    ): ilExcRepoObjAssignmentAccessInfoInterface {
        return ilExcRepoObjAssignmentAccessInfo::getInfo($a_ref_id, $a_user_id);
    }
}
