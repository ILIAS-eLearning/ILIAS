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
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceFile.php");
require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResourceDependency.php");

/**
* SCORM Resource
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResource extends ilSCORMObject
{
    public $import_id;
    public $resourcetype;
    public $scormtype;
    public $href;
    public $xml_base;
    public $files;
    public $dependencies;


    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct($a_id = 0)
    {
        $this->files = array();
        $this->dependencies = array();
        $this->setType("sre");
        parent::__construct($a_id);
    }

    public function getImportId()
    {
        return $this->import_id;
    }

    public function setImportId($a_import_id)
    {
        $this->import_id = $a_import_id;
    }

    public function getResourceType()
    {
        return $this->resourcetype;
    }

    public function setResourceType($a_type)
    {
        $this->resourcetype = $a_type;
    }

    public function getScormType()
    {
        return $this->scormtype;
    }

    public function setScormType($a_scormtype)
    {
        $this->scormtype = $a_scormtype;
    }

    public function getHRef()
    {
        return $this->href;
    }

    public function setHRef($a_href)
    {
        $this->href = $a_href;
        $this->setTitle($a_href);
    }

    public function getXmlBase()
    {
        return $this->xml_base;
    }

    public function setXmlBase($a_xml_base)
    {
        $this->xml_base = $a_xml_base;
    }

    public function addFile(&$a_file_obj)
    {
        $this->files[] = &$a_file_obj;
    }

    public function &getFiles()
    {
        return $this->files;
    }

    public function addDependency(&$a_dependency)
    {
        $this->dependencies[] = &$a_dependency;
    }

    public function &getDependencies()
    {
        return $this->dependencies;
    }

    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::read();
    
        $obj_set = $ilDB->queryF(
            'SELECT * FROM sc_resource WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->setImportId($obj_rec["import_id"]);
        $this->setResourceType($obj_rec["resourcetype"]);
        $this->setScormType($obj_rec["scormtype"]);
        $this->setHRef($obj_rec["href"]);
        $this->setXmlBase($obj_rec["xml_base"]);

        // read files
        $file_set = $ilDB->queryF(
            'SELECT href FROM sc_resource_file WHERE res_id = %s ORDER BY nr',
            array('integer'),
            array($this->getId())
        );
        while ($file_rec = $ilDB->fetchAssoc($file_set)) {
            $res_file = new ilSCORMResourceFile();
            $res_file->setHref($file_rec["href"]);
            $this->addFile($res_file);
        }
        // read dependencies

        $dep_set = $ilDB->queryF(
            'SELECT identifierref FROM sc_resource_dependen WHERE res_id = %s ORDER BY nr',
            array('integer'),
            array($this->getId())
        );
        while ($dep_rec = $ilDB->fetchAssoc($dep_set)) {
            $res_dep = new ilSCORMResourceDependency();
            $res_dep->setIdentifierRef($dep_rec["identifierref"]);
            $this->addDependency($res_dep);
        }
    }

    public function readByIdRef($a_id_ref, $a_slm_id)
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];
        
        $ilBench->start("SCORMResource", "readByIdRef_Query");
        
        $id_set = $ilDB->queryF(
            'SELECT ob.obj_id id FROM sc_resource res, scorm_object ob
			WHERE ob.obj_id = res.obj_id 
			AND res.import_id = %s 
			AND ob.slm_id = %s',
            array('text', 'integer'),
            array($a_id_ref, $a_slm_id)
        );
        
        $ilBench->stop("SCORMResource", "readByIdRef_Query");
        
        if ($id_rec = $ilDB->fetchAssoc($id_set)) {
            $this->setId($id_rec["id"]);
            $this->read();
        }
    }

    public static function _lookupIdByIdRef($a_id_ref, $a_slm_id)
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC['ilDB'];
        
        $id_set = $ilDB->queryF(
            'SELECT ob.obj_id id FROM sc_resource res, scorm_object ob
			WHERE ob.obj_id = res.obj_id 
			AND res.import_id = %s 
			AND ob.slm_id = %s',
            array('text', 'integer'),
            array($a_id_ref ,$a_slm_id)
        );
        
        if ($id_rec = $ilDB->fetchAssoc($id_set)) {
            return $id_rec["id"];
        }
        return 0;
    }

    public static function _lookupScormType($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        $st_set = $ilDB->queryF(
            'SELECT scormtype FROM sc_resource WHERE obj_id = %s',
            array('integer'),
            array($a_obj_id)
        );
        if ($st_rec = $ilDB->fetchAssoc($st_set)) {
            return $st_rec["scormtype"];
        }
        return "";
    }

    public function create()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::create();

        $ilDB->manipulateF(
            '
			INSERT INTO sc_resource 
			(obj_id, import_id, resourcetype, scormtype, href, xml_base) 
			VALUES(%s, %s, %s, %s, %s, %s)',
            array('integer','text','text','text','text','text'),
            array(	$this->getId(),
                    $this->getImportId(),
                    $this->getResourceType(),
                    $this->getScormType(),
                    $this->getHref(),
                    $this->getXmlBase()
            )
        );

        // save files
        for ($i = 0; $i < count($this->files); $i++) {
            $nextId = $ilDB->nextId('sc_resource_file');

            $ilDB->manipulateF(
                '
				INSERT INTO sc_resource_file (id,res_id, href, nr) 
				VALUES(%s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $this->files[$i]->getHref(), ($i + 1))
            );
        }

        // save dependencies
        for ($i = 0; $i < count($this->dependencies); $i++) {
            $nextId = $ilDB->nextId('sc_resource_dependen');

            $ilDB->manipulateF(
                '
				INSERT INTO sc_resource_dependen (id, res_id, identifierref, nr)
				VALUES(%s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $this->files[$i]->getHref(), ($i + 1))
            );
        }
    }

    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::update();

        $ilDB->manipulateF(
            '
			UPDATE sc_resource 
			SET import_id = %s,
				resourcetype = %s,
				scormtype = %s,
				href = %s,
				xml_base = %s
			WHERE obj_id = %s',
            array('text', 'text', 'text', 'text', 'text', 'integer'),
            array(	$this->getImportId(),
                    $this->getResourceType(),
                    $this->getScormType(),
                    $this->getHRef(),
                    $this->getXmlBase(),
                    $this->getId())
        );

        // save files
        $ilDB->manipulateF(
            'DELETE FROM sc_resource_file WHERE res_id = %s',
            array('integer'),
            array($this->getId())
        );
        
        for ($i = 0; $i < count($this->files); $i++) {
            $nextId = $ilDB->nextId('sc_resource_file');
            
            $ilDB->manipulateF(
                'INSERT INTO sc_resource_file (id, res_id, href, nr) 
				VALUES (%s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $this->files[$i]->getHref(), ($i + 1))
            );
        }

        // save dependencies
        $ilDB->manipulateF(
            'DELETE FROM sc_resource_dependen WHERE res_id = %s',
            array('integer'),
            array($this->getId())
        );
        
        for ($i = 0; $i < count($this->dependencies); $i++) {
            $nextId = $ilDB->nextId('sc_resource_dependen');

            $ilDB->manipulateF(
                '
				INSERT INTO sc_resource_dependen (id, res_id, identifierref, nr) VALUES
				(%s, %s, %s, %s) ',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $this->dependencies[$i]->getIdentifierRef(), ($i + 1))
            );
        }
    }

    public function delete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        parent::delete();

        $ilDB->manipulateF(
            'DELETE FROM sc_resource WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );

        $ilDB->manipulateF(
            'DELETE FROM sc_resource_file WHERE res_id = %s',
            array('integer'),
            array($this->getId())
        );

        $ilDB->manipulateF(
            'DELETE FROM sc_resource_dependen WHERE res_id = %s',
            array('integer'),
            array($this->getId())
        );
    }
}
