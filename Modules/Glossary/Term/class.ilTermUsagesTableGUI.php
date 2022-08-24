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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTermUsagesTableGUI extends ilTable2GUI
{
    protected int $term_id;
    protected ilAccessHandler $access;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_term_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->term_id = $a_term_id;

        $this->addColumn("", "", "1");	// checkbox
        $this->setEnableHeader(false);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.term_usage_row.html", "Modules/Glossary");
        $this->getItems();
        $this->setTitle($lng->txt("cont_usage"));
    }

    public function getItems(): void
    {
        $usages = ilGlossaryTerm::getUsages($this->term_id);

        $agg_usages = array();
        foreach ($usages as $k => $usage) {
            if (empty($agg_usages[$usage["type"] . ":" . $usage["id"]])) {
                $usage["hist_nr"] = array($usage["hist_nr"]);
                $agg_usages[$usage["type"] . ":" . $usage["id"]] = $usage;
            } else {
                $agg_usages[$usage["type"] . ":" . $usage["id"]]["hist_nr"][] =
                    $usage["hist_nr"];
            }
        }

        $this->setData($agg_usages);
    }

    protected function fillRow(array $a_set): void
    {
        $usage = $a_set;

        $cont_type = "";
        if (is_int(strpos($usage["type"], ":"))) {
            $us_arr = explode(":", $usage["type"]);
            $usage["type"] = $us_arr[1];
            $cont_type = $us_arr[0];
        }

        switch ($usage["type"]) {
            case "pg":
                $item = array();

                //$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
                switch ($cont_type) {
                    case "lm":
                        $page_obj = new ilLMPage($usage["id"]);
                        $lm_obj = new ilObjLearningModule($page_obj->getParentId(), false);
                        $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                        $item["obj_title"] = $lm_obj->getTitle();
                        $item["sub_txt"] = $this->lng->txt("pg");
                        $item["sub_title"] = ilLMObject::_lookupTitle($page_obj->getId());
                        $ref_id = $this->getFirstWritableRefId($lm_obj->getId());
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($ref_id, "lm");
                        }
                        break;

                    case "wpg":
                        $page_obj = new ilWikiPage($usage["id"]);
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
                        $page_obj = new ilGlossaryDefPage($usage["id"]);
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

                    case "fold":
                    case "root":
                    case "crs":
                    case "grp":
                    case "cat":
                    case "cont":
                        $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                        $item["obj_title"] = ilObject::_lookupTitle($usage["id"]);
                        $ref_id = $this->getFirstWritableRefId($usage["id"]);
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($ref_id, $cont_type);
                        }
                        break;

                    default:
                        $item["obj_title"] = "Page " . $cont_type . ", " . $usage["id"];
                        break;
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

            case "sqst":
                $item["obj_type_txt"] = $this->lng->txt("cont_sqst");
                $obj_id = SurveyQuestion::lookupObjFi($usage["id"]);
                $item["obj_title"] = ilObject::_lookupTitle($obj_id);
                $item["sub_txt"] = $this->lng->txt("question");
                $item["sub_title"] = SurveyQuestion::_getTitle($usage["id"]);
                $ref_id = $this->getFirstWritableRefId($obj_id);
                if ($ref_id > 0) {
                    $item["obj_link"] = ilLink::_getStaticLink($ref_id);
                }

                break;

            case "termref":
                $item["obj_type_txt"] = $this->lng->txt("obj_glo");
                $item["obj_title"] = ilObject::_lookupTitle($usage["id"]);
                $item["sub_txt"] = $this->lng->txt("glo_referenced_term");
                $ref_id = $this->getFirstWritableRefId($usage["id"]);
                if ($ref_id > 0) {
                    $item["obj_link"] = ilLink::_getStaticLink($ref_id);
                }
                break;

            default:
                $item["obj_title"] = "Type " . $usage["type"] . ", " . $usage["id"];
                break;
        }

        // show versions
        if (is_array($usage["hist_nr"]) &&
            (count($usage["hist_nr"]) > 1 || $usage["hist_nr"][0] > 0)) {
            asort($usage["hist_nr"]);
            $ver = $sep = "";
            if ($usage["hist_nr"][0] == 0) {
                array_shift($usage["hist_nr"]);
                $usage["hist_nr"][] = 0;
            }
            foreach ($usage["hist_nr"] as $nr) {
                if ($nr > 0) {
                    $ver .= $sep . $nr;
                } else {
                    $ver .= $sep . $this->lng->txt("cont_current_version");
                }
                $sep = ", ";
            }

            $this->tpl->setCurrentBlock("versions");
            $this->tpl->setVariable("TXT_VERSIONS", $this->lng->txt("cont_versions"));
            $this->tpl->setVariable("VAL_VERSIONS", $ver);
            $this->tpl->parseCurrentBlock();
        }

        if ($item["obj_type_txt"] != "") {
            $this->tpl->setCurrentBlock("type");
            $this->tpl->setVariable("TXT_TYPE", $this->lng->txt("type"));
            $this->tpl->setVariable("VAL_TYPE", $item["obj_type_txt"]);
            $this->tpl->parseCurrentBlock();
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

            if ($item["sub_txt"] != "") {
                $this->tpl->setVariable("SEP", ", ");
                $this->tpl->setVariable("SUB_TXT", $item["sub_txt"]);
                if ($item["sub_title"] != "") {
                    $this->tpl->setVariable("SEP2", ": ");
                    $this->tpl->setVariable("SUB_TITLE", $item["sub_title"]);
                }
            }
        } else {
            $this->tpl->setVariable("TXT_OBJECT_NO_LINK", $this->lng->txt("cont_users_have_mob_in_clip1") .
                " " . $usage["cnt"] . " " . $this->lng->txt("cont_users_have_mob_in_clip2"));
        }
    }

    public function getFirstWritableRefId(int $a_obj_id): int
    {
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
