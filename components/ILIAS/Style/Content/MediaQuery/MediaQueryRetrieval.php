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

namespace ILIAS\Style\Content\MediaQuery;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class MediaQueryRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilObjStyleSheet $style_obj
    ) {
    }

    public function getData(
        array $visible_column_ids,
        ?Range $range = null,
        ?Order $order = null,
        array $filter_data = [],
        array $additional_parameters = []
    ): \Generator {
        $data = [];
        foreach ($this->style_obj->getMediaQueries() as $media_query) {
            $data[] = [
                "id" => (int) $media_query["id"],
                "mquery" => (string) $media_query["mquery"],
                "order_nr" => (int) $media_query["order_nr"]
            ];
        }

        $data = $this->applyOrder($data, $order);
        yield from $this->applyRange($data, $range);
    }

    public function count(
        array $filter_data = [],
        array $additional_parameters = []
    ): int {
        return count($this->style_obj->getMediaQueries());
    }

    public function isFieldNumeric(string $field): bool
    {
        return in_array($field, ["id", "order_nr"], true);
    }
}
