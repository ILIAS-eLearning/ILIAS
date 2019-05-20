<?php



/**
 * Lti2ResourceLink
 */
class Lti2ResourceLink
{
    /**
     * @var int
     */
    private $resourceLinkPk = '0';

    /**
     * @var int|null
     */
    private $contextPk;

    /**
     * @var int|null
     */
    private $consumerPk;

    /**
     * @var string
     */
    private $ltiResourceLinkId = '';

    /**
     * @var string|null
     */
    private $settings;

    /**
     * @var int|null
     */
    private $primaryResourceLinkPk;

    /**
     * @var bool|null
     */
    private $shareApproved;

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $updated = '1970-01-01 00:00:00';


    /**
     * Get resourceLinkPk.
     *
     * @return int
     */
    public function getResourceLinkPk()
    {
        return $this->resourceLinkPk;
    }

    /**
     * Set contextPk.
     *
     * @param int|null $contextPk
     *
     * @return Lti2ResourceLink
     */
    public function setContextPk($contextPk = null)
    {
        $this->contextPk = $contextPk;

        return $this;
    }

    /**
     * Get contextPk.
     *
     * @return int|null
     */
    public function getContextPk()
    {
        return $this->contextPk;
    }

    /**
     * Set consumerPk.
     *
     * @param int|null $consumerPk
     *
     * @return Lti2ResourceLink
     */
    public function setConsumerPk($consumerPk = null)
    {
        $this->consumerPk = $consumerPk;

        return $this;
    }

    /**
     * Get consumerPk.
     *
     * @return int|null
     */
    public function getConsumerPk()
    {
        return $this->consumerPk;
    }

    /**
     * Set ltiResourceLinkId.
     *
     * @param string $ltiResourceLinkId
     *
     * @return Lti2ResourceLink
     */
    public function setLtiResourceLinkId($ltiResourceLinkId)
    {
        $this->ltiResourceLinkId = $ltiResourceLinkId;

        return $this;
    }

    /**
     * Get ltiResourceLinkId.
     *
     * @return string
     */
    public function getLtiResourceLinkId()
    {
        return $this->ltiResourceLinkId;
    }

    /**
     * Set settings.
     *
     * @param string|null $settings
     *
     * @return Lti2ResourceLink
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
     * Set primaryResourceLinkPk.
     *
     * @param int|null $primaryResourceLinkPk
     *
     * @return Lti2ResourceLink
     */
    public function setPrimaryResourceLinkPk($primaryResourceLinkPk = null)
    {
        $this->primaryResourceLinkPk = $primaryResourceLinkPk;

        return $this;
    }

    /**
     * Get primaryResourceLinkPk.
     *
     * @return int|null
     */
    public function getPrimaryResourceLinkPk()
    {
        return $this->primaryResourceLinkPk;
    }

    /**
     * Set shareApproved.
     *
     * @param bool|null $shareApproved
     *
     * @return Lti2ResourceLink
     */
    public function setShareApproved($shareApproved = null)
    {
        $this->shareApproved = $shareApproved;

        return $this;
    }

    /**
     * Get shareApproved.
     *
     * @return bool|null
     */
    public function getShareApproved()
    {
        return $this->shareApproved;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Lti2ResourceLink
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
     * @return Lti2ResourceLink
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
