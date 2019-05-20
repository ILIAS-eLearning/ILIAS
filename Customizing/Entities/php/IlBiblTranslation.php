<?php



/**
 * IlBiblTranslation
 */
class IlBiblTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $fieldId;

    /**
     * @var string
     */
    private $languageKey;

    /**
     * @var string|null
     */
    private $translation;

    /**
     * @var string|null
     */
    private $description;


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
     * Set fieldId.
     *
     * @param int $fieldId
     *
     * @return IlBiblTranslation
     */
    public function setFieldId($fieldId)
    {
        $this->fieldId = $fieldId;

        return $this;
    }

    /**
     * Get fieldId.
     *
     * @return int
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }

    /**
     * Set languageKey.
     *
     * @param string $languageKey
     *
     * @return IlBiblTranslation
     */
    public function setLanguageKey($languageKey)
    {
        $this->languageKey = $languageKey;

        return $this;
    }

    /**
     * Get languageKey.
     *
     * @return string
     */
    public function getLanguageKey()
    {
        return $this->languageKey;
    }

    /**
     * Set translation.
     *
     * @param string|null $translation
     *
     * @return IlBiblTranslation
     */
    public function setTranslation($translation = null)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get translation.
     *
     * @return string|null
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlBiblTranslation
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
}
