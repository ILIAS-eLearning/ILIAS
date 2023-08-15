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


declare(strict_types=1);

class ilObjDataCollectionListGUI extends ilObjectListGUI
{
    /**
     * initialisation
     */
    public function init(): void
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "dcl";
        $this->gui_class_name = "ilobjdatacollectiongui";

        // general commands array
        $this->commands = ilObjDataCollectionAccess::_getCommands();
    }

    /**
     * Get item properties
     * @return    array        array of property arrays:
     *                    "alert" (boolean) => display as an alert property (usually in red)
     *                    "property" (string) => property name
     *                    "value" (string) => property value
     */
    public function getProperties(): array
    {
        $props = [];

        if (!ilObjDataCollectionAccess::_lookupOnline($this->obj_id)) {
            $props[] = [
                "alert" => true,
                "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline"),
            ];
        }

        return $props;
    }
}
