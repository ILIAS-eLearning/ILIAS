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
    protected ilOrgUnitUserAssignment $orgu_assignment;
    protected int $src_id;

    public function __construct(
        ilObjOrgUnitTree $orgu_tree,
        ilOrgUnitUserAssignment $orgu_assignment,
        int $src_id
    ) {
        $this->orgu_tree = $orgu_tree;
        $this->orgu_assignment = $orgu_assignment;
        $this->src_id = $src_id;
    }

    /**
     * @inheritdoc
     */
    public function getMemberIds(): array
    {
        $assignees = $this->orgu_assignment::where(
            ['orgu_id' => $this->src_id]
        )->getArray('id', 'user_id');

        return array_map(
            'intval',
            array_values($assignees)
        );
    }
}
