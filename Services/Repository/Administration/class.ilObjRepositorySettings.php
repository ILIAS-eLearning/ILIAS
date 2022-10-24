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

/**
 * Class ilObjRepositorySettings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilObjRepositorySettings extends ilObject
{
    public const NEW_ITEM_GROUP_TYPE_GROUP = 1;
    public const NEW_ITEM_GROUP_TYPE_SEPARATOR = 2;

    public function __construct(int $a_id, bool $a_call_by_reference = true)
    {
        $this->type = "reps";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function delete(): bool
    {
        // DISABLED
        return false;
    }

    public static function addNewItemGroupSeparator(): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        // append
        $pos = $ilDB->query("SELECT max(pos) mpos FROM il_new_item_grp");
        $pos = $ilDB->fetchAssoc($pos);
        $pos = (int) $pos["mpos"];
        $pos += 10;

        $seq = $ilDB->nextID("il_new_item_grp");

        $ilDB->manipulate("INSERT INTO il_new_item_grp" .
            " (id, pos, type) VALUES (" .
            $ilDB->quote($seq, "integer") .
            ", " . $ilDB->quote($pos, "integer") .
            ", " . $ilDB->quote(self::NEW_ITEM_GROUP_TYPE_SEPARATOR, "integer") .
            ")");
        return true;
    }

    public static function addNewItemGroup(array $a_titles): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        // append
        $pos = $ilDB->query("SELECT max(pos) mpos FROM il_new_item_grp");
        $pos = $ilDB->fetchAssoc($pos);
        $pos = (int) $pos["mpos"];
        $pos += 10;

        $seq = $ilDB->nextID("il_new_item_grp");

        $ilDB->manipulate("INSERT INTO il_new_item_grp" .
            " (id, titles, pos, type) VALUES (" .
            $ilDB->quote($seq, "integer") .
            ", " . $ilDB->quote(serialize($a_titles), "text") .
            ", " . $ilDB->quote($pos, "integer") .
            ", " . $ilDB->quote(self::NEW_ITEM_GROUP_TYPE_GROUP, "integer") .
            ")");
        return true;
    }

    public static function updateNewItemGroup(int $a_id, array $a_titles): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate("UPDATE il_new_item_grp" .
            " SET titles = " . $ilDB->quote(serialize($a_titles), "text") .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        return true;
    }

    public static function deleteNewItemGroup(int $a_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        // move subitems to unassigned
        $sub_items = self::getNewItemGroupSubItems();
        $sub_items = $sub_items[$a_id];
        if ($sub_items) {
            foreach ($sub_items as $obj_type) {
                $old_pos = $ilSetting->get("obj_add_new_pos_" . $obj_type);
                if (strlen($old_pos) === 8) {
                    $new_pos = "9999" . substr($old_pos, 4);
                    $ilSetting->set("obj_add_new_pos_" . $obj_type, $new_pos);
                    $ilSetting->set("obj_add_new_pos_grp_" . $obj_type, '0');
                }
            }
        }

        $ilDB->manipulate("DELETE FROM il_new_item_grp" .
            " WHERE id = " . $ilDB->quote($a_id, "integer"));
        return true;
    }

    public static function getNewItemGroups(): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $ilUser = $DIC->user();

        $def_lng = $lng->getDefaultLanguage();
        $usr_lng = $ilUser->getLanguage();

        $res = [];

        $set = $ilDB->query("SELECT * FROM il_new_item_grp ORDER BY pos");
        while ($row = $ilDB->fetchAssoc($set)) {
            if ((int) $row["type"] === self::NEW_ITEM_GROUP_TYPE_GROUP) {
                $row["titles"] = unserialize($row["titles"], ["allowed_classes" => false]);

                $title = $row["titles"][$usr_lng];
                if (!$title) {
                    $title = $row["titles"][$def_lng];
                }
                if (!$title) {
                    $title = array_shift($row["titles"]);
                }
                $row["title"] = $title;
            } else {
                $row["title"] = $lng->txt("rep_new_item_group_separator");
            }

            $res[$row["id"]] = $row;
        }

        return $res;
    }

    public static function updateNewItemGroupOrder(array $a_order): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        asort($a_order);
        $pos = 0;
        foreach (array_keys($a_order) as $id) {
            $pos += 10;

            $ilDB->manipulate("UPDATE il_new_item_grp" .
                " SET pos = " . $ilDB->quote($pos, "integer") .
                " WHERE id = " . $ilDB->quote($id, "integer"));
        }
    }

    protected static function getAllObjTypes(): array
    {
        global $DIC;

        $component_repository = $DIC["component.repository"];
        $objDefinition = $DIC["objDefinition"];

        $res = [];

        // parse modules
        foreach ($component_repository->getComponents() as $mod) {
            if ($mod->getType() !== ilComponentInfo::TYPE_MODULES) {
                continue;
            }
            $has_repo = false;
            $rep_types = $objDefinition->getRepositoryObjectTypesForComponent(ilComponentInfo::TYPE_MODULES, $mod->getName());
            if (count($rep_types) > 0) {
                foreach ($rep_types as $ridx => $rt) {
                    // we only want to display repository modules
                    if ($rt["repository"]) {
                        $has_repo = true;
                    } else {
                        unset($rep_types[$ridx]);
                    }
                }
            }
            if ($has_repo) {
                foreach ($rep_types as $rt) {
                    $res[] = $rt["id"];
                }
            }
        }

        // parse plugins
        $pl_names = $component_repository->getPluginSlotById("robj")->getActivePlugins();
        foreach ($pl_names as $plugin) {
            $res[] = $plugin->getId();
        }

        return $res;
    }

    public static function getNewItemGroupSubItems(): array
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $res = [];

        foreach (self::getAllObjTypes() as $type) {
            $pos_grp = $ilSetting->get("obj_add_new_pos_grp_" . $type, '0');
            $res[$pos_grp][] = $type;
        }

        return $res;
    }

    public static function getDefaultNewItemGrouping(): array
    {
        global $DIC;

        $lng = $DIC->language();

        $res = [];

        $groups = [
            "organisation" => ["fold", "sess", "cat", "catr", "crs", "crsr", "grp", "grpr", "itgr", "book", "prg", "prgr"],
            "communication" => ["frm", "chtr"],
            "breaker1" => null,
            "content" => ["file", "webr", "feed", "copa", "wiki", "blog", "lm", "htlm", "sahs", 'cmix', 'lti', "lso", "glo", "dcl", "bibl", "mcst", "mep"],
            "breaker2" => null,
            "assessment" => ["exc", "tst", "qpl", "iass"],
            "feedback" => ["poll", "svy", "spl"],
            "templates" => ["prtt"]
        ];

        $pos = 0;
        foreach ($groups as $group => $items) {
            $pos += 10;
            $grp_id = $pos / 10;

            if (is_array($items)) {
                $title = $lng->txt("rep_add_new_def_grp_" . $group);

                $res["groups"][$grp_id] = [
                    "id" => $grp_id,
                    "titles" => [$lng->getUserLanguage() => $title],
                    "pos" => $pos,
                    "type" => self::NEW_ITEM_GROUP_TYPE_GROUP,
                    "title" => $title
                ];

                foreach ($items as $idx => $item) {
                    $res["items"][$item] = $grp_id;
                    $res["sort"][$item] = str_pad((string) $pos, 4, "0", STR_PAD_LEFT) .
                        str_pad((string) ($idx + 1), 4, "0", STR_PAD_LEFT);
                }
            } else {
                $title = "COL_SEP";

                $res["groups"][$grp_id] = [
                    "id" => $grp_id,
                    "titles" => [$lng->getUserLanguage() => $title],
                    "pos" => $pos,
                    "type" => self::NEW_ITEM_GROUP_TYPE_SEPARATOR,
                    "title" => $title
                ];
            }
        }

        return $res;
    }

    public static function deleteObjectType(string $a_type): void
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        // see ilObjRepositorySettingsGUI::saveModules()
        $ilSetting->delete("obj_dis_creation_" . $a_type);
        $ilSetting->delete("obj_add_new_pos_" . $a_type);
        $ilSetting->delete("obj_add_new_pos_grp_" . $a_type);
    }
}
