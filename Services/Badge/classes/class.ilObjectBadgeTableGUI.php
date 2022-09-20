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
 * TableGUI class for badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjectBadgeTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected bool $has_write;
    protected array $filter = [];

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd = "",
        bool $a_has_write = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("bdgobdg");
        $this->has_write = $a_has_write;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);

        $this->setTitle($lng->txt("badge_object_badges"));

        if ($this->has_write) {
            $this->addColumn("", "", 1);
        }

        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("type"), "type");
        $this->addColumn($lng->txt("object"), "container");
        $this->addColumn($lng->txt("active"), "active");
        $this->addColumn($lng->txt("action"), "");

        if ($this->has_write) {
            $this->addMultiCommand("activateObjectBadges", $lng->txt("activate"));
            $this->addMultiCommand("deactivateObjectBadges", $lng->txt("deactivate"));
            $this->addMultiCommand("confirmDeleteObjectBadges", $lng->txt("delete"));
            $this->setSelectAllCheckbox("id");
        }

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.object_badge_row.html", "Services/Badge");
        $this->setDefaultOrderField("title");

        $this->setFilterCommand("applyObjectFilter");
        $this->setResetCommand("resetObjectFilter");

        $this->initFilter();

        $this->getItems();
    }

    public function initFilter(): void
    {
        $lng = $this->lng;

        $title = $this->addFilterItemByMetaType("title", self::FILTER_TEXT, false, $lng->txt("title"));
        $this->filter["title"] = $title->getValue();

        $object = $this->addFilterItemByMetaType("object", self::FILTER_TEXT, false, $lng->txt("object"));
        $this->filter["object"] = $object->getValue();

        $lng->loadLanguageModule("search");

        $options = array(
            "" => $lng->txt("search_any"),
        );
        foreach (ilBadgeHandler::getInstance()->getAvailableTypes() as $id => $type) {
            // no activity badges
            if (!in_array("bdga", $type->getValidObjectTypes(), true)) {
                $options[$id] = ilBadge::getExtendedTypeCaption($type);
            }
        }
        asort($options);

        $type = $this->addFilterItemByMetaType("type", self::FILTER_SELECT, false, $lng->txt("type"));
        $type->setOptions($options);
        $this->filter["type"] = $type->getValue();
    }

    public function getItems(): void
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $data = [];

        $types = ilBadgeHandler::getInstance()->getAvailableTypes();

        foreach (ilBadge::getObjectInstances($this->filter) as $badge_item) {
            // :TODO: container presentation
            $container_url = null;
            $container = '<img class="ilIcon" src="' .
                    ilObject::_getIcon((int) $badge_item["parent_id"], "big", $badge_item["parent_type"]) .
                    '" alt="' . $lng->txt("obj_" . $badge_item["parent_type"]) .
                    '" title="' . $lng->txt("obj_" . $badge_item["parent_type"]) . '" /> ' .
                    $badge_item["parent_title"];

            if ($badge_item["deleted"] ?? false) {
                $container .= ' <span class="il_ItemAlertProperty">' . $lng->txt("deleted") . '</span>';
            } else {
                $ref_ids = ilObject::_getAllReferences($badge_item["parent_id"]);
                $ref_id = array_shift($ref_ids);
                if ($ilAccess->checkAccess("read", "", $ref_id)) {
                    $container_url = ilLink::_getLink($ref_id);
                }
            }

            $type_caption = ilBadge::getExtendedTypeCaption($types[$badge_item["type_id"]]);

            $data[] = array(
                "id" => $badge_item["id"],
                "active" => $badge_item["active"],
                "type" => $type_caption,
                "title" => $badge_item["title"],
                "container_meta" => $container,
                "container_url" => $container_url,
                "container_id" => $badge_item["parent_id"],
                "renderer" => new ilBadgeRenderer(null, new ilBadge($badge_item["id"]))
            );
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($a_set["container_url"]) {
            $this->tpl->setCurrentBlock("container_link_bl");
            $this->tpl->setVariable("TXT_CONTAINER", $a_set["container_meta"]);
            $this->tpl->setVariable("URL_CONTAINER", $a_set["container_url"]);
        } else {
            $this->tpl->setCurrentBlock("container_nolink_bl");
            $this->tpl->setVariable("TXT_CONTAINER_STATIC", $a_set["container_meta"]);
        }
        $this->tpl->parseCurrentBlock();

        if ($this->has_write) {
            $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        }

        $this->tpl->setVariable("PREVIEW", $a_set["renderer"]->getHTML());
        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_TYPE", $a_set["type"]);
        $this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
            ? $lng->txt("yes")
            : $lng->txt("no"));

        if ($this->has_write) {
            $ilCtrl->setParameter($this->getParentObject(), "pid", $a_set["container_id"]);
            $ilCtrl->setParameter($this->getParentObject(), "bid", $a_set["id"]);
            $url = $ilCtrl->getLinkTarget($this->getParentObject(), "listObjectBadgeUsers");
            $ilCtrl->setParameter($this->getParentObject(), "bid", "");
            $ilCtrl->setParameter($this->getParentObject(), "pid", "");

            $this->tpl->setVariable("TXT_LIST", $lng->txt("users"));
            $this->tpl->setVariable("URL_LIST", $url);
        }
    }
}
