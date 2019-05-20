<?php



/**
 * ChatroomSettings
 */
class ChatroomSettings
{
    /**
     * @var int
     */
    private $roomId = '0';

    /**
     * @var int|null
     */
    private $objectId = '0';

    /**
     * @var string
     */
    private $roomType = '';

    /**
     * @var bool|null
     */
    private $allowAnonymous = '0';

    /**
     * @var bool|null
     */
    private $allowCustomUsernames = '0';

    /**
     * @var bool|null
     */
    private $enableHistory = '0';

    /**
     * @var bool|null
     */
    private $restrictHistory = '0';

    /**
     * @var string|null
     */
    private $autogenUsernames = 'Anonymous #';

    /**
     * @var bool|null
     */
    private $allowPrivateRooms = '0';

    /**
     * @var int
     */
    private $displayPastMsgs = '0';

    /**
     * @var int
     */
    private $privateRoomsEnabled = '0';

    /**
     * @var bool
     */
    private $onlineStatus = '0';


    /**
     * Get roomId.
     *
     * @return int
     */
    public function getRoomId()
    {
        return $this->roomId;
    }

    /**
     * Set objectId.
     *
     * @param int|null $objectId
     *
     * @return ChatroomSettings
     */
    public function setObjectId($objectId = null)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set roomType.
     *
     * @param string $roomType
     *
     * @return ChatroomSettings
     */
    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;

        return $this;
    }

    /**
     * Get roomType.
     *
     * @return string
     */
    public function getRoomType()
    {
        return $this->roomType;
    }

    /**
     * Set allowAnonymous.
     *
     * @param bool|null $allowAnonymous
     *
     * @return ChatroomSettings
     */
    public function setAllowAnonymous($allowAnonymous = null)
    {
        $this->allowAnonymous = $allowAnonymous;

        return $this;
    }

    /**
     * Get allowAnonymous.
     *
     * @return bool|null
     */
    public function getAllowAnonymous()
    {
        return $this->allowAnonymous;
    }

    /**
     * Set allowCustomUsernames.
     *
     * @param bool|null $allowCustomUsernames
     *
     * @return ChatroomSettings
     */
    public function setAllowCustomUsernames($allowCustomUsernames = null)
    {
        $this->allowCustomUsernames = $allowCustomUsernames;

        return $this;
    }

    /**
     * Get allowCustomUsernames.
     *
     * @return bool|null
     */
    public function getAllowCustomUsernames()
    {
        return $this->allowCustomUsernames;
    }

    /**
     * Set enableHistory.
     *
     * @param bool|null $enableHistory
     *
     * @return ChatroomSettings
     */
    public function setEnableHistory($enableHistory = null)
    {
        $this->enableHistory = $enableHistory;

        return $this;
    }

    /**
     * Get enableHistory.
     *
     * @return bool|null
     */
    public function getEnableHistory()
    {
        return $this->enableHistory;
    }

    /**
     * Set restrictHistory.
     *
     * @param bool|null $restrictHistory
     *
     * @return ChatroomSettings
     */
    public function setRestrictHistory($restrictHistory = null)
    {
        $this->restrictHistory = $restrictHistory;

        return $this;
    }

    /**
     * Get restrictHistory.
     *
     * @return bool|null
     */
    public function getRestrictHistory()
    {
        return $this->restrictHistory;
    }

    /**
     * Set autogenUsernames.
     *
     * @param string|null $autogenUsernames
     *
     * @return ChatroomSettings
     */
    public function setAutogenUsernames($autogenUsernames = null)
    {
        $this->autogenUsernames = $autogenUsernames;

        return $this;
    }

    /**
     * Get autogenUsernames.
     *
     * @return string|null
     */
    public function getAutogenUsernames()
    {
        return $this->autogenUsernames;
    }

    /**
     * Set allowPrivateRooms.
     *
     * @param bool|null $allowPrivateRooms
     *
     * @return ChatroomSettings
     */
    public function setAllowPrivateRooms($allowPrivateRooms = null)
    {
        $this->allowPrivateRooms = $allowPrivateRooms;

        return $this;
    }

    /**
     * Get allowPrivateRooms.
     *
     * @return bool|null
     */
    public function getAllowPrivateRooms()
    {
        return $this->allowPrivateRooms;
    }

    /**
     * Set displayPastMsgs.
     *
     * @param int $displayPastMsgs
     *
     * @return ChatroomSettings
     */
    public function setDisplayPastMsgs($displayPastMsgs)
    {
        $this->displayPastMsgs = $displayPastMsgs;

        return $this;
    }

    /**
     * Get displayPastMsgs.
     *
     * @return int
     */
    public function getDisplayPastMsgs()
    {
        return $this->displayPastMsgs;
    }

    /**
     * Set privateRoomsEnabled.
     *
     * @param int $privateRoomsEnabled
     *
     * @return ChatroomSettings
     */
    public function setPrivateRoomsEnabled($privateRoomsEnabled)
    {
        $this->privateRoomsEnabled = $privateRoomsEnabled;

        return $this;
    }

    /**
     * Get privateRoomsEnabled.
     *
     * @return int
     */
    public function getPrivateRoomsEnabled()
    {
        return $this->privateRoomsEnabled;
    }

    /**
     * Set onlineStatus.
     *
     * @param bool $onlineStatus
     *
     * @return ChatroomSettings
     */
    public function setOnlineStatus($onlineStatus)
    {
        $this->onlineStatus = $onlineStatus;

        return $this;
    }

    /**
     * Get onlineStatus.
     *
     * @return bool
     */
    public function getOnlineStatus()
    {
        return $this->onlineStatus;
    }
}
