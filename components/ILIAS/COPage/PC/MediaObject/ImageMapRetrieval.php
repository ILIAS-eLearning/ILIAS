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

namespace ILIAS\COPage\PC\MediaObject;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class ImageMapRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected \ilPCInteractiveImage|\ilPCMediaObject $content_obj,
        protected string $parent_node_name
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->applyOrder($this->collectData(), $order);
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

        $alias_item = new \ilMediaAliasItem(
            $this->content_obj->getDomDoc(),
            $this->content_obj->hier_id,
            "Standard",
            $this->content_obj->getPCId(),
            $this->parent_node_name
        );
        $data = [];
        foreach ($alias_item->getMapAreas() as $area) {
            $link = $area["Link"] ?? [];
            $link_type = strtolower($link["LinkType"] ?? "");
            $link_type = match ($link_type) {
                "extlink" => "ext",
                "intlink" => "int",
                default => $link_type
            };
            $data[] = [
                "id" => (int) $area["Nr"],
                "title" => $link["Title"] ?? "",
                "shape" => $area["Shape"],
                "coords" => implode(", ", explode(",", $area["Coords"])),
                "highlight_mode" => $area["HighlightMode"],
                "highlight_class" => $area["HighlightClass"],
                "link_type" => $link_type,
                "href" => $link["Href"] ?? "",
                "target" => $link["Target"] ?? "",
                "type" => $link["Type"] ?? "",
                "target_frame" => $link["TargetFrame"] ?? ""
            ];
        }

        usort(
            $data,
            static fn(array $left, array $right): int => strcasecmp($left["title"], $right["title"])
        );

        return $this->data = $data;
    }
}
