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

namespace ILIAS\LearningModule\Editing;

use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Repository\RetrievalInterface;

class SubObjectRetrieval implements RetrievalInterface
{
    protected \ilLanguage $lng;
    protected \ILIAS\UI\Factory $f;
    protected ?array $childs = null;

    public function __construct(
        protected \ilLMTree $lm_tree,
        protected $type = "",
        protected $current_node = 0,
        protected $transl = ""
    ) {
        global $DIC;
        $this->f = $DIC->ui()->factory();
        $this->lng = $DIC->language();
    }

    public function getChildTitle(array $child): string
    {
        if (!in_array($this->transl, ["-", ""])) {
            $lmobjtrans = new \ilLMObjTranslation($child["child"], $this->transl);
            return $lmobjtrans->getTitle();
        }
        return $child["title"];
    }

    protected function getChilds(): array
    {
        $current_node = ($this->current_node > 0)
            ? $this->current_node
            : $this->lm_tree->readRootId();
        if (is_null($this->childs)) {
            $this->childs = $this->lm_tree->getChildsByType($current_node, $this->type);
        }
        return $this->childs;
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): \Generator {
        foreach ($this->getChilds() as $child) {
            $active = true;
            $scheduled = false;
            $deactivated_elements = false;
            if ($child["type"] === "pg") {
                // check activation
                $lm_set = new \ilSetting("lm");
                $active = \ilLMPage::_lookupActive(
                    $child["obj_id"],
                    "lm",
                    (bool) $lm_set->get("time_scheduled_page_activation")
                );

                // is page scheduled?
                $scheduled = ((bool) $lm_set->get("time_scheduled_page_activation") &&
                    \ilLMPage::_isScheduledActivation($child["obj_id"], "lm"));
                if ($active) {
                    $deactivated_elements = (\ilLMPage::_lookupContainsDeactivatedElements(
                        $child["obj_id"],
                        "lm"
                    ));
                }
            }
            $trans_title = "";
            if (!in_array($this->transl, ["-", ""])) {
                $trans_title = $this->getChildTitle($child);
            }

            yield [
                "id" => $child["child"],
                "deactivated_elements" => $deactivated_elements,
                "active" => $active,
                "scheduled" => $scheduled,
                "type" => $child["type"],
                "title" => $child["title"],
                "trans_title" => $trans_title
            ];
        }
    }

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->getChilds());
    }

    public function isFieldNumeric(string $field): bool
    {
        return $field === "id";
    }
}
