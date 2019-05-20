<?php



/**
 * LogComponents
 */
class LogComponents
{
    /**
     * @var string
     */
    private $componentId = '';

    /**
     * @var int|null
     */
    private $logLevel;


    /**
     * Get componentId.
     *
     * @return string
     */
    public function getComponentId()
    {
        return $this->componentId;
    }

    /**
     * Set logLevel.
     *
     * @param int|null $logLevel
     *
     * @return LogComponents
     */
    public function setLogLevel($logLevel = null)
    {
        $this->logLevel = $logLevel;

        return $this;
    }

    /**
     * Get logLevel.
     *
     * @return int|null
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }
}
