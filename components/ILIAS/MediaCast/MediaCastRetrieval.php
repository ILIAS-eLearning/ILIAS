<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MediaCast;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class MediaCastRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    public function __construct(
        protected \ilObjMediaCast $media_cast
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->media_cast->getSortedItemsArray();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        foreach ($data as $row) {
            $row['is_image'] = false;
            if (isset($row['mob_id'])) {
                $mob = new \ilObjMediaObject((int) $row['mob_id']);
                $med = $mob->getMediaItem('Standard');
                $row['is_image'] = $med !== null
                    && str_starts_with((string) $med->getFormat(), 'image/');
            }
            yield $row;
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->media_cast->getSortedItemsArray());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === 'id';
    }
}
