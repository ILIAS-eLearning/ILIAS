<?php



/**
 * GlossaryTerm
 */
class GlossaryTerm
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $gloId = '0';

    /**
     * @var string|null
     */
    private $term;

    /**
     * @var string|null
     */
    private $language;

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var \DateTime|null
     */
    private $createDate;

    /**
     * @var \DateTime|null
     */
    private $lastUpdate;


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
     * Set gloId.
     *
     * @param int $gloId
     *
     * @return GlossaryTerm
     */
    public function setGloId($gloId)
    {
        $this->gloId = $gloId;

        return $this;
    }

    /**
     * Get gloId.
     *
     * @return int
     */
    public function getGloId()
    {
        return $this->gloId;
    }

    /**
     * Set term.
     *
     * @param string|null $term
     *
     * @return GlossaryTerm
     */
    public function setTerm($term = null)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Get term.
     *
     * @return string|null
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Set language.
     *
     * @param string|null $language
     *
     * @return GlossaryTerm
     */
    public function setLanguage($language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return GlossaryTerm
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime|null $createDate
     *
     * @return GlossaryTerm
     */
    public function setCreateDate($createDate = null)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime|null
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set lastUpdate.
     *
     * @param \DateTime|null $lastUpdate
     *
     * @return GlossaryTerm
     */
    public function setLastUpdate($lastUpdate = null)
    {
        $this->lastUpdate = $lastUpdate;

        return $this;
    }

    /**
     * Get lastUpdate.
     *
     * @return \DateTime|null
     */
    public function getLastUpdate()
    {
        return $this->lastUpdate;
    }
}
