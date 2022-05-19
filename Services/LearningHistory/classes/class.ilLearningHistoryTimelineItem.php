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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryTimelineItem implements ilTimelineItemInt
{
    protected ilLearningHistoryEntry $lh_entry;
    protected \ILIAS\DI\UIServices $ui;
    protected int $user_id;
    protected ilAccessHandler $access;
    protected ilTree $tree;

    public function __construct(
        ilLearningHistoryEntry $lh_entry,
        \ILIAS\DI\UIServices $ui,
        int $user_id,
        ilAccessHandler $access,
        ilTree $tree
    ) {
        $this->access = $access;
        $this->lh_entry = $lh_entry;
        $this->ui = $ui;
        $this->user_id = $user_id;
        $this->tree = $tree;
    }

    public function getDatetime() : ilDateTime
    {
        return new ilDateTime($this->lh_entry->getTimestamp(), IL_CAL_UNIX);
    }

    public function render() : string
    {
        $access = $this->access;
        $parent_ref_id = 0;

        $tpl = new ilTemplate("tpl.timeline_item_inner.html", true, true, "Services/LearningHistory");

        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        $ico = $f->symbol()->icon()->custom($this->lh_entry->getIconPath(), '')->withSize(\ILIAS\UI\Component\Symbol\Icon\Icon::MEDIUM);

        $obj_id = $this->lh_entry->getObjId();
        $title = ilObject::_lookupTitle($obj_id);
        if ($this->lh_entry->getRefId() === 0) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
        } else {
            $ref_ids = [$this->lh_entry->getRefId()];
        }
        $readable_ref_id = 0;
        foreach ($ref_ids as $ref_id) {
            if ($readable_ref_id === 0 && $access->checkAccessOfUser($this->user_id, "read", "", $ref_id)) {
                $readable_ref_id = $ref_id;
            }
        }

        if ($readable_ref_id > 0) {
            if (ilObject::_lookupType(ilObject::_lookupObjId($readable_ref_id)) === "crs") {
                $parent_ref_id = $readable_ref_id;
            } else {
                $parent_ref_id = $this->tree->checkForParentType($readable_ref_id, "crs", true);
            }
        }

        if ($parent_ref_id > 0) {
            $text = $this->lh_entry->getAchieveInText();
            $obj_placeholder = "<a href='" . ilLink::_getLink($parent_ref_id) . "'>" .
                $this->getEmphasizedTitle(ilObject::_lookupTitle(ilObject::_lookupObjId($parent_ref_id))) . "</a>";
            $text = str_replace("$2$", $obj_placeholder, $text);
        } else {
            $text = $this->lh_entry->getAchieveText();
        }

        $obj_placeholder = ($readable_ref_id > 0)
                ? "<a href='" . ilLink::_getLink($readable_ref_id) . "'>" . $this->getEmphasizedTitle($title) . "</a>"
                : $this->getEmphasizedTitle($title);
        $text = str_replace("$1$", $obj_placeholder, $text);

        $tpl->setVariable("TEXT", $text);
        $tpl->setVariable("ICON", $r->render($ico));

        return $tpl->get();
    }

    protected function getEmphasizedTitle(string $title) : string
    {
        $tpl = new ilTemplate("tpl.emphasized_title.php", true, true, "Services/LearningHistory");
        $tpl->setVariable("TITLE", $title);
        return $tpl->get();
    }

    public function renderFooter() : string
    {
        return "";
    }
}
