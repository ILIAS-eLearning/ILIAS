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

namespace ILIAS\Search\Result\Filter;

use ILIAS\Search\Result\Filter\Criterion\Criterion;

class FilterImpl implements Filter
{
    public function filter(
        Criterion $criterion,
        int $offset,
        int $limit,
        int ...$raw_ids
    ): FilteredResult {
        $filtered = [];
        $is_completed = true;
        $criterion->preloadData(...$raw_ids);
        foreach ($raw_ids as $id) {
            if ($criterion->doesFulfill($id)) {
                $filtered[] = $id;
            }
            // always look for one more valid result find out whether the result is complete
            if (count($filtered) > $offset + $limit) {
                $is_completed = false;
                break;
            }
        }
        $filtered = array_slice($filtered, $offset, $limit);
        return new FilteredResultImpl($is_completed, $filtered);
    }
}
