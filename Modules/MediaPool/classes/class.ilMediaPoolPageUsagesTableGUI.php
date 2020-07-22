<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for media pool page usages listing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPageUsagesTableGUI extends ilTable2GUI
{
    /**
     * @var ilAccessHandler
     */
    protected $access;

    
    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_page, $a_incl_hist)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $this->repo_tree = $DIC->repositoryTree();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->page = $a_page;
        $this->incl_hist = $a_incl_hist;
        $this->addColumn("", "", "1");	// checkbox
        $this->setEnableHeader(false);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.mep_page_usage_row.html", "Modules/MediaPool");
        $this->getItems();
        $this->setTitle($lng->txt("cont_mob_usages"));
    }

    /**
    * Get usages
    */
    public function getItems()
    {
        $usages = $this->page->getUsages($this->incl_hist);

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
                    $ref_ids = array_filter(ilObject::_getAllReferences($page_obj->getRepoObjId()), function($ref_id) use ($repo_tree) {
                        return $repo_tree->isInTree($ref_id);
                    });
                    $usage["ref_ids"] = $ref_ids;
                    if (count($ref_ids) == 0) {
                        $usage["trash"] = true;
                    }
                }
            }

            if ($usage["type"] == "clip") {
                $clip_cnt++;
            } else {
                if ($this->incl_hist || !$usage["trash"]) {
                    if (empty($agg_usages[$usage["type"] . ":" . $usage["id"]])) {
                        $agg_usages[$usage["type"] . ":" . $usage["id"]] = $usage;
                    }
                    $agg_usages[$usage["type"] . ":" . $usage["id"]]["versions"][] =
                        ["hist_nr" => $usage["hist_nr"],
                         "lang" => $usage["lang"]];
                }
            }
        }

        // usages in clipboards
        if ($clip_cnt > 0) {
            $agg_usages[] = array("type" => "clip", "cnt" => $clip_cnt);
        }
        $this->setData($agg_usages);
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;

        $usage = $a_set;
        
        //var_dump($usage);

        if (is_int(strpos($usage["type"], ":"))) {
            $us_arr = explode(":", $usage["type"]);
            $usage["type"] = $us_arr[1];
            $cont_type = $us_arr[0];
        }
        //var_dump($usage);

        include_once('./Services/Link/classes/class.ilLink.php');

        switch ($usage["type"]) {
            case "pg":
                include_once("./Services/COPage/classes/class.ilPageObjectFactory.php");
                $page_obj = $usage["page"];
                $item = array();

                //$this->tpl->setVariable("TXT_OBJECT", $usage["type"].":".$usage["id"]);
                switch ($cont_type) {
                    case "lm":
                        require_once("./Modules/LearningModule/classes/class.ilObjContentObject.php");
                        require_once("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
                        require_once("./Modules/LearningModule/classes/class.ilLMObject.php");
                        $lm_obj = new ilObjLearningModule($page_obj->getParentId(), false);
                        $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                        $item["obj_title"] = $lm_obj->getTitle();
                        $item["sub_txt"] = $this->lng->txt("pg");
                        $item["sub_title"] = ilLMObject::_lookupTitle($page_obj->getId());
                        $ref_id = $this->getFirstWritableRefId($lm_obj->getId());
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($page_obj->getId() . "_" . $ref_id, "pg");
                        }
                        break;

                    case "wpg":
                        require_once("./Modules/Wiki/classes/class.ilWikiPage.php");
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
                        require_once("./Modules/Glossary/classes/class.ilGlossaryTerm.php");
                        require_once("./Modules/Glossary/classes/class.ilGlossaryDefinition.php");
                        $term_id = ilGlossaryDefinition::_lookupTermId($page_obj->getId());
                        $glo_id = ilGlossaryTerm::_lookGlossaryId($term_id);
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
                        $item["obj_type_txt"] = $this->lng->txt("obj_" . $cont_type);
                        $item["obj_title"] = ilObject::_lookupTitle($page_obj->getId());
                        $ref_id = $this->getFirstWritableRefId($page_obj->getId());
                        if ($ref_id > 0) {
                            $item["obj_link"] = ilLink::_getStaticLink($ref_id, $cont_type);
                        }
                        break;
                }

                if ($usage["trash"]) {
                    $item["obj_title"].= " (".$lng->txt("trash").")";
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
        if (is_array($usage["versions"]) && is_object($usage["page"])) {
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
                    $ver.= "/".$version["lang"];
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

    public function getFirstWritableRefId($a_obj_id)
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
