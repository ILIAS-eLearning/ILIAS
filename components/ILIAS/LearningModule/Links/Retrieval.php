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

namespace ILIAS\LearningModule\Links;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;
use ilCtrl;
use ilLanguage;
use ilLMPage;
use ilLMObject;
use ilLMPageObject;
use ilLMPageObjectGUI;
use ilGlossaryTerm;
use ilObject;

class Retrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected int $lm_id,
        protected string $lm_type,
        protected ilCtrl $ctrl,
        protected ilLanguage $lng
    ) {
    }

    public function getData(
        array $fields,
        ?Range $range = null,
        ?Order $order = null,
        array $filter = [],
        array $parameters = []
    ): Generator {
        $data = $this->getPages();
        $data = $this->applyOrder($data, $order);
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

        $data = [];
        foreach (ilLMPageObject::getPagesWithLinksList($this->lm_id, $this->lm_type) as $page) {
            $page_id = (int) $page["obj_id"];
            $page_object = new ilLMPage($page_id);
            $page_object->buildDom();

            $data[] = [
                "id" => $page_id,
                "title" => (string) $page["title"],
                "page_link" => $this->getPageLink($page_id),
                "links" => $this->getLinkLabels($page_object->getInternalLinks())
            ];
        }

        return $this->data = $data;
    }

    protected function getPageLink(int $page_id): string
    {
        $this->ctrl->setParameterByClass(ilLMPageObjectGUI::class, "obj_id", $page_id);
        $link = $this->ctrl->getLinkTargetByClass(ilLMPageObjectGUI::class, "edit");
        $this->ctrl->clearParameterByClass(ilLMPageObjectGUI::class, "obj_id");

        return $link;
    }

    protected function getLinkLabels(array $links): array
    {
        $labels = [];
        foreach ($links as $link) {
            $label = $this->getLinkLabel(
                (string) ($link["Type"] ?? ""),
                (string) ($link["Target"] ?? "")
            );
            if ($label !== null) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    protected function getLinkLabel(string $type, string $target): ?string
    {
        $type_label = $this->getLinkTypeLabel($type);
        if ($type_label === null) {
            return null;
        }

        if (!str_starts_with($target, "il__")) {
            return $type_label . ": " . $this->getMissingTargetLabel($target);
        }

        $target_parts = explode("_", $target);
        $target_id = (int) $target_parts[count($target_parts) - 1];
        $title = $this->getTargetTitle($type, $target_id);

        return $type_label . ": " . ($title ?? $this->getMissingTargetLabel($target_id));
    }

    protected function getLinkTypeLabel(string $type): ?string
    {
        return match ($type) {
            "PageObject" => $this->lng->txt("pg"),
            "StructureObject" => $this->lng->txt("st"),
            "GlossaryItem" => $this->lng->txt("cont_term"),
            "MediaObject" => $this->lng->txt("mob"),
            "RepositoryItem" => $this->lng->txt("cont_repository_item"),
            default => null
        };
    }

    protected function getTargetTitle(string $type, int $target_id): ?string
    {
        return match ($type) {
            "PageObject", "StructureObject" => $this->getLMObjectTitle($target_id),
            "GlossaryItem" => ilGlossaryTerm::_exists($target_id)
                ? ilGlossaryTerm::_lookGlossaryTerm($target_id)
                : null,
            "MediaObject" => ilObject::_exists($target_id)
                ? ilObject::_lookupTitle($target_id)
                : null,
            "RepositoryItem" => $this->getRepositoryObjectTitle($target_id),
            default => null
        };
    }

    protected function getLMObjectTitle(int $target_id): ?string
    {
        if (!ilLMObject::_exists($target_id)) {
            return null;
        }

        $target_lm_id = ilLMObject::_lookupContObjID($target_id);
        $suffix = $target_lm_id !== $this->lm_id
            ? " (" . ilObject::_lookupTitle($target_lm_id) . ")"
            : "";

        return ilLMObject::_lookupTitle($target_id) . $suffix;
    }

    protected function getRepositoryObjectTitle(int $target_id): ?string
    {
        $object_type = ilObject::_lookupType($target_id, true);
        $object_id = ilObject::_lookupObjId($target_id);
        if (!ilObject::_exists($object_id)) {
            return null;
        }

        return ilObject::_lookupTitle($object_id) . " ("
            . $this->lng->txt("obj_" . $object_type)
            . ")";
    }

    protected function getMissingTargetLabel(int|string $target): string
    {
        return $this->lng->txt("cont_target_missing") . " [" . $target . "]";
    }
}
