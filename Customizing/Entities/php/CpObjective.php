<?php



/**
 * CpObjective
 */
class CpObjective
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $minnormalmeasure;

    /**
     * @var string|null
     */
    private $objectiveid;

    /**
     * @var bool|null
     */
    private $cPrimary;

    /**
     * @var bool|null
     */
    private $satisfiedbymeasure;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set minnormalmeasure.
     *
     * @param string|null $minnormalmeasure
     *
     * @return CpObjective
     */
    public function setMinnormalmeasure($minnormalmeasure = null)
    {
        $this->minnormalmeasure = $minnormalmeasure;

        return $this;
    }

    /**
     * Get minnormalmeasure.
     *
     * @return string|null
     */
    public function getMinnormalmeasure()
    {
        return $this->minnormalmeasure;
    }

    /**
     * Set objectiveid.
     *
     * @param string|null $objectiveid
     *
     * @return CpObjective
     */
    public function setObjectiveid($objectiveid = null)
    {
        $this->objectiveid = $objectiveid;

        return $this;
    }

    /**
     * Get objectiveid.
     *
     * @return string|null
     */
    public function getObjectiveid()
    {
        return $this->objectiveid;
    }

    /**
     * Set cPrimary.
     *
     * @param bool|null $cPrimary
     *
     * @return CpObjective
     */
    public function setCPrimary($cPrimary = null)
    {
        $this->cPrimary = $cPrimary;

        return $this;
    }

    /**
     * Get cPrimary.
     *
     * @return bool|null
     */
    public function getCPrimary()
    {
        return $this->cPrimary;
    }

    /**
     * Set satisfiedbymeasure.
     *
     * @param bool|null $satisfiedbymeasure
     *
     * @return CpObjective
     */
    public function setSatisfiedbymeasure($satisfiedbymeasure = null)
    {
        $this->satisfiedbymeasure = $satisfiedbymeasure;

        return $this;
    }

    /**
     * Get satisfiedbymeasure.
     *
     * @return bool|null
     */
    public function getSatisfiedbymeasure()
    {
        return $this->satisfiedbymeasure;
    }
}
