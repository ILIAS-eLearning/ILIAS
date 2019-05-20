<?php



/**
 * AdmSetTemplHideTab
 */
class AdmSetTemplHideTab
{
    /**
     * @var int
     */
    private $templateId = '0';

    /**
     * @var string
     */
    private $tabId = '';


    /**
     * Set templateId.
     *
     * @param int $templateId
     *
     * @return AdmSetTemplHideTab
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

    /**
     * Set tabId.
     *
     * @param string $tabId
     *
     * @return AdmSetTemplHideTab
     */
    public function setTabId($tabId)
    {
        $this->tabId = $tabId;

        return $this;
    }

    /**
     * Get tabId.
     *
     * @return string
     */
    public function getTabId()
    {
        return $this->tabId;
    }
}
