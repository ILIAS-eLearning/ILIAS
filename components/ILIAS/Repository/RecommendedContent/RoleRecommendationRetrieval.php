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

namespace ILIAS\Repository\RecommendedContent;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class RoleRecommendationRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilRecommendedContentManager $manager,
        protected \ilTree $tree,
        protected int $role_id
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

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === 'id' || $field === 'ref_id';
    }

    protected function collectData(): array
    {
        $data = [];

        foreach ($this->manager->getRecommendationsOfRole($this->role_id) as $ref_id) {
            $ref_id = (int) $ref_id;
            $data[] = [
                'id' => $ref_id,
                'ref_id' => $ref_id,
                'title' => \ilObject::_lookupTitle(\ilObject::_lookupObjectId($ref_id)),
                'path' => $this->formatPath($this->tree->getPathFull($ref_id))
            ];
        }

        return $data;
    }

    protected function formatPath(array $path): string
    {
        return implode(' &raquo; ', array_column($path, 'title'));
    }

}
