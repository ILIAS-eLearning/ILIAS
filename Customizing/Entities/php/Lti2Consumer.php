<?php



/**
 * Lti2Consumer
 */
class Lti2Consumer
{
    /**
     * @var int
     */
    private $consumerPk = '0';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $consumerKey256 = '';

    /**
     * @var string|null
     */
    private $consumerKey;

    /**
     * @var string
     */
    private $secret = '';

    /**
     * @var string|null
     */
    private $ltiVersion;

    /**
     * @var string|null
     */
    private $consumerName;

    /**
     * @var string|null
     */
    private $consumerVersion;

    /**
     * @var string|null
     */
    private $consumerGuid;

    /**
     * @var string|null
     */
    private $profile;

    /**
     * @var string|null
     */
    private $toolProxy;

    /**
     * @var string|null
     */
    private $settings;

    /**
     * @var bool
     */
    private $protected = '0';

    /**
     * @var bool
     */
    private $enabled = '0';

    /**
     * @var \DateTime|null
     */
    private $enableFrom;

    /**
     * @var \DateTime|null
     */
    private $enableUntil;

    /**
     * @var \DateTime|null
     */
    private $lastAccess;

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var \DateTime
     */
    private $updated = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $extConsumerId = '0';

    /**
     * @var int
     */
    private $refId = '0';


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
     * Set name.
     *
     * @param string $name
     *
     * @return Lti2Consumer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set consumerKey256.
     *
     * @param string $consumerKey256
     *
     * @return Lti2Consumer
     */
    public function setConsumerKey256($consumerKey256)
    {
        $this->consumerKey256 = $consumerKey256;

        return $this;
    }

    /**
     * Get consumerKey256.
     *
     * @return string
     */
    public function getConsumerKey256()
    {
        return $this->consumerKey256;
    }

    /**
     * Set consumerKey.
     *
     * @param string|null $consumerKey
     *
     * @return Lti2Consumer
     */
    public function setConsumerKey($consumerKey = null)
    {
        $this->consumerKey = $consumerKey;

        return $this;
    }

    /**
     * Get consumerKey.
     *
     * @return string|null
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * Set secret.
     *
     * @param string $secret
     *
     * @return Lti2Consumer
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set ltiVersion.
     *
     * @param string|null $ltiVersion
     *
     * @return Lti2Consumer
     */
    public function setLtiVersion($ltiVersion = null)
    {
        $this->ltiVersion = $ltiVersion;

        return $this;
    }

    /**
     * Get ltiVersion.
     *
     * @return string|null
     */
    public function getLtiVersion()
    {
        return $this->ltiVersion;
    }

    /**
     * Set consumerName.
     *
     * @param string|null $consumerName
     *
     * @return Lti2Consumer
     */
    public function setConsumerName($consumerName = null)
    {
        $this->consumerName = $consumerName;

        return $this;
    }

    /**
     * Get consumerName.
     *
     * @return string|null
     */
    public function getConsumerName()
    {
        return $this->consumerName;
    }

    /**
     * Set consumerVersion.
     *
     * @param string|null $consumerVersion
     *
     * @return Lti2Consumer
     */
    public function setConsumerVersion($consumerVersion = null)
    {
        $this->consumerVersion = $consumerVersion;

        return $this;
    }

    /**
     * Get consumerVersion.
     *
     * @return string|null
     */
    public function getConsumerVersion()
    {
        return $this->consumerVersion;
    }

    /**
     * Set consumerGuid.
     *
     * @param string|null $consumerGuid
     *
     * @return Lti2Consumer
     */
    public function setConsumerGuid($consumerGuid = null)
    {
        $this->consumerGuid = $consumerGuid;

        return $this;
    }

    /**
     * Get consumerGuid.
     *
     * @return string|null
     */
    public function getConsumerGuid()
    {
        return $this->consumerGuid;
    }

    /**
     * Set profile.
     *
     * @param string|null $profile
     *
     * @return Lti2Consumer
     */
    public function setProfile($profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile.
     *
     * @return string|null
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set toolProxy.
     *
     * @param string|null $toolProxy
     *
     * @return Lti2Consumer
     */
    public function setToolProxy($toolProxy = null)
    {
        $this->toolProxy = $toolProxy;

        return $this;
    }

    /**
     * Get toolProxy.
     *
     * @return string|null
     */
    public function getToolProxy()
    {
        return $this->toolProxy;
    }

    /**
     * Set settings.
     *
     * @param string|null $settings
     *
     * @return Lti2Consumer
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
     * Set protected.
     *
     * @param bool $protected
     *
     * @return Lti2Consumer
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;

        return $this;
    }

    /**
     * Get protected.
     *
     * @return bool
     */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return Lti2Consumer
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled.
     *
     * @return bool
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enableFrom.
     *
     * @param \DateTime|null $enableFrom
     *
     * @return Lti2Consumer
     */
    public function setEnableFrom($enableFrom = null)
    {
        $this->enableFrom = $enableFrom;

        return $this;
    }

    /**
     * Get enableFrom.
     *
     * @return \DateTime|null
     */
    public function getEnableFrom()
    {
        return $this->enableFrom;
    }

    /**
     * Set enableUntil.
     *
     * @param \DateTime|null $enableUntil
     *
     * @return Lti2Consumer
     */
    public function setEnableUntil($enableUntil = null)
    {
        $this->enableUntil = $enableUntil;

        return $this;
    }

    /**
     * Get enableUntil.
     *
     * @return \DateTime|null
     */
    public function getEnableUntil()
    {
        return $this->enableUntil;
    }

    /**
     * Set lastAccess.
     *
     * @param \DateTime|null $lastAccess
     *
     * @return Lti2Consumer
     */
    public function setLastAccess($lastAccess = null)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get lastAccess.
     *
     * @return \DateTime|null
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Lti2Consumer
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
     * @return Lti2Consumer
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

    /**
     * Set extConsumerId.
     *
     * @param int $extConsumerId
     *
     * @return Lti2Consumer
     */
    public function setExtConsumerId($extConsumerId)
    {
        $this->extConsumerId = $extConsumerId;

        return $this;
    }

    /**
     * Get extConsumerId.
     *
     * @return int
     */
    public function getExtConsumerId()
    {
        return $this->extConsumerId;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return Lti2Consumer
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }
}
