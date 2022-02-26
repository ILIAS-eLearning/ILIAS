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
    private array $senders = array();
    private ?int $sender_index = null;
    private array $receivers = array();
    private $url = array();
    private string $content_type = "";
    private int $owner = 0;

    private array $receiver_info = array();
    private ilLogger $logger;

    public function __construct($a_server_id, $a_econtent_id, $a_resource_type)
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();

        $resource = $this->loadFromServer($a_server_id, $a_econtent_id, $a_resource_type);
        $this->loadFromJson($resource);
    }
    
    /**
     * Get data from server
     *
     * @param int $a_server_id
     * @param int $a_econtent_id
     * @param string $a_resource_type
     * @return ilECSEContentDetails
     */
    public static function getInstance($a_server_id, $a_econtent_id, $a_resource_type) : ilECSEContentDetails
    {
        return new self($a_server_id, $a_econtent_id, $a_resource_type);
    }


    private function loadFromServer($a_server_id, $a_econtent_id, $a_resource_type) : object
    {
        try {
            $connector = new ilECSConnector(ilECSSetting::getInstanceByServerId($a_server_id));
            $res = $connector->getResource($a_resource_type, $a_econtent_id, true);
            if ($res->getHTTPCode() == ilECSConnector::HTTP_CODE_NOT_FOUND) {
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
     * @return array
     */
    public function getSenders()
    {
        return (array) $this->senders;
    }

    /**
     * get first sender
     */
    public function getFirstSender()
    {
        return isset($this->senders[0]) ? $this->senders[0] : 0;
    }
    
    /**
     * Get sender from whom we received the ressource
     * According to the documentation the sender and receiver arrays have corresponding indexes.
     */
    public function getMySender()
    {
        return $this->senders[$this->sender_index];
    }

    /**
     * Get recievers
     * @return <type>
     */
    public function getReceivers()
    {
        return (array) $this->receivers;
    }
    
    /**
     * Get first receiver
     * @return int
     */
    public function getFirstReceiver()
    {
        foreach ($this->getReceivers() as $mid) {
            return $mid;
        }
        return 0;
    }

    /**
     * Get receiver info
     * @return array
     */
    public function getReceiverInfo()
    {
        return (array) $this->receiver_info;
    }

    /**
     * Get url
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function getOwner()
    {
        return (int) $this->owner;
    }

    /**
     * Load from JSON object
     *
     * @access public
     * @param object JSON object
     * @throws ilException
     */
    public function loadFromJson($json)
    {
        if (!is_object($json)) {
            $this->logger->info(__METHOD__ . ': Cannot load from JSON. No object given.');
            throw new ilException('Cannot parse ECS content details.');
        }
        $this->logger->info(print_r($json, true));
        foreach ((array) $json->senders as $sender) {
            $this->senders[] = $sender->mid;
        }

        $index = 0;
        foreach ((array) $json->receivers as $receiver) {
            $this->receivers[] = $receiver->mid;
            if ($receiver->itsyou and $this->sender_index === null) {
                $this->sender_index = $index;
            }
            ++$index;
        }

        // Collect in one array
        for ($i = 0; $i < count($this->getReceivers()); ++$i) {
            $this->receiver_info[$this->senders[$i]] = $this->receivers[$i];
        }

        if (is_object($json->owner)) {
            $this->owner = (int) $json->owner->pid;
        }

        $this->url = $json->url;
        $this->content_type = $json->content_type;
        return true;
    }
}
