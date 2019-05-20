<?php



/**
 * RbacTemplates
 */
class RbacTemplates
{
    /**
     * @var int
     */
    private $rolId = '0';

    /**
     * @var string
     */
    private $type = '';

    /**
     * @var int
     */
    private $opsId = '0';

    /**
     * @var int
     */
    private $parent = '0';


    /**
     * Set rolId.
     *
     * @param int $rolId
     *
     * @return RbacTemplates
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
     * Set type.
     *
     * @param string $type
     *
     * @return RbacTemplates
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set opsId.
     *
     * @param int $opsId
     *
     * @return RbacTemplates
     */
    public function setOpsId($opsId)
    {
        $this->opsId = $opsId;

        return $this;
    }

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
     * Set parent.
     *
     * @param int $parent
     *
     * @return RbacTemplates
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
}
