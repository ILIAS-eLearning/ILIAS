<?php



/**
 * SvyQuestion
 */
class SvyQuestion
{
    /**
     * @var int
     */
    private $questionId = '0';

    /**
     * @var int
     */
    private $questiontypeFi = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var int
     */
    private $ownerFi = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $author;

    /**
     * @var string|null
     */
    private $obligatory = '1';

    /**
     * @var string|null
     */
    private $complete = '0';

    /**
     * @var string|null
     */
    private $created;

    /**
     * @var int|null
     */
    private $originalId;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $questiontext;

    /**
     * @var string|null
     */
    private $label;


    /**
     * Get questionId.
     *
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * Set questiontypeFi.
     *
     * @param int $questiontypeFi
     *
     * @return SvyQuestion
     */
    public function setQuestiontypeFi($questiontypeFi)
    {
        $this->questiontypeFi = $questiontypeFi;

        return $this;
    }

    /**
     * Get questiontypeFi.
     *
     * @return int
     */
    public function getQuestiontypeFi()
    {
        return $this->questiontypeFi;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return SvyQuestion
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set ownerFi.
     *
     * @param int $ownerFi
     *
     * @return SvyQuestion
     */
    public function setOwnerFi($ownerFi)
    {
        $this->ownerFi = $ownerFi;

        return $this;
    }

    /**
     * Get ownerFi.
     *
     * @return int
     */
    public function getOwnerFi()
    {
        return $this->ownerFi;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SvyQuestion
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
     * @return SvyQuestion
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
     * Set author.
     *
     * @param string|null $author
     *
     * @return SvyQuestion
     */
    public function setAuthor($author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set obligatory.
     *
     * @param string|null $obligatory
     *
     * @return SvyQuestion
     */
    public function setObligatory($obligatory = null)
    {
        $this->obligatory = $obligatory;

        return $this;
    }

    /**
     * Get obligatory.
     *
     * @return string|null
     */
    public function getObligatory()
    {
        return $this->obligatory;
    }

    /**
     * Set complete.
     *
     * @param string|null $complete
     *
     * @return SvyQuestion
     */
    public function setComplete($complete = null)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete.
     *
     * @return string|null
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set created.
     *
     * @param string|null $created
     *
     * @return SvyQuestion
     */
    public function setCreated($created = null)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return string|null
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set originalId.
     *
     * @param int|null $originalId
     *
     * @return SvyQuestion
     */
    public function setOriginalId($originalId = null)
    {
        $this->originalId = $originalId;

        return $this;
    }

    /**
     * Get originalId.
     *
     * @return int|null
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyQuestion
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set questiontext.
     *
     * @param string|null $questiontext
     *
     * @return SvyQuestion
     */
    public function setQuestiontext($questiontext = null)
    {
        $this->questiontext = $questiontext;

        return $this;
    }

    /**
     * Get questiontext.
     *
     * @return string|null
     */
    public function getQuestiontext()
    {
        return $this->questiontext;
    }

    /**
     * Set label.
     *
     * @param string|null $label
     *
     * @return SvyQuestion
     */
    public function setLabel($label = null)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }
}
