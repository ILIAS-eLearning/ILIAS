<?php



/**
 * IlBtValue
 */
class IlBtValue
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var bool|null
     */
    private $hasParentTask;

    /**
     * @var int|null
     */
    private $parentTaskId;

    /**
     * @var string|null
     */
    private $hash;

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
     * @var string|null
     */
    private $serialized;

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
     * Set hasParentTask.
     *
     * @param bool|null $hasParentTask
     *
     * @return IlBtValue
     */
    public function setHasParentTask($hasParentTask = null)
    {
        $this->hasParentTask = $hasParentTask;

        return $this;
    }

    /**
     * Get hasParentTask.
     *
     * @return bool|null
     */
    public function getHasParentTask()
    {
        return $this->hasParentTask;
    }

    /**
     * Set parentTaskId.
     *
     * @param int|null $parentTaskId
     *
     * @return IlBtValue
     */
    public function setParentTaskId($parentTaskId = null)
    {
        $this->parentTaskId = $parentTaskId;

        return $this;
    }

    /**
     * Get parentTaskId.
     *
     * @return int|null
     */
    public function getParentTaskId()
    {
        return $this->parentTaskId;
    }

    /**
     * Set hash.
     *
     * @param string|null $hash
     *
     * @return IlBtValue
     */
    public function setHash($hash = null)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return IlBtValue
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
     * @return IlBtValue
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
     * @return IlBtValue
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
     * Set serialized.
     *
     * @param string|null $serialized
     *
     * @return IlBtValue
     */
    public function setSerialized($serialized = null)
    {
        $this->serialized = $serialized;

        return $this;
    }

    /**
     * Get serialized.
     *
     * @return string|null
     */
    public function getSerialized()
    {
        return $this->serialized;
    }

    /**
     * Set bucketId.
     *
     * @param int|null $bucketId
     *
     * @return IlBtValue
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
