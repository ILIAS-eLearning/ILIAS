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

    public function setImportId($a_import_id): void
    {
        $this->import_id = $a_import_id;
    }

    public function getStructure()
    {
        return $this->structure;
    }

    public function setStructure($a_structure): void
    {
        $this->structure = $a_structure;
    }

    public function read(): void
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

    public function create(): void
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

    public function update(): void
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

    public function delete(): void
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
