<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

namespace ILIAS\Container\Content;

use ILIAS\Container\InternalDomainService;

/**
 * Manages container subitems set
 * @author Alexander Killing <killing@leifos.de>
 */
class ItemSetManager
{
    public const FLAT = 0;
    public const TREE = 1;
    public const SINGLE = 2;
    protected bool $admin_mode;
    protected bool $hiddenfilesfound = false;
    protected string $parent_type;
    protected int $parent_obj_id;

    protected int $parent_ref_id = 0;
    protected int $single_ref_id = 0;
    protected InternalDomainService $domain;
    protected array $raw = [];
    protected array $raw_by_type = [];
    /** @var array<int,bool> */
    protected array $rendered = [];
    protected int $mode = self::FLAT;
    protected ?\ilContainerUserFilter $user_filter = null;

    /**
     * @param int $mode self::TREE|self::FLAT|self::SINGLE
     */
    public function __construct(
        InternalDomainService $domain,
        int $mode,
        int $parent_ref_id,
        ?\ilContainerUserFilter $user_filter = null,
        int $single_ref_id = 0,
        bool $admin_mode = false
    ) {
        $this->parent_ref_id = $parent_ref_id;
        $this->parent_obj_id = \ilObject::_lookupObjId($this->parent_ref_id);
        $this->parent_type = \ilObject::_lookupType($this->parent_obj_id);
        $this->user_filter = $user_filter;

        $this->single_ref_id = $single_ref_id;
        $this->domain = $domain;
        $this->mode = $mode;        // might be refactored as subclasses
        $this->admin_mode = $admin_mode;
        $this->init();
    }

    public function setHiddenFilesFound(bool $a_hiddenfilesfound): void
    {
        $this->hiddenfilesfound = $a_hiddenfilesfound;
    }

    public function getHiddenFilesFound(): bool
    {
        return $this->hiddenfilesfound;
    }

    /**
     * @todo from ilContainer, should be removed there
     */
    protected function init(): void
    {
        $tree = $this->domain->repositoryTree();
        if ($this->mode === self::TREE) {
            $this->raw = $tree->getSubTree($tree->getNodeData($this->parent_ref_id));
        } elseif ($this->mode === self::FLAT) {
            $this->raw = $tree->getChilds($this->parent_ref_id, "title");
        } else {
            $this->raw[] = $tree->getNodeData($this->single_ref_id);
        }
        $this->applyUserFilter();
        $this->getCompleteDescriptions();
        $this->applyClassificationFilter();
        $this->applySorting();
        $this->groupItems();
        $this->sortSessions();
    }

    /**
     * @todo from ilContainer, should be removed there
     */
    public function hasItems(): bool
    {
        $this->init();
        return count($this->raw) > 0;
    }

    public function getRefIdsOfType(string $type): array
    {
        $this->init();
        if (isset($this->raw_by_type[$type])) {
            return array_map(static function ($item) {
                return (int) $item["child"];
            }, $this->raw_by_type[$type]);
        }
        return [];
    }

    public function getAllRefIds(): array
    {
        $this->init();
        return array_keys($this->raw_by_type["_all"]);
    }

    public function getRawDataByRefId(int $ref_id): ?array
    {
        $this->init();
        return $this->raw_by_type["_all"][$ref_id] ?? null;
    }

    public function isSideBlockItem(int $ref_id): bool
    {
        $this->init();
        $type = $this->raw_by_type["_all"][$ref_id]["type"] ?? "";
        $obj_definition = $this->domain->objectDefinition();
        return $obj_definition->isSideBlock($type);
    }

    protected function applySorting(): void
    {
        $sort = \ilContainerSorting::_getInstance($this->parent_obj_id);
        $all = $sort->sortItems(["all" => $this->raw]);
        $this->raw = $all["all"];
        //$this->raw_by_type = $sort->sortItems($this->raw_by_type);
    }

    /**
     * Internally group all items
     */
    protected function groupItems(): void
    {
        $obj_definition = $this->domain->objectDefinition();
        $classification_filter_active = $this->isClassificationFilterActive();
        $this->raw_by_type["_all"] = [];
        foreach ($this->raw as $key => $object) {

            // hide object types in devmode
            if ($object["type"] === "adm" || $object["type"] === "rolf" ||
                $obj_definition->getDevMode($object["type"])) {
                continue;
            }

            // remove inactive plugins
            if ($obj_definition->isInactivePlugin($object["type"])) {
                continue;
            }

            // BEGIN WebDAV: Don't display hidden Files, Folders and Categories
            if (in_array($object['type'], array('file','fold','cat'))) {
                if (\ilObjFileAccess::_isFileHidden($object['title'])) {
                    $this->setHiddenFilesFound(true);
                    if (!$this->admin_mode) {
                        continue;
                    }
                }
            }
            // END WebDAV: Don't display hidden Files, Folders and Categories

            // group object type groups together (e.g. learning resources)
            $type = $obj_definition->getGroupOfObj($object["type"]);
            if ($type == "") {
                $type = $object["type"];
            }

            // this will add activation properties
            $this->addAdditionalSubItemInformation($object);

            $new_key = (int) $object["child"];
            $this->rendered[$new_key] = false;
            $this->raw_by_type[$type][$new_key] = $object;

            $this->raw_by_type["_all"][$new_key] = $object;
            if ($object["type"] !== "sess") {
                $this->raw_by_type["_non_sess"][$new_key] = $object;
            }
        }
    }

