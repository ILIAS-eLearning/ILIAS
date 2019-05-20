<?php



/**
 * TosDocuments
 */
class TosDocuments
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var int
     */
    private $creationTs = '0';

    /**
     * @var int
     */
    private $modificationTs = '0';

    /**
     * @var int
     */
    private $sorting = '0';

    /**
     * @var int
     */
    private $ownerUsrId = '0';

    /**
     * @var int
     */
    private $lastModifiedUsrId = '0';

    /**
     * @var string|null
     */
    private $text;


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
     * Set title.
     *
     * @param string|null $title
     *
     * @return TosDocuments
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
     * Set creationTs.
     *
     * @param int $creationTs
     *
     * @return TosDocuments
     */
    public function setCreationTs($creationTs)
    {
        $this->creationTs = $creationTs;

        return $this;
    }

    /**
     * Get creationTs.
     *
     * @return int
     */
    public function getCreationTs()
    {
        return $this->creationTs;
    }

    /**
     * Set modificationTs.
     *
     * @param int $modificationTs
     *
     * @return TosDocuments
     */
    public function setModificationTs($modificationTs)
    {
        $this->modificationTs = $modificationTs;

        return $this;
    }

    /**
     * Get modificationTs.
     *
     * @return int
     */
    public function getModificationTs()
    {
        return $this->modificationTs;
    }

    /**
     * Set sorting.
     *
     * @param int $sorting
     *
     * @return TosDocuments
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting.
     *
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Set ownerUsrId.
     *
     * @param int $ownerUsrId
     *
     * @return TosDocuments
     */
    public function setOwnerUsrId($ownerUsrId)
    {
        $this->ownerUsrId = $ownerUsrId;

        return $this;
    }

    /**
     * Get ownerUsrId.
     *
     * @return int
     */
    public function getOwnerUsrId()
    {
        return $this->ownerUsrId;
    }

    /**
     * Set lastModifiedUsrId.
     *
     * @param int $lastModifiedUsrId
     *
     * @return TosDocuments
     */
    public function setLastModifiedUsrId($lastModifiedUsrId)
    {
        $this->lastModifiedUsrId = $lastModifiedUsrId;

        return $this;
    }

    /**
     * Get lastModifiedUsrId.
     *
     * @return int
     */
    public function getLastModifiedUsrId()
    {
        return $this->lastModifiedUsrId;
    }

    /**
     * Set text.
     *
     * @param string|null $text
     *
     * @return TosDocuments
     */
    public function setText($text = null)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }
}
