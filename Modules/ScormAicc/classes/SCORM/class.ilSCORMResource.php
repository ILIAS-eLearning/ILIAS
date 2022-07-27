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
* SCORM Resource
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResource extends ilSCORMObject
{
    public string $import_id;
    public string $resourcetype;
    public ?string $scormtype = null;
    public ?string $href;
    public ?string $xml_base = null;
    public array $files;
    public array $dependencies;

    /**
    * @param int $a_id Object ID
    */
    public function __construct(int $a_id = 0)
    {
        $this->files = array();
        $this->dependencies = array();
        $this->setType("sre");
        parent::__construct($a_id);
    }

    public function getImportId() : string
    {
        return $this->import_id;
    }

    public function setImportId(string $a_import_id) : void
    {
        $this->import_id = $a_import_id;
    }

    public function getResourceType() : string
    {
        return $this->resourcetype;
    }

    public function setResourceType(string $a_type) : void
    {
        $this->resourcetype = $a_type;
    }

    public function getScormType() : ?string
    {
        return $this->scormtype;
    }

    public function setScormType(?string $a_scormtype) : void
    {
        $this->scormtype = $a_scormtype;
    }

    public function getHRef() : ?string
    {
        return $this->href;
    }

    public function setHRef(?string $a_href) : void
    {
        $this->href = $a_href;
        $this->setTitle($a_href);
    }

    public function getXmlBase() : ?string
    {
        return $this->xml_base;
    }

    public function setXmlBase(?string $a_xml_base) : void
    {
        $this->xml_base = $a_xml_base;
    }

    public function addFile(ilSCORMResourceFile $a_file_obj) : void
    {
        $this->files[] = &$a_file_obj;
    }

    public function &getFiles() : array
    {
        return $this->files;
    }

    public function addDependency(ilSCORMResourceDependency $a_dependency) : void
    {
        $this->dependencies[] = &$a_dependency;
    }

    public function &getDependencies() : array
    {
        return $this->dependencies;
    }

    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
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

    public function readByIdRef(string $a_id_ref, int $a_slm_id) : void
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC->database();
        
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

    public static function _lookupIdByIdRef(string $a_id_ref, int $a_slm_id) : int
    {
        global $DIC;
        $ilBench = $DIC['ilBench'];
        $ilDB = $DIC->database();
        
        $id_set = $ilDB->queryF(
            'SELECT ob.obj_id id FROM sc_resource res, scorm_object ob
			WHERE ob.obj_id = res.obj_id 
			AND res.import_id = %s 
			AND ob.slm_id = %s',
            array('text', 'integer'),
            array($a_id_ref ,$a_slm_id)
        );
        
        if ($id_rec = $ilDB->fetchAssoc($id_set)) {
            return (int) $id_rec["id"];
        }
        return 0;
    }

    public static function _lookupScormType(int $a_obj_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $st_set = $ilDB->queryF(
            'SELECT scormtype FROM sc_resource WHERE obj_id = %s',
            array('integer'),
            array($a_obj_id)
        );
        if ($st_rec = $ilDB->fetchAssoc($st_set)) {
            return (string) $st_rec["scormtype"];//check UK usually null
        }
        return "";
    }

    public function create() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
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
        foreach ($this->files as $i => $value) {
            $nextId = $ilDB->nextId('sc_resource_file');

            $ilDB->manipulateF(
                '
				INSERT INTO sc_resource_file (id,res_id, href, nr) 
				VALUES(%s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $value->getHref(), ($i + 1))
            );
        }

        // save dependencies
        for ($i = 0, $max = count($this->dependencies); $i < $max; $i++) {
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

    public function update() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
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

        foreach ($this->files as $i => $value) {
            $nextId = $ilDB->nextId('sc_resource_file');
            
            $ilDB->manipulateF(
                'INSERT INTO sc_resource_file (id, res_id, href, nr) 
				VALUES (%s, %s, %s, %s)',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $value->getHref(), ($i + 1))
            );
        }

        // save dependencies
        $ilDB->manipulateF(
            'DELETE FROM sc_resource_dependen WHERE res_id = %s',
            array('integer'),
            array($this->getId())
        );

        foreach ($this->dependencies as $i => $value) {
            $nextId = $ilDB->nextId('sc_resource_dependen');

            $ilDB->manipulateF(
                '
				INSERT INTO sc_resource_dependen (id, res_id, identifierref, nr) VALUES
				(%s, %s, %s, %s) ',
                array('integer', 'integer', 'text', 'integer'),
                array($nextId, $this->getId(), $value->getIdentifierRef(), ($i + 1))
            );
        }
    }

    public function delete() : void
    {
        global $DIC;
        $ilDB = $DIC->database();

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
