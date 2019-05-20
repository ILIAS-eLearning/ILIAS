<?php



/**
 * Note
 */
class Note
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $repObjId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var int
     */
    private $type = '0';

    /**
     * @var int
     */
    private $author = '0';

    /**
     * @var string|null
     */
    private $noteText;

    /**
     * @var int
     */
    private $label = '0';

    /**
     * @var \DateTime|null
     */
    private $creationDate;

    /**
     * @var \DateTime|null
     */
    private $updateDate;

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var bool|null
     */
    private $noRepository = '0';

    /**
     * @var int
     */
    private $newsId = '0';


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
     * Set repObjId.
     *
     * @param int $repObjId
     *
     * @return Note
     */
    public function setRepObjId($repObjId)
    {
        $this->repObjId = $repObjId;

        return $this;
    }

    /**
     * Get repObjId.
     *
     * @return int
     */
    public function getRepObjId()
    {
        return $this->repObjId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return Note
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
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return Note
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return Note
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set author.
     *
     * @param int $author
     *
     * @return Note
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return int
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set noteText.
     *
     * @param string|null $noteText
     *
     * @return Note
     */
    public function setNoteText($noteText = null)
    {
        $this->noteText = $noteText;

        return $this;
    }

    /**
     * Get noteText.
     *
     * @return string|null
     */
    public function getNoteText()
    {
        return $this->noteText;
    }

    /**
     * Set label.
     *
     * @param int $label
     *
     * @return Note
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return int
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set creationDate.
     *
     * @param \DateTime|null $creationDate
     *
     * @return Note
     */
    public function setCreationDate($creationDate = null)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate.
     *
     * @return \DateTime|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime|null $updateDate
     *
     * @return Note
     */
    public function setUpdateDate($updateDate = null)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate.
     *
     * @return \DateTime|null
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * Set subject.
     *
     * @param string|null $subject
     *
     * @return Note
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set noRepository.
     *
     * @param bool|null $noRepository
     *
     * @return Note
     */
    public function setNoRepository($noRepository = null)
    {
        $this->noRepository = $noRepository;

        return $this;
    }

    /**
     * Get noRepository.
     *
     * @return bool|null
     */
    public function getNoRepository()
    {
        return $this->noRepository;
    }

    /**
     * Set newsId.
     *
     * @param int $newsId
     *
     * @return Note
     */
    public function setNewsId($newsId)
    {
        $this->newsId = $newsId;

        return $this;
    }

    /**
     * Get newsId.
     *
     * @return int
     */
    public function getNewsId()
    {
        return $this->newsId;
    }
}
