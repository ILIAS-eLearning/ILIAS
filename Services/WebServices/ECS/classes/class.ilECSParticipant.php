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
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSParticipant
{
    protected object $json_obj;
    protected int $cid;
    protected int $pid;
    protected $mid;
    protected $email;
    protected $certid;
    protected $dns;
    protected $description;
    protected $participantname;
    protected bool $is_self;

    private ilECSOrganisation $org;

    private ilLogger $logger;
    
    public function __construct(object $json_obj, int $a_cid)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();

        $this->json_obj = $json_obj;
        $this->cid = $a_cid;
        $this->read();
    }
    
    /**
     * get community id
     */
    public function getCommunityId() : int
    {
        return $this->cid;
    }
    
    /**
     * get mid
     */
    public function getMID()
    {
        return $this->mid;
    }
    
    /**
     * get email
     */
    public function getEmail()
    {
        return $this->email;
    }

    
    /**
     * get dns
     */
    public function getDNS()
    {
        return $this->dns;
    }
    
    /**
     * get description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * get participant name
     */
    public function getParticipantName()
    {
        return $this->participantname;
    }
    
    /**
     * get abbreviation of participant
     */
    public function getAbbreviation()
    {
        return $this->abr;
    }

    /**
     * Get pid
     */
    public function getPid() : int
    {
        return $this->pid;
    }
    
    /**
     * is publishable (enabled and mid with own cert id)
     */
    public function isPublishable() : bool
    {
        return $this->isSelf();
    }
    
    /**
     * is self
     */
    public function isSelf() : bool
    {
        return $this->is_self;
    }
    
    
    /**
     * is Enabled
     *
     * @access public
     *
     */
    public function isEnabled()
    {
        $this->logger->err(__METHOD__ . ': Using deprecated call');
        $this->logger->logStack();
        return false;
    }

    /**
     * Get organisation
     * @return ilECSOrganisation $org
     */
    public function getOrganisation() : ilECSOrganisation
    {
        return $this->org;
    }

    /**
     * Read
     */
    private function read() : bool
    {
        $this->pid = $this->json_obj->pid;
        $this->mid = $this->json_obj->mid;
        $this->email = $this->json_obj->email;
        $this->dns = $this->json_obj->dns;
        $this->description = $this->json_obj->description;

        $this->participantname = $this->json_obj->name;
        $this->is_self = $this->json_obj->itsyou;

        $this->org = new ilECSOrganisation();
        if (is_object($this->json_obj->org)) {
            $this->org->loadFromJson($this->json_obj->org);
        }
        return true;
    }
}
