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

namespace ILIAS\Exercise\PeerReview\Criteria;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class CriteriaCatalogueRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;
    protected bool $has_protected_assignments = false;

    public function __construct(
        protected InternalDomainService $domain,
        protected int $exc_id
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
        $order ??= new Order('pos', Order::ASC);
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ['id', 'pos'], true);
    }

    public function hasProtectedAssignments(): bool
    {
        $this->collectData();

        return $this->has_protected_assignments;
    }

    protected function collectData(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $assigned = [];
        $protected = [];
        foreach (\ilExAssignment::getInstancesByExercise($this->exc_id) as $assignment) {
            $catalogue_id = $assignment->getPeerReviewCriteriaCatalogue();
            if (!$catalogue_id) {
                continue;
            }

            $assigned[$catalogue_id][$assignment->getId()] = $assignment->getTitle();

            $peer_review = new \ilExPeerReview($assignment);
            if ($peer_review->hasPeerReviewGroups()) {
                $protected[$catalogue_id][] = $assignment->getId();
                $this->has_protected_assignments = true;
            }
        }

        $this->data = [];
        $pos = 0;
        foreach (\ilExcCriteriaCatalogue::getInstancesByParentId($this->exc_id) as $catalogue) {
            $pos += 10;

            $criteria = [];
            foreach (\ilExcCriteria::getInstancesByParentId($catalogue->getId()) as $criterion) {
                $criteria[] = $criterion->getTitle() . ' (' . $criterion->getTranslatedType() . ')';
            }

            $assignments = [];
            foreach ($assigned[$catalogue->getId()] ?? [] as $assignment_id => $assignment_title) {
                $assignment_row = $assignment_title;
                if (in_array($assignment_id, $protected[$catalogue->getId()] ?? [], true)) {
                    $assignment_row .= ' <span class="ilAlert small">(' .
                        $this->domain->lng()->txt('exc_crit_cat_protected_assignment') .
                        ')</span>';
                }
                $assignments[] = $assignment_row;
            }

            $this->data[] = [
                'id' => (int) $catalogue->getId(),
                'pos' => $pos,
                'title' => (string) $catalogue->getTitle(),
                'criterias' => implode('<br>', $criteria),
                'assignments' => implode('<br>', $assignments),
                'protected' => isset($protected[$catalogue->getId()])
            ];
        }

        return $this->data;
    }

}
