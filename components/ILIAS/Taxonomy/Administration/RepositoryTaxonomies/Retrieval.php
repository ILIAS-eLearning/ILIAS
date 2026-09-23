<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Taxonomy\Administration\RepositoryTaxonomies;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(protected array $taxonomies)
    {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getRepositoryTaxonomies();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->getRepositoryTaxonomies());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    protected function getRepositoryTaxonomies(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $this->data = [];
        foreach ($this->taxonomies as $tax_id => $objects) {
            foreach ($objects as $obj_id => $obj) {
                array_pop($obj["path"]);
                $path = implode(" › ", $obj["path"]);
                $this->data[] = [
                    "id" => $tax_id . "_" . $obj_id,
                    "tax_title" => (string) $obj["tax_title"],
                    "tax_status" => (bool) $obj["tax_status"],
                    "obj_title" => (string) $obj["obj_title"],
                    "references" => [[
                        "path" => $path,
                        "url" => \ilLink::_getLink((int) $obj["ref_id"])
                    ]]
                ];
            }
        }

        return $this->data;
    }
}
