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
 * List booking participants
 * @author Jesús López <lopez@leifos.com>
 */
class ilBookingParticipantsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected int $ref_id;
    protected int $pool_id;
    protected array $filter;
    protected array $objects;
    
    public function __construct(
        ilBookingParticipantGUI $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        int $a_pool_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->ref_id = $a_ref_id;
        $this->pool_id = $a_pool_id;

        $this->setId("bkprt" . $a_ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt("participants"));

        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("book_bobj"));
        $this->addColumn($this->lng->txt("action"));

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.booking_participant_row.html", "Modules/BookingManager");
        $this->setResetCommand("resetParticipantsFilter");
        $this->setFilterCommand("applyParticipantsFilter");
        $this->setDisableFilterHiding(true);

        $this->initFilter();

        $this->getItems($this->getCurrentFilter());
    }

    public function initFilter() : void
    {
        //object
        $this->objects = array();
        foreach (ilBookingObject::getList($this->pool_id) as $item) {
            $this->objects[$item["booking_object_id"]] = $item["title"];
        }
        $item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
        if ($item !== null) {
            $item->setOptions([
                    "" => $this->lng->txt('book_all'),
                    -1 => $this->lng->txt('book_no_objects')
                ] + $this->objects);
            $this->filter["object"] = $item->getValue();
            $title = $this->addFilterItemByMetaType(
                "title",
                ilTable2GUI::FILTER_TEXT,
                false,
                $this->lng->txt("object") . " " . $this->lng->txt("title") . "/" . $this->lng->txt("description")
            );
            if ($title !== null) {
                $this->filter["title"] = $title->getValue();
            }
            //user
            $options = array("" => $this->lng->txt('book_all')) +
                ilBookingParticipant::getUserFilter($this->pool_id);
            $item = $this->addFilterItemByMetaType("user", ilTable2GUI::FILTER_SELECT);
            $item->setOptions($options);
            $this->filter["user_id"] = $item->getValue();
        }
    }

    /**
     * Get current filter settings
     */
    public function getCurrentFilter() : array
    {
        $filter = array();
        if ($this->filter["object"]) {
            $filter["object"] = $this->filter["object"];
        }
        if ($this->filter["title"]) {
            $filter["title"] = $this->filter["title"];
        }
        if ($this->filter["user_id"]) {
            $filter["user_id"] = $this->filter["user_id"];
        }

        return $filter;
    }

    /**
     * Gather data and build rows
     */
    public function getItems(array $filter) : void
    {
        $filter_object = (int) ($filter["object"] ?? 0);
        if ($filter_object > 0) {
            $data = ilBookingParticipant::getList($this->pool_id, $filter, $filter["object"]);
        } elseif ($filter_object == -1) {
            $data = ilBookingParticipant::getList($this->pool_id, $filter);
            $data = array_filter($data, static function ($item) {
                return $item["obj_count"] == 0;
            });
        } else {
            $data = ilBookingParticipant::getList($this->pool_id, $filter);
        }

        $this->setMaxCount(count($data));
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        $this->tpl->setVariable("TXT_NAME", $a_set['name']);
        $this->tpl->setCurrentBlock('object_titles');
        foreach ($a_set['object_title'] as $obj_title) {
            $this->tpl->setVariable("TXT_OBJECT", $obj_title);
            $this->tpl->parseCurrentBlock();
        }

        // determin actions form data
        // action assign only if user did not booked all objects.
        $actions = [];
        if ($a_set['obj_count'] < ilBookingObject::getNumberOfObjectsForPool($this->pool_id)) {
            $ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', $a_set['user_id']);
            $actions[] = array(
                'text' => $lng->txt("book_assign_object"),
                'url' => $ctrl->getLinkTargetByClass("ilbookingparticipantgui", 'assignObjects')
            );
            $ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');
        }

        $bp = new ilObjBookingPool($this->pool_id, false);
        if ($a_set['obj_count'] == 1 && $bp->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'bkusr', $a_set['user_id']);
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'object_id', $a_set['object_ids'][0]);
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'part_view', ilBookingParticipantGUI::PARTICIPANT_VIEW);

            $actions[] = array(
                'text' => $lng->txt("book_deassign"),
                'url' => $ctrl->getLinkTargetByClass("ilbookingreservationsgui", 'rsvConfirmCancelUser')
            );

            $ctrl->setParameterByClass('ilbookingreservationsgui', 'bkusr', '');
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'object_id', '');
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'part_view', '');
        } elseif ($a_set['obj_count'] > 1 || $bp->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'user_id', $a_set['user_id']);
            $actions[] = array(
                'text' => $lng->txt("book_deassign"),
                'url' => $ctrl->getLinkTargetByClass("ilbookingreservationsgui", 'log')
            );
            $ctrl->setParameterByClass('ilbookingreservationsgui', 'user_id', '');
        }

        $this->tpl->setCurrentBlock('actions');
        foreach ($actions as $action) {
            $this->tpl->setVariable("TXT_ACTION", $action['text']);
            $this->tpl->setVariable("URL_ACTION", $action['url']);
            $this->tpl->parseCurrentBlock();
        }
    }
}
