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
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/
class ilECSParticipant
{
    protected $json_obj;
    protected $cid;
    protected $pid;
    protected $mid;
    protected $email;
    protected $certid;
    protected $dns;
    protected $description;
    protected $participantname;
    protected $is_self;

    /**
     * @var null | \ilLogger
     */
    private ilLogger $logger;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($json_obj, $a_cid)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();

        $this->json_obj = $json_obj;
        $this->cid = $a_cid;
        $this->read();
    }
    
    /**
     * get community id
     *
     * @access public
     *
     */
    public function getCommunityId()
    {
        return $this->cid;
    }
    
    /**
     * get mid
     *
     * @access public
     * @param
     *
     */
    public function getMID()
    {
        return $this->mid;
    }
    
    /**
     * get email
     *
     * @access public
     *
     */
    public function getEmail()
    {
        return $this->email;
    }

    
    /**
     * get dns
     *
     * @access public
     * @param
     *
     */
    public function getDNS()
    {
        return $this->dns;
    }
    
    /**
     * get description
     *
     * @access public
     *
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * get participant name
     *
     * @access public
     *
     */
    public function getParticipantName()
    {
        return $this->participantname;
    }
    
    /**
     * get abbreviation of participant
     *
     * @access public
     *
     */
    public function getAbbreviation()
    {
        return $this->abr;
    }

    /**
     * Get pid
     * @return int
     */
    public function getPid()
    {
        return $this->pid;
    }
    
    /**
     * is publishable (enabled and mid with own cert id)
     *
     * @access public
     * @param
     *
     */
    public function isPublishable()
    {
        return $this->isSelf();
    }
    
    /**
     * is self
     *
     * @access public
     * @param
     *
     */
    public function isSelf()
    {
        return (bool) $this->is_self;
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
    public function getOrganisation()
    {
        return $this->org;
    }

    /**
     * Read
     *
     * @access private
     *
     */
    private function read()
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
