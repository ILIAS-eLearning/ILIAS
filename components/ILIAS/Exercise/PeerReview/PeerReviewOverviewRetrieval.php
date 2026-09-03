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

namespace ILIAS\Exercise\PeerReview;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Exercise\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class PeerReviewOverviewRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;
    protected array $panel_info = [];

    public function __construct(
        protected InternalDomainService $domain,
        protected \ilExAssignment $assignment
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
        return $field === 'id';
    }

    public function getPanelInfo(): array
    {
        $this->collectData();

        return $this->panel_info;
    }

    protected function collectData(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $this->data = [];
        $this->panel_info = [];

        $peer_review = $this->domain->peerReview()->exPeerReview($this->assignment);
        if ($peer_review === null) {
            return $this->data;
        }

        $peer_review_data = $peer_review->validatePeerReviewGroups();
        if (!is_array($peer_review_data)) {
            return $this->data;
        }

        $id = 0;
        foreach ($peer_review_data['reviews'] as $peer_id => $reviews) {
            $peer = current($this->translateUserIds([$peer_id]));

            foreach ($reviews as $giver_id => $status) {
                $this->data[] = [
                    'id' => ++$id,
                    'recipient' => $peer,
                    'giver' => current($this->translateUserIds([$giver_id])),
                    'status' => $status ? $this->domain->lng()->txt('valid') : ''
                ];
            }
        }

        $this->addPanelInfo(
            $peer_review_data,
            'missing_user_ids',
            'exc_peer_review_missing_users'
        );
        $this->addPanelInfo(
            $peer_review_data,
            'not_returned_ids',
            'exc_peer_review_not_returned_users'
        );
        $this->addPanelInfo(
            $peer_review_data,
            'invalid_peer_ids',
            'exc_peer_review_invalid_peer_ids'
        );
        $this->addPanelInfo(
            $peer_review_data,
            'invalid_giver_ids',
            'exc_peer_review_invalid_giver_ids'
        );

        return $this->data;
    }

    protected function addPanelInfo(
        array $peer_review_data,
        string $user_ids_key,
        string $title_key
    ): void {
        if (!$peer_review_data[$user_ids_key]) {
            return;
        }

        $this->panel_info[] = [
            'title' => $this->domain->lng()->txt($title_key),
            'value' => $this->translateUserIds($peer_review_data[$user_ids_key])
        ];
    }

    protected function translateUserIds(array $user_ids): array
    {
        $result = [];

        foreach (array_unique($user_ids) as $user_id) {
            $result[] = \ilUserUtil::getNamePresentation($user_id);
        }

        return $result;
    }
}
