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
* SCORM Manifest
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMManifest extends ilSCORMObject
{
    public string $import_id;
    public ?string $version;
    public ?string $xml_base;


    /**
    * Constructor
    *
    * @param	int		$a_id		Object ID
    * @access	public
    */
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sma");
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
        $this->setTitle($a_import_id);
    }

    /**
     * @return string|null
     */
    public function getVersion() : ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $a_version
     * @return void
     */
    public function setVersion(?string $a_version) : void
    {
        $this->version = $a_version;
    }

    /**
     * @return string|null
     */
    public function getXmlBase() : ?string
    {
        return $this->xml_base;
    }

    /**
     * @param string|null $a_xml_base
     * @return void
     */
    public function setXmlBase(?string $a_xml_base) : void
    {
        $this->xml_base = $a_xml_base;
    }

    /**
     * @return void
     */
    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
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

    /**
     * @return void
     */
    public function create() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        parent::create();

        $ilDB->manipulateF(
            '
		INSERT INTO sc_manifest (obj_id, import_id, version, xml_base) 
		VALUES (%s,%s,%s,%s)',
            array('integer','text','text','text'),
            array($this->getId(),$this->getImportId(),$this->getVersion(),$this->getXmlBase())
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

    /**
     * @return void
     */
    public function delete() : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        parent::delete();

        $ilDB->manipulateF('DELETE FROM sc_manifest WHERE obj_id = %s', array('integer'), array($this->getId()));
    }
}
