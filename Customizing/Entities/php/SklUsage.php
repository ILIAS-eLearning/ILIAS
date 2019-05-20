<?php



/**
 * SklUsage
 */
class SklUsage
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $skillId = '0';

    /**
     * @var int
     */
    private $trefId = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return SklUsage
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return SklUsage
     */
    public function setSkillId($skillId)
    {
        $this->skillId = $skillId;

        return $this;
    }

    /**
     * Get skillId.
     *
     * @return int
     */
    public function getSkillId()
    {
        return $this->skillId;
    }

    /**
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return SklUsage
     */
    public function setTrefId($trefId)
    {
        $this->trefId = $trefId;

        return $this;
    }

    /**
     * Get trefId.
     *
     * @return int
     */
    public function getTrefId()
    {
        return $this->trefId;
    }
}
