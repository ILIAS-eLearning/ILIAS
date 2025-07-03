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

namespace ILIAS\Blog\Contributor;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalBase;

class ContributorRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected \ilRbacReview $rbac_review;
    protected array $local_roles = [];

    public function __construct(
        \ilRbacReview $rbac_review,
        array $local_roles
    ) {
        $this->rbac_review = $rbac_review;
        $this->local_roles = $local_roles;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->collectData();

        // Apply ordering if specified
        $data = $this->applyOrder($data, $order);

        // Apply range (pagination) if specified
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            yield $row;
        }
    }

    protected function applyOrder(array $data, ?Order $order = null): array
    {
        if ($order !== null) {
            $order_field = array_keys($order->get())[0];
            $order_direction = $order->get()[$order_field];

            array_multisort(
                array_column($data, $order_field),
                $order_direction === 'ASC' ? SORT_ASC : SORT_DESC,
                $this->isFieldNumeric($order_field) ? SORT_NUMERIC : SORT_STRING,
                $data
            );
        }
        return $data;
    }

    protected function applyRange(array $data, ?Range $range = null): array
    {
        if ($range !== null) {
            $offset = $range->getStart();
            $limit = $range->getLength();
            $data = array_slice($data, $offset, $limit);
        }
        return $data;
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->collectData());
    }

    protected function collectData(): array
    {
        $user_map = $assigned = array();
        foreach ($this->local_roles as $id => $title) {
            $local = $this->rbac_review->assignedUsers($id);
            $assigned = array_merge($assigned, $local);
            foreach ($local as $user_id) {
                $user_map[$user_id][] = $title;
            }
        }

        $data = array();
        foreach (array_unique($assigned) as $id) {
            $data[] = array(
                "id" => $id,
                "name" => \ilUserUtil::getNamePresentation($id, false, false, "", true),
                "role" => $user_map[$id]
            );
        }

        return $data;
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

}
