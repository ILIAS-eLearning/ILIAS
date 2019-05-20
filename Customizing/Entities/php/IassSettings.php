<?php



/**
 * IassSettings
 */
class IassSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $recordTemplate;

    /**
     * @var bool
     */
    private $eventTimePlaceRequired = '0';

    /**
     * @var bool
     */
    private $fileRequired = '0';


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
     * Set content.
     *
     * @param string|null $content
     *
     * @return IassSettings
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
     * Set recordTemplate.
     *
     * @param string|null $recordTemplate
     *
     * @return IassSettings
     */
    public function setRecordTemplate($recordTemplate = null)
    {
        $this->recordTemplate = $recordTemplate;

        return $this;
    }

    /**
     * Get recordTemplate.
     *
     * @return string|null
     */
    public function getRecordTemplate()
    {
        return $this->recordTemplate;
    }

    /**
     * Set eventTimePlaceRequired.
     *
     * @param bool $eventTimePlaceRequired
     *
     * @return IassSettings
     */
    public function setEventTimePlaceRequired($eventTimePlaceRequired)
    {
        $this->eventTimePlaceRequired = $eventTimePlaceRequired;

        return $this;
    }

    /**
     * Get eventTimePlaceRequired.
     *
     * @return bool
     */
    public function getEventTimePlaceRequired()
    {
        return $this->eventTimePlaceRequired;
    }

    /**
     * Set fileRequired.
     *
     * @param bool $fileRequired
     *
     * @return IassSettings
     */
    public function setFileRequired($fileRequired)
    {
        $this->fileRequired = $fileRequired;

        return $this;
    }

    /**
     * Get fileRequired.
     *
     * @return bool
     */
    public function getFileRequired()
    {
        return $this->fileRequired;
    }
}
