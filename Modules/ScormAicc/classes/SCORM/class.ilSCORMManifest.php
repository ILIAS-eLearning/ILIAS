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
* SCORM Manifest
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMManifest extends ilSCORMObject
{
    public $import_id;
    public $version;
    public $xml_base;


    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sma");
    }

    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_import_id)
    {
        $this->import_id = $a_import_id;
        $this->setTitle($a_import_id);
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($a_version)
    {
        $this->version = $a_version;
    }

    public function getXmlBase()
    {
        return $this->xml_base;
    }

    public function setXmlBase($a_xml_base)
    {
        $this->xml_base = $a_xml_base;
    }

    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::read();

        $obj_set = $ilDB->queryF(
            'SELECT * FROM sc_manifest WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        
        $this->setImportId($obj_rec["import_id"]);
        $this->setVersion($obj_rec["version"]);
        $this->setXmlBase($obj_rec["xml_base"]);
    }

    public function create()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::create();

        $ilDB->manipulateF(
            '
		INSERT INTO sc_manifest (obj_id, import_id, version, xml_base) 
		VALUES (%s,%s,%s,%s)',
            array('integer','text','text','text'),
            array($this->getId(),$this->getImportId(),$this->getVersion(),$this->getXmlBase())
        );
    }

    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::update();

        $ilDB->manipulateF(
            '
		UPDATE sc_manifest 
		SET import_id = %s,  
			version = %s,  
			xml_base = %s 
		WHERE obj_id = %s',
            array('text','text','text','integer'),
            array($this->getImportId(),$this->getVersion(),$this->getXmlBase(),$this->getId())
        );
    }

    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        parent::delete();

        $ilDB->manipulateF('DELETE FROM sc_manifest WHERE obj_id = %s', array('integer'), array($this->getId()));
    }
}
