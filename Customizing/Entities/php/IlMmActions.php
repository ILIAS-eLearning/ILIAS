<?php



/**
 * IlMmActions
 */
class IlMmActions
{
    /**
     * @var string
     */
    private $identification = '';

    /**
     * @var string|null
     */
    private $action;

    /**
     * @var bool|null
     */
    private $external;


    /**
     * Get identification.
     *
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set action.
     *
     * @param string|null $action
     *
     * @return IlMmActions
     */
    public function setAction($action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set external.
     *
     * @param bool|null $external
     *
     * @return IlMmActions
     */
    public function setExternal($external = null)
    {
        $this->external = $external;

        return $this;
    }

    /**
     * Get external.
     *
     * @return bool|null
     */
    public function getExternal()
    {
        return $this->external;
    }
}
