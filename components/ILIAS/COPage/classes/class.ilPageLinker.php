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

use ILIAS\User\Profile\PublicProfileGUI;

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
    protected \ILIAS\StaticURL\Services $static_url;

    public function __construct(
        string $cmd_gui_class,
        bool $offline = false,
        string $profile_back_url = "",
        ?ilCtrl $ctrl = null
    ) {
        global $DIC;

        $this->offline = $offline;
        $this->profile_back_url = $profile_back_url;
        $this->cmd_gui = $cmd_gui_class;

        $this->ctrl = (is_null($ctrl))
            ? $DIC->ctrl()
            : $ctrl;
        $this->static_url = $DIC["static_url"];
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
        $ilCtrl = $this->ctrl;
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
                if (($int_link["Anchor"] ?? "") != "") {
                    $anc = $int_link["Anchor"];
                    $anc_add = "_" . rawurlencode($int_link["Anchor"]);
                }

                $href = "";
                $lcontent = "";
                switch ($type) {
                    case "PageObject":
                    case "StructureObject":
                        $lm_id = ilLMObject::_lookupContObjID($target_id);
                        if ($type === "PageObject") {
                            $href = (string) $this->static_url->builder()->build(
                                "pg",
                                null,
                                [$target_id]
                            ) . $anc_add;
                        } else {
                            $href = (string) $this->static_url->builder()->build(
                                "st",
                                null,
                                [$target_id]
                            ) . $anc_add;
                        }
                        if ($lm_id == "") {
                            $href = "";
                        }
                        break;

                    case "GlossaryItem":
                        if ($targetframe == "Glossary") {
                            $ltarget = "";
                        }
                        if ($this->offline) {
                            $href = "term_" . $target_id . ".html";
                        } else {
                            $href = "./goto.php?target=git_" . $target_id;
                        }
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
                        if (($int_link["Anchor"] ?? "") != "") {
                            $wiki_anc = "#" . rawurlencode("copganc_" . $int_link["Anchor"]);
                        }
                        $href = ilWikiPage::getGotoForWikiPageTarget($target_id) . $wiki_anc;
                        break;

                    case "PortfolioPage":
                        $href = ilPortfolioPage::getGotoForPortfolioPageTarget($target_id, $this->offline);
                        break;

                    case "RepositoryItem":
                        $obj_type = ilObject::_lookupType((int) $target_id, true);
                        if ((int) $target_id > 0) {
                            $href = (string) $this->static_url->builder()->build(
                                $obj_type,
                                new \ILIAS\Data\ReferenceId($target_id)
                            );
                        } else {
                            $href = "#";
                        }
                        break;

                    case "File":
                        if (!$this->offline) {
                            $href = "#";
                        }
                        break;

                    case "User":
                        // target = il__user_329
                        $obj_type = ilObject::_lookupType((int) $target_id);
                        if ($obj_type == "usr") {
                            $back = $this->profile_back_url;
                            //var_dump($back); exit;
                            $this->ctrl->setParameterByClass(PublicProfileGUI::class, "user_id", $target_id);
                            if (strlen($back)) {
                                $this->ctrl->setParameterByClass(
                                    PublicProfileGUI::class,
                                    "back_url",
                                    rawurlencode($back)
                                );
                            }
                            $href = "";
                            if (ilUserUtil::hasPublicProfile($target_id)) {
                                $href = $this->ctrl->getLinkTargetByClass(
                                    [ilPublicProfileBaseClassGUI::class, PublicProfileGUI::class],
                                    "getHTML",
                                    "",
                                    false,
                                    true
                                );
                            }
                            $this->ctrl->setParameterByClass(PublicProfileGUI::class, "user_id", "");
                            $lcontent = ilUserUtil::getNamePresentation($target_id, false, false);
                            $lcontent = str_replace("&", "&amp;", htmlentities($lcontent));
                        }
                        break;
                }
                if ($href != "" || $type === "User") {
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
