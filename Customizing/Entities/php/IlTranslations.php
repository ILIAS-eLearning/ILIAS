<?php



/**
 * IlTranslations
 */
class IlTranslations
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $idType = '';

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
     * Set id.
     *
     * @param int $id
     *
     * @return IlTranslations
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set idType.
     *
     * @param string $idType
     *
     * @return IlTranslations
     */
    public function setIdType($idType)
    {
        $this->idType = $idType;

        return $this;
    }

    /**
     * Get idType.
     *
     * @return string
     */
    public function getIdType()
    {
        return $this->idType;
    }

    /**
     * Set langCode.
     *
     * @param string $langCode
     *
     * @return IlTranslations
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
     * @return IlTranslations
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
     * @return IlTranslations
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
     * @return IlTranslations
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
