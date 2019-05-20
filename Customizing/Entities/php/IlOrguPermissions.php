<?php



/**
 * IlOrguPermissions
 */
class IlOrguPermissions
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $contextId;

    /**
     * @var string|null
     */
    private $operations;

    /**
     * @var int|null
     */
    private $parentId;

    /**
     * @var int|null
     */
    private $positionId;

    /**
     * @var bool|null
     */
    private $protected = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contextId.
     *
     * @param int|null $contextId
     *
     * @return IlOrguPermissions
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

    /**
     * Set operations.
     *
     * @param string|null $operations
     *
     * @return IlOrguPermissions
     */
    public function setOperations($operations = null)
    {
        $this->operations = $operations;

        return $this;
    }

    /**
     * Get operations.
     *
     * @return string|null
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * Set parentId.
     *
     * @param int|null $parentId
     *
     * @return IlOrguPermissions
     */
    public function setParentId($parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set positionId.
     *
     * @param int|null $positionId
     *
     * @return IlOrguPermissions
     */
    public function setPositionId($positionId = null)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * Get positionId.
     *
     * @return int|null
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * Set protected.
     *
     * @param bool|null $protected
     *
     * @return IlOrguPermissions
     */
    public function setProtected($protected = null)
    {
        $this->protected = $protected;

        return $this;
    }

    /**
     * Get protected.
     *
     * @return bool|null
     */
    public function getProtected()
    {
        return $this->protected;
    }
}
