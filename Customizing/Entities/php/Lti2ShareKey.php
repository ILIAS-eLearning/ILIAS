<?php



/**
 * Lti2ShareKey
 */
class Lti2ShareKey
{
    /**
     * @var string
     */
    private $shareKeyId = '';

    /**
     * @var int
     */
    private $resourceLinkPk = '0';

    /**
     * @var bool
     */
    private $autoApprove = '0';

    /**
     * @var \DateTime
     */
    private $expires = '1970-01-01 00:00:00';


    /**
     * Get shareKeyId.
     *
     * @return string
     */
    public function getShareKeyId()
    {
        return $this->shareKeyId;
    }

    /**
     * Set resourceLinkPk.
     *
     * @param int $resourceLinkPk
     *
     * @return Lti2ShareKey
     */
    public function setResourceLinkPk($resourceLinkPk)
    {
        $this->resourceLinkPk = $resourceLinkPk;

        return $this;
    }

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
     * Set autoApprove.
     *
     * @param bool $autoApprove
     *
     * @return Lti2ShareKey
     */
    public function setAutoApprove($autoApprove)
    {
        $this->autoApprove = $autoApprove;

        return $this;
    }

    /**
     * Get autoApprove.
     *
     * @return bool
     */
    public function getAutoApprove()
    {
        return $this->autoApprove;
    }

    /**
     * Set expires.
     *
     * @param \DateTime $expires
     *
     * @return Lti2ShareKey
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires.
     *
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }
}
