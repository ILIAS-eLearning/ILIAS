<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    /**
     * @param ilObjCmiXapiGUI   $a_parent_gui
     * @param ilObjCmiXapi|null $a_main_obj
     */
    public function __construct(ilObjCmiXapiGUI $a_parent_gui, ?ilObjCmiXapi $a_main_obj = null)
    {
        if (null === $a_main_obj) {
            /** @var ilObjCmiXapi $a_main_obj */
            $a_main_obj = $a_parent_gui->object;
        }
        parent::__construct($a_parent_gui, $a_main_obj);
        $this->addFormat('xml');
        new ilCmiXapiExporter($a_main_obj);
    }
}
