<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the license along with the
 * source code, too.
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MediaObjects\ImageMap;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class ImageMapRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected \ilObjMediaObject $media_object
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

    public function count(array $filter = [], array $parameters = []): int
    {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function collectData(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $item = $this->media_object->getMediaItem("Standard");
        $data = [];
        $highlight_modes = \ilMapArea::getAllHighlightModes();
        $highlight_classes = \ilMapArea::getAllHighlightClasses();

        for ($id = 1, $max = \ilMapArea::_getMaxNr($item->getId()); $id <= $max; $id++) {
            $area = new \ilMapArea($item->getId(), $id);
            $data[] = [
                "id" => $id,
                "title" => $area->getTitle(),
                "shape" => $area->getShape(),
                "coords" => implode(", ", explode(",", $area->getCoords())),
                "highlight_mode" => $highlight_modes[$area->getHighlightMode()] ?? $area->getHighlightMode(),
                "highlight_class" => $highlight_classes[$area->getHighlightClass()] ?? $area->getHighlightClass(),
                "link_type" => $area->getLinkType(),
                "href" => $area->getHref(),
                "target" => $area->getTarget(),
                "type" => $area->getType(),
                "target_frame" => $area->getTargetFrame()
            ];
        }

        return $this->data = $data;
    }
}
