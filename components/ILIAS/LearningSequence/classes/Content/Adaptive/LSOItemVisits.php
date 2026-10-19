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

declare(strict_types=1);

namespace ILIAS\LearningSequence\Content\Adaptive;

use ilDBInterface;
use ILIAS\LearningSequence\LearningMap\LSOLearningMapPosition;

/**
 * Removes entries of the visit log of the learners (lso_item_visits).
 * The entries are written by LSOLearningMapPosition.
 */
class LSOItemVisits
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    /**
     * Removes all visits of a learner in a learning sequence.
     */
    public function reset(int $usr_id, int $lso_obj_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . LSOLearningMapPosition::VISITS_TABLE . ' WHERE usr_id = %s AND lso_obj_id = %s',
            ['integer', 'integer'],
            [$usr_id, $lso_obj_id]
        );
    }

    /**
     * Removes all visits of an object of a learning sequence.
     */
    public function deleteForItemRefId(int $lso_obj_id, int $ref_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM ' . LSOLearningMapPosition::VISITS_TABLE . ' WHERE lso_obj_id = %s AND ref_id = %s',
            ['integer', 'integer'],
            [$lso_obj_id, $ref_id]
        );
    }
}
