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

namespace ILIAS\Exercise\Grades;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class GradesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    /**
     * @param \ilExAssignment[] $assignments
     */
    public function __construct(
        protected InternalDomainService $domain,
        protected \ilObjExercise $exercise,
        protected \ilExerciseMembers $members,
        protected array $assignments
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();
        $data = $this->applyOrder($data, $order ?? new Order('name', Order::ASC));
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(array $filter = [], array $parameters = []): int
    {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === 'id';
    }

    protected function collectData(): array
    {
        $data = [];
        $user_ids = $this->domain->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'edit_submissions_grades',
            'edit_submissions_grades',
            $this->exercise->getRefId(),
            $this->members->getMembers()
        );

        foreach ($user_ids as $user_id) {
            $user = \ilObjUser::_lookupName($user_id);
            $row = [
                'id' => (int) $user_id,
                'name' => $user['lastname'] . ', ' . $user['firstname'],
                'login' => $user['login'],
                'mark' => (string) \ilLPMarks::_lookupMark($user_id, $this->exercise->getId()),
                'remark' => (string) \ilLPMarks::_lookupComment($user_id, $this->exercise->getId()),
                'total' => \ilExerciseMembers::_lookupStatus($this->exercise->getId(), $user_id)
            ];

            foreach ($this->assignments as $assignment) {
                $status = new \ilExAssignmentMemberStatus($assignment->getId(), $user_id);
                $row['assignment_' . $assignment->getId()] = $status->getStatus();
            }

            $data[] = $row;
        }

        return $data;
    }
}
