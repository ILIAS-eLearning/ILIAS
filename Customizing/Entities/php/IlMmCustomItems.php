<?php



/**
 * IlMmCustomItems
 */
class IlMmCustomItems
{
    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $action;

    /**
     * @var bool|null
     */
    private $topItem;

    /**
     * @var string|null
     */
    private $defaultTitle;


    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return IlMmCustomItems
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set action.
     *
     * @param string|null $action
     *
     * @return IlMmCustomItems
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
     * Set topItem.
     *
     * @param bool|null $topItem
     *
     * @return IlMmCustomItems
     */
    public function setTopItem($topItem = null)
    {
        $this->topItem = $topItem;

        return $this;
    }

    /**
     * Get topItem.
     *
     * @return bool|null
     */
    public function getTopItem()
    {
        return $this->topItem;
    }

    /**
     * Set defaultTitle.
     *
     * @param string|null $defaultTitle
     *
     * @return IlMmCustomItems
     */
    public function setDefaultTitle($defaultTitle = null)
    {
        $this->defaultTitle = $defaultTitle;

        return $this;
    }

    /**
     * Get defaultTitle.
     *
     * @return string|null
     */
    public function getDefaultTitle()
    {
        return $this->defaultTitle;
    }
}
