<?php



/**
 * StyleTemplate
 */
class StyleTemplate
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $styleId = '0';

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string|null
     */
    private $preview;

    /**
     * @var string|null
     */
    private $tempType;


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
     * Set styleId.
     *
     * @param int $styleId
     *
     * @return StyleTemplate
     */
    public function setStyleId($styleId)
    {
        $this->styleId = $styleId;

        return $this;
    }

    /**
     * Get styleId.
     *
     * @return int
     */
    public function getStyleId()
    {
        return $this->styleId;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return StyleTemplate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set preview.
     *
     * @param string|null $preview
     *
     * @return StyleTemplate
     */
    public function setPreview($preview = null)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview.
     *
     * @return string|null
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Set tempType.
     *
     * @param string|null $tempType
     *
     * @return StyleTemplate
     */
    public function setTempType($tempType = null)
    {
        $this->tempType = $tempType;

        return $this;
    }

    /**
     * Get tempType.
     *
     * @return string|null
     */
    public function getTempType()
    {
        return $this->tempType;
    }
}
