<?php



/**
 * SvySettings
 */
class SvySettings
{
    /**
     * @var int
     */
    private $settingsId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $keyword = '';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $value;


    /**
     * Get settingsId.
     *
     * @return int
     */
    public function getSettingsId()
    {
        return $this->settingsId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return SvySettings
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set keyword.
     *
     * @param string $keyword
     *
     * @return SvySettings
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword.
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SvySettings
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return SvySettings
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
}
