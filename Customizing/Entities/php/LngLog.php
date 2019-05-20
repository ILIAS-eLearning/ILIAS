<?php



/**
 * LngLog
 */
class LngLog
{
    /**
     * @var string
     */
    private $module = '';

    /**
     * @var string
     */
    private $identifier = '';


    /**
     * Set module.
     *
     * @param string $module
     *
     * @return LngLog
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module.
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set identifier.
     *
     * @param string $identifier
     *
     * @return LngLog
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}
