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
* SCORM Organization
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOrganization extends ilSCORMObject
{
    public string $import_id;
    public ?string $structure;


    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType('sor');
    }

    /**
     * @return string
     */
    public function getImportId() : string
    {
        return $this->import_id;
    }

    /**
     * @param string $a_import_id
     * @return void
     */
    public function setImportId(string $a_import_id) : void
    {
        $this->import_id = $a_import_id;
    }

    /**
     * @return string|null
     */
    public function getStructure() : ?string
    {
        return $this->structure;
    }

    /**
     * @param string|null $a_structure
     * @return void
     */
    public function setStructure(?string $a_structure) : void
    {
        $this->structure = $a_structure;
    }

    /**
     * @return void
     */
    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
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

    /**
     * @return void
     */
    public function create() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::create();
        
        $query = 'INSERT INTO sc_organization (obj_id, import_id, structure) VALUES(%s, %s, %s)';
        $ilDB->manipulateF(
            $query,
            array('integer', 'text', 'text'),
            array($this->getId(), $this->getImportId(), $this->getStructure())
        );
    }

    /**
     * @return void
     */
    public function update() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::update();
        
        $query = 'UPDATE sc_organization SET import_id = %s, structure = %s WHERE obj_id = %s';
        $ilDB->manipulateF(
            $query,
            array('text', 'text', 'integer'),
            array($this->getImportId(), $this->getStructure(), $this->getId())
        );
    }

    /**
     * @return void
     */
    public function delete() : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        parent::delete();
        
        $query = 'DELETE FROM sc_organization WHERE obj_id = %s';
        $ilDB->manipulateF(
            $query,
            array('integer'),
            array($this->getId())
        );
    }
}
