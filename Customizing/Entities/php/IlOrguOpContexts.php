<?php



/**
 * IlOrguOpContexts
 */
class IlOrguOpContexts
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $context;

    /**
     * @var int|null
     */
    private $parentContextId;


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
     * Set context.
     *
     * @param string|null $context
     *
     * @return IlOrguOpContexts
     */
    public function setContext($context = null)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     *
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set parentContextId.
     *
     * @param int|null $parentContextId
     *
     * @return IlOrguOpContexts
     */
    public function setParentContextId($parentContextId = null)
    {
        $this->parentContextId = $parentContextId;

        return $this;
    }

    /**
     * Get parentContextId.
     *
     * @return int|null
     */
    public function getParentContextId()
    {
        return $this->parentContextId;
    }
}
