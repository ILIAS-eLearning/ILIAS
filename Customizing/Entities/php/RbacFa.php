<?php



/**
 * RbacFa
 */
class RbacFa
{
    /**
     * @var int
     */
    private $rolId = '0';

    /**
     * @var int
     */
    private $parent = '0';

    /**
     * @var string|null
     */
    private $assign;

    /**
     * @var string|null
     */
    private $protected = 'n';

    /**
     * @var bool
     */
    private $blocked = '0';


    /**
     * Set rolId.
     *
     * @param int $rolId
     *
     * @return RbacFa
     */
    public function setRolId($rolId)
    {
        $this->rolId = $rolId;

        return $this;
    }

    /**
     * Get rolId.
     *
     * @return int
     */
    public function getRolId()
    {
        return $this->rolId;
    }

    /**
     * Set parent.
     *
     * @param int $parent
     *
     * @return RbacFa
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set assign.
     *
     * @param string|null $assign
     *
     * @return RbacFa
     */
    public function setAssign($assign = null)
    {
        $this->assign = $assign;

        return $this;
    }

    /**
     * Get assign.
     *
     * @return string|null
     */
    public function getAssign()
    {
        return $this->assign;
    }

    /**
     * Set protected.
     *
     * @param string|null $protected
     *
     * @return RbacFa
     */
    public function setProtected($protected = null)
    {
        $this->protected = $protected;

        return $this;
    }

    /**
     * Get protected.
     *
     * @return string|null
     */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
     * Set blocked.
     *
     * @param bool $blocked
     *
     * @return RbacFa
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Get blocked.
     *
     * @return bool
     */
    public function getBlocked()
    {
        return $this->blocked;
    }
}
