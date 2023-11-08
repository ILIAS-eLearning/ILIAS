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

/**
 * Provides adapters to read member-ids from a specific source.
 */
class ilStudyProgrammeMembershipSourceReaderOrgu implements ilStudyProgrammeMembershipSourceReader
{
    protected ilObjOrgUnitTree $orgu_tree;
    protected int $src_id;
    protected ilOrgUnitUserAssignmentDBRepository $assignmentRepo;

    public function __construct(
        ilObjOrgUnitTree $orgu_tree,
        int $src_id
    ) {
        $this->orgu_tree = $orgu_tree;
        $this->src_id = $src_id;

        $dic = ilOrgUnitLocalDIC::dic();
        $this->assignmentRepo = $dic["repo.UserAssignments"];
    }

    /**
     * @inheritdoc
     */
    public function getMemberIds(): array
    {
        return $this->assignmentRepo->getUsersByOrgUnits([$this->src_id]);
    }
}
