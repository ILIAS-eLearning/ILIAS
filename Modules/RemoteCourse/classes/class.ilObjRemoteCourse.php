<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php');

/**
* Remote course app class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ModulesRemoteCourse
*/

class ilObjRemoteCourse extends ilRemoteObjectBase
{
    const DB_TABLE_NAME = "remote_course_settings";
    
    const ACTIVATION_OFFLINE = 0;
    const ACTIVATION_UNLIMITED = 1;
    const ACTIVATION_LIMITED = 2;
    
    protected $availability_type;
    protected $end;
    protected $start;
    
    public function initType()
    {
        $this->type = "rcrs";
    }
    
    protected function getTableName()
    {
        return self::DB_TABLE_NAME;
    }
    
    protected function getECSObjectType()
    {
        return "/campusconnect/courselinks";
    }
    
    /**
     * Set Availability type
     *
     * @param int $a_type availability type
     */
    public function setAvailabilityType($a_type)
    {
        $this->availability_type = $a_type;
    }
    
    /**
     * get availability type
     *
     * @return int
     */
    public function getAvailabilityType()
    {
        return $this->availability_type;
    }
    
    /**
     * set starting time
     *
     * @param timestamp $a_time starting time
     */
    public function setStartingTime($a_time)
    {
        $this->start = $a_time;
    }
    
    /**
     * get starting time
     *
     * @return timestamp
     */
    public function getStartingTime()
    {
        return $this->start;
    }

    /**
     * set ending time
     *
     * @param timestamp $a_time ending time
     */
    public function setEndingTime($a_time)
    {
        $this->end = $a_time;
    }
    
    /**
     * get ending time
     *
     * @return timestamp
     */
    public function getEndingTime()
    {
        return $this->end;
    }
        
    /**
     * Lookup online
     *
     * @param int $a_obj_id obj_id
     * @return bool
     */
    public static function _lookupOnline($a_obj_id)
    {
        global $ilDB;
        
        $query = "SELECT * FROM " . self::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        switch ($row->availability_type) {
            case self::ACTIVATION_UNLIMITED:
                return true;
                
            case self::ACTIVATION_OFFLINE:
                return false;
                
            case self::ACTIVATION_LIMITED:
                return time() > $row->r_start && time < $row->r_end;
                
            default:
                return false;
        }
        
        return false;
    }
    
    protected function doCreateCustomFields(array &$a_fields)
    {
        $a_fields["availability_type"] = array("integer", 0);
        $a_fields["r_start"] = array("integer", 0);
        $a_fields["r_end"] = array("integer", 0);
    }

    protected function doUpdateCustomFields(array &$a_fields)
    {
        $a_fields["availability_type"] = array("integer", $this->getAvailabilityType());
        $a_fields["r_start"] = array("integer",(int) $this->getStartingTime());
        $a_fields["r_end"] = array("integer", (int) $this->getEndingTime());
    }

    protected function doReadCustomFields($a_row)
    {
        $this->setAvailabilityType($a_row->availability_type);
        $this->setStartingTime($a_row->r_start);
        $this->setEndingTime($a_row->r_end);
    }
    
    protected function updateCustomFromECSContent(ilECSSetting $a_server, $a_ecs_content)
    {
        // add custom values
        $this->setAvailabilityType($a_ecs_content->status == 'online' ? self::ACTIVATION_UNLIMITED : self::ACTIVATION_OFFLINE);
        
        // :TODO: ACTIVATION_LIMITED is currently not supported in ECS yet
        
        // adv. metadata
        include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
        include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSetting.php');
        $definition = ilECSUtils::getEContentDefinition($this->getECSObjectType());
        $this->importMetadataFromJson(
            $a_ecs_content,
            $a_server,
            $definition,
            ilECSDataMappingSetting::MAPPING_IMPORT_RCRS
        );
        
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $import = new ilECSImport($a_server->getServerId(), $this->getId());
        $import->setContentId($a_ecs_content->courseID);
        $import->save();
    }
}
