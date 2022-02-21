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
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSCmsCourseCommandQueueHandler implements ilECSCommandQueueHandler
{
    private ilLogger $logger;
    
    private ilECSSetting $server;
    private int $mid = 0;
    
    
    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        
        $this->server = $server;
    }
    
    /**
     * Get server
     */
    public function getServer() : ilECSSetting
    {
        return $this->server;
    }
    
    /**
     * Get mid
     */
    public function getMid() : int
    {
        return $this->mid;
    }
    
    /**
     * Check if course allocation is activated for one recipient of the
     * @param ilECSSetting $server
     * @param $a_content_id
     */
    public function checkAllocationActivation(ilECSSetting $server, $a_content_id)
    {
        try {
            $crs_reader = new ilECSCourseConnector($server);
            $details = $crs_reader->getCourse($a_content_id, true);
            $this->mid = $details->getMySender();
            
            // Check if import is enabled
            $part = ilECSParticipantSetting::getInstance($this->getServer()->getServerId(), $this->getMid());
            if (!$part->isImportEnabled()) {
                $this->logger->info(__METHOD__ . ': Import disabled for mid ' . $this->getMid());
                return false;
            }
            // Check course allocation setting
            $gl_settings = ilECSNodeMappingSettings::getInstanceByServerMid(
                $this->getServer()->getServerId(),
                $this->getMid()
            );
            $enabled = $gl_settings->isCourseAllocationEnabled();
            if (!$enabled) {
                $this->logger->info(__METHOD__ . ': Course allocation disabled for ' . $this->getMid());
            }
            return $enabled;
        } catch (ilECSConnectorException $e) {
            $this->logger->error(__METHOD__ . ': Reading course details failed with message ' . $e->getMessage());
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
        if (!$this->checkAllocationActivation($server, $a_content_id)) {
            return true;
        }
        try {
            $course = $this->readCourse($server, $a_content_id);
            $this->logger->info(__METHOD__ . ': ' . print_r($course, true));
            $this->doUpdate($a_content_id, $course);
            return true;
        } catch (ilECSConnectorException $e) {
            $this->logger->error(__METHOD__ . ': Course creation failed  with mesage ' . $e->getMessage());
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
            $this->logger->error(__METHOD__ . ': Course creation failed  with mesage ' . $e->getMessage());
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
        $this->logger->info(__METHOD__ . ': Starting course creation/update');
        
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
            $crs_reader = new ilECSCourseConnector($server);
            return $crs_reader->getCourse($a_content_id, $a_details);
        } catch (ilECSConnectorException $e) {
            throw $e;
        }
    }
}
