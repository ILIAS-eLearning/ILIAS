<?php



/**
 * RbacOperations
 */
class RbacOperations
{
    /**
     * @var int
     */
    private $opsId = '0';

    /**
     * @var string|null
     */
    private $operation;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var int|null
     */
    private $opOrder;


    /**
     * Get opsId.
     *
     * @return int
     */
    public function getOpsId()
    {
        return $this->opsId;
    }

    /**
     * Set operation.
     *
     * @param string|null $operation
     *
     * @return RbacOperations
     */
    public function setOperation($operation = null)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * Get operation.
     *
     * @return string|null
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return RbacOperations
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
     * Set class.
     *
     * @param string|null $class
     *
     * @return RbacOperations
     */
    public function setClass($class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class.
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set opOrder.
     *
     * @param int|null $opOrder
     *
     * @return RbacOperations
     */
    public function setOpOrder($opOrder = null)
    {
        $this->opOrder = $opOrder;

        return $this;
    }

    /**
     * Get opOrder.
     *
     * @return int|null
     */
    public function getOpOrder()
    {
        return $this->opOrder;
    }
}
