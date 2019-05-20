<?php



/**
 * IlOrguOperations
 */
class IlOrguOperations
{
    /**
     * @var int
     */
    private $operationId = '0';

    /**
     * @var string|null
     */
    private $operationString;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int|null
     */
    private $listOrder;

    /**
     * @var int|null
     */
    private $contextId;


    /**
     * Get operationId.
     *
     * @return int
     */
    public function getOperationId()
    {
        return $this->operationId;
    }

    /**
     * Set operationString.
     *
     * @param string|null $operationString
     *
     * @return IlOrguOperations
     */
    public function setOperationString($operationString = null)
    {
        $this->operationString = $operationString;

        return $this;
    }

    /**
     * Get operationString.
     *
     * @return string|null
     */
    public function getOperationString()
    {
        return $this->operationString;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlOrguOperations
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

    /**
     * Set listOrder.
     *
     * @param int|null $listOrder
     *
     * @return IlOrguOperations
     */
    public function setListOrder($listOrder = null)
    {
        $this->listOrder = $listOrder;

        return $this;
    }

    /**
     * Get listOrder.
     *
     * @return int|null
     */
    public function getListOrder()
    {
        return $this->listOrder;
    }

    /**
     * Set contextId.
     *
     * @param int|null $contextId
     *
     * @return IlOrguOperations
     */
    public function setContextId($contextId = null)
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * Get contextId.
     *
     * @return int|null
     */
    public function getContextId()
    {
        return $this->contextId;
    }
}
