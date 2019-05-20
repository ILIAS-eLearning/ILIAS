<?php



/**
 * AdmSetTemplValue
 */
class AdmSetTemplValue
{
    /**
     * @var int
     */
    private $templateId = '0';

    /**
     * @var string
     */
    private $setting = '';

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var bool|null
     */
    private $hide;


    /**
     * Set templateId.
     *
     * @param int $templateId
     *
     * @return AdmSetTemplValue
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
     * Set setting.
     *
     * @param string $setting
     *
     * @return AdmSetTemplValue
     */
    public function setSetting($setting)
    {
        $this->setting = $setting;

        return $this;
    }

    /**
     * Get setting.
     *
     * @return string
     */
    public function getSetting()
    {
        return $this->setting;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return AdmSetTemplValue
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set hide.
     *
     * @param bool|null $hide
     *
     * @return AdmSetTemplValue
     */
    public function setHide($hide = null)
    {
        $this->hide = $hide;

        return $this;
    }

    /**
     * Get hide.
     *
     * @return bool|null
     */
    public function getHide()
    {
        return $this->hide;
    }
}
