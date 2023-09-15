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
 * Class ilObjDataCollectionListGUI
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilObjBibliographicListGUI extends ilObjectListGUI
{
    /**
     * initialisation
     */
    public function init(): void
    {
        $this->lng->loadLanguageModule('bibl');
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = false;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = "bibl";
        $this->gui_class_name = "ilobjbibliographicgui";
        $this->commands = ilObjBibliographicAccess::_getCommands();
    }


    /**
     * Get item properties
     *
     * @return    array        array of property arrays:
     *                    "alert" (boolean) => display as an alert property (usually in red)
     *                    "property" (string) => property name
     *                    "value" (string) => property value
     */
    public function getProperties(): array
    {
        global $DIC;
        $lng = $DIC['lng'];

        $props = array();
        $obj = new ilObjBibliographic($this->obj_id);
        if (!$obj->isMigrated()) {
            $props[] = [
                "alert" => true,
                "property" => $lng->txt("migrated"),
                "value" => $lng->txt("not_yet_migrated"),
                "propertyNameVisible" => false,
            ];
        }
        if (!ilObjBibliographicAccess::_lookupOnline($this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $lng->txt("status"),
                "value" => $lng->txt("offline"),
                "newline" => true
            );
        }

        return $props;
    }
}
