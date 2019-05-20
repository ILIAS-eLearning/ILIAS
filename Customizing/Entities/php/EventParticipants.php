<?php



/**
 * EventParticipants
 */
class EventParticipants
{
    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var bool
     */
    private $registered = '0';

    /**
     * @var bool
     */
    private $participated = '0';

    /**
     * @var string|null
     */
    private $mark;

    /**
     * @var string|null
     */
    private $eComment;

    /**
     * @var bool
     */
    private $contact = '0';


    /**
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return EventParticipants
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return EventParticipants
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set registered.
     *
     * @param bool $registered
     *
     * @return EventParticipants
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;

        return $this;
    }

    /**
     * Get registered.
     *
     * @return bool
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Set participated.
     *
     * @param bool $participated
     *
     * @return EventParticipants
     */
    public function setParticipated($participated)
    {
        $this->participated = $participated;

        return $this;
    }

    /**
     * Get participated.
     *
     * @return bool
     */
    public function getParticipated()
    {
        return $this->participated;
    }

    /**
     * Set mark.
     *
     * @param string|null $mark
     *
     * @return EventParticipants
     */
    public function setMark($mark = null)
    {
        $this->mark = $mark;

        return $this;
    }

    /**
     * Get mark.
     *
     * @return string|null
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * Set eComment.
     *
     * @param string|null $eComment
     *
     * @return EventParticipants
     */
    public function setEComment($eComment = null)
    {
        $this->eComment = $eComment;

        return $this;
    }

    /**
     * Get eComment.
     *
     * @return string|null
     */
    public function getEComment()
    {
        return $this->eComment;
    }

    /**
     * Set contact.
     *
     * @param bool $contact
     *
     * @return EventParticipants
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact.
     *
     * @return bool
     */
    public function getContact()
    {
        return $this->contact;
    }
}
