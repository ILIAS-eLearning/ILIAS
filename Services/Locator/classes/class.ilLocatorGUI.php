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
 * locator handling class
 *
 * This class supplies an implementation for the locator.
 * The locator will send its output to ist own frame, enabling more flexibility in
 * the design of the desktop.
 * @author Arjan Ammerlaan <a.l.ammerlaan@web.de>
 */
class ilLocatorGUI
{
    protected ?int $ref_id = null;
    protected bool $textonly;
    protected bool $offline = false;
    protected ilTree $tree;
    protected ilCtrl $ctrl;
    protected ilObjectDefinition $obj_definition;
    protected ilAccessHandler $access;
    protected ilSetting $settings;
    protected ilLanguage $lng;
    protected array $entries = [];
    protected bool $initialised = false;

    public function __construct()
    {
        $this->entries = array();
        $this->offline = false;
        $this->setTextOnly(false);
    }

    protected function init(): void
    {
        global $DIC;

        if ($this->initialised) {
            return;
        }
        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->obj_definition = $DIC["objDefinition"];
        $this->access = $DIC->access();
        $this->settings = $DIC->settings();
        $lng = $DIC->language();

        $this->lng = $lng;
        $this->ref_id = ($DIC->http()->wrapper()->query()->has("ref_id"))
            ? $DIC->http()->wrapper()->query()->retrieve("ref_id", $DIC->refinery()->kindlyTo()->int())
            : null;
        if ($this->ref_id == 0) {
            $this->ref_id = null;
        }
    }

    public function setTextOnly(bool $a_textonly): void
    {
        $this->textonly = $a_textonly;
    }

    public function setOffline(bool $a_offline): void
    {
        $this->offline = $a_offline;
    }

    public function getOffline(): bool
    {
        return $this->offline;
    }

    public function getTextOnly(): bool
    {
        return $this->textonly;
    }

    /**
     * @param int $a_ref_id
     * @return void
     * @throws ilCtrlException
     */
    public function addRepositoryItems(int $a_ref_id = 0): void
    {
        $this->init();
        $setting = $this->settings;
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;

        if ($a_ref_id == 0) {
            $a_ref_id = $this->ref_id;
        }

        $a_start = ROOT_FOLDER_ID;
        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id, $a_start);

            // check if path contains crs
            $crs_ref_id = 0;
            foreach ($path as $k => $v) {
                if ($v["type"] == "crs") {
                    $crs_ref_id = $v["child"];
                }
            }
            if (!$setting->get("rep_breadcr_crs")) { // no overwrite
                $crs_ref_id = 0;
            } elseif ($setting->get("rep_breadcr_crs_overwrite")) { // overwrite
                // course wants full path
                if (ilContainer::_lookupContainerSetting(ilObject::_lookupObjId($crs_ref_id), "rep_breacrumb") == ilObjCourseGUI::BREADCRUMB_FULL_PATH) {
                    $crs_ref_id = 0;
                }
                // course wants default and default wants full path
                if (ilContainer::_lookupContainerSetting(ilObject::_lookupObjId($crs_ref_id), "rep_breacrumb") == ilObjCourseGUI::BREADCRUMB_DEFAULT && !$setting->get("rep_breadcr_crs_default")) {
                    $crs_ref_id = 0;
                }
            }

