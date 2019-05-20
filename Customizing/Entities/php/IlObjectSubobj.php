<?php



/**
 * IlObjectSubobj
 */
class IlObjectSubobj
{
    /**
     * @var string
     */
    private $parent = '';

    /**
     * @var string
     */
    private $subobj = '';

    /**
     * @var bool
     */
    private $mmax = '0';


    /**
     * Set parent.
     *
     * @param string $parent
     *
     * @return IlObjectSubobj
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
     * Set subobj.
     *
     * @param string $subobj
     *
     * @return IlObjectSubobj
     */
    public function setSubobj($subobj)
    {
        $this->subobj = $subobj;

        return $this;
    }

    /**
     * Get subobj.
     *
     * @return string
     */
    public function getSubobj()
    {
        return $this->subobj;
    }

    /**
     * Set mmax.
     *
     * @param bool $mmax
     *
     * @return IlObjectSubobj
     */
    public function setMmax($mmax)
    {
        $this->mmax = $mmax;

        return $this;
    }

    /**
     * Get mmax.
     *
     * @return bool
     */
    public function getMmax()
    {
        return $this->mmax;
    }
}
