<?php



/**
 * HelpMap
 */
class HelpMap
{
    /**
     * @var int
     */
    private $chap = '0';

    /**
     * @var string
     */
    private $component = '';

    /**
     * @var string
     */
    private $screenId = '';

    /**
     * @var string
     */
    private $screenSubId = '';

    /**
     * @var string
     */
    private $perm = '';

    /**
     * @var int
     */
    private $moduleId = '0';


    /**
     * Set chap.
     *
     * @param int $chap
     *
     * @return HelpMap
     */
    public function setChap($chap)
    {
        $this->chap = $chap;

        return $this;
    }

    /**
     * Get chap.
     *
     * @return int
     */
    public function getChap()
    {
        return $this->chap;
    }

    /**
     * Set component.
     *
     * @param string $component
     *
     * @return HelpMap
     */
    public function setComponent($component)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set screenId.
     *
     * @param string $screenId
     *
     * @return HelpMap
     */
    public function setScreenId($screenId)
    {
        $this->screenId = $screenId;

        return $this;
    }

    /**
     * Get screenId.
     *
     * @return string
     */
    public function getScreenId()
    {
        return $this->screenId;
    }

    /**
     * Set screenSubId.
     *
     * @param string $screenSubId
     *
     * @return HelpMap
     */
    public function setScreenSubId($screenSubId)
    {
        $this->screenSubId = $screenSubId;

        return $this;
    }

    /**
     * Get screenSubId.
     *
     * @return string
     */
    public function getScreenSubId()
    {
        return $this->screenSubId;
    }

    /**
     * Set perm.
     *
     * @param string $perm
     *
     * @return HelpMap
     */
    public function setPerm($perm)
    {
        $this->perm = $perm;

        return $this;
    }

    /**
     * Get perm.
     *
     * @return string
     */
    public function getPerm()
    {
        return $this->perm;
    }

    /**
     * Set moduleId.
     *
     * @param int $moduleId
     *
     * @return HelpMap
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
