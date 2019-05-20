<?php



/**
 * IlMmTranslation
 */
class IlMmTranslation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $identification;

    /**
     * @var string|null
     */
    private $translation;

    /**
     * @var string|null
     */
    private $languageKey;


    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set identification.
     *
     * @param string|null $identification
     *
     * @return IlMmTranslation
     */
    public function setIdentification($identification = null)
    {
        $this->identification = $identification;

        return $this;
    }

    /**
     * Get identification.
     *
     * @return string|null
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set translation.
     *
     * @param string|null $translation
     *
     * @return IlMmTranslation
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
     * Set languageKey.
     *
     * @param string|null $languageKey
     *
     * @return IlMmTranslation
     */
    public function setLanguageKey($languageKey = null)
    {
        $this->languageKey = $languageKey;

        return $this;
    }

    /**
     * Get languageKey.
     *
     * @return string|null
     */
    public function getLanguageKey()
    {
        return $this->languageKey;
    }
}
