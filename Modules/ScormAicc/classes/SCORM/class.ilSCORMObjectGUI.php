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
* Parent object for SCORM GUI objects
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMObjectGUI
{

    /**
     * @var ilSCORMManifest|ilSCORMItem|ilSCORMOrganization|ilSCORMOrganizations
     */
    public $sc_object;
    public ilGlobalTemplate $tpl;
    public ilLanguage $lng;

    public function __construct(int $a_id = 0)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $lng = $DIC->language();

        if ($a_id !== 0) {
            $this->sc_object = new ilSCORMItem($a_id);
        }
        $this->tpl = $tpl;
        $this->lng = $lng;
    }

    /**
     * @return ilSCORMItemGUI|ilSCORMManifestGUI|ilSCORMOrganizationGUI|ilSCORMOrganizationsGUI|ilSCORMResourceGUI|ilSCORMResourcesGUI
     */
    public function &getInstance(int $a_id)
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

            default:
                case "sre":					// resource
                $sre_gui = new ilSCORMResourceGUI($a_id);
                return $sre_gui;
        }
    }

    public function displayParameter(string $a_name, string $a_value) : void
    {
        $this->tpl->setCurrentBlock("parameter");
        $this->tpl->setVariable("TXT_PARAMETER_NAME", $a_name);
        $this->tpl->setVariable("TXT_PARAMETER_VALUE", $a_value);
        $this->tpl->parseCurrentBlock();
    }
}
