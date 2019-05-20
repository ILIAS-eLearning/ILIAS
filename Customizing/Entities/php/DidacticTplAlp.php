<?php



/**
 * DidacticTplAlp
 */
class DidacticTplAlp
{
    /**
     * @var int
     */
    private $actionId = '0';

    /**
     * @var bool
     */
    private $filterType = '0';

    /**
     * @var bool
     */
    private $templateType = '0';

    /**
     * @var int
     */
    private $templateId = '0';


    /**
     * Get actionId.
     *
     * @return int
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * Set filterType.
     *
     * @param bool $filterType
     *
     * @return DidacticTplAlp
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;

        return $this;
    }

    /**
     * Get filterType.
     *
     * @return bool
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * Set templateType.
     *
     * @param bool $templateType
     *
     * @return DidacticTplAlp
     */
    public function setTemplateType($templateType)
    {
        $this->templateType = $templateType;

        return $this;
    }

    /**
     * Get templateType.
     *
     * @return bool
     */
    public function getTemplateType()
    {
        return $this->templateType;
    }

    /**
     * Set templateId.
     *
     * @param int $templateId
     *
     * @return DidacticTplAlp
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId.
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }
}
