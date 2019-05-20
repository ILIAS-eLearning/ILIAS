<?php



/**
 * HelpTooltip
 */
class HelpTooltip
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $ttText;

    /**
     * @var string
     */
    private $ttId = '';

    /**
     * @var string
     */
    private $comp = '';

    /**
     * @var string
     */
    private $lang = 'de';

    /**
     * @var int
     */
    private $moduleId = '0';


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
     * Set ttText.
     *
     * @param string|null $ttText
     *
     * @return HelpTooltip
     */
    public function setTtText($ttText = null)
    {
        $this->ttText = $ttText;

        return $this;
    }

    /**
     * Get ttText.
     *
     * @return string|null
     */
    public function getTtText()
    {
        return $this->ttText;
    }

    /**
     * Set ttId.
     *
     * @param string $ttId
     *
     * @return HelpTooltip
     */
    public function setTtId($ttId)
    {
        $this->ttId = $ttId;

        return $this;
    }

    /**
     * Get ttId.
     *
     * @return string
     */
    public function getTtId()
    {
        return $this->ttId;
    }

    /**
     * Set comp.
     *
     * @param string $comp
     *
     * @return HelpTooltip
     */
    public function setComp($comp)
    {
        $this->comp = $comp;

        return $this;
    }

    /**
     * Get comp.
     *
     * @return string
     */
    public function getComp()
    {
        return $this->comp;
    }

    /**
     * Set lang.
     *
     * @param string $lang
     *
     * @return HelpTooltip
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang.
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set moduleId.
     *
     * @param int $moduleId
     *
     * @return HelpTooltip
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * Get moduleId.
     *
     * @return int
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }
}
