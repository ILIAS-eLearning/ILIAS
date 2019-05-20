<?php



/**
 * CtrlStructure
 */
class CtrlStructure
{
    /**
     * @var string
     */
    private $rootClass = ' ';

    /**
     * @var string|null
     */
    private $callNode;

    /**
     * @var string|null
     */
    private $forward;

    /**
     * @var string|null
     */
    private $parent;


    /**
     * Get rootClass.
     *
     * @return string
     */
    public function getRootClass()
    {
        return $this->rootClass;
    }

    /**
     * Set callNode.
     *
     * @param string|null $callNode
     *
     * @return CtrlStructure
     */
    public function setCallNode($callNode = null)
    {
        $this->callNode = $callNode;

        return $this;
    }

    /**
     * Get callNode.
     *
     * @return string|null
     */
    public function getCallNode()
    {
        return $this->callNode;
    }

    /**
     * Set forward.
     *
     * @param string|null $forward
     *
     * @return CtrlStructure
     */
    public function setForward($forward = null)
    {
        $this->forward = $forward;

        return $this;
    }

    /**
     * Get forward.
     *
     * @return string|null
     */
    public function getForward()
    {
        return $this->forward;
    }

    /**
     * Set parent.
     *
     * @param string|null $parent
     *
     * @return CtrlStructure
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return string|null
     */
    public function getParent()
    {
        return $this->parent;
    }
}
