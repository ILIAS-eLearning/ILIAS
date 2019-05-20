<?php



/**
 * Lti2Nonce
 */
class Lti2Nonce
{
    /**
     * @var int
     */
    private $consumerPk = '0';

    /**
     * @var string
     */
    private $value = '';

    /**
     * @var \DateTime
     */
    private $expires = '1970-01-01 00:00:00';


    /**
     * Set consumerPk.
     *
     * @param int $consumerPk
     *
     * @return Lti2Nonce
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
     * Set value.
     *
     * @param string $value
     *
     * @return Lti2Nonce
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set expires.
     *
     * @param \DateTime $expires
     *
     * @return Lti2Nonce
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
