<?php



/**
 * BadgeImageTemplType
 */
class BadgeImageTemplType
{
    /**
     * @var int
     */
    private $tmplId = '0';

    /**
     * @var string
     */
    private $typeId = '';


    /**
     * Set tmplId.
     *
     * @param int $tmplId
     *
     * @return BadgeImageTemplType
     */
    public function setTmplId($tmplId)
    {
        $this->tmplId = $tmplId;

        return $this;
    }

    /**
     * Get tmplId.
     *
     * @return int
     */
    public function getTmplId()
    {
        return $this->tmplId;
    }

    /**
     * Set typeId.
     *
     * @param string $typeId
     *
     * @return BadgeImageTemplType
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * Get typeId.
     *
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }
}
