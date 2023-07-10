<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingBulkCreationTableGUI extends ilTable2GUI
{
    public function __construct(
        ilBookBulkCreationGUI $a_parent_obj,
        string $a_parent_cmd,
        string $raw_data,
        int $pool_id
    ) {
        global $DIC;

        $this->setId("bulk_creation");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $objects_manager = $DIC->bookingManager()
                               ->internal()
                               ->domain()
                               ->objects($pool_id);

        $ctrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->setMaxCount(9999);

        $this->setTitle($lng->txt("book_booking_objects"));
        $this->setData($objects_manager->getDataArrayFromInputString($raw_data));

        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("description"));
        $this->addColumn($this->lng->txt("booking_nr_of_items"));

        $this->setFormAction($ctrl->getFormAction($a_parent_obj, "createObjects"));
        $this->setRowTemplate(
            "tpl.bulk_creation_row.html",
            "Modules/BookingManager/Objects"
        );
        $this->addHiddenInput("data", $raw_data);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("DESCRIPTION", $a_set["description"]);
        $this->tpl->setVariable("NR", $a_set["nr"]);
    }

}