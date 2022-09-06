<?php

declare(strict_types=1);

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

use Psr\Http\Message\RequestInterface;

/**
 * Tag classification provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilTaggingClassificationProvider extends ilClassificationProvider
{
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ilObjectDefinition $obj_definition;
    protected ilCtrl $ctrl;
    protected RequestInterface $request;
    protected string $requested_type;
    protected string $requested_tag_code;
    protected bool $enable_all_users = false;
    protected array $selection;

    public function __construct(
        int $a_parent_ref_id,
        int $a_parent_obj_id,
        string $a_parent_obj_type
    ) {
        global $DIC;
        parent::__construct($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type);

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->obj_definition = $DIC["objDefinition"];
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();

        $params = $this->request->getQueryParams();
        $body = $this->request->getParsedBody();
        $this->requested_type = trim($body["tag_type"] ?? ($params["tag_type"] ?? ""));
        $this->requested_tag_code = trim($body["tag"] ?? ($params["tag"] ?? ""));
    }

    protected function init(): void
    {
        $tags_set = new ilSetting("tags");
        $this->enable_all_users = (bool) $tags_set->get("enable_all_users", '0');
    }

    /**
     * @inheritDoc
     */
    public static function isActive(
        int $a_parent_ref_id,
        int $a_parent_obj_id,
        string $a_parent_obj_type
    ): bool {
        global $DIC;

        $ilUser = $DIC->user();

        // we currently only check for the parent object setting
        // might change later on (parent containers)
        $valid = (bool) ilContainer::_lookupContainerSetting(
            $a_parent_obj_id,
            ilObjectServiceSettingsGUI::TAG_CLOUD,
            '0'
        );

        if ($valid) {
            $tags_set = new ilSetting("tags");
            if (!$tags_set->get("enable_all_users", '0') &&
                $ilUser->getId() === ANONYMOUS_USER_ID) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * @inheritDoc
     */
    public function render(
        array &$a_html,
        object $a_parent_gui
    ): void {
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $lng->loadLanguageModule("tagging");
        $all_tags = $this->getSubTreeTags();
        if ($all_tags) {
            $map = array(
                "personal" => $lng->txt("tagging_my_tags"),
                "other" => $lng->txt("tagging_other_users")
            );
            foreach ($map as $type => $title) {
                $tags = $all_tags[$type] ?? null;
                if ($tags) {
                    $max = 1;
                    foreach ($tags as $tag => $counter) {
                        $max = max($counter, $max);
                    }
                    reset($tags);

                    $tpl = new ilTemplate("tpl.tag_cloud_block.html", true, true, "Services/Tagging");

                    $tpl->setCurrentBlock("tag_bl");
                    foreach ($tags as $tag => $counter) {
                        $ctrl->setParameter($a_parent_gui, "tag_type", $type);
                        $ctrl->setParameter($a_parent_gui, "tag", md5((string) $tag));
                        $tpl->setVariable("HREF", $ctrl->getLinkTarget($a_parent_gui, "toggle"));

                        $tpl->setVariable("TAG_TYPE", $type);
                        $tpl->setVariable("TAG_TITLE", $tag);
                        $tpl->setVariable("TAG_CODE", md5((string) $tag));
                        $tpl->setVariable(
                            "REL_CLASS",
                            ilTagging::getRelevanceClass($counter, $max)
                        );
                        if (isset($this->selection[$type]) &&
                            in_array($tag, $this->selection[$type])) {
                            $tpl->setVariable("HIGHL_CLASS", ' ilHighlighted');
                        }

                        $tpl->parseCurrentBlock();
                    }

                    $a_html[] = array(
                        "title" => $title,
                        "html" => $tpl->get()
                    );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function importPostData(?array $a_saved = null): array
    {
        $type = $this->requested_type;
        $tag_code = $this->requested_tag_code;
        if ($type && $tag_code) {
            // code to tag
            $found = null;
            foreach ($this->getSubTreeTags() as $tags) {
                foreach (array_keys($tags) as $tag) {
                    if (md5((string) $tag) === $tag_code) {
                        $found = $tag;
                        break(2);
                    }
                }
            }
            if ($found) {
                // multi select
                if (isset($a_saved[$type]) &&
                    in_array($found, $a_saved[$type])) {
                    $key = array_search($found, $a_saved[$type]);
                    unset($a_saved[$type][$key]);
                    if (!sizeof($a_saved[$type])) {
                        unset($a_saved[$type]);
                    }
                } else {
                    $a_saved[$type][] = $found;
                }
            }
            return $a_saved ?? [];
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function setSelection(array $a_value): void
    {
        $this->selection = $a_value;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredObjects(): array
    {
        $ilUser = $this->user;

        if (!$this->selection) {
            return[];
        }

        $types = array("personal");
        if ($this->enable_all_users) {
            $types[] = "other";
        }

        $found = array();
        foreach ($types as $type) {
            if (isset($this->selection[$type])) {
                $invert = ($type == "personal")
                    ? false
                    : true;

                foreach ($this->selection[$type] as $tag) {
                    $found[$tag] = array_keys(ilTagging::_findObjectsByTag($tag, $ilUser->getId(), $invert));
                }
            }
        }

        /* OR
        $res = array();
        foreach($found as $tag => $ids)
        {
            $res = array_merge($res, $ids);
        }
        */

        // AND
        $res = null;
        foreach ($found as $tag => $ids) {
            if ($res === null) {
                $res = $ids;
            } else {
                $res = array_intersect($res, $ids);
            }
        }

        if (!is_null($res) && count($res) > 0) {
            return array_unique($res);
        }
        return [];
    }

    protected function getSubTreeTags(): array
    {
        $tree = $this->tree;
        $ilUser = $this->user;
        $sub_ids = array();

        // categories show only direct children and their tags
        if (ilObject::_lookupType($this->parent_ref_id, true) == "cat") {
            foreach ($tree->getChilds($this->parent_ref_id) as $sub_item) {
                if ($sub_item["ref_id"] != $this->parent_ref_id &&
                    $sub_item["type"] != "rolf" &&
                    !$tree->isDeleted((int) $sub_item["ref_id"])) {
                    $sub_ids[$sub_item["obj_id"]] = $sub_item["type"];
                }
            }
        } else {
            foreach ($tree->getSubTree($tree->getNodeData($this->parent_ref_id)) as $sub_item) {
                if ($sub_item["ref_id"] != $this->parent_ref_id &&
                    $sub_item["type"] != "rolf" &&
                    !$tree->isDeleted((int) $sub_item["ref_id"])) {
                    $sub_ids[$sub_item["obj_id"]] = $sub_item["type"];
                }
            }
        }

        if ($sub_ids) {
            $only_user = $this->enable_all_users
                ? null
                : $ilUser->getId();

            return ilTagging::_getTagCloudForObjects($sub_ids, $only_user, $ilUser->getId());
        }
        return [];
    }

    public function initListGUI(ilObjectListGUI $a_list_gui): void
    {
        $a_list_gui->enableTags(true);
    }
}
