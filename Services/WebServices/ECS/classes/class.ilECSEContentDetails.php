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
 * Presentation of ecs content details (http://...campusconnect/courselinks/id/details)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSEContentDetails
{
    private array $senders = [];
    private ?int $sender_index = null;
    private array $receivers = [];
    private string $url = "";
    private int $owner = 0;

    private array $receiver_info = [];
    private ilLogger $logger;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
    }
    
    /**
     * Get data from server
     */
    public static function getInstanceFromServer(int $a_server_id, int $a_econtent_id, string $a_resource_type) : ilECSEContentDetails
    {
        $instance = new self();
        $instance->loadFromJson($instance->loadFromServer($a_server_id, $a_econtent_id, $a_resource_type));
        return $instance;
    }


    private function loadFromServer(int $a_server_id, int $a_econtent_id, string $a_resource_type) : ?object
    {
        try {
            $connector = new ilECSConnector(ilECSSetting::getInstanceByServerId($a_server_id));
            $res = $connector->getResource($a_resource_type, $a_econtent_id, true);
            if ($res->getHTTPCode() === ilECSConnector::HTTP_CODE_NOT_FOUND) {
                return null;
            }
            if (!is_object($res->getResult())) {
                $this->logger->error(__METHOD__ . ': Error parsing result. Expected result of type array.');
                $this->logger->logStack();
                throw new ilECSConnectorException('error parsing json');
            }
        } catch (ilECSConnectorException $exc) {
            return null;
        }
        return $res->getResult();
    }
    /**
     * Get senders
     */
    public function getSenders() : array
    {
        return $this->senders;
    }

    /**
     * get first sender
     */
    public function getFirstSender() : int
    {
        return $this->senders[0] ?? 0;
    }
    
    /**
     * Get sender from whom we received the ressource
     * According to the documentation the sender and receiver arrays have corresponding indexes.
     */
    public function getMySender() : int
    {
        return $this->senders[$this->sender_index];
    }

    /**
     * Get recievers
     */
    public function getReceivers() : array
    {
        return $this->receivers;
    }
    
    /**
     * Get first receiver
     */
    public function getFirstReceiver() : int
    {
        return count($this->receivers) ? $this->receivers[0] : 0;
    }

    /**
     * Get receiver info
     */
    public function getReceiverInfo() : array
    {
        return $this->receiver_info;
    }

    /**
     * Get url
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    public function getOwner() : int
    {
        return $this->owner;
    }

    /**
     * Load from JSON object
     *
     * @param object JSON object
     * @throws ilException
     */
    public function loadFromJson(object $json) : bool
    {
        $this->logger->info(print_r($json, true));
        foreach ((array) $json->senders as $sender) {
            $this->senders[] = $sender->mid;
        }

        $index = 0;
        foreach ((array) $json->receivers as $receiver) {
            $this->receivers[] = $receiver->mid;
            if ($receiver->itsyou && $this->sender_index === null) {
                $this->sender_index = $index;
            }
            ++$index;
        }

        // Collect in one array
        for ($i = 0, $iMax = count($this->getReceivers()); $i < $iMax; ++$i) {
            $this->receiver_info[$this->senders[$i]] = $this->receivers[$i];
        }

        if (is_object($json->owner)) {
            $this->owner = (int) $json->owner->pid;
        }

        $this->url = $json->url;
        return true;
    }
}
