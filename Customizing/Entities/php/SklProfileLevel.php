<?php



/**
 * SklProfileLevel
 */
class SklProfileLevel
{
    /**
     * @var int
     */
    private $profileId = '0';

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
     * Set profileId.
     *
     * @param int $profileId
     *
     * @return SklProfileLevel
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * Get profileId.
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Set baseSkillId.
     *
     * @param int $baseSkillId
     *
     * @return SklProfileLevel
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
     * @return SklProfileLevel
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
     * @return SklProfileLevel
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
}
