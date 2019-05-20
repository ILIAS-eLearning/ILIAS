<?php



/**
 * CpItem
 */
class CpItem
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $completionthreshold = '1.0';

    /**
     * @var string|null
     */
    private $datafromlms;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $isvisible;

    /**
     * @var string|null
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $resourceid;

    /**
     * @var string|null
     */
    private $sequencingid;

    /**
     * @var string|null
     */
    private $timelimitaction;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $progressweight = '1.0';

    /**
     * @var bool|null
     */
    private $completedbymeasure = '0';


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
     * Set completionthreshold.
     *
     * @param string|null $completionthreshold
     *
     * @return CpItem
     */
    public function setCompletionthreshold($completionthreshold = null)
    {
        $this->completionthreshold = $completionthreshold;

        return $this;
    }

    /**
     * Get completionthreshold.
     *
     * @return string|null
     */
    public function getCompletionthreshold()
    {
        return $this->completionthreshold;
    }

    /**
     * Set datafromlms.
     *
     * @param string|null $datafromlms
     *
     * @return CpItem
     */
    public function setDatafromlms($datafromlms = null)
    {
        $this->datafromlms = $datafromlms;

        return $this;
    }

    /**
     * Get datafromlms.
     *
     * @return string|null
     */
    public function getDatafromlms()
    {
        return $this->datafromlms;
    }

    /**
     * Set id.
     *
     * @param string|null $id
     *
     * @return CpItem
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
     * Set isvisible.
     *
     * @param string|null $isvisible
     *
     * @return CpItem
     */
    public function setIsvisible($isvisible = null)
    {
        $this->isvisible = $isvisible;

        return $this;
    }

    /**
     * Get isvisible.
     *
     * @return string|null
     */
    public function getIsvisible()
    {
        return $this->isvisible;
    }

    /**
     * Set parameters.
     *
     * @param string|null $parameters
     *
     * @return CpItem
     */
    public function setParameters($parameters = null)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return string|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set resourceid.
     *
     * @param string|null $resourceid
     *
     * @return CpItem
     */
    public function setResourceid($resourceid = null)
    {
        $this->resourceid = $resourceid;

        return $this;
    }

    /**
     * Get resourceid.
     *
     * @return string|null
     */
    public function getResourceid()
    {
        return $this->resourceid;
    }

    /**
     * Set sequencingid.
     *
     * @param string|null $sequencingid
     *
     * @return CpItem
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
     * Set timelimitaction.
     *
     * @param string|null $timelimitaction
     *
     * @return CpItem
     */
    public function setTimelimitaction($timelimitaction = null)
    {
        $this->timelimitaction = $timelimitaction;

        return $this;
    }

    /**
     * Get timelimitaction.
     *
     * @return string|null
     */
    public function getTimelimitaction()
    {
        return $this->timelimitaction;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return CpItem
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

    /**
     * Set progressweight.
     *
     * @param string|null $progressweight
     *
     * @return CpItem
     */
    public function setProgressweight($progressweight = null)
    {
        $this->progressweight = $progressweight;

        return $this;
    }

    /**
     * Get progressweight.
     *
     * @return string|null
     */
    public function getProgressweight()
    {
        return $this->progressweight;
    }

    /**
     * Set completedbymeasure.
     *
     * @param bool|null $completedbymeasure
     *
     * @return CpItem
     */
    public function setCompletedbymeasure($completedbymeasure = null)
    {
        $this->completedbymeasure = $completedbymeasure;

        return $this;
    }

    /**
     * Get completedbymeasure.
     *
     * @return bool|null
     */
    public function getCompletedbymeasure()
    {
        return $this->completedbymeasure;
    }
}
