<?php



/**
 * WfeStartupEvents
 */
class WfeStartupEvents
{
    /**
     * @var int
     */
    private $eventId = '0';

    /**
     * @var string
     */
    private $workflowId = '';

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
     * Get eventId.
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set workflowId.
     *
     * @param string $workflowId
     *
     * @return WfeStartupEvents
     */
    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;

        return $this;
    }

    /**
     * Get workflowId.
     *
     * @return string
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
     * @return WfeStartupEvents
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
     * @return WfeStartupEvents
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
     * @return WfeStartupEvents
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
     * @return WfeStartupEvents
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
     * @return WfeStartupEvents
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
     * @return WfeStartupEvents
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
}
