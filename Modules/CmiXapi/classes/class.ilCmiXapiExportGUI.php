<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiExportGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiExportGUI extends ilExportGUI
{
    public function __construct(ilObjCmiXapiGUI $a_parent_gui, $a_main_obj = null)
    {
        if (null === $a_main_obj) {
            /** @var ilObjCmiXapi $a_main_obj */
            $a_main_obj = $a_parent_gui->object;
        }
        parent::__construct($a_parent_gui, $a_main_obj);
        $this->addFormat('xml');

        include_once("./Modules/CmiXapi/classes/class.ilCmiXapiExporter.php");
        new ilCmiXapiExporter($a_main_obj);
    }
}
