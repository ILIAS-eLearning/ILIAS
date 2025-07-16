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

namespace ILIAS\Container\StartObjects\Objects\Table;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalInterface;
use ILIAS\Repository\RetrievalBase;
use ilContainerStartObjects;
use ilObject;
use ilObjectFactory;
use ILIAS\UI\Factory as UIFactory;

class PossibleObjectsRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected ilContainerStartObjects $start_objects
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        foreach ($this->start_objects->getPossibleStarters() as $item_ref_id) {
            $data = [];
            $tmp_obj = ilObjectFactory::getInstanceByRefId($item_ref_id);

            $data['id'] = $item_ref_id;
            $data['title'] = $tmp_obj->getTitle();
            $data['type'] = $tmp_obj->getType();

            if ($tmp_obj->getDescription() !== '') {
                $data['description'] = $tmp_obj->getDescription();
            }

            yield $data;
        }
    }

    public function count(
        array $filter,
        array $parameters
    ): int {
        return count($this->start_objects->getPossibleStarters());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === 'id';
    }
}
