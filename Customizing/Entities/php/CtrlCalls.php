<?php



/**
 * CtrlCalls
 */
class CtrlCalls
{
    /**
     * @var string
     */
    private $parent = '';

    /**
     * @var string
     */
    private $child = '';

    /**
     * @var string|null
     */
    private $compPrefix;


    /**
     * Set parent.
     *
     * @param string $parent
     *
     * @return CtrlCalls
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set child.
     *
     * @param string $child
     *
     * @return CtrlCalls
     */
    public function setChild($child)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child.
     *
     * @return string
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Set compPrefix.
     *
     * @param string|null $compPrefix
     *
     * @return CtrlCalls
     */
    public function setCompPrefix($compPrefix = null)
    {
        $this->compPrefix = $compPrefix;

        return $this;
    }

    /**
     * Get compPrefix.
     *
     * @return string|null
     */
    public function getCompPrefix()
    {
        return $this->compPrefix;
    }
}
