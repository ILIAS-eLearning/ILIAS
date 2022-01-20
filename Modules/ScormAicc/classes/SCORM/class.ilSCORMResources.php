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
* SCORM Resources Element
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilSCORMResources extends ilSCORMObject
{
    public $xml_base;


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
        
        parent::__construct($a_id);
        $this->setType('srs');

        $this->setTitle($lng->txt('cont_resources'));
    }

    public function getXmlBase()
    {
        return $this->xml_base;
    }

    public function setXmlBase($a_xml_base) : void
    {
        $this->xml_base = $a_xml_base;
    }

    public function read() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::read();

        $obj_set = $ilDB->queryF(
            'SELECT xml_base FROM sc_resources WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
        $obj_rec = $ilDB->fetchAssoc($obj_set);
        $this->setXmlBase($obj_rec['xml_base']);
    }

    public function create() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::create();
        
        $ilDB->manipulateF(
            'INSERT INTO sc_resources (obj_id, xml_base) VALUES (%s, %s)',
            array('integer', 'text'),
            array($this->getId(), $this->getXmlBase())
        );
    }

    public function update() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        
        parent::update();

        $ilDB->manipulateF(
            '
			UPDATE sc_resources SET xml_base = %s WHERE obj_id = %s',
            array('text', 'integer'),
            array($this->getXmlBase() ,$this->getId())
        );
    }

    public function delete() : void
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        parent::delete();

        $ilDB->manipulateF(
            'DELETE FROM sc_resources WHERE obj_id = %s',
            array('integer'),
            array($this->getId())
        );
    }
}
