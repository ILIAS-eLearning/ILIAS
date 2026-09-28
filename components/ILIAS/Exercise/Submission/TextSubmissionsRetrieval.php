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

class TextSubmissionsRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExAssignment $assignment,
        protected bool $show_peer_review
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
        return $field === 'timestamp';
    }

    protected function collectData(): array
    {
        $peer_data = [];
        if ($this->show_peer_review) {
            $peer_data = (new \ilExPeerReview($this->assignment))->getAllPeerReviews();
        }

        $data = [];
        foreach ($this->domain->submission($this->assignment->getId())->getAllAssignmentFiles() as $file) {
            if (!trim($file['atext'])) {
                continue;
            }

            $user_id = (int) $file['user_id'];
            $data[$user_id] = [
                'id' => $user_id,
                'user' => \ilUserUtil::getNamePresentation($user_id),
                'timestamp' => strtotime($file['ts']) ?: 0,
                'text' => \ilRTE::_replaceMediaObjectImageSrc($file['atext'], 1)
            ];

            if (isset($peer_data[$user_id])) {
                $data[$user_id]['peer'] = array_keys($peer_data[$user_id]);
            }
        }

        return array_values($data);
    }
}
