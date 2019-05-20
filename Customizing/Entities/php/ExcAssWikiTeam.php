<?php



/**
 * ExcAssWikiTeam
 */
class ExcAssWikiTeam
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $containerRefId = '0';

    /**
     * @var int
     */
    private $templateRefId = '0';


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
     * Set containerRefId.
     *
     * @param int $containerRefId
     *
     * @return ExcAssWikiTeam
     */
    public function setContainerRefId($containerRefId)
    {
        $this->containerRefId = $containerRefId;

        return $this;
    }

    /**
     * Get containerRefId.
     *
     * @return int
     */
    public function getContainerRefId()
    {
        return $this->containerRefId;
    }

    /**
     * Set templateRefId.
     *
     * @param int $templateRefId
     *
     * @return ExcAssWikiTeam
     */
    public function setTemplateRefId($templateRefId)
    {
        $this->templateRefId = $templateRefId;

        return $this;
    }

    /**
     * Get templateRefId.
     *
     * @return int
     */
    public function getTemplateRefId()
    {
        return $this->templateRefId;
    }
}
