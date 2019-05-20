<?php



/**
 * UsrSessIstorage
 */
class UsrSessIstorage
{
    /**
     * @var string
     */
    private $sessionId = '';

    /**
     * @var string
     */
    private $componentId = '';

    /**
     * @var string
     */
    private $vkey = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set sessionId.
     *
     * @param string $sessionId
     *
     * @return UsrSessIstorage
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId.
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set componentId.
     *
     * @param string $componentId
     *
     * @return UsrSessIstorage
     */
    public function setComponentId($componentId)
    {
        $this->componentId = $componentId;

        return $this;
    }

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
     * Set vkey.
     *
     * @param string $vkey
     *
     * @return UsrSessIstorage
     */
    public function setVkey($vkey)
    {
        $this->vkey = $vkey;

        return $this;
    }

    /**
     * Get vkey.
     *
     * @return string
     */
    public function getVkey()
    {
        return $this->vkey;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return UsrSessIstorage
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
