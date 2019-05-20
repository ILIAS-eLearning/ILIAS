<?php



/**
 * DidacticTplAbr
 */
class DidacticTplAbr
{
    /**
     * @var int
     */
    private $actionId = '0';

    /**
     * @var bool
     */
    private $filterType = '0';


    /**
     * Get actionId.
     *
     * @return int
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * Set filterType.
     *
     * @param bool $filterType
     *
     * @return DidacticTplAbr
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;

        return $this;
    }

    /**
     * Get filterType.
     *
     * @return bool
     */
    public function getFilterType()
    {
        return $this->filterType;
    }
}