    protected function sortSessions(): void
    {
        if (isset($this->raw_by_type["sess"]) && count($this->raw_by_type["sess"]) > 0) {
            $this->raw_by_type["sess"] = \ilArrayUtil::sortArray($this->raw_by_type["sess"], 'start', 'ASC', true, true);
        }
    }

    protected function addAdditionalSubItemInformation(array &$object): void
    {
        \ilObjectActivation::addAdditionalSubItemInformation($object);
    }

    /**
     * @todo from ilContainer, remove there
     */
    public function isClassificationFilterActive(): bool
    {
        // apply container classification filters
        $repo = new \ilClassificationSessionRepository($this->parent_ref_id);
        foreach (\ilClassificationProvider::getValidProviders(
            $this->parent_ref_id,
            $this->parent_obj_id,
            $this->parent_type
        ) as $class_provider) {
            $id = get_class($class_provider);
            $current = $repo->getValueForProvider($id);
            if ($current) {
                return true;
            }
        }
        return false;
    }


    /**
     * Apply container user filter on objects
     * @throws \ilException
     */
    protected function applyUserFilter(): void
    {
        if (is_null($this->user_filter)) {
            return;
        }
        $filter = $this->domain->content()->filter(
            $this->raw,
            $this->user_filter,
            !\ilContainer::_lookupContainerSetting(
                $this->parent_obj_id,
                "filter_show_empty",
                "0"
            )
        );
        $this->raw = $filter->apply();
    }

    /**
     * From ilContainer @todo remove there
     */
    protected function getCompleteDescriptions(): void
    {
        $ilSetting = $this->domain->settings();
        $ilObjDataCache = $this->domain->objectDataCache();

        // using long descriptions?
        $short_desc = $ilSetting->get("rep_shorten_description");
        $short_desc_max_length = (int) $ilSetting->get("rep_shorten_description_length");
        if (!$short_desc || $short_desc_max_length != \ilObject::DESC_LENGTH) {
            // using (part of) shortened description
            if ($short_desc && $short_desc_max_length && $short_desc_max_length < \ilObject::DESC_LENGTH) {
                foreach ($this->raw as $key => $object) {
                    $this->raw[$key]["description"] = \ilStr::shortenTextExtended(
                        $object["description"],
                        $short_desc_max_length,
                        true
                    );
                }
            }
            // using (part of) long description
            else {
                $obj_ids = array();
                foreach ($this->raw as $key => $object) {
                    $obj_ids[] = $object["obj_id"];
                }
                if (count($obj_ids) > 0) {
                    $long_desc = \ilObject::getLongDescriptions($obj_ids);
                    foreach ($this->raw as $key => $object) {
                        // #12166 - keep translation, ignore long description
                        if ($ilObjDataCache->isTranslatedDescription((int) $object["obj_id"])) {
                            $long_desc[$object["obj_id"]] = $object["description"];
                        }
                        if ($short_desc && $short_desc_max_length) {
                            $long_desc[$object["obj_id"]] = \ilStr::shortenTextExtended(
                                (string) $long_desc[$object["obj_id"]],
                                $short_desc_max_length,
                                true
                            );
                        }
                        $this->raw[$key]["description"] = $long_desc[$object["obj_id"]];
                    }
                }
            }
        }
    }

    /**
     * From ilContainer @todo remove there
     */
    protected function applyClassificationFilter(): void
    {
        // apply container classification filters
        $repo = new \ilClassificationSessionRepository($this->parent_ref_id);
        foreach (\ilClassificationProvider::getValidProviders(
            $this->parent_ref_id,
            $this->parent_obj_id,
            $this->parent_type
        ) as $class_provider) {
            $id = get_class($class_provider);
            $current = $repo->getValueForProvider($id);
            if ($current) {
                $class_provider->setSelection($current);
                $filtered = $class_provider->getFilteredObjects();
                $this->raw = array_filter($this->raw, static function ($i) use ($filtered) {
                    return (is_array($filtered) && in_array($i["obj_id"], $filtered));
                });
            }
        }
    }
}
