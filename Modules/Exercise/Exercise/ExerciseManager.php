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

namespace ILIAS\Exercise;

use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\DI\Container;
use ILIAS\Exercise\Object\ObjectManager;
use ILIAS\Exercise\Notification\NotificationManager;
use ILIAS\Refinery\Logical\Not;
use ILIAS\Exercise\InstructionFile\InstructionFileManager;
use ILIAS\Exercise\Team\TeamManager;
use ILIAS\Exercise\IndividualDeadline\IndividualDeadlineManager;

class ExerciseManager
{
    public function __construct(
        protected InternalRepoService $repo,
        protected InternalDomainService $domain,
        protected $obj_id
    ) {
    }

    public function delete(
        \ilObjExercise $exc
    ): void {
        // delete assignments
        $ass_manager = $this->domain->assignment()->assignments(
            $this->obj_id,
            0
        );
        foreach ($ass_manager->getAll() as $assignment) {
            $this->domain->assignment()->getAssignment(
                $assignment->getId()
            )->delete($exc, false);
        }

        foreach (\ilExcCriteriaCatalogue::getInstancesByParentId($this->obj_id) as $crit_cat) {
            $crit_cat->delete();
        }
    }
}
