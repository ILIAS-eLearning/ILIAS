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
* SCORM Organization
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOrganization extends ilSCORMObject
{
    public $import_id;
    public $structure;


    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType('sor');
    }

    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_import_id)
    {
        $this->import_id = $a_import_id;
    }

    public function getStructure()
    {
        return $this->structure;
    }

    public function setStructure($a_structure)
    {
        $this->structure = $a_structure;
    }

    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::read();

        $query = 'SELECT import_id, structure FROM sc_organization WHERE obj_id = %s';
        $obj_set = $ilDB->queryF(
            $query,
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);

        $this->setImportId($obj_rec['import_id']);
        $this->setStructure($obj_rec['structure']);
    }

    public function create()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::create();
        
        $query = 'INSERT INTO sc_organization (obj_id, import_id, structure) VALUES(%s, %s, %s)';
        $ilDB->manipulateF(
            $query,
            array('integer', 'text', 'text'),
            array($this->getId(), $this->getImportId(), $this->getStructure())
        );
    }

    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::update();
        
        $query = 'UPDATE sc_organization SET import_id = %s, structure = %s WHERE obj_id = %s';
        $ilDB->manipulateF(
            $query,
            array('text', 'text', 'integer'),
            array($this->getImportId(), $this->getStructure(), $this->getId())
        );
    }

    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        parent::delete();
        
        $query = 'DELETE FROM sc_organization WHERE obj_id = %s';
        $ilDB->manipulateF(
            $query,
            array('integer'),
            array($this->getId())
        );
    }
}
