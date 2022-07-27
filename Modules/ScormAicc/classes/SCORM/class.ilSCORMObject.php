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
* Parent object for all SCORM objects, that are stored in table scorm_object
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMObject
{
    public int $id;
    public string $title = "";
    public ?string $type;
    public int $slm_id;

    /**
    * Constructor
    * @param int $a_id Object ID
    */
    public function __construct(int $a_id = 0)
    {
        $this->id = $a_id;
        if ($a_id > 0) {
            $this->read();
        }
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $a_id) : void
    {
        $this->id = $a_id;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setType(?string $a_type) : void
    {
        $this->type = $a_type;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getSLMId() : int
    {
        return $this->slm_id;
    }

    public function setSLMId(int $a_slm_id) : void
    {
        $this->slm_id = $a_slm_id;
    }

    /**
     * @return void
     */
    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $obj_set = $ilDB->queryF(
            'SELECT * FROM scorm_object WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->setTitle($obj_rec["title"]);
        $this->setType($obj_rec["c_type"]);
        $this->setSLMId((int) $obj_rec["slm_id"]);
    }

    /**
     * Count number of presentable SCOs/Assets of SCORM learning module.
     */
    public static function _lookupPresentableItems(int $a_slm_id) : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $set = $ilDB->queryF(
            "
			SELECT sit.obj_id id 
			FROM scorm_object sob, sc_item sit
			WHERE sob.slm_id = %s
			AND sob.obj_id = sit.obj_id
			AND sit.identifierref IS NOT NULL",
            array('integer'),
            array($a_slm_id)
        );
        $items = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $items[] = $rec["id"];
        }
        
        return $items;
    }

    /**
     * Create database record for SCORM object.
     */
    public function create() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
      
        $nextId = $ilDB->nextId('scorm_object');
        $this->setId($nextId);
         
        $ilDB->manipulateF(
            '
        INSERT INTO scorm_object (obj_id,title, c_type, slm_id) 
        VALUES (%s,%s,%s,%s) ',
            array('integer','text','text','integer'),
            array($nextId, $this->getTitle(),$this->getType(), $this->getSLMId())
        );
    }

    /**
     * Updates database record for SCORM object.
     */
    public function update() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $ilDB->manipulateF(
            '
        UPDATE scorm_object 
        SET title = %s,
        	c_type = %s,
        	slm_id = %s
        WHERE obj_id = %s',
            array('text','text','integer','integer'),
            array($this->getTitle(),$this->getType(), $this->getSLMId(),$this->getId())
        );
    }

    public function delete() : void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilDB->manipulateF(
            'DELETE FROM scorm_object WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
    }

    /**
     * @return ilSCORMItem|ilSCORMManifest|ilSCORMOrganization|ilSCORMOrganizations|ilSCORMResource|ilSCORMResources
     */
    public static function &_getInstance(int $a_id, int $a_slm_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $sc_set = $ilDB->queryF(
            '
			SELECT c_type FROM scorm_object 
			WHERE obj_id = %s
			AND slm_id = %s',
            array('integer','integer'),
            array($a_id, $a_slm_id)
        );
        $sc_rec = $ilDB->fetchAssoc($sc_set);
            
        switch ($sc_rec["c_type"]) {
            case "sit":					$item = new ilSCORMItem($a_id);
                return $item;

            case "sos":					$sos = new ilSCORMOrganizations($a_id);
                return $sos;

            case "sor":					$sor = new ilSCORMOrganization($a_id);
                return $sor;

            case "sma":					$sma = new ilSCORMManifest($a_id);
                return $sma;

            case "srs":					$srs = new ilSCORMResources($a_id);
                return $srs;

            default:
            case "sre":					$sre = new ilSCORMResource($a_id);
                return $sre;
        }
    }
}
