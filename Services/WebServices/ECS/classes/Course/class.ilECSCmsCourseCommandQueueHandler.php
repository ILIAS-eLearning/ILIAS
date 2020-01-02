<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/WebServices/ECS/interfaces/interface.ilECSCommandQueueHandler.php';
include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';
include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';


/**
 * Description of class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseCommandQueueHandler implements ilECSCommandQueueHandler
{
    private $server = null;
    private $mid = 0;
    
    
    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server)
    {
        $this->server = $server;
    }
    
    /**
     * Get server
     * @return ilECSServerSetting
     */
    public function getServer()
    {
        return $this->server;
    }
    
    /**
     * Get mid
     * @return type
     */
    public function getMid()
    {
        return $this->mid;
    }
    
    /**
     * Check if course allocation is activated for one recipient of the
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function checkAllocationActivation(ilECSSetting $server, $a_content_id)
    {
        try {
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';
            $crs_reader = new ilECSCourseConnector($server);
            $details = $crs_reader->getCourse($a_content_id, true);
            $this->mid = $details->getMySender();
            
            // Check if import is enabled
            include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
            $part = ilECSParticipantSetting::getInstance($this->getServer()->getServerId(), $this->getMid());
            if (!$part->isImportEnabled()) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Import disabled for mid ' . $this->getMid());
                return false;
            }
            // Check course allocation setting
            include_once './Services/WebServices/ECS/classes/Mapping/class.ilECSNodeMappingSettings.php';
            $gl_settings = ilECSNodeMappingSettings::getInstanceByServerMid(
                $this->getServer()->getServerId(),
                $this->getMid()
            );
            $enabled = $gl_settings->isCourseAllocationEnabled();
            if (!$enabled) {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Course allocation disabled for ' . $this->getMid());
            }
            return $enabled;
        } catch (ilECSConnectorException $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Reading course details failed with message ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Handle create
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleCreate(ilECSSetting $server, $a_content_id)
    {
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsData.php';
        include_once './Services/WebServices/ECS/classes/Tree/class.ilECSCmsTree.php';
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';

        if (!$this->checkAllocationActivation($server, $a_content_id)) {
            return true;
        }
        try {
            $course = $this->readCourse($server, $a_content_id);
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ' . print_r($course, true));
            $this->doUpdate($a_content_id, $course);
            return true;
        } catch (ilECSConnectorException $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Course creation failed  with mesage ' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Handle delete
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleDelete(ilECSSetting $server, $a_content_id)
    {
        // nothing todo
        return true;
    }

    /**
     * Handle update
     * @param ilECSSetting $server
     * @param type $a_content_id
     */
    public function handleUpdate(ilECSSetting $server, $a_content_id)
    {
        if (!$this->checkAllocationActivation($server, $a_content_id)) {
            return true;
        }
        
        try {
            $course = $this->readCourse($server, $a_content_id);
            $this->doUpdate($a_content_id, $course);
            return true;
        } catch (ilECSConnectorException $e) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Course creation failed  with mesage ' . $e->getMessage());
            return false;
        }
        return true;
    }
    
    
    /**
     * Perform update
     * @param type $a_content_id
     * @param type $course
     */
    protected function doUpdate($a_content_id, $course)
    {
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Starting course creation/update');
        
        include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseCreationHandler.php';
        $creation_handler = new ilECSCourseCreationHandler($this->getServer(), $this->mid);
        $creation_handler->handle($a_content_id, $course);
    }
    

    /**
     * Read course from ecs
     * @return boolean
     */
    private function readCourse(ilECSSetting $server, $a_content_id, $a_details = false)
    {
        try {
            include_once './Services/WebServices/ECS/classes/Course/class.ilECSCourseConnector.php';
            $crs_reader = new ilECSCourseConnector($server);
            return $crs_reader->getCourse($a_content_id, $a_details);
        } catch (ilECSConnectorException $e) {
            throw $e;
        }
    }
}
