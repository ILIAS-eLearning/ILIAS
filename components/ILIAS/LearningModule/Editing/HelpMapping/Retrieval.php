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

namespace ILIAS\LearningModule\Editing\HelpMapping;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Help\Map\MapManager;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected \ilObjLearningModule $lm,
        protected int $chapter_id,
        protected MapManager $help_map
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getHelpMappings();
        $data = $this->applyOrder($data, $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(array $filter, array $parameters): int
    {
        return count($this->getHelpMappings());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function getHelpMappings(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $tree = $this->lm->getTree();
        if ($this->chapter_id > 0 && $tree->isInTree($this->chapter_id)) {
            $chapters = $tree->getFilteredSubTree($this->chapter_id, ["pg"]);
            unset($chapters[0]);
        } else {
            $chapters = \ilStructureObject::getChapterList($this->lm->getId());
        }

        $this->data = [];
        foreach ($chapters as $chapter) {
            $id = (int) $chapter["obj_id"];
            $this->data[] = [
                "id" => $id,
                "title" => (string) $chapter["title"],
                "screen_ids" => $this->help_map->getScreenIdsOfChapter($id)
            ];
        }

        return $this->data;
    }
}
