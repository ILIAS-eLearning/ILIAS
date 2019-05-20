<?php



/**
 * ChatroomProoms
 */
class ChatroomProoms
{
    /**
     * @var int
     */
    private $proomId = '0';

    /**
     * @var int
     */
    private $parentId = '0';

    /**
     * @var string
     */
    private $title = '0';

    /**
     * @var int
     */
    private $owner = '0';

    /**
     * @var int
     */
    private $created = '0';

    /**
     * @var int|null
     */
    private $closed = '0';

    /**
     * @var bool|null
     */
    private $isPublic = '1';


    /**
     * Get proomId.
     *
     * @return int
     */
    public function getProomId()
    {
        return $this->proomId;
    }

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return ChatroomProoms
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return ChatroomProoms
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set owner.
     *
     * @param int $owner
     *
     * @return ChatroomProoms
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return int
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set created.
     *
     * @param int $created
     *
     * @return ChatroomProoms
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set closed.
     *
     * @param int|null $closed
     *
     * @return ChatroomProoms
     */
    public function setClosed($closed = null)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed.
     *
     * @return int|null
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Set isPublic.
     *
     * @param bool|null $isPublic
     *
     * @return ChatroomProoms
     */
    public function setIsPublic($isPublic = null)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic.
     *
     * @return bool|null
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }
}
