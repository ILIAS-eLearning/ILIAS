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

}