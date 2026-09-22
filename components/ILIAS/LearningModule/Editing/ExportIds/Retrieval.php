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

namespace ILIAS\LearningModule\Editing\ExportIds;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;
use ilLMPageObject;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(protected int $lm_id)
    {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getExportIds();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->getExportIds());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function getExportIds(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $duplicate_export_ids = ilLMPageObject::getDuplicateExportIDs($this->lm_id);
        $data = [];
        foreach (ilLMPageObject::getPageList($this->lm_id) as $page) {
            $id = (int) $page["obj_id"];
            $export_id = ilLMPageObject::getExportId(
                $this->lm_id,
                $id,
                $page["type"]
            );
            $data[] = [
                "id" => $id,
                "title" => $page["title"],
                "export_id" => $export_id,
                "duplicate" => ($duplicate_export_ids[$export_id] ?? 0) > 1
            ];
        }

        return $this->data = $data;
    }
}
