<?php



/**
 * IlBtValueToTask
 */
class IlBtValueToTask
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $taskId;

    /**
     * @var int|null
     */
    private $valueId;

    /**
     * @var int|null
     */
    private $bucketId;


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
     * Set taskId.
     *
     * @param int|null $taskId
     *
     * @return IlBtValueToTask
     */
    public function setTaskId($taskId = null)
    {
        $this->taskId = $taskId;

        return $this;
    }

    /**
     * Get taskId.
     *
     * @return int|null
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Set valueId.
     *
     * @param int|null $valueId
     *
     * @return IlBtValueToTask
     */
    public function setValueId($valueId = null)
    {
        $this->valueId = $valueId;

        return $this;
    }

    /**
     * Get valueId.
     *
     * @return int|null
     */
    public function getValueId()
    {
        return $this->valueId;
    }

    /**
     * Set bucketId.
     *
     * @param int|null $bucketId
     *
     * @return IlBtValueToTask
     */
    public function setBucketId($bucketId = null)
    {
        $this->bucketId = $bucketId;

        return $this;
    }

    /**
     * Get bucketId.
     *
     * @return int|null
     */
    public function getBucketId()
    {
        return $this->bucketId;
    }
}
