<?php



/**
 * UserActionActivation
 */
class UserActionActivation
{
    /**
     * @var string
     */
    private $contextComp = '';

    /**
     * @var string
     */
    private $contextId = '';

    /**
     * @var string
     */
    private $actionComp = '';

    /**
     * @var string
     */
    private $actionType = '';

    /**
     * @var bool
     */
    private $active = '0';


    /**
     * Set contextComp.
     *
     * @param string $contextComp
     *
     * @return UserActionActivation
     */
    public function setContextComp($contextComp)
    {
        $this->contextComp = $contextComp;

        return $this;
    }

    /**
     * Get contextComp.
     *
     * @return string
     */
    public function getContextComp()
    {
        return $this->contextComp;
    }

    /**
     * Set contextId.
     *
     * @param string $contextId
     *
     * @return UserActionActivation
     */
    public function setContextId($contextId)
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * Get contextId.
     *
     * @return string
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * Set actionComp.
     *
     * @param string $actionComp
     *
     * @return UserActionActivation
     */
    public function setActionComp($actionComp)
    {
        $this->actionComp = $actionComp;

        return $this;
    }

    /**
     * Get actionComp.
     *
     * @return string
     */
    public function getActionComp()
    {
        return $this->actionComp;
    }

    /**
     * Set actionType.
     *
     * @param string $actionType
     *
     * @return UserActionActivation
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get actionType.
     *
     * @return string
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return UserActionActivation
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
}
