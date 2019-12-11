<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItemGUI.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganizationsGUI.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMOrganizationGUI.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourcesGUI.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceGUI.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMManifestGUI.php");

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
                break;

            case "sos":					// organizations
                $sos_gui = new ilSCORMOrganizationsGUI($a_id);
                return $sos_gui;
                break;

            case "sor":					// organization
                $sor_gui = new ilSCORMOrganizationGUI($a_id);
                return $sor_gui;
                break;

            case "sma":					// manifest
                $sma_gui = new ilSCORMManifestGUI($a_id);
                return $sma_gui;
                break;

            case "srs":					// resources
                $srs_gui = new ilSCORMResourcesGUI($a_id);
                return $srs_gui;
                break;

            case "sre":					// resource
                $sre_gui = new ilSCORMResourceGUI($a_id);
                return $sre_gui;
                break;
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
