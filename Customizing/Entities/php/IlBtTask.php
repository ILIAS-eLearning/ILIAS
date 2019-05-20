<?php



/**
 * IlBtTask
 */
class IlBtTask
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $classPath;

    /**
     * @var string|null
     */
    private $className;

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
     * Set type.
     *
     * @param string|null $type
     *
     * @return IlBtTask
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
     * Set classPath.
     *
     * @param string|null $classPath
     *
     * @return IlBtTask
     */
    public function setClassPath($classPath = null)
    {
        $this->classPath = $classPath;

        return $this;
    }

    /**
     * Get classPath.
     *
     * @return string|null
     */
    public function getClassPath()
    {
        return $this->classPath;
    }

    /**
     * Set className.
     *
     * @param string|null $className
     *
     * @return IlBtTask
     */
    public function setClassName($className = null)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * Get className.
     *
     * @return string|null
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Set bucketId.
     *
     * @param int|null $bucketId
     *
     * @return IlBtTask
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
