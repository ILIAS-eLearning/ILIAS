<?php



/**
 * ObjectDescription
 */
class ObjectDescription
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $description;


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return ObjectDescription
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
}