            // add item for each node on path
            foreach ($path as $key => $row) {
                if (!in_array($row["type"], array("root", "cat", "crs", "fold", "grp", "prg", "lso"))) {
                    continue;
                }
                if ($crs_ref_id > 0 && $row["child"] == $crs_ref_id) {
                    $crs_ref_id = 0;
                }
                if ($crs_ref_id > 0) {
                    continue;
                }

                if ($row["title"] == "ILIAS" && $row["type"] == "root") {
                    $row["title"] = $this->lng->txt("repository");
                }

                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $row["child"]);
                $this->addItem(
                    $row["title"],
                    $ilCtrl->getLinkTargetByClass("ilrepositorygui", ""),
                    ilFrameTargetInfo::_getFrame("MainContent"),
                    $row["child"]
                );
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
            }
        }
    }

    /**
     * add administration tree items
     * @throws ilCtrlException
     */
    public function addAdministrationItems(int $a_ref_id = 0): void
    {
        $this->init();
        $tree = $this->tree;
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->obj_definition;
        $lng = $this->lng;

        if ($a_ref_id == 0) {
            $a_ref_id = $this->ref_id;
        }

        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id);

            // add item for each node on path
            foreach ($path as $key => $row) {
                if (!in_array($row["type"], array("root", "cat", "crs", "fold", "grp"))) {
                    continue;
                }

                if ($row["child"] == ROOT_FOLDER_ID) {
                    $row["title"] = $lng->txt("repository");
                }

                $class_name = $objDefinition->getClassName($row["type"]);
                $class = strtolower("ilObj" . $class_name . "GUI");
                $ilCtrl->setParameterByClass($class, "ref_id", $row["child"]);
                $this->addItem(
                    $row["title"],
                    $ilCtrl->getLinkTargetByClass($class, "view"),
                    "",
                    $row["child"]
                );
            }
        }
    }

    public function addContextItems(
        int $a_ref_id,
        bool $a_omit_node = false,
        int $a_stop = 0
    ): void {
        $this->init();
        $tree = $this->tree;

        if ($a_ref_id > 0) {
            $path = $tree->getPathFull($a_ref_id);

            // we want to show the full path, from the major container to the item
            // (folders are not! treated as containers here), at least one parent item
            $r_path = array_reverse($path);
            $first = "";
            $omit = array();
            $do_omit = false;
            foreach ($r_path as $key => $row) {
                if ($first == "") {
                    if (in_array($row["type"], array("root", "cat", "grp", "crs")) &&
                        $row["child"] != $a_ref_id) {
                        $first = $row["child"];
                    }
                }
                if ($a_stop == $row["child"]) {
                    $do_omit = true;
                }
                $omit[$row["child"]] = $do_omit;
            }

            $add_it = false;
            foreach ($path as $key => $row) {
                if ($first == $row["child"]) {
                    $add_it = true;
                }


                if ($add_it && !$omit[$row["child"]] &&
                    (!$a_omit_node || ($row["child"] != $a_ref_id))) {
                    //echo "-".ilObject::_lookupTitle($row["obj_id"])."-";
                    if ($row["title"] == "ILIAS" && $row["type"] == "root") {
                        $row["title"] = $this->lng->txt("repository");
                    }
                    $this->addItem(
                        $row["title"],
                        "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" . $row["type"] . "_" . $row["child"],
                        "_top",
                        $row["child"],
                        $row["type"]
                    );
                }
            }
        }
    }

    public function addItem(
        string $a_title,
        string $a_link,
        string $a_frame = "",
        int $a_ref_id = 0,
        ?string $type = null
    ): void {
        // LTI
        global $DIC;
        $ltiview = $DIC['lti'];

        $this->init();

        $ilAccess = $this->access;

        if ($a_ref_id > 0 && !$ilAccess->checkAccess("visible", "", $a_ref_id)) {
            return;
        }
        // LTI
        if ($ltiview->isActive()) {
            $a_frame = "_self";
        }
        $this->entries[] = array("title" => $a_title,
            "link" => $a_link, "frame" => $a_frame, "ref_id" => $a_ref_id, "type" => $type);
    }

    public function clearItems(): void
    {
        $this->entries = array();
    }

    public function getItems(): array
    {
        return $this->entries;
    }

    public function getHTML(): string
    {
        $this->init();
        $lng = $this->lng;
        $icon_path = "";

        if ($this->getTextOnly()) {
            $loc_tpl = new ilTemplate("tpl.locator_text_only.html", true, true, "Services/Locator");
        } else {
            $loc_tpl = new ilTemplate("tpl.locator.html", true, true, "Services/Locator");
        }

        $items = $this->getItems();
        $first = true;
        if (count($items) > 0) {
            foreach ($items as $item) {
                if (!$first) {
                    $loc_tpl->touchBlock("locator_separator_prefix");
                }

                if ($item["ref_id"] > 0) {
                    $obj_id = ilObject::_lookupObjId($item["ref_id"]);
                    $type = ilObject::_lookupType($obj_id);

                    if (!$this->getTextOnly()) {
                        $icon_path = ilObject::_getIcon(
                            $obj_id,
                            "tiny",
                            $type,
                            $this->getOffline()
                        );
                    }

                    $loc_tpl->setCurrentBlock("locator_img");
                    $loc_tpl->setVariable("IMG_SRC", $icon_path);
                    $loc_tpl->setVariable(
                        "IMG_ALT",
                        $lng->txt("obj_" . $type)
                    );
                    $loc_tpl->parseCurrentBlock();
                }

                $loc_tpl->setCurrentBlock("locator_item");
                if ($item["link"] != "") {
                    $loc_tpl->setVariable("LINK_ITEM", $item["link"]);
                    if ($item["frame"] != "") {
                        $loc_tpl->setVariable("LINK_TARGET", ' target="' . $item["frame"] . '" ');
                    }
                    $loc_tpl->setVariable("ITEM", $item["title"]);
                } else {
                    $loc_tpl->setVariable("PREFIX", $item["title"]);
                }
                $loc_tpl->parseCurrentBlock();

                $first = false;
            }
        } else {
//            $loc_tpl->setVariable("NOITEM", "&nbsp;");
//            $loc_tpl->touchBlock("locator");
        }
        $loc_tpl->setVariable("TXT_BREADCRUMBS", $lng->txt("breadcrumb_navigation"));

        return trim($loc_tpl->get());
    }

    /**
     * Get text version
     */
    public function getTextVersion(): string
    {
        $this->init();
        $items = $this->getItems();
        $first = true;

        $str = "";
        foreach ($items as $item) {
            if (!$first) {
                $str .= " > ";
            }

            $str .= $item["title"];

            $first = false;
        }

        return $str;
    }
}
