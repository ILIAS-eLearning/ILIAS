<?php



/**
 * WfeDetListening
 */
class WfeDetListening
{
    /**
     * @var int
     */
    private $detectorId = '0';

    /**
     * @var int
     */
    private $workflowId = '0';

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $subjectType;

    /**
     * @var int|null
     */
    private $subjectId;

    /**
     * @var string|null
     */
    private $contextType;

    /**
     * @var int|null
     */
    private $contextId;

    /**
     * @var int|null
     */
    private $listeningStart;

    /**
     * @var int|null
     */
    private $listeningEnd;


    /**
     * Get detectorId.
     *
     * @return int
     */
    public function getDetectorId()
    {
        return $this->detectorId;
    }

    /**
     * Set workflowId.
     *
     * @param int $workflowId
     *
     * @return WfeDetListening
     */
    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;

        return $this;
    }

    /**
     * Get workflowId.
     *
     * @return int
     */
    public function getWorkflowId()
    {
        return $this->workflowId;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return WfeDetListening
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set content.
     *
     * @param string|null $content
     *
     * @return WfeDetListening
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set subjectType.
     *
     * @param string|null $subjectType
     *
     * @return WfeDetListening
     */
    public function setSubjectType($subjectType = null)
    {
        $this->subjectType = $subjectType;

        return $this;
    }

    /**
     * Get subjectType.
     *
     * @return string|null
     */
    public function getSubjectType()
    {
        return $this->subjectType;
    }

    /**
     * Set subjectId.
     *
     * @param int|null $subjectId
     *
     * @return WfeDetListening
     */
    public function setSubjectId($subjectId = null)
    {
        $this->subjectId = $subjectId;

        return $this;
    }

    /**
     * Get subjectId.
     *
     * @return int|null
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * Set contextType.
     *
     * @param string|null $contextType
     *
     * @return WfeDetListening
     */
    public function setContextType($contextType = null)
    {
        $this->contextType = $contextType;

        return $this;
    }

    /**
     * Get contextType.
     *
     * @return string|null
     */
    public function getContextType()
    {
        return $this->contextType;
    }

    /**
     * Set contextId.
     *
     * @param int|null $contextId
     *
     * @return WfeDetListening
     */
    public function setContextId($contextId = null)
    {
        $this->contextId = $contextId;

        return $this;
    }

    /**
     * Get contextId.
     *
     * @return int|null
     */
    public function getContextId()
    {
        return $this->contextId;
    }

    /**
     * Set listeningStart.
     *
     * @param int|null $listeningStart
     *
     * @return WfeDetListening
     */
    public function setListeningStart($listeningStart = null)
    {
        $this->listeningStart = $listeningStart;

        return $this;
    }

    /**
     * Get listeningStart.
     *
     * @return int|null
     */
    public function getListeningStart()
    {
        return $this->listeningStart;
    }

    /**
     * Set listeningEnd.
     *
     * @param int|null $listeningEnd
     *
     * @return WfeDetListening
     */
    public function setListeningEnd($listeningEnd = null)
    {
        $this->listeningEnd = $listeningEnd;

        return $this;
    }

    /**
     * Get listeningEnd.
     *
     * @return int|null
     */
    public function getListeningEnd()
    {
        return $this->listeningEnd;
    }
}
