<?php declare(strict_types=1);
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
* SCORM Organizations
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOrganizations extends ilSCORMObject
{
    public string $default_organization;

    /**
    * Constructor
    * @param int $a_id Object ID
    */
    public function __construct(int $a_id = 0)
    {
        global $DIC;
        $lng = $DIC->language();

        // title should be overrriden by ilSCORMExplorer
        $this->setTitle($lng->txt("cont_organizations"));

        parent::__construct($a_id);
        $this->setType("sos");
    }

    public function getDefaultOrganization() : string
    {
        return $this->default_organization;
    }

    public function setDefaultOrganization(string $a_def_org) : void
    {
        $this->default_organization = $a_def_org;
    }

    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::read();

        $obj_set = $ilDB->queryF(
            'SELECT default_organization FROM sc_organizations WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->setDefaultOrganization($obj_rec["default_organization"]);
    }

    public function create() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::create();

        $ilDB->manipulateF(
            '
			INSERT INTO sc_organizations (obj_id, default_organization) VALUES (%s, %s)',
            array('integer', 'text'),
            array($this->getId(), $this->getDefaultOrganization())
        );
    }

    public function update() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::update();

        $ilDB->manipulateF(
            '
			UPDATE sc_organizations 
			SET default_organization = %s
			WHERE obj_id = %s',
            array('text', 'integer'),
            array($this->getDefaultOrganization(), $this->getId())
        );
    }

    public function delete() : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        parent::delete();

        $ilDB->manipulateF(
            'DELETE FROM sc_organizations WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
    }
}
