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

use ILIAS\DI\UIServices;
use ILIAS\Badge\Tile;

/**
 * TableGUI class for user badge listing
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgePersonalTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    private readonly UIServices $ui;
    private readonly Tile $tile;
    private readonly ilLanguage $language;

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
        $this->ui = $DIC->ui();
        $this->tile = new Tile($DIC);
        $this->language = $DIC->language();
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
        $this->setEnableTitle(false);
        $this->setEnableNumInfo(false);
        $this->setShowRowsSelector(false);
        $this->setLimit(PHP_INT_MAX);
        $this->addColumn("", "", 1);
        $this->addColumn($lng->txt("title"), "title");
        $this->addColumn($lng->txt("awarded_by"), "parent_title");
        $this->addColumn($lng->txt("badge_issued_on"), "issued_on");
        $this->addColumn($lng->txt("badge_in_profile"), "active");

        $this->setDefaultOrderField("title");

        $this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.personal_row.html", "components/ILIAS/Badge");

        $this->addMultiCommand("activate", $lng->txt("badge_add_to_profile"));
        $this->addMultiCommand("deactivate", $lng->txt("badge_remove_from_profile"));
        $this->setSelectAllCheckbox("badge_id");

        $this->getItems($a_user_id);
    }

    public function getItems(int $a_user_id): void
    {
        $lng = $this->lng;

        $data = [];

        foreach (ilBadgeAssignment::getInstancesByUserId($a_user_id) as $ass) {
            $badge = new ilBadge($ass->getBadgeId());

            $parent = null;
            if ($badge->getParentId()) {
                $parent = $badge->getParentMeta();
                if ($parent["type"] === "bdga") {
                    $parent = null;
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
                "renderer" => fn() => $this->tile->asTitle(
                    $this->tile->modalContentWithAssignment($badge, $ass)
                ),
            );
        }

        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $current = $a_set["active"] ? [
            'target' => 'deactivate',
            'text' => 'badge_remove_from_profile',
            'active' => $this->language->txt('yes'),
        ] : [
            'target' => 'activate',
            'text' => 'badge_add_to_profile',
            'active' => $this->language->txt('no'),
        ];

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("PREVIEW", $this->ui->renderer()->render($a_set["renderer"]()));
        $this->tpl->setVariable("TXT_ISSUED_ON", ilDatePresentation::formatDate(new ilDateTime($a_set["issued_on"], IL_CAL_UNIX)));
        $this->tpl->setVariable("TXT_ACTIVE", $current["active"]);

        if ($a_set["parent"]) {
            $this->tpl->setVariable("TXT_PARENT", $a_set["parent_title"]);
            $this->tpl->setVariable(
                "SRC_PARENT",
                ilObject::_getIcon((int) $a_set["parent"]["id"], "big", $a_set["parent"]["type"])
            );
        }

        $ilCtrl->setParameter($this->getParentObject(), "badge_id", $a_set["id"]);
        $url = $ilCtrl->getLinkTarget($this->getParentObject(), $current['target']);
        $ilCtrl->setParameter($this->getParentObject(), "badge_id", "");
    }
}
