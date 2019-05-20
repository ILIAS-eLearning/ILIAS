<?php



/**
 * ContSkills
 */
class ContSkills
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $skillId = '0';

    /**
     * @var int
     */
    private $trefId = '0';


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return ContSkills
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set skillId.
     *
     * @param int $skillId
     *
     * @return ContSkills
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
     * @return ContSkills
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
