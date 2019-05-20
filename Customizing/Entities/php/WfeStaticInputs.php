<?php



/**
 * WfeStaticInputs
 */
class WfeStaticInputs
{
    /**
     * @var int
     */
    private $inputId = '0';

    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $value;


    /**
     * Get inputId.
     *
     * @return int
     */
    public function getInputId()
    {
        return $this->inputId;
    }

    /**
     * Set eventId.
     *
     * @param int $eventId
     *
     * @return WfeStaticInputs
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
     * Set name.
     *
     * @param string|null $name
     *
     * @return WfeStaticInputs
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return WfeStaticInputs
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
