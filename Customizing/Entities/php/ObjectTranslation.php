<?php



/**
 * ObjectTranslation
 */
class ObjectTranslation
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string
     */
    private $langCode = '';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var bool
     */
    private $langDefault = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjectTranslation
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

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
     * Set langCode.
     *
     * @param string $langCode
     *
     * @return ObjectTranslation
     */
    public function setLangCode($langCode)
    {
        $this->langCode = $langCode;

        return $this;
    }

    /**
     * Get langCode.
     *
     * @return string
     */
    public function getLangCode()
    {
        return $this->langCode;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return ObjectTranslation
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
     * Set description.
     *
     * @param string|null $description
     *
     * @return ObjectTranslation
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set langDefault.
     *
     * @param bool $langDefault
     *
     * @return ObjectTranslation
     */
    public function setLangDefault($langDefault)
    {
        $this->langDefault = $langDefault;

        return $this;
    }

    /**
     * Get langDefault.
     *
     * @return bool
     */
    public function getLangDefault()
    {
        return $this->langDefault;
    }
}
