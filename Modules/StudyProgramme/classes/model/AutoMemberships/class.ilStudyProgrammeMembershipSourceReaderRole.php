<?php declare(strict_types=1);

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
class ilStudyProgrammeMembershipSourceReaderRole implements ilStudyProgrammeMembershipSourceReader
{
    protected int $src_id;
    protected ilRbacReview $rbac_review;

    public function __construct(ilRbacReview $rbac_review, int $src_id)
    {
        $this->src_id = $src_id;
        $this->rbac_review = $rbac_review;
    }

    /**
     * @inheritdoc
     */
    public function getMemberIds() : array
    {
        return array_map(
            'intval',
            $this->rbac_review->assignedUsers($this->src_id)
        );
    }
}
