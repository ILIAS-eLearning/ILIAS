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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class AssignmentsRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $exercise_id
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
        $data = $this->applyOrder($data, $order ?? new Order('order_val', Order::ASC));
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
        return in_array($field, ['order_val', 'deadline', 'start_time'], true);
    }

    protected function collectData(): array
    {
        $data = \ilExAssignment::getAssignmentDataOfExercise($this->exercise_id);
        $types = \ilExAssignmentTypes::getInstance();
        $exercise = new \ilObjExercise($this->exercise_id, false);
        $random_manager = $this->domain->assignment()->randomAssignments($exercise);

        foreach ($data as $idx => $row) {
            if ($row['peer']) {
                $data[$idx]['peer_invalid'] = true;
                $peer_review = new \ilExPeerReview(new \ilExAssignment($row['id']));
                $peer_reviews = $peer_review->validatePeerReviewGroups();
                if (is_array($peer_reviews)) {
                    $data[$idx]['peer_invalid'] = $peer_reviews['invalid'];
                }
                if (is_null($peer_reviews)) {
                    $data[$idx]['peer_invalid'] = false;
                }
            }

            $data[$idx]['type'] = $types->getById($row['type'])->getTitle();
            $data[$idx]['random'] = $random_manager->isActivated();
        }

        return $data;
    }
}
