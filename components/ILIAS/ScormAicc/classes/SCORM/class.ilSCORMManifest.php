<?php

declare(strict_types=1);
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
    public ?string $version = null;
    public ?string $xml_base = null;

    /**
    * @param int $a_id Object ID
    */
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sma");
    }

    public function getImportId(): string
    {
        return $this->import_id;
    }

    public function setImportId(string $a_import_id): void
    {
        $this->import_id = $a_import_id;
        $this->setTitle($a_import_id);
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $a_version): void
    {
        $this->version = $a_version;
    }

    public function getXmlBase(): ?string
    {
        return $this->xml_base;
    }

    public function setXmlBase(?string $a_xml_base): void
    {
        $this->xml_base = $a_xml_base;
    }

    public function read(): void
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

    public function create(): void
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

    public function update(): void
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

    public function delete(): void
    {
        global $DIC;
        $ilDB = $DIC->database();

        parent::delete();

        $ilDB->manipulateF('DELETE FROM sc_manifest WHERE obj_id = %s', array('integer'), array($this->getId()));
    }
}
