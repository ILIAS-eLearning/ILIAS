<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilLMPresentationLinker
{
    const TARGET_GUI = "illmpresentationgui";

    /**
     * Constructor
     */
    public function __construct(ilObjLearningModule $lm,
        ilLMTree $lm_tree,
        ilLMPresentationStatus $pres_status,
        ilLMNavigationStatus $nav_status,
        ilLMPresentationRequest $r,
        ilCtrl $ctrl)
    {
        $this->ctrl = $ctrl;
        $this->current_page = $nav_status->getCurrentPage();
        $this->lm_tree = $lm_tree;
        $this->back_pg = $r->getRequestedBackPage();
        $this->from_page = $r->getRequestedFromPage();
        $this->offline = $pres_status->offline();
        $this->export_all_languages = $pres_status->exportAllLanguages();
        $this->lang = $pres_status->getLang();
        $this->lm = $lm;
        $this->requested_ref_id = $r->getRequestedRefId();
        $this->offline = $pres_status->offline();
        $this->export_format = $pres_status->getExportFormat();
    }

    /**
     * handles links for learning module presentation
     */
    function getLink($a_cmd = "", $a_obj_id = "", $a_frame = "", $a_type = "",
        $a_back_link = "append", $a_anchor = "", $a_srcstring = "")
    {
       if ($a_cmd == "")
        {
            $a_cmd = "layout";
        }

        // handling of free pages
        $cur_page_id = $this->current_page;
        $back_pg = $this->back_pg;
        if ($a_obj_id != "" && !$this->lm_tree->isInTree($a_obj_id) && $cur_page_id != "" &&
            $a_back_link == "append")
        {
            if ($back_pg != "")
            {
                $back_pg = $cur_page_id.":".$back_pg;
            }
            else
            {
                $back_pg = $cur_page_id;
            }
        }
        else
        {
            if ($a_back_link == "reduce")
            {
                $limpos = strpos($this->back_pg, ":");

                if ($limpos > 0)
                {
                    $back_pg = substr($back_pg, strpos($back_pg, ":") + 1);
                }
                else
                {
                    $back_pg = "";
                }
            }
            else if ($a_back_link != "keep")
            {
                $back_pg = "";
            }
        }

        // handle online links
        if (!$this->offline)
        {
            if ($this->from_page == "")
            {
                // added if due to #23216 (from page has been set in lots of usual navigation links)
                if (!in_array($a_frame, array("", "_blank")))
                {
                    $this->ctrl->setParameterByClass(self::TARGET_GUI, "from_page", $cur_page_id);
                }
            }
            else
            {
                // faq link on page (in faq frame) includes faq link on other page
                // if added due to bug #11007
                if (!in_array($a_frame, array("", "_blank")))
                {
                    $this->ctrl->setParameterByClass(self::TARGET_GUI, "from_page", $this->from_page);
                }
            }

            if ($a_anchor !=  "")
            {
                $this->ctrl->setParameterByClass(self::TARGET_GUI, "anchor", rawurlencode($a_anchor));
            }
            if ($a_srcstring != "")
            {
                $this->ctrl->setParameterByClass(self::TARGET_GUI, "srcstring", $a_srcstring);
            }
            switch ($a_cmd)
            {
                case "fullscreen":
                    $link = $this->ctrl->getLinkTargetByClass(self::TARGET_GUI, "fullscreen", "", false, false);
                    break;

                default:

                    if ($back_pg != "")
                    {
                        $this->ctrl->setParameterByClass(self::TARGET_GUI, "back_pg", $back_pg);
                    }
                    if ($a_frame != "")
                    {
                        $this->ctrl->setParameterByClass(self::TARGET_GUI, "frame", $a_frame);
                    }
                    if ($a_obj_id != "")
                    {
                        switch ($a_type)
                        {
                            case "MediaObject":
                                $this->ctrl->setParameterByClass(self::TARGET_GUI, "mob_id", $a_obj_id);
                                break;

                            default:
                                $this->ctrl->setParameterByClass(self::TARGET_GUI, "obj_id", $a_obj_id);
                                $link.= "&amp;obj_id=".$a_obj_id;
                                break;
                        }
                    }
                    if ($a_type != "")
                    {
                        $this->ctrl->setParameterByClass(self::TARGET_GUI, "obj_type", $a_type);
                    }
                    $link = $this->ctrl->getLinkTargetByClass(self::TARGET_GUI, $a_cmd, $a_anchor);
//					$link = str_replace("&", "&amp;", $link);

                    $this->ctrl->setParameterByClass(self::TARGET_GUI, "frame", "");
                    $this->ctrl->setParameterByClass(self::TARGET_GUI, "obj_id", "");
                    $this->ctrl->setParameterByClass(self::TARGET_GUI, "mob_id", "");
                    break;
            }
        }
        else	// handle offline links
        {
            $lang_suffix = "";
            if ($this->export_all_languages)
            {
                if ($this->lang != "" && $this->lang != "-")
                {
                    $lang_suffix = "_".$this->lang;
                }
            }

            switch ($a_cmd)
            {
                case "downloadFile":
                    break;

                case "fullscreen":
                    $link = "fullscreen.html";		// id is handled by xslt
                    break;

                case "layout":

                    if ($a_obj_id == "")
                    {
                        $a_obj_id = $this->lm_tree->getRootId();
                        $pg_node = $this->lm_tree->fetchSuccessorNode($a_obj_id, "pg");
                        $a_obj_id = $pg_node["obj_id"];
                    }
                    if ($a_type == "StructureObject")
                    {
                        $pg_node = $this->lm_tree->fetchSuccessorNode($a_obj_id, "pg");
                        $a_obj_id = $pg_node["obj_id"];
                    }
                    if ($a_frame != "" && $a_frame != "_blank")
                    {
                        if ($a_frame != "toc")
                        {
                            $link = "frame_".$a_obj_id."_".$a_frame.$lang_suffix.".html";
                        }
                        else	// don't save multiple toc frames (all the same)
                        {
                            $link = "frame_".$a_frame.$lang_suffix.".html";
                        }
                    }
                    else
                    {
                        //if ($nid = ilLMObject::_lookupNID($this->lm->getId(), $a_obj_id, "pg"))
                        if ($nid = ilLMPageObject::getExportId($this->lm->getId(), $a_obj_id))
                        {
                            $link = "lm_pg_".$nid.$lang_suffix.".html";
                        }
                        else
                        {
                            $link = "lm_pg_".$a_obj_id.$lang_suffix.".html";
                        }
                    }
                    break;

                case "glossary":
                    $link = "term_".$a_obj_id.".html";
                    break;

                case "media":
                    $link = "media_".$a_obj_id.".html";
                    break;

                default:
                    break;
            }
        }

        $this->ctrl->clearParametersByClass(self::TARGET_GUI);

        return $link;
    }

    public function getLayoutLinkTargets()
    {
        $targets = [
            "New" => [
                "Type" => "New",
                "Frame" => "_blank",
                "OnClick" => ""],
            "FAQ" => [
                "Type" => "FAQ",
                "Frame" => "faq",
                "OnClick" => "return il.LearningModule.showContentFrame(event, 'faq');"],
            "Glossary" => [
                "Type" => "Glossary",
                "Frame" => "glossary",
                "OnClick" => "return il.LearningModule.showContentFrame(event, 'glossary');"],
            "Media" => [
                "Type" => "Media",
                "Frame" => "media",
                "OnClick" => "return il.LearningModule.showContentFrame(event, 'media');"]
        ];

        return $targets;
    }

    /**
     * Get XMl for Link Targets
     */
    public function getLinkTargetsXML()
    {
        $link_info = "<LinkTargets>";
        foreach ($this->getLayoutLinkTargets() as $k => $t)
        {
            $link_info.="<LinkTarget TargetFrame=\"".$t["Type"]."\" LinkTarget=\"".$t["Frame"]."\" OnClick=\"".$t["OnClick"]."\" />";
        }
        $link_info.= "</LinkTargets>";
        return $link_info;
    }

    /**
     * get xml for links
     */
    function getLinkXML($a_int_links, $a_layoutframes)
    {
        $ilCtrl = $this->ctrl;

        // Determine whether the view of a learning resource should
        // be shown in the frameset of ilias, or in a separate window.
        $showViewInFrameset = true;

        if ($a_layoutframes == "")
        {
            $a_layoutframes = array();
        }
        $link_info = "<IntLinkInfos>";
        foreach ($a_int_links as $int_link)
        {
            $target = $int_link["Target"];
            if (substr($target, 0, 4) == "il__")
            {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $int_link["Type"];
                $targetframe = ($int_link["TargetFrame"] != "")
                    ? $int_link["TargetFrame"]
                    : "None";

                // anchor
                $anc = $anc_add = "";
                if ($int_link["Anchor"] != "")
                {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_".rawurlencode($int_link["Anchor"]);
                }
                $lcontent = "";
                switch($type)
                {
                    case "PageObject":
                    case "StructureObject":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        if ($lm_id == $this->lm->getId() ||
                            ($targetframe != "None" && $targetframe != "New"))
                        {
                            $ltarget = $a_layoutframes[$targetframe]["Frame"];
                            $nframe = ($ltarget == "")
                                ? ""
                                : $ltarget;
                            if ($ltarget == "")
                            {
                                if ($showViewInFrameset) {
                                    $ltarget="_parent";
                                } else {
                                    $ltarget="_top";
                                }
                            }
                            // scorm always in 1window view and link target
                            // is always same frame
                            if ($this->export_format == "scorm" &&
                                $this->offline)
                            {
                                $ltarget = "";
                            }
                            $cmd = "layout";
                            if ($nframe != "") {
                                $cmd = "page";
                            }
                            $href =
                                $this->getLink($cmd, $target_id, $nframe, $type,
                                    "append", $anc);
                            if ($lm_id == "")
                            {
                                $href = "";
                            }
                        }
                        else
                        {
                            if (!$this->offline)
                            {
                                if ($type == "PageObject")
                                {
                                    $href = "./goto.php?target=pg_".$target_id.$anc_add;
                                }
                                else
                                {
                                    $href = "./goto.php?target=st_".$target_id;
                                }
                            }
                            else
                            {
                                if ($type == "PageObject")
                                {
                                    $href = ILIAS_HTTP_PATH."/goto.php?target=pg_".$target_id.$anc_add."&amp;client_id=".CLIENT_ID;
                                }
                                else
                                {
                                    $href = ILIAS_HTTP_PATH."/goto.php?target=st_".$target_id."&amp;client_id=".CLIENT_ID;
                                }
                            }
                            if ($targetframe != "New")
                            {
                                $ltarget = ilFrameTargetInfo::_getFrame("MainContent");
                            }
                            else
                            {
                                $ltarget = "_blank";
                            }
                        }
                        break;

                    case "GlossaryItem":
                        if ($targetframe == "None")
                        {
                            $targetframe = "Glossary";
                        }
                        $ltarget = $a_layoutframes[$targetframe]["Frame"];
                        $nframe = ($ltarget == "")
                            ? $_GET["frame"]
                            : $ltarget;
                        $href =
                            $this->getLink($a_cmd = "glossary", $target_id, $nframe, $type);
                        break;

                    case "MediaObject":
                        $ltarget = $a_layoutframes[$targetframe]["Frame"];
                        $nframe = ($ltarget == "")
                            ? $_GET["frame"]
                            : $ltarget;
                        $href =
                            $this->getLink($a_cmd = "media", $target_id, $nframe, $type);
                        break;

                    case "RepositoryItem":
                        $obj_type = ilObject::_lookupType($target_id, true);
                        $obj_id = ilObject::_lookupObjId($target_id);
                        if (!$this->offline)
                        {
                            $href = "./goto.php?target=".$obj_type."_".$target_id;
                        }
                        else
                        {
                            $href = ILIAS_HTTP_PATH."/goto.php?target=".$obj_type."_".$target_id."&amp;client_id=".CLIENT_ID;
                        }
                        $ltarget = ilFrameTargetInfo::_getFrame("MainContent");
                        break;

                    case "WikiPage":
                        $href = ilWikiPage::getGotoForWikiPageTarget($target_id);
                        break;

                    case "File":
                        if (!$this->offline)
                        {
                            $ilCtrl->setParameter($this, "obj_id", $this->current_page);
                            $ilCtrl->setParameter($this, "file_id", "il__file_".$target_id);
                            $href = $ilCtrl->getLinkTarget($this, "downloadFile");
                            $ilCtrl->setParameter($this, "file_id", "");
                            $ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
                        }
                        break;

                    case "User":
                        $obj_type = ilObject::_lookupType($target_id);
                        if ($obj_type == "usr")
                        {
                            $back = $this->ctrl->getLinkTarget($this, "layout");
                            //var_dump($back); exit;
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $target_id);
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
                                rawurlencode($back));
                            $href = "";
                            if (ilUserUtil::hasPublicProfile($target_id))
                            {
                                $href = $this->ctrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML");
                            }
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", "");
                            $lcontent = ilUserUtil::getNamePresentation($target_id, false, false);
                        }
                        break;

                }

                $anc_par = 'Anchor="'.$anc.'"';

                if ($href != "")
                {
                    $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " .
                        "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" LinkContent=\"$lcontent\" $anc_par/>";
                }
            }
        }
        $link_info.= "</IntLinkInfos>";
        return $link_info;
    }


}