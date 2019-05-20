<?php



/**
 * OscConversation
 */
class OscConversation
{
    /**
     * @var string
     */
    private $id = '';

    /**
     * @var bool
     */
    private $isGroup = '0';

    /**
     * @var string|null
     */
    private $participants;


    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isGroup.
     *
     * @param bool $isGroup
     *
     * @return OscConversation
     */
    public function setIsGroup($isGroup)
    {
        $this->isGroup = $isGroup;

        return $this;
    }

    /**
     * Get isGroup.
     *
     * @return bool
     */
    public function getIsGroup()
    {
        return $this->isGroup;
    }

    /**
     * Set participants.
     *
     * @param string|null $participants
     *
     * @return OscConversation
     */
    public function setParticipants($participants = null)
    {
        $this->participants = $participants;

        return $this;
    }

    /**
     * Get participants.
     *
     * @return string|null
     */
    public function getParticipants()
    {
        return $this->participants;
    }
}
