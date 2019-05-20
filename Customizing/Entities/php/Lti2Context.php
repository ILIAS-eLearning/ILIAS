<?php



/**
 * Lti2Context
 */
class Lti2Context
{
    /**
     * @var int
     */
    private $contextPk = '0';

    /**
     * @var int
     */
    private $consumerPk = '0';

    /**
     * @var string
     */
    private $ltiContextId = '';

    /**
     * @var string|null
     */
    private $settings;

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $updated = '1970-01-01 00:00:00';


    /**
     * Get contextPk.
     *
     * @return int
     */
    public function getContextPk()
    {
        return $this->contextPk;
    }

    /**
     * Set consumerPk.
     *
     * @param int $consumerPk
     *
     * @return Lti2Context
     */
    public function setConsumerPk($consumerPk)
    {
        $this->consumerPk = $consumerPk;

        return $this;
    }

    /**
     * Get consumerPk.
     *
     * @return int
     */
    public function getConsumerPk()
    {
        return $this->consumerPk;
    }

    /**
     * Set ltiContextId.
     *
     * @param string $ltiContextId
     *
     * @return Lti2Context
     */
    public function setLtiContextId($ltiContextId)
    {
        $this->ltiContextId = $ltiContextId;

        return $this;
    }

    /**
     * Get ltiContextId.
     *
     * @return string
     */
    public function getLtiContextId()
    {
        return $this->ltiContextId;
    }

    /**
     * Set settings.
     *
     * @param string|null $settings
     *
     * @return Lti2Context
     */
    public function setSettings($settings = null)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get settings.
     *
     * @return string|null
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Lti2Context
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Lti2Context
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
