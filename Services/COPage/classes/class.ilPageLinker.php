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
 * Page linker
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPageLinker implements \ILIAS\COPage\PageLinker
{
    protected bool $offline;
    protected string $profile_back_url = "";
    protected ilCtrl $ctrl;
    protected string $cmd_gui;

    public function __construct(
        string $cmd_gui_class,
        bool $offline = false,
        string $profile_back_url = "",
        ilCtrl $ctrl = null
    ) {
        global $DIC;

        $this->offline = $offline;
        $this->profile_back_url = $profile_back_url;
        $this->cmd_gui = $cmd_gui_class;

        $this->ctrl = (is_null($ctrl))
            ? $DIC->ctrl()
            : $ctrl;
    }

    public function setOffline(bool $offline = true): void
    {
        $this->offline = $offline;
    }

    public function setProfileBackUrl(string $url): void
    {
        $this->profile_back_url = $url;
    }


    public function getLayoutLinkTargets(): array
    {
        $targets = [];
        return $targets;
    }

    public function getLinkTargetsXML(): string
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

    public function getLinkXML(array $int_links): string
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
                        if ($targetframe == "None") {
                            $targetframe = "Glossary";
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
                        $href = ilWikiPage::getGotoForWikiPageTarget($target_id);
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
                            if (ilUserUtil::hasPublicProfile($target_id)) {
                                $href = $this->ctrl->getLinkTargetByClass(
                                    ["ilpublicuserprofilegui"],
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

    public function getFullscreenLink(): string
    {
        if ($this->offline) {
            return "fullscreen.html";
        }

        return $this->ctrl->getLinkTargetByClass($this->cmd_gui, "fullscreen", "", false, false);
    }
}
