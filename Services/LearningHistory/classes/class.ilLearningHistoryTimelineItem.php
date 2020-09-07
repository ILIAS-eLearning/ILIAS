<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author @leifos.de
 * @ingroup
 */
class ilLearningHistoryTimelineItem implements ilTimelineItemInt
{
    /**
     * @var ilLearningHistoryEntry
     */
    protected $lh_entry;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * Constructor
     * ilLearningHistoryTimelineItem constructor.
     * @param ilLearningHistoryEntry $lh_entry
     */
    public function __construct(
        ilLearningHistoryEntry $lh_entry,
        \ILIAS\DI\UIServices $ui,
        $user_id,
        ilAccessHandler $access,
        ilTree $tree
    ) {
        $this->access = $access;
        $this->lh_entry = $lh_entry;
        $this->ui = $ui;
        $this->user_id = $user_id;
        $this->tree = $tree;
    }

    /**
     * @inheritdoc
     */
    public function getDatetime()
    {
        return new ilDateTime($this->lh_entry->getTimestamp(), IL_CAL_UNIX);
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $access = $this->access;

        $tpl = new ilTemplate("tpl.timeline_item_inner.html", true, true, "Services/LearningHistory");

        $f = $this->ui->factory();
        $r = $this->ui->renderer();

        $ico = $f->icon()->custom($this->lh_entry->getIconPath(), '')->withSize(\ILIAS\UI\Component\Icon\Custom::MEDIUM);

        $obj_id = $this->lh_entry->getObjId();
        $title = ilObject::_lookupTitle($obj_id);
        if ($this->lh_entry->getRefId() == 0) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
        } else {
            $ref_ids = [$this->lh_entry->getRefId()];
        }
        $readable_ref_id = 0;
        foreach ($ref_ids as $ref_id) {
            if ($readable_ref_id == 0 && $access->checkAccessOfUser($this->user_id, "read", "", $ref_id)) {
                $readable_ref_id = $ref_id;
            }
        }

        if ($readable_ref_id > 0) {
            if (ilObject::_lookupType(ilObject::_lookupObjId($readable_ref_id)) == "crs") {
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

    /**
     * Get emphasized title
     *
     * @param string
     * @return string
     */
    protected function getEmphasizedTitle($title)
    {
        $tpl = new ilTemplate("tpl.emphasized_title.php", true, true, "Services/LearningHistory");
        $tpl->setVariable("TITLE", $title);
        ;
        return $tpl->get();
    }

    /**
     * Render footer
     * @throws ilCtrlException
     */
    public function renderFooter()
    {
    }
}
