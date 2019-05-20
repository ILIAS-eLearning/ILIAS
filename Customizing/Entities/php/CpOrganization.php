<?php



/**
 * CpOrganization
 */
class CpOrganization
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var bool|null
     */
    private $objectivesglobtosys;

    /**
     * @var string|null
     */
    private $sequencingid;

    /**
     * @var string|null
     */
    private $structure;

    /**
     * @var string|null
     */
    private $title;


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
     * Set id.
     *
     * @param string|null $id
     *
     * @return CpOrganization
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objectivesglobtosys.
     *
     * @param bool|null $objectivesglobtosys
     *
     * @return CpOrganization
     */
    public function setObjectivesglobtosys($objectivesglobtosys = null)
    {
        $this->objectivesglobtosys = $objectivesglobtosys;

        return $this;
    }

    /**
     * Get objectivesglobtosys.
     *
     * @return bool|null
     */
    public function getObjectivesglobtosys()
    {
        return $this->objectivesglobtosys;
    }

    /**
     * Set sequencingid.
     *
     * @param string|null $sequencingid
     *
     * @return CpOrganization
     */
    public function setSequencingid($sequencingid = null)
    {
        $this->sequencingid = $sequencingid;

        return $this;
    }

    /**
     * Get sequencingid.
     *
     * @return string|null
     */
    public function getSequencingid()
    {
        return $this->sequencingid;
    }

    /**
     * Set structure.
     *
     * @param string|null $structure
     *
     * @return CpOrganization
     */
    public function setStructure($structure = null)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure.
     *
     * @return string|null
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return CpOrganization
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
}
