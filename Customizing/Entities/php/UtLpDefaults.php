<?php



/**
 * UtLpDefaults
 */
class UtLpDefaults
{
    /**
     * @var string
     */
    private $typeId = '';

    /**
     * @var bool
     */
    private $lpMode = '0';


    /**
     * Get typeId.
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * Set lpMode.
     *
     * @param bool $lpMode
     *
     * @return UtLpDefaults
     */
    public function setLpMode($lpMode)
    {
        $this->lpMode = $lpMode;

        return $this;
    }

    /**
     * Get lpMode.
     *
     * @return bool
     */
    public function getLpMode()
    {
        return $this->lpMode;
    }
}
