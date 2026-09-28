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

namespace ILIAS\Exercise\Submission;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class DeliveredFilesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExSubmission $submission
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
        $data = $this->applyOrder($data, $order ?? new Order('title', Order::ASC));
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
        return in_array($field, ['id', 'timestamp', 'user_id'], true);
    }

    protected function collectData(): array
    {
        $data = [];
        $submissions = $this->domain->submission(
            $this->submission->getAssignment()->getId()
        );

        foreach ($submissions->getSubmissionsOfUser($this->submission->getUserId()) as $submission) {
            $data[] = [
                'id' => $submission->getId(),
                'title' => $submission->getTitle(),
                'timestamp' => (int) (strtotime($submission->getTimestamp()) ?: 0),
                'user_id' => $submission->getUserId(),
                'late' => $submission->getLate()
            ];
        }

        return $data;
    }
}
