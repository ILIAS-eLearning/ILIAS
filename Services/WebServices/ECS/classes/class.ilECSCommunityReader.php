<?php

declare(strict_types=1);

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
class ilECSCommunityReader
{
    private static ?array $instances = null;

    private int $position = 0;

    private ilLogger $logger;
    private ilECSSetting $settings;
    private ilECSConnector $connector;

    /**
     * @var ilECSCommunity[]
     */
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
     */
    public static function getInstanceByServerId(int $a_server_id): \ilECSCommunityReader
    {
        return self::$instances[$a_server_id] ?? (self::$instances[$a_server_id] = new ilECSCommunityReader(ilECSSetting::getInstanceByServerId($a_server_id)));
    }

    /**
     * Get server setting
     */
    public function getServer(): \ilECSSetting
    {
        return $this->settings;
    }

    /**
     * Get participants
     * @return ilECSParticipant[]
     */
    public function getParticipants(): array
    {
        return $this->participants;
    }


    /**
     * get publishable ids
     */
    public function getOwnMIDs(): array
    {
        return $this->own_ids ?: [];
    }

    /**
     * get communities
     *
     * @access public
     * @return \ilECSCommunity[]
     */
    public function getCommunities(): array
    {
        return $this->communities ?: [];
    }

    /**
     * get community by id
     *
     * @access public
     * @param int comm_id
     */
    public function getCommunityById($a_id): ?ilECSCommunity
    {
        foreach ($this->communities as $community) {
            if ($community->getId() === $a_id) {
                return $community;
            }
        }
        return null;
    }

    /**
     * @return \ilECSParticipant[]
     */
    public function getParticipantsByPid(int $a_pid): array
    {
        $participants = [];
        foreach ($this->getCommunities() as $community) {
            foreach ($community->getParticipants() as $participant) {
                if ($participant->getPid() === $a_pid) {
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
        return $this->participants[$a_mid] ?? false;
    }

    public function getParticipantNameByMid($a_mid): string
    {
        return isset($this->participants[$a_mid]) ?
            $this->participants[$a_mid]-> getParticipantName() :
            '';
    }

    /**
     * Get community by mid
     */
    public function getCommunityByMID(int $a_mid): ?\ilECSCommunity
    {
        foreach ($this->communities as $community) {
            foreach ($community->getParticipants() as $part) {
                if ($part->getMID() === $a_mid) {
                    return $community;
                }
            }
        }
        return null;
    }

    /**
     * get enabled participants
     */
    public function getEnabledParticipants(): array
    {
        $ps = ilECSParticipantSettings::getInstanceByServerId($this->getServer()->getServerId());
        $en = $ps->getEnabledParticipants();
        $e_part = [];
        foreach ($this->getCommunities() as $community) {
            foreach ($community->getParticipants() as $participant) {
                if (in_array($participant->getMid(), $en, true)) {
                    $e_part[] = $participant;
                }
            }
        }
        return $e_part;
    }

    /**
     * Read
     *
     * @throws ilECSConnectorException
     */
    private function read(): void
    {
        try {
            $res = $this->connector->getMemberships();

            if (!is_array($res->getResult())) {
                return;
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
