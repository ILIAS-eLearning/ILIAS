<?php



/**
 * SklSkillResource
 */
class SklSkillResource
{
    /**
     * @var int
     */
    private $baseSkillId = '0';

    /**
     * @var int
     */
    private $trefId = '0';

    /**
     * @var int
     */
    private $levelId = '0';

    /**
     * @var int
     */
    private $repRefId = '0';

    /**
     * @var bool
     */
    private $imparting = '0';

    /**
     * @var bool
     */
    private $ltrigger = '0';


    /**
     * Set baseSkillId.
     *
     * @param int $baseSkillId
     *
     * @return SklSkillResource
     */
    public function setBaseSkillId($baseSkillId)
    {
        $this->baseSkillId = $baseSkillId;

        return $this;
    }

    /**
     * Get baseSkillId.
     *
     * @return int
     */
    public function getBaseSkillId()
    {
        return $this->baseSkillId;
    }

    /**
     * Set trefId.
     *
     * @param int $trefId
     *
     * @return SklSkillResource
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

    /**
     * Set levelId.
     *
     * @param int $levelId
     *
     * @return SklSkillResource
     */
    public function setLevelId($levelId)
    {
        $this->levelId = $levelId;

        return $this;
    }

    /**
     * Get levelId.
     *
     * @return int
     */
    public function getLevelId()
    {
        return $this->levelId;
    }

    /**
     * Set repRefId.
     *
     * @param int $repRefId
     *
     * @return SklSkillResource
     */
    public function setRepRefId($repRefId)
    {
        $this->repRefId = $repRefId;

        return $this;
    }

    /**
     * Get repRefId.
     *
     * @return int
     */
    public function getRepRefId()
    {
        return $this->repRefId;
    }

    /**
     * Set imparting.
     *
     * @param bool $imparting
     *
     * @return SklSkillResource
     */
    public function setImparting($imparting)
    {
        $this->imparting = $imparting;

        return $this;
    }

    /**
     * Get imparting.
     *
     * @return bool
     */
    public function getImparting()
    {
        return $this->imparting;
    }

    /**
     * Set ltrigger.
     *
     * @param bool $ltrigger
     *
     * @return SklSkillResource
     */
    public function setLtrigger($ltrigger)
    {
        $this->ltrigger = $ltrigger;

        return $this;
    }

    /**
     * Get ltrigger.
     *
     * @return bool
     */
    public function getLtrigger()
    {
        return $this->ltrigger;
    }
}
