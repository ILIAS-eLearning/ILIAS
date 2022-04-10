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
class ilECSEnrolmentStatusCommandQueueHandler implements ilECSCommandQueueHandler
{
    private ilECSSetting $server;
    private int $mid = 0;

    protected \ilRecommendedContentManager $recommended_content_manager;
    
    private ilLogger $logger;

    /**
     * Constructor
     */
    public function __construct(ilECSSetting $server)
    {
        global $DIC;
        
        $this->logger = $DIC->logger()->wsrv();
        $this->server = $server;
        $this->recommended_content_manager = new ilRecommendedContentManager();
    }
    
    /**
     * Get server
     */
    public function getServer() : \ilECSSetting
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
     * Handle create
     */
    public function handleCreate(ilECSSetting $server, $a_content_id) : bool
    {
        try {
            $enrolment_con = new ilECSEnrolmentStatusConnector($server);
            $status = $enrolment_con->getEnrolmentStatus($a_content_id);
            $this->logger->debug(print_r($status, true));
            $this->logger->debug($status->getPersonIdType());
            $this->logger->debug($status->getPersonId());
            $i = $status->getPersonIdType();
            if ($i === ilECSEnrolmentStatus::ID_UID) {
                $id_arr = ilUtil::parseImportId($status->getPersonId());
                $this->logger->debug('Handling status change to ' . $status->getStatus() . ' for user ' . $id_arr['id']);
                $this->doUpdate($id_arr['id'], $status);
            } else {
                $this->logger->debug('Not implemented yes: person id type: ' . $status->getPersonIdType());
            }
        } catch (ilECSConnectorException $e) {
            $this->logger->error('Enrollment status change failed with message: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * Handle delete
     */
    public function handleDelete(ilECSSetting $server, $a_content_id) : bool
    {
        // nothing todo
        return true;
    }

    /**
     * Handle update

     */
    public function handleUpdate(ilECSSetting $server, $a_content_id) : bool
    {
        // Shouldn't happen
        return true;
    }
    
    
    /**
     * Perform update
     */
    protected function doUpdate($a_usr_id, ilECSEnrolmentStatus $status) : bool
    {
        $obj_ids = ilECSImportManager::getInstance()->lookupObjIdsByContentId($status->getId());
        $obj_id = end($obj_ids);
        $ref_ids = ilObject::_getAllReferences($obj_id);
        $ref_id = end($ref_ids);
        
        
        if (!$ref_id) {
            // Remote object not found
            return true;
        }
        
        switch ($status->getStatus()) {
            case ilECSEnrolmentStatus::STATUS_PENDING:
                // nothing todo in the moment: maybe send mail
                break;
                
            case ilECSEnrolmentStatus::STATUS_ACTIVE:
                $this->logger->info(': Add recommended content: ' . $a_usr_id . ' ' . $ref_id . ' ' . $obj_id);
                // deactivated for now, see discussion at
                // https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
                //$this->recommended_content_manager->addObjectRecommendation($a_usr_id, $ref_id);
                break;
            
            case ilECSEnrolmentStatus::STATUS_ACCOUNT_DEACTIVATED:
            case ilECSEnrolmentStatus::STATUS_DENIED:
            case ilECSEnrolmentStatus::STATUS_REJECTED:
            case ilECSEnrolmentStatus::STATUS_UNSUBSCRIBED:
                $this->logger->info(': Remove recommended content: ' . $a_usr_id . ' ' . $ref_id . ' ' . $obj_id);
                $this->recommended_content_manager->removeObjectRecommendation($a_usr_id, $ref_id);
                break;
        }
        return true;
    }
}
