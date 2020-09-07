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
    public $default_organization;


    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        global $DIC;
        $lng = $DIC['lng'];

        // title should be overrriden by ilSCORMExplorer
        $this->setTitle($lng->txt("cont_organizations"));

        parent::__construct($a_id);
        $this->setType("sos");
    }

    public function getDefaultOrganization()
    {
        return $this->default_organization;
    }

    public function setDefaultOrganization($a_def_org)
    {
        $this->default_organization = $a_def_org;
    }

    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::read();

        $obj_set = $ilDB->queryF(
            'SELECT default_organization FROM sc_organizations WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->setDefaultOrganization($obj_rec["default_organization"]);
    }

    public function create()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::create();

        $ilDB->manipulateF(
            '
			INSERT INTO sc_organizations (obj_id, default_organization) VALUES (%s, %s)',
            array('integer', 'text'),
            array($this->getId(), $this->getDefaultOrganization())
        );
    }

    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
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

    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        parent::delete();

        $ilDB->manipulateF(
            'DELETE FROM sc_organizations WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
    }
}
