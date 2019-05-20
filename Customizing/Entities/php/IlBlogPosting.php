<?php



/**
 * IlBlogPosting
 */
class IlBlogPosting
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $blogId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var \DateTime
     */
    private $created = '1970-01-01 00:00:00';

    /**
     * @var int
     */
    private $author = '0';

    /**
     * @var bool|null
     */
    private $approved = '0';


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
     * Set blogId.
     *
     * @param int $blogId
     *
     * @return IlBlogPosting
     */
    public function setBlogId($blogId)
    {
        $this->blogId = $blogId;

        return $this;
    }

    /**
     * Get blogId.
     *
     * @return int
     */
    public function getBlogId()
    {
        return $this->blogId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlBlogPosting
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return IlBlogPosting
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set author.
     *
     * @param int $author
     *
     * @return IlBlogPosting
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
     * Set approved.
     *
     * @param bool|null $approved
     *
     * @return IlBlogPosting
     */
    public function setApproved($approved = null)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved.
     *
     * @return bool|null
     */
    public function getApproved()
    {
        return $this->approved;
    }
}
