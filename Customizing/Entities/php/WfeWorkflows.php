<?php



/**
 * WfeWorkflows
 */
class WfeWorkflows
{
    /**
     * @var int
     */
    private $workflowId = '0';

    /**
     * @var string|null
     */
    private $workflowType;

    /**
     * @var string|null
     */
    private $workflowContent;

    /**
     * @var string|null
     */
    private $workflowClass;

    /**
     * @var string|null
     */
    private $workflowLocation;

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
     * @var string|null
     */
    private $workflowInstance;

    /**
     * @var int|null
     */
    private $active;


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
     * Set workflowType.
     *
     * @param string|null $workflowType
     *
     * @return WfeWorkflows
     */
    public function setWorkflowType($workflowType = null)
    {
        $this->workflowType = $workflowType;

        return $this;
    }

    /**
     * Get workflowType.
     *
     * @return string|null
     */
    public function getWorkflowType()
    {
        return $this->workflowType;
    }

    /**
     * Set workflowContent.
     *
     * @param string|null $workflowContent
     *
     * @return WfeWorkflows
     */
    public function setWorkflowContent($workflowContent = null)
    {
        $this->workflowContent = $workflowContent;

        return $this;
    }

    /**
     * Get workflowContent.
     *
     * @return string|null
     */
    public function getWorkflowContent()
    {
        return $this->workflowContent;
    }

    /**
     * Set workflowClass.
     *
     * @param string|null $workflowClass
     *
     * @return WfeWorkflows
     */
    public function setWorkflowClass($workflowClass = null)
    {
        $this->workflowClass = $workflowClass;

        return $this;
    }

    /**
     * Get workflowClass.
     *
     * @return string|null
     */
    public function getWorkflowClass()
    {
        return $this->workflowClass;
    }

    /**
     * Set workflowLocation.
     *
     * @param string|null $workflowLocation
     *
     * @return WfeWorkflows
     */
    public function setWorkflowLocation($workflowLocation = null)
    {
        $this->workflowLocation = $workflowLocation;

        return $this;
    }

    /**
     * Get workflowLocation.
     *
     * @return string|null
     */
    public function getWorkflowLocation()
    {
        return $this->workflowLocation;
    }

    /**
     * Set subjectType.
     *
     * @param string|null $subjectType
     *
     * @return WfeWorkflows
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
     * @return WfeWorkflows
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
     * @return WfeWorkflows
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
     * @return WfeWorkflows
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
     * Set workflowInstance.
     *
     * @param string|null $workflowInstance
     *
     * @return WfeWorkflows
     */
    public function setWorkflowInstance($workflowInstance = null)
    {
        $this->workflowInstance = $workflowInstance;

        return $this;
    }

    /**
     * Get workflowInstance.
     *
     * @return string|null
     */
    public function getWorkflowInstance()
    {
        return $this->workflowInstance;
    }

    /**
     * Set active.
     *
     * @param int|null $active
     *
     * @return WfeWorkflows
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return int|null
     */
    public function getActive()
    {
        return $this->active;
    }
}
