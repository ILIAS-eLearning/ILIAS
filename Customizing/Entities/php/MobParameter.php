<?php



/**
 * MobParameter
 */
class MobParameter
{
    /**
     * @var int
     */
    private $medItemId = '0';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set medItemId.
     *
     * @param int $medItemId
     *
     * @return MobParameter
     */
    public function setMedItemId($medItemId)
    {
        $this->medItemId = $medItemId;

        return $this;
    }

    /**
     * Get medItemId.
     *
     * @return int
     */
    public function getMedItemId()
    {
        return $this->medItemId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return MobParameter
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
     * Set value.
     *
     * @param string|null $value
     *
     * @return MobParameter
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
