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
 * List objects / booking pool  assignment.
 * @author Jesús López <lopez@leifos.com>
 */
class ilBookingAssignObjectsTableGUI extends ilTable2GUI
{
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected int $ref_id;
    protected int $pool_id;
    protected int $user_id_to_book;
    protected int $current_bookings;
    protected array $filter;
    protected array $objects;
    protected ilObjBookingPool $pool;

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
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();

        $this->pool_id = $a_pool_id;
        $this->pool = new ilObjBookingPool($this->pool_id, false);

        $user_name = "";
        if ($this->book_request->getBookedUser() > 0) {
            $this->user_id_to_book = $this->book_request->getBookedUser();
            if (!ilObjUser::_exists($this->user_id_to_book)) {
                $this->ctrl->redirect($a_parent_obj, $a_parent_cmd);
            }
            $user_name_data = ilObjUser::_lookupName($this->user_id_to_book);
            $user_name = $user_name_data['lastname'] . ", " . $user_name_data['firstname'];
        } else {
            $this->ctrl->redirect($a_parent_obj, 'render');
        }

        $this->setId("bkaobj" . $a_ref_id);

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->lng->txt("book_assign_object") . ": " . $user_name);

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("description"));
        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $this->addColumn($this->lng->txt("available"));
        }
        $this->addColumn($this->lng->txt("action"));

        //Fix this order field
        //$this->setDefaultOrderField("title");
        //$this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        //$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.booking_assign_object_row.html", "Modules/BookingManager");

        $this->getItems();
    }

    public function getItems() : void
    {
        $data = array();
        $obj_items = ilBookingObject::getList($this->pool_id);
        foreach ($obj_items as $item) {
            if ($this->pool->getScheduleType() === ilObjBookingPool::TYPE_FIX_SCHEDULE ||
                count(ilBookingReservation::getObjectReservationForUser($item['booking_object_id'], $this->user_id_to_book)) === 0) {
                $this->ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', $this->user_id_to_book);
                $this->ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $item['booking_object_id']);
                $this->ctrl->setParameterByClass('ilbookingobjectgui', 'part_view', ilBookingParticipantGUI::PARTICIPANT_VIEW);
                $data[] = array(
                    'object_id' => $item['booking_object_id'],
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'nr_items' => ilBookingReservation::numAvailableFromObjectNoSchedule($item['booking_object_id']) . '/' . $item['nr_items'],
                    'url_assign' => $this->ctrl->getLinkTargetByClass(["ilbookingobjectgui", "ilbookingprocessgui"], 'book')
                );
                $this->ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', '');
                $this->ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', '');
                $this->ctrl->setParameterByClass('ilbookingobjectgui', 'part_view', '');
            }
        }
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        if ($this->pool->getScheduleType() !== ilObjBookingPool::TYPE_FIX_SCHEDULE) {
            $this->tpl->setCurrentBlock("available");
            $this->tpl->setVariable("TXT_AVAILABLE", $a_set['nr_items']);
            $this->tpl->parseCurrentBlock();
        }
        $this->tpl->setVariable("TXT_TITLE", $a_set['title']);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set['description']);
        $this->tpl->setVariable("TXT_ACTION", $this->lng->txt("book_assign"));
        $this->tpl->setVariable("URL_ACTION", $a_set['url_assign']);
    }
}
