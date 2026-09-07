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

namespace ILIAS\MediaObjects\Usage;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\MediaObjects\InternalDomainService;
use ILIAS\Repository\RetrievalBase;
use ILIAS\Repository\RetrievalInterface;

class UsageRetrieval implements RetrievalInterface
{
    use RetrievalBase;

    protected ?array $data = null;

    public function __construct(
        protected \ilObjMediaObject $media_object,
        protected bool $include_hist,
        protected InternalDomainService $domain
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

    public function count(
        array $filter = [],
        array $parameters = []
    ): int {
        return count($this->collectData());
    }

    public function isFieldNumeric(string $field): bool
    {
        return false;
    }

    protected function collectData(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $usages = $this->media_object->getUsages($this->include_hist);
        $clip_count = 0;
        $aggregated_usages = [];

        foreach ($usages as $usage) {
            $usage["trash"] = false;
            if (str_contains((string) ($usage["type"] ?? ""), ":")) {
                [$container_type, $usage_type] = explode(":", (string) $usage["type"], 2);

                if ($usage_type === "pg") {
                    $page = \ilPageObjectFactory::getInstance($container_type, (int) $usage["id"]);
                    $usage["page"] = $page;
                    $repo_tree = $this->domain->repositoryTree();
                    $ref_ids = array_filter(
                        \ilObject::_getAllReferences($page->getRepoObjId()),
                        static fn($ref_id): bool => $repo_tree->isInTree($ref_id)
                    );
                    if ($ref_ids === []) {
                        $usage["trash"] = true;
                    }
                }
            }

            if (($usage["type"] ?? "") === "clip") {
                $clip_count++;
                continue;
            }

            if (!$this->include_hist && ($usage["trash"] ?? false)) {
                continue;
            }

            $key = (string) $usage["type"] . ":" . (string) $usage["id"];
            if (!isset($aggregated_usages[$key])) {
                $aggregated_usages[$key] = $usage;
            }
            $aggregated_usages[$key]["versions"][] = [
                "hist_nr" => $usage["hist_nr"] ?? 0,
                "lang" => $usage["lang"] ?? ""
            ];
        }

        if ($clip_count > 0) {
            $aggregated_usages["clip"] = [
                "type" => "clip",
                "cnt" => $clip_count
            ];
        }

        $this->data = [];
        foreach ($aggregated_usages as $id => $usage) {
            $this->data[] = $this->buildRow((string) $id, $usage);
        }

        return $this->data;
    }

    protected function buildRow(string $id, array $usage): array
    {
        $usage_type = (string) ($usage["type"] ?? "");
        $container_type = "";
        if (str_contains($usage_type, ":")) {
            [$container_type, $usage_type] = explode(":", $usage_type, 2);
        }

        $item = [
            "type" => "",
            "title" => "",
            "sub_txt" => "",
            "sub_title" => "",
            "link" => ""
        ];

        switch ($usage_type) {
            case "pg":
                $page = $usage["page"] ?? null;
                if (!$page instanceof \ilPageObject) {
                    break;
                }
                switch ($container_type) {
                    case "lm":
                        if (\ilObject::_lookupType($page->getParentId()) === "lm") {
                            $learning_module = new \ilObjLearningModule($page->getParentId(), false);
                            $item["type"] = $this->domain->lng()->txt("obj_" . $container_type);
                            $item["title"] = $learning_module->getTitle();
                            $item["sub_txt"] = $this->domain->lng()->txt("pg");
                            $item["sub_title"] = \ilLMObject::_lookupTitle($page->getId());
                            $ref_id = $this->getFirstWritableRefId($learning_module->getId());
                            if ($ref_id > 0) {
                                $item["link"] = \ilLink::_getStaticLink(
                                    null,
                                    "pg",
                                    true,
                                    $page->getId() . "_" . $ref_id
                                );
                            }
                        }
                        break;

                    case "wpg":
                        $item["type"] = $this->domain->lng()->txt("obj_wiki");
                        $item["title"] = \ilObject::_lookupTitle($page->getParentId());
                        $item["sub_txt"] = $this->domain->lng()->txt("pg");
                        $item["sub_title"] = \ilWikiPage::lookupTitle($page->getId());
                        $ref_id = $this->getFirstWritableRefId($page->getParentId());
                        if ($ref_id > 0) {
                            $item["link"] = \ilLink::_getStaticLink($ref_id, "wiki");
                        }
                        break;

                    case "term":
                        $term_id = $page->getId();
                        $glossary_id = \ilGlossaryTerm::_lookGlossaryID($term_id);
                        $item["type"] = $this->domain->lng()->txt("obj_glo");
                        $item["title"] = \ilObject::_lookupTitle($glossary_id);
                        $item["sub_txt"] = $this->domain->lng()->txt("cont_term");
                        $item["sub_title"] = \ilGlossaryTerm::_lookGlossaryTerm($term_id);
                        $ref_id = $this->getFirstWritableRefId($page->getParentId());
                        if ($ref_id > 0) {
                            $item["link"] = \ilLink::_getStaticLink($ref_id, "glo");
                        }
                        break;

                    case "cont":
                        $object_type = \ilObject::_lookupType($page->getId());
                        $item["type"] = $this->domain->lng()->txt("obj_" . $object_type);
                        $item["title"] = \ilObject::_lookupTitle($page->getId());
                        $ref_id = $this->getFirstWritableRefId($page->getId());
                        if ($ref_id > 0) {
                            $item["link"] = \ilLink::_getStaticLink($ref_id, $object_type);
                        }
                        break;

                    case "mep":
                        $item["type"] = $this->domain->lng()->txt("mep_page_type_mep");
                        $item["sub_txt"] = $this->domain->lng()->txt("mep_page_type_mep");
                        $item["sub_title"] = \ilMediaPoolItem::lookupTitle((int) $usage["id"]);
                        foreach (\ilMediaPoolItem::getPoolForItemId((int) $usage["id"]) as $media_pool_id) {
                            $ref_ids = \ilObject::_getAllReferences($media_pool_id);
                            $item["title"] = \ilObject::_lookupTitle($media_pool_id);
                            foreach ($ref_ids as $ref_id) {
                                $item["link"] = \ilLink::_getStaticLink($ref_id, "mep");
                                break;
                            }
                            break;
                        }
                        break;

                    default:
                        $object_id = \ilObjMediaObject::getParentObjectIdForUsage($usage);
                        if ($object_id > 0) {
                            $object_type = \ilObject::_lookupType($object_id);
                            $item["type"] = $this->domain->lng()->txt("obj_" . $object_type);
                            $item["title"] = \ilObject::_lookupTitle($object_id);
                            $ref_id = $this->getFirstWritableRefId($object_id);
                            if ($ref_id > 0) {
                                $item["link"] = \ilLink::_getStaticLink($ref_id, $object_type);
                            }
                        }
                        break;
                }

                if (($usage["trash"] ?? false) === true) {
                    $item["title"] .= " (" . $this->domain->lng()->txt("trash") . ")";
                }
                break;

            case "mep":
                $item["type"] = $this->domain->lng()->txt("obj_mep");
                $item["title"] = \ilObject::_lookupTitle((int) $usage["id"]);
                $ref_id = $this->getFirstWritableRefId((int) $usage["id"]);
                if ($ref_id > 0) {
                    $item["link"] = \ilLink::_getStaticLink($ref_id, "mep");
                }
                break;

            case "map":
                $item["type"] = $this->domain->lng()->txt("obj_mob");
                $item["title"] = \ilObject::_lookupTitle((int) $usage["id"]);
                $item["sub_txt"] = $this->domain->lng()->txt("cont_link_area");
                break;

            case "news":
                $object_id = \ilNewsItem::_lookupContextObjId((int) $usage["id"]);
                $object_type = \ilObject::_lookupType($object_id);
                $item["type"] = $this->domain->lng()->txt("obj_" . $object_type);
                $item["title"] = \ilObject::_lookupTitle($object_id);
                $item["sub_txt"] = $this->domain->lng()->txt("news");
                $ref_id = $this->getFirstWritableRefId($object_id);
                if ($ref_id > 0) {
                    $item["link"] = \ilLink::_getStaticLink($ref_id, $object_type);
                }
                break;
        }

        $title = $item["title"];
        if ($item["sub_txt"] !== "") {
            $title .= ", " . $item["sub_txt"];
            if ($item["sub_title"] !== "") {
                $title .= ": " . $item["sub_title"];
            }
        }

        if ($usage_type === "clip") {
            $title = $this->domain->lng()->txt("cont_users_have_mob_in_clip1") .
                " " . (int) ($usage["cnt"] ?? 0) . " " .
                $this->domain->lng()->txt("cont_users_have_mob_in_clip2");
        }

        return [
            "id" => $id,
            "object" => $title,
            "object_link" => $item["link"],
            "type" => $item["type"],
            "versions" => $this->buildVersions($usage)
        ];
    }

    protected function buildVersions(array $usage): string
    {
        if (!is_array($usage["versions"] ?? null) || !isset($usage["page"])) {
            return " ";
        }

        $versions = $usage["versions"];
        $version_text = "";
        if (count($versions) > 5) {
            $version_text = "..., ";
            $versions = array_slice($versions, -5);
        }

        $separator = "";
        foreach ($versions as $version) {
            $history_number = $version["hist_nr"] ?? 0;
            if ($history_number == 0) {
                $history_number = $this->domain->lng()->txt("cont_current_version");
            }
            $version_text .= $separator . $history_number;
            if (($version["lang"] ?? "") !== "") {
                $version_text .= "/" . $version["lang"];
            }
            $separator = ", ";
        }

        return $version_text;
    }

    protected function getFirstWritableRefId(int $object_id): int
    {
        foreach (\ilObject::_getAllReferences($object_id) as $ref_id) {
            if ($this->domain->access()->checkAccess("write", "", $ref_id)) {
                return (int) $ref_id;
            }
        }
        return 0;
    }
}
