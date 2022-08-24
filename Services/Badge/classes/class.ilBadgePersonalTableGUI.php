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
 * TableGUI class for user badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgePersonalTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    protected array $filter = [];

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_user_id = null
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }

        $this->setId("bdgprs");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("badge_personal_badges"));

        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("object"), "parent_title");
        $this->addColumn($lng->txt("badge_issued_on"), "issued_on");
        $this->addColumn($lng->txt("badge_in_profile"), "active");
        $this->addColumn($lng->txt("actions"), "");

        $this->setDefaultOrderField("title");

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.personal_row.html", "Services/Badge");

        $this->addMultiCommand("activate", $lng->txt("badge_add_to_profile"));
        $this->addMultiCommand("deactivate", $lng->txt("badge_remove_from_profile"));
        $this->setSelectAllCheckbox("badge_id");

        $this->getItems($a_user_id);
    }

    public function initFilters(array $a_parents): void
    {
        $lng = $this->lng;

        $title = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("title"));
        $this->filter["title"] = $title->getValue();

        $lng->loadLanguageModule("search");

        $options = array(
            "" => $lng->txt("search_any"),
            "-1" => $lng->txt("none")
        );
        asort($a_parents);

        $obj = $this->addFilterItemByMetaType("obj", self::FILTER_SELECT, false, $lng->txt("object"));
        $obj->setOptions($options + $a_parents);
        $this->filter["obj"] = $obj->getValue();
    }

    public function getItems(int $a_user_id): void
    {
        $lng = $this->lng;

        $data = $filter_parent = array();

        foreach (ilBadgeAssignment::getInstancesByUserId($a_user_id) as $ass) {
            $badge = new ilBadge($ass->getBadgeId());

            $parent = null;
            if ($badge->getParentId()) {
                $parent = $badge->getParentMeta();
                if ($parent["type"] === "bdga") {
                    $parent = null;
                } else {
                    $filter_parent[$parent["id"]] =
                        "(" . $lng->txt($parent["type"]) . ") " . $parent["title"];
                }
            }

            $data[] = array(
                "id" => $badge->getId(),
                "title" => $badge->getTitle(),
                "image" => $badge->getImagePath(),
                "issued_on" => $ass->getTimestamp(),
                "parent_title" => $parent ? $parent["title"] : null,
                "parent" => $parent,
                "active" => (bool) $ass->getPosition(),
                "renderer" => new ilBadgeRenderer($ass)
            );
        }

        $this->initFilters($filter_parent);

        if ($this->filter["title"]) {
            foreach ($data as $idx => $row) {
                if (stripos($row["title"], $this->filter["title"]) === false) {
                    unset($data[$idx]);
                }
            }
        }

        if ($this->filter["obj"]) {
            foreach ($data as $idx => $row) {
                if ($this->filter["obj"] > 0) {
                    if (!$row["parent"] || $row["parent"]["id"] != $this->filter["obj"]) {
                        unset($data[$idx]);
                    }
                } elseif ($row["parent"]) {
                    unset($data[$idx]);
                }
            }
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("PREVIEW", $a_set["renderer"]->getHTML());
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_ISSUED_ON", ilDatePresentation::formatDate(new ilDateTime($a_set["issued_on"], IL_CAL_UNIX)));
        $this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
            ? $lng->txt("yes")
            : $lng->txt("no"));

        if ($a_set["parent"]) {
            $this->tpl->setVariable("TXT_PARENT", $a_set["parent_title"]);
            $this->tpl->setVariable(
                "SRC_PARENT",
                ilObject::_getIcon((int) $a_set["parent"]["id"], "big", $a_set["parent"]["type"])
            );
        }

        $actions = new ilAdvancedSelectionListGUI();
        $actions->setListTitle("");

        $ilCtrl->setParameter($this->getParentObject(), "badge_id", $a_set["id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), $a_set["active"]
            ? "deactivate"
            : "activate");
        $ilCtrl->setParameter($this->getParentObject(), "badge_id", "");
        $actions->addItem($lng->txt(!$a_set["active"]
            ? "badge_add_to_profile"
            : "badge_remove_from_profile"), "", $url);


        $this->tpl->setVariable("ACTIONS", $actions->getHTML());
    }
}
