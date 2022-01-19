<?php
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
* Parent object for SCORM GUI objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMObjectGUI
{
    public $sc_object;
    public $tpl;
    public $lng;


    public function __construct($a_id = 0)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];

        if ($a_id != 0) {
            $this->sc_object = new ilSCORMItem($a_id);
        }
        $this->tpl = $tpl;
        $this->lng = $lng;
    }

    /**
    * get instance of specialized GUI class
    *
    * static
    */
    public function &getInstance($a_id)
    {
        $object = new ilSCORMObject($a_id);
        switch ($object->getType()) {
            case "sit":					// item
                $item = new ilSCORMItemGUI($a_id);
                return $item;

            case "sos":					// organizations
                $sos_gui = new ilSCORMOrganizationsGUI($a_id);
                return $sos_gui;

            case "sor":					// organization
                $sor_gui = new ilSCORMOrganizationGUI($a_id);
                return $sor_gui;

            case "sma":					// manifest
                $sma_gui = new ilSCORMManifestGUI($a_id);
                return $sma_gui;

            case "srs":					// resources
                $srs_gui = new ilSCORMResourcesGUI($a_id);
                return $srs_gui;

            case "sre":					// resource
                $sre_gui = new ilSCORMResourceGUI($a_id);
                return $sre_gui;
        }
    }


    public function displayParameter($a_name, $a_value)
    {
        $this->tpl->setCurrentBlock("parameter");
        $this->tpl->setVariable("TXT_PARAMETER_NAME", $a_name);
        $this->tpl->setVariable("TXT_PARAMETER_VALUE", $a_value);
        $this->tpl->parseCurrentBlock();
    }
}
