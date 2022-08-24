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
 * News data set class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsDataSet extends ilDataSet
{
    public function getSupportedVersions(): array
    {
        return ["5.4.0", "4.1.0"];
    }

    protected function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return "https://www.ilias.de/xml/Services/News/" . $a_entity;
    }

    protected function getTypes(string $a_entity, string $a_version): array
    {
        if ($a_entity === "news") {
            switch ($a_version) {
                case "4.1.0":
                case "5.4.0":
                    return [
                        "Id" => "integer",
                        "Title" => "text",
                        "Content" => "text",
                        "Priority" => "integer",
                        "ContextObjId" => "integer",
                        "ContextObjType" => "text",
                        "ContextSubObjId" => "integer",
                        "ContextSubObjType" => "text",
                        "ContentType" => "text",
                        "Visibility" => "text",
                        "ContentLong" => "text",
                        "ContentIsLangVar" => "integer",
                        "MobId" => "integer",
                        "Playtime" => "text"
                    ];
            }
        }
        if ($a_entity === "news_settings") {
            switch ($a_version) {
                case "5.4.0":
                    return [
                        "ObjId" => "integer",
                        "PublicFeed" => "integer",
                        "DefaultVisibility" => "text",
                        "KeepRssMin" => "integer",
                        "HideNewsPerDate" => "integer",
                        "HideNewsDate" => "text",
                        "PublicNotifications" => "integer"
                    ];
            }
        }
        return [];
    }

    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $ilDB = $this->db;

        if (!is_array($a_ids)) {
            $a_ids = [$a_ids];
        }

        if ($a_entity === "news") {
            switch ($a_version) {
                case "4.1.0":
                case "5.4.0":
                    $this->getDirectDataFromQuery("SELECT id, title, content, priority," .
                        " context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, " .
                        " content_type, visibility, content_long, content_is_lang_var, mob_id, playtime" .
                        " FROM il_news_item " .
                        "WHERE " .
                        $ilDB->in("id", $a_ids, false, "integer"));
                    break;
            }
        }

        if ($a_entity === "news_settings") {
            switch ($a_version) {
                case "5.4.0":
                    foreach ($a_ids as $obj_id) {
                        $this->data[$obj_id]["ObjId"] = $obj_id;
                        $this->data[$obj_id]["PublicFeed"] = ilBlockSetting::_lookup("news", "public_feed", 0, $obj_id);
                        $this->data[$obj_id]["KeepRssMin"] = (int) ilBlockSetting::_lookup("news", "keep_rss_min", 0, $obj_id);
                        $this->data[$obj_id]["DefaultVisibility"] = ilBlockSetting::_lookup("news", "default_visibility", 0, $obj_id);
                        $this->data[$obj_id]["HideNewsPerDate"] = (int) ilBlockSetting::_lookup("news", "hide_news_per_date", 0, $obj_id);
                        $this->data[$obj_id]["HideNewsDate"] = ilBlockSetting::_lookup("news", "hide_news_date", 0, $obj_id);
                        $this->data[$obj_id]["PublicNotifications"] = (int) ilBlockSetting::_lookup("news", "public_notifications", 0, $obj_id);
                    }
                    break;
            }
        }
    }

    public function importRecord(string $a_entity, array $a_types, array $a_rec, ilImportMapping $a_mapping, string $a_schema_version): void
    {
        switch ($a_entity) {
            case "news":
                $mob_id = null;
                if ($a_rec["MobId"] > 0) {
                    $mob_id = $a_mapping->getMapping("Services/MediaObjects", "mob", $a_rec["MobId"]);
                }
                $c = (int) $a_rec["ContextObjId"] . ":" . $a_rec["ContextObjType"] . ":" . (int) $a_rec["ContextSubObjId"] .
                    ":" . $a_rec["ContextSubObjType"];
                $context = $a_mapping->getMapping("Services/News", "news_context", $c);
                $context = explode(":", $context);

                $newObj = new ilNewsItem();
                $newObj->setTitle($a_rec["Title"]);
                $newObj->setContent($a_rec["Content"]);
                $newObj->setPriority($a_rec["Priority"]);
                $newObj->setContextObjId((int) $context[0]);
                $newObj->setContextObjType($context[1]);
                $newObj->setContextSubObjId((int) $context[2]);
                $newObj->setContextSubObjType($context[3]);
                $newObj->setContentType($a_rec["ContentType"]);
                $newObj->setVisibility($a_rec["Visibility"]);
                $newObj->setContentLong($a_rec["ContentLong"]);
                $newObj->setContentIsLangVar($a_rec["ContentIsLangVar"]);
                $newObj->setMobId((int) $mob_id);
                $newObj->setPlaytime($a_rec["Playtime"]);
                $newObj->create();
                $a_mapping->addMapping("Services/News", "news", $a_rec["Id"], (string) $newObj->getId());
                break;

            case "news_settings":

                $dummy_dataset = new ilObjectDataSet();
                $new_obj_id = $dummy_dataset->getNewObjId($a_mapping, $a_rec["ObjId"]);

                if ($new_obj_id > 0 && $a_schema_version === "5.4.0") {
                    foreach ([
                        "public_feed" => "PublicFeed",
                        "keep_rss_min" => "KeepRssMin",
                        "default_visibility" => "DefaultVisibility",
                        "hide_news_per_date" => "HideNewsPerDate",
                        "hide_news_date" => "HideNewsDate",
                        "public_notifications" => "PublicNotifications"
                         ] as $set => $field) {
                        ilBlockSetting::_write(
                            "news",
                            $set,
                            $a_rec[$field],
                            0,
                            $new_obj_id
                        );
                    }
                }
                break;
        }
    }
}
