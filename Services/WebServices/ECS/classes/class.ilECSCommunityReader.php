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
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/
class ilECSCommunityReader
{
    private static ?array $instances = null;

    private int $position = 0;

    private ilLogger $logger;
    private ilECSSetting $settings;
    private ilECSConnector $connector;
    
    private array $communities = array();
    private array $participants = array();
    private array $own_ids = array();

    /**
     * Singleton constructor
     *
     * @access private
     * @throws ilECSConnectorException
     */
    private function __construct(ilECSSetting $setting)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->logger->debug(print_r($setting->getServerId(), true));
        $this->settings = $setting;

        $this->connector = new ilECSConnector($this->settings);
        
        $this->read();
        $this->logger->debug(__METHOD__ . ': Finished reading communities');
    }

    /**
     * Get instance by server id
     * @param int $a_server_id
     * @return ilECSCommunityReader
     */
    public static function getInstanceByServerId(int $a_server_id)
    {
        if (isset(self::$instances[$a_server_id])) {
            return self::$instances[$a_server_id];
        }
        return self::$instances[$a_server_id] = new ilECSCommunityReader(ilECSSetting::getInstanceByServerId($a_server_id));
    }

    /**
     * Get server setting
     * @return ilECSSetting
     */
    public function getServer()
    {
        return $this->settings;
    }
    
    /**
     * Get participants
     * @return ilECSParticipant[]
     */
    public function getParticipants()
    {
        return $this->participants;
    }


    /**
     * get publishable ids
     *
     * @access public
     *
     */
    public function getOwnMIDs()
    {
        return $this->own_ids ? $this->own_ids : array();
    }
    
    /**
     * get communities
     *
     * @access public
     * @return \ilECSCommunity[]
     */
    public function getCommunities()
    {
        return $this->communities ? $this->communities : array();
    }
    
    /**
     * get community by id
     *
     * @access public
     * @param int comm_id
     *
     */
    public function getCommunityById($a_id)
    {
        foreach ($this->communities as $community) {
            if ($community->getId() == $a_id) {
                return $community;
            }
        }
        return null;
    }

    /**
     * @param int $a_pid
     * @return \ilECSParticipant[]
     */
    public function getParticipantsByPid($a_pid)
    {
        $participants = [];
        foreach ($this->getCommunities() as $community) {
            foreach ($community->getParticipants() as $participant) {
                if ($participant->getPid() == $a_pid) {
                    $participants[] = $participant;
                }
            }
        }
        return $participants;
    }
    
    /**
     * get participant by id
     *
     * @access public
     * @param int mid
     */
    public function getParticipantByMID($a_mid)
    {
        return isset($this->participants[$a_mid]) ? $this->participants[$a_mid] : false;
    }

    public function getParticipantNameByMid($a_mid)
    {
        return isset($this->participants[$a_mid]) ?
            $this->participants[$a_mid]-> getParticipantName():
            '';
    }

    /**
     * Get community by mid
     * @param int $a_mid
     * @return ilECSCommunity
     */
    public function getCommunityByMID($a_mid)
    {
        foreach ($this->communities as $community) {
            foreach ($community->getParticipants() as $part) {
                if ($part->getMID() == $a_mid) {
                    return $community;
                }
            }
        }
        return null;
    }
    
    /**
     * get publishable communities
     *
     * @access public
     *
     */
    public function getPublishableParticipants()
    {
        foreach ($this->getCommunities() as $community) {
            foreach ($community->getParticipants() as $participant) {
                if ($participant->isPublishable()) {
                    $p_part[] = $participant;
                }
            }
        }
        return $p_part ? $p_part : array();
    }
    
    /**
     * get enabled participants
     *
     * @access public
     *
     */
    public function getEnabledParticipants()
    {
        $ps = ilECSParticipantSettings::getInstanceByServerId($this->getServer()->getServerId());
        $en = $ps->getEnabledParticipants();
        foreach ($this->getCommunities() as $community) {
            foreach ($community->getParticipants() as $participant) {
                if (in_array($participant->getMid(), $en)) {
                    $e_part[] = $participant;
                }
            }
        }
        return $e_part ? $e_part : array();
    }

    /**
     * Read
     * @access private
     * @throws ilECSConnectorException
     *
     */
    private function read()
    {
        try {
            $res = $this->connector->getMemberships();

            if (!is_array($res->getResult())) {
                return false;
            }
            foreach ($res->getResult() as $community) {
                $tmp_comm = new ilECSCommunity($community);
                foreach ($tmp_comm->getParticipants() as $participant) {
                    $this->participants[$participant->getMID()] = $participant;
                    if ($participant->isSelf()) {
                        $this->own_ids[] = $participant->getMID();
                    }
                }
                $this->communities[] = $tmp_comm;
            }
        } catch (ilECSConnectorException $e) {
            $this->logger->error(__METHOD__ . ': Error connecting to ECS server. ' . $e->getMessage());
            throw $e;
        }
    }
}
