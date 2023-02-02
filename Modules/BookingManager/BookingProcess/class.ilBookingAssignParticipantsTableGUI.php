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
 * List participant / booking pool  assignment.
 * @author Jesús López <lopez@leifos.com>
 */
class ilBookingAssignParticipantsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected int $ref_id;
    protected int $pool_id;
    protected int $bp_object_id;
    protected ilBookingObject $bp_object;
    protected int $current_bookings;
    protected array $filter;
    protected array $objects;
    protected ilObjBookingPool $pool;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_ref_id,
        int $a_pool_id,
        int $a_booking_obj_id
    ) {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $this->access = $DIC->access();
        $this->ref_id = $a_ref_id;
        $this->bp_object_id = $a_booking_obj_id;
        $this->pool_id = $a_pool_id;
        $this->bp_object = new ilBookingObject($a_booking_obj_id);
        $this->pool = new ilObjBookingPool($this->pool_id, false);

        $this->setId("bkaprt" . $a_ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt("book_assign_participant") . ": " . $this->bp_object->getTitle());

        $this->addColumn("", "", 1);
        $this->addColumn($this->lng->txt("name"), "name");
        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $this->addColumn($this->lng->txt("book_bobj"));
        }
        $this->addColumn($this->lng->txt("action"));

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.booking_assign_participant_row.html", "Modules/BookingManager");


        $this->addHiddenInput('object_id', $a_booking_obj_id);
        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $this->setSelectAllCheckbox('mass');
            $this->addMultiCommand("bookMultipleParticipants", $this->lng->txt("assign"));
        }
        $this->getItems();


        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_NO_SCHEDULE) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $this->lng->txt("book_objects_available"),
                ilBookingReservation::numAvailableFromObjectNoSchedule($a_booking_obj_id)
            ));
        }
    }

    public function getItems(): void
    {
        if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $data = ilBookingParticipant::getList($this->pool_id, []);
        } else {
            $data = ilBookingParticipant::getAssignableParticipants($this->bp_object_id);
        }
        $this->setMaxCount(count($data));
        $this->setData($data);
    }

    protected function fillRow(array $a_set): void
    {
        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $this->tpl->setCurrentBlock("multi");
            $this->tpl->setVariable("MULTI_ID", $a_set['user_id']);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TXT_NAME", $a_set['name']);
        $this->tpl->setCurrentBlock('object_titles');

        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            foreach ($a_set['object_title'] as $obj_title) {
                $this->tpl->setCurrentBlock("object_title");
                $this->tpl->setVariable("TXT_OBJECT", $obj_title);
                $this->tpl->parseCurrentBlock();
            }
            $this->tpl->setCurrentBlock("object_titles");
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameter($this->parent_obj, 'bkusr', $a_set['user_id']);
        $this->ctrl->setParameter($this->parent_obj, 'object_id', $this->bp_object_id);

        $this->tpl->setVariable("TXT_ACTION", $this->lng->txt("book_assign"));
        $this->tpl->setVariable("URL_ACTION", $this->ctrl->getLinkTarget($this->parent_obj, 'book'));

        $this->ctrl->setParameter($this->parent_obj, 'bkusr', '');
        $this->ctrl->setParameter($this->parent_obj, 'object_id', '');
    }
}
