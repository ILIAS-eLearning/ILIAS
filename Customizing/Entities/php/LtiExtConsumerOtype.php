<?php



/**
 * LtiExtConsumerOtype
 */
class LtiExtConsumerOtype
{
    /**
     * @var int
     */
    private $consumerId = '0';

    /**
     * @var string
     */
    private $objectType = '';


    /**
     * Set consumerId.
     *
     * @param int $consumerId
     *
     * @return LtiExtConsumerOtype
     */
    public function setConsumerId($consumerId)
    {
        $this->consumerId = $consumerId;

        return $this;
    }

    /**
     * Get consumerId.
     *
     * @return int
     */
    public function getConsumerId()
    {
        return $this->consumerId;
    }

    /**
     * Set objectType.
     *
     * @param string $objectType
     *
     * @return LtiExtConsumerOtype
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * Get objectType.
     *
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }
}
