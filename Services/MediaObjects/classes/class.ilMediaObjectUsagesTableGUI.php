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
 * TableGUI class for media object usages listing
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaObjectUsagesTableGUI extends ilTable2GUI
{
    protected bool $include_hist;
    protected ilAccessHandler $access;
    protected ilObjMediaObject $media_object;
    protected ilTree $repo_tree;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjMediaObject $a_media_object,
        bool $a_include_hist = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->repo_tree = $DIC->repositoryTree();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->media_object = $a_media_object;
        $this->include_hist = $a_include_hist;
        $this->addColumn($lng->txt("mob_object"));
        $this->addColumn($this->lng->txt("type"));
        $this->addColumn($this->lng->txt("cont_versions"));
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.mob_usage_row.html", "Services/MediaObjects");
        $this->getItems();
        $this->setTitle($lng->txt("cont_mob_usages"));
    }

    /**
     * Get items of current folder
     */
    public function getItems(): void
    {
        $usages = $this->media_object->getUsages($this->include_hist);

        $clip_cnt = 0;
        $to_del = array();
        $agg_usages = array();
        foreach ($usages as $k => $usage) {
            $usage["trash"] = false;
            if (is_int(strpos($usage["type"], ":"))) {
                $us_arr = explode(":", $usage["type"]);

                // try to figure out object id of pages
                if ($us_arr[1] == "pg") {
                    $page_obj = ilPageObjectFactory::getInstance($us_arr[0], $usage["id"]);
                    $usage["page"] = $page_obj;
                    $repo_tree = $this->repo_tree;
                    $ref_ids = array_filter(
                        ilObject::_getAllReferences($page_obj->getRepoObjId()),
                        function ($ref_id) use ($repo_tree) {
                            return $repo_tree->isInTree($ref_id);
                        }
                    );
                    $usage["ref_ids"] = $ref_ids;
                    if (count($ref_ids) == 0) {
                        $usage["trash"] = true;
                    }
                }
            }


            if ($usage["type"] == "clip") {
                $clip_cnt++;
            } else {
                if ($this->include_hist || !$usage["trash"]) {
                    if (empty($agg_usages[$usage["type"] . ":" . $usage["id"]])) {
                        $agg_usages[$usage["type"] . ":" . $usage["id"]] = $usage;
                    }
                    $agg_usages[$usage["type"] . ":" . $usage["id"]]["versions"][] =
                        ["hist_nr" => $usage["hist_nr"] ?? 0,
                         "lang" => $usage["lang"] ?? ""];
                }
            }
        }

        // usages in clipboards
        if ($clip_cnt > 0) {
            $agg_usages[] = array("type" => "clip", "cnt" => $clip_cnt);
        }

        $this->setData($agg_usages);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $cont_type = "";
        $usage = $a_set;
        $item = [];

        if (is_int(strpos($usage["type"], ":"))) {
            $us_arr = explode(":", $usage["type"]);
            $usage["type"] = $us_arr[1];
            $cont_type = $us_arr[0];
        }

        switch ($usage["type"]) {
            case "pg":
                $page_obj = $usage["page"];

                $item = array();

                //$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
                switch ($cont_type) {
                    case "lm":
                        if (ilObject::_lookupType($page_obj->getParentId()) == "lm") {
                            $lm_obj = new ilObjLearningModule($page_obj->getParentId(), false);
                            $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                            $item["obj_title"] = $lm_obj->getTitle();
                            $item["sub_txt"] = $this->lng->txt("pg");
                            $item["sub_title"] = ilLMObject::_lookupTitle($page_obj->getId());
                            $ref_id = $this->getFirstWritableRefId($lm_obj->getId());
                            if ($ref_id > 0) {
                                $item["obj_link"] = ilLink::_getStaticLink($page_obj->getId() . "_" . $ref_id, "pg");
                            }
                        }
                        break;

                    case "wpg":
                        $item["obj_type_txt"] = $this->lng->txt("obj_wiki");
                        $item["obj_title"] = ilObject::_lookupTitle($page_obj->getParentId());
                        $item["sub_txt"] = $this->lng->txt("pg");
                        $item["sub_title"] = ilWikiPage::lookupTitle($page_obj->getId());
                        $ref_id = $this->getFirstWritableRefId($page_obj->getParentId());
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($ref_id, "wiki");
                        }
                        break;

                    case "gdf":
                        $term_id = ilGlossaryDefinition::_lookupTermId($page_obj->getId());
                        $glo_id = ilGlossaryTerm::_lookGlossaryID($term_id);
                        $item["obj_type_txt"] = $this->lng->txt("obj_glo");
                        $item["obj_title"] = ilObject::_lookupTitle($glo_id);
                        $item["sub_txt"] = $this->lng->txt("cont_term");
                        $item["sub_title"] = ilGlossaryTerm::_lookGlossaryTerm($term_id);
                        $ref_id = $this->getFirstWritableRefId($page_obj->getParentId());
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($ref_id, "glo");
                        }
                        break;

                    case "cont":
                        $otype = ilObject::_lookupType($page_obj->getId());
                        $item["obj_type_txt"] = $this->lng->txt("obj_" . $otype);
                        $item["obj_title"] = ilObject::_lookupTitle($page_obj->getId());
                        $ref_id = $this->getFirstWritableRefId($page_obj->getId());
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($ref_id, $otype);
                        }
                        break;

                    default:
                        $oid = ilObjMediaObject::getParentObjectIdForUsage($a_set);
                        if ($oid > 0) {
                            $type = ilObject::_lookupType($oid);
                            $item["obj_type_txt"] = $this->lng->txt("obj_" . $type);
                            $item["obj_title"] = ilObject::_lookupTitle($oid);
                            $ref_id = $this->getFirstWritableRefId($oid);
                            if ($ref_id > 0) {
                                $item["obj_link"] = ilLink::_getStaticLink($ref_id, $type);
                            }
                        }
                        break;
                }

                if ($usage["trash"]) {
                    $item["obj_title"] .= " (" . $lng->txt("trash") . ")";
                }

                break;

            case "mep":
                $item["obj_type_txt"] = $this->lng->txt("obj_mep");
                $item["obj_title"] = ilObject::_lookupTitle($usage["id"]);
                $ref_id = $this->getFirstWritableRefId($usage["id"]);
                if ($ref_id > 0) {
                    $item["obj_link"] = ilLink::_getStaticLink($ref_id, "mep");
                }
                break;

            case "map":
                $item["obj_type_txt"] = $this->lng->txt("obj_mob");
                $item["obj_title"] = ilObject::_lookupTitle($usage["id"]);
                $item["sub_txt"] = $this->lng->txt("cont_link_area");
                break;
        }

        // show versions
        if (is_array($usage["versions"]) && is_object($usage["page"] ?? null)) {
            $ver = $sep = "";

            if (count($usage["versions"]) > 5) {
                $ver .= "..., ";
                $cnt = count($usage["versions"]) - 5;
                for ($i = 0; $i < $cnt; $i++) {
                    unset($usage["versions"][$i]);
                }
            }
            foreach ($usage["versions"] as $version) {
                if ($version["hist_nr"] == 0) {
                    $version["hist_nr"] = $this->lng->txt("cont_current_version");
                }
                $ver .= $sep . $version["hist_nr"];
                if ($version["lang"] != "") {
                    $ver .= "/" . $version["lang"];
                }
                $sep = ", ";
            }

            $this->tpl->setCurrentBlock("versions");
            $this->tpl->setVariable("VAL_VERSIONS", $ver);
        } else {
            $this->tpl->setCurrentBlock("versions");
            $this->tpl->setVariable("VAL_VERSIONS", " ");
        }
        $this->tpl->parseCurrentBlock();

        if ($item["obj_type_txt"] != "") {
            $this->tpl->setVariable("VAL_TYPE", $item["obj_type_txt"]);
        }

        if ($usage["type"] != "clip") {
            if ($item["obj_link"]) {
                $this->tpl->setCurrentBlock("linked_item");
                $this->tpl->setVariable("TXT_OBJECT", $item["obj_title"]);
                $this->tpl->setVariable("HREF_LINK", $item["obj_link"]);
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setVariable("TXT_OBJECT_NO_LINK", $item["obj_title"]);
            }

            if (($item["sub_txt"] ?? "") != "") {
                $this->tpl->setVariable("SEP", ", ");
                $this->tpl->setVariable("SUB_TXT", $item["sub_txt"]);
                if (($item["sub_title"] ?? "") != "") {
                    $this->tpl->setVariable("SEP2", ": ");
                    $this->tpl->setVariable("SUB_TITLE", $item["sub_title"]);
                }
            }
        } else {
            $this->tpl->setVariable("TXT_OBJECT_NO_LINK", $this->lng->txt("cont_users_have_mob_in_clip1") .
                " " . $usage["cnt"] . " " . $this->lng->txt("cont_users_have_mob_in_clip2"));
        }
    }

    public function getFirstWritableRefId(
        int $a_obj_id
    ): int {
        $ilAccess = $this->access;

        $ref_ids = ilObject::_getAllReferences($a_obj_id);
        foreach ($ref_ids as $ref_id) {
            if ($ilAccess->checkAccess("write", "", $ref_id)) {
                return $ref_id;
            }
        }
        return 0;
    }
}
