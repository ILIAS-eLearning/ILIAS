<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page linker
 *
 * @author killing@leifos.de
 */
class ilPageLinker implements \ILIAS\COPage\PageLinker
{
    /**
     * @var bool
     */
    protected $offline;

    /**
     * @var string
     */
    protected $profile_back_url;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var string
     */
    protected $cmd_gui;

    /**
     * Constructor
     */
    public function __construct(string $cmd_gui_class, $offline = false, $profile_back_url = "", ilCtrl $ctrl = null)
    {
        global $DIC;

        $this->offline = $offline;
        $this->profile_back_url = $profile_back_url;
        $this->cmd_gui = $cmd_gui_class;

        $this->ctrl = (is_null($ctrl))
            ? $DIC->ctrl()
            : $ctrl;
    }

    /**
     * @inheritDoc
     */
    public function setOffline($offline = true)
    {
        $this->offline = $offline;
    }

    public function setProfileBackUrl($url)
    {
        $this->profile_back_url = $url;
    }


    /**
     * @inheritDoc
     */
    public function getLayoutLinkTargets() : array
    {
        $targets = [];

        return $targets;
    }

    /**
     * Get XMl for Link Targets
     */
    public function getLinkTargetsXML()
    {
        $layoutLinkTargets = $this->getLayoutLinkTargets();

        if (0 === count($layoutLinkTargets)) {
            return '';
        }

        $link_info = "<LinkTargets>";
        foreach ($layoutLinkTargets as $k => $t) {
            $link_info .= "<LinkTarget TargetFrame=\"" . $t["Type"] . "\" LinkTarget=\"" . $t["Frame"] . "\" OnClick=\"" . $t["OnClick"] . "\" />";
        }
        $link_info .= "</LinkTargets>";
        return $link_info;
    }

    /**
     * @inheritDoc
     */
    public function getLinkXML($int_links) : string
    {
        $link_info = "<IntLinkInfos>";
        foreach ($int_links as $int_link) {
            $target = $int_link["Target"];
            if (substr($target, 0, 4) == "il__") {
                $target_arr = explode("_", $target);
                $target_id = $target_arr[count($target_arr) - 1];
                $type = $int_link["Type"];

                $targetframe = ($int_link["TargetFrame"] != "")
                    ? $int_link["TargetFrame"]
                    : "None";

                $ltarget = "_top";
                if ($targetframe != "None") {
                    $ltarget = "_blank";
                }

                // anchor
                $anc = $anc_add = "";
                if ($int_link["Anchor"] != "") {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_" . rawurlencode($int_link["Anchor"]);
                }

                $href = "";
                $lcontent = "";
                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        if ($type == "PageObject") {
                            $href = "./goto.php?target=pg_" . $target_id . $anc_add;
                        } else {
                            $href = "./goto.php?target=st_" . $target_id;
                        }
                        if ($lm_id == "") {
                            $href = "";
                        }
                        break;

                    case "GlossaryItem":
                        if ($targetframe == "Glossary") {
                            $ltarget = "";
                        }
                        $href = "./goto.php?target=git_" . $target_id;
                        break;

                    case "MediaObject":
                        if ($this->offline) {
                            $href = "media_" . $target_id . ".html";
                        } else {
                            $this->ctrl->setParameterByClass($this->cmd_gui, "mob_id", $target_id);
                            $href = $this->ctrl->getLinkTargetByClass(
                                $this->cmd_gui,
                                "displayMedia",
                                "",
                                false,
                                true
                            );
                            $this->ctrl->setParameterByClass($this->cmd_gui, "mob_id", "");
                        }
                        break;

                    case "WikiPage":
                        $wiki_anc = "";
                        if ($int_link["Anchor"] != "") {
                            $wiki_anc = "#".rawurlencode($int_link["Anchor"]);
                        }
                        $href = ilWikiPage::getGotoForWikiPageTarget($target_id) . $wiki_anc;
                        break;

                    case "PortfolioPage":
                        $href = ilPortfolioPage::getGotoForPortfolioPageTarget($target_id, $this->offline);
                        break;

                    case "RepositoryItem":
                        $obj_type = ilObject::_lookupType($target_id, true);
                        $obj_id = ilObject::_lookupObjId($target_id);
                        $href = "./goto.php?target=" . $obj_type . "_" . $target_id;
                        break;

                    case "User":
                        $obj_type = ilObject::_lookupType($target_id);
                        if ($obj_type == "usr") {
                            include_once("./Services/User/classes/class.ilUserUtil.php");
                            $back = $this->profile_back_url;
                            //var_dump($back); exit;
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $target_id);
                            if (strlen($back)) {
                                $this->ctrl->setParameterByClass(
                                    "ilpublicuserprofilegui",
                                    "back_url",
                                    rawurlencode($back)
                                );
                            }
                            $href = "";
                            include_once("./Services/User/classes/class.ilUserUtil.php");
                            if (ilUserUtil::hasPublicProfile($target_id)) {
                                $href = $this->ctrl->getLinkTargetByClass(
                                    ["ildashboardgui", "ilpublicuserprofilegui"],
                                    "getHTML",
                                    "",
                                    false,
                                    true
                                );
                            }
                            $this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user_id", "");
                            $lcontent = ilUserUtil::getNamePresentation($target_id, false, false);
                            $lcontent = str_replace("&", "&amp;", htmlentities($lcontent));
                        }
                        break;

                }
                if ($href != "") {
                    $anc_par = 'Anchor="' . $anc . '"';
                    $link_info .= "<IntLinkInfo Target=\"$target\" Type=\"$type\" " . $anc_par . " " .
                        "TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" LinkContent=\"$lcontent\" />";
                }
            }
        }
        $link_info .= "</IntLinkInfos>";
        $link_info .= $this->getLinkTargetsXML();
        return $link_info;
    }

    /**
     * @inheritDoc
     */
    public function getFullscreenLink() : string
    {
        if ($this->offline) {
            return "fullscreen.html";
        }

        return $this->ctrl->getLinkTargetByClass($this->cmd_gui, "fullscreen", "", false, false);
    }
}
