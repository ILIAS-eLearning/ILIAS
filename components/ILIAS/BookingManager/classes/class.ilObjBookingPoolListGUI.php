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
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBookingPoolListGUI extends ilObjectListGUI
{
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        parent::__construct();
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();
    }

    public function init(): void
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "book";
        $this->gui_class_name = "ilobjbookingpoolgui";

        // general commands array
        $this->commands = ilObjBookingPoolAccess::_getCommands();
    }

    public function getCommandLink(string $cmd): string
    {
        $ilCtrl = $this->ctrl;

        switch ($cmd) {
            default:
                $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
                $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $cmd);
                $ilCtrl->setParameterByClass(
                    "ilrepositorygui",
                    "ref_id",
                    $this->book_request->getRefId()
                );
                break;
        }

        return $cmd_link;
    }

    public function getProperties(): array
    {
        $lng = $this->lng;

        // #11193

        $props = array();

        if (!ilObjBookingPool::_lookupOnline($this->obj_id)) {
            $props[] = array("alert" => true, "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"));
        }
        return $props;
    }
}
