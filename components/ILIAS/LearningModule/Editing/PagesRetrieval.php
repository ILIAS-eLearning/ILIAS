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

namespace ILIAS\LearningModule\Editing;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class PagesRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected int $lm_id,
        protected string $lm_type,
        protected bool $layout_per_page
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        $data = $this->applyOrder($this->getPages(), $order);
        $data = $this->applyRange($data, $range);

        yield from $data;
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->getPages());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }

    protected function getPages(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $lm_set = new \ilSetting("lm");
        $scheduled_activation = (bool) $lm_set->get("time_scheduled_page_activation");
        $data = [];

        foreach (\ilLMPageObject::getPageList($this->lm_id) as $page) {
            $id = (int) $page["obj_id"];
            $active = \ilLMPage::_lookupActive(
                $id,
                $this->lm_type,
                $scheduled_activation
            );
            $scheduled = $scheduled_activation &&
                \ilLMPage::_isScheduledActivation($id, $this->lm_type);

            $data[] = [
                "id" => $id,
                "title" => $page["title"],
                "active" => $active,
                "scheduled" => $scheduled,
                "deactivated_elements" => $active &&
                    \ilLMPage::_lookupContainsDeactivatedElements($id, $this->lm_type),
                "layout" => $this->layout_per_page
                    ? \ilLMObject::lookupLayout($id)
                    : ""
            ];
        }

        return $this->data = $data;
    }
}
