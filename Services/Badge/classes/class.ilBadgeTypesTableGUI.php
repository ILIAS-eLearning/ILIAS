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
 * TableGUI class for badge type listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeTypesTableGUI extends ilTable2GUI
{
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd = "",
        bool $a_has_write = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("bdgtps");

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setLimit(9999);

        $this->setTitle($lng->txt("badge_types"));

        $lng->loadLanguageModule("cmps");

        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("name"), "name");
        $this->addColumn($lng->txt("cmps_component"), "comp");
        $this->addColumn($lng->txt("badge_manual"), "manual");
        $this->addColumn($lng->txt("badge_activity_badges"), "activity");
        $this->addColumn($lng->txt("active"), "inactive");

        if ($a_has_write) {
            $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
            $this->addMultiCommand("activateTypes", $lng->txt("activate"));
            $this->addMultiCommand("deactivateTypes", $lng->txt("deactivate"));
        }

        $this->setRowTemplate("tpl.type_row.html", "Services/Badge");
        $this->setDefaultOrderField("name");
        $this->setSelectAllCheckbox("id");

        $this->getItems();
    }

    public function getItems(): void
    {
        $data = array();

        $handler = ilBadgeHandler::getInstance();
        $inactive = $handler->getInactiveTypes();
        foreach ($handler->getComponents() as $component) {
            $provider = $handler->getProviderInstance($component);
            if ($provider) {
                foreach ($provider->getBadgeTypes() as $badge_obj) {
                    $id = $handler->getUniqueTypeId($component, $badge_obj);

                    $data[] = array(
                        "id" => $id,
                        "comp" => $handler->getComponentCaption($component),
                        "name" => $badge_obj->getCaption(),
                        "manual" => (!$badge_obj instanceof ilBadgeAuto),
                        "active" => !in_array($id, $inactive, true),
                        "activity" => in_array("bdga", $badge_obj->getValidObjectTypes(), true)
                    );
                }
            }
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_COMP", $a_set["comp"]);
        $this->tpl->setVariable("TXT_NAME", $a_set["name"]);
        $this->tpl->setVariable("TXT_MANUAL", $a_set["manual"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
        $this->tpl->setVariable("TXT_ACTIVE", $a_set["active"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
        $this->tpl->setVariable("TXT_ACTIVITY", $a_set["activity"]
            ? $lng->txt("yes")
            : $lng->txt("no"));
    }
}
