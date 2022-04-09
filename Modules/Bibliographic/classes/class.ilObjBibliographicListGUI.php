<?php

/**
 * Class ilObjDataCollectionListGUI
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * @extends ilObjectListGUI
 */
class ilObjBibliographicListGUI extends ilObjectListGUI
{

    /**
     * initialisation
     */
    public function init() : void
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
    public function getProperties() : array
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

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
