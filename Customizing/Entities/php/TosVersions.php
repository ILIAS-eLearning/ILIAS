<?php



/**
 * TosVersions
 */
class TosVersions
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $text;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var int
     */
    private $ts = '0';

    /**
     * @var int
     */
    private $docId = '0';

    /**
     * @var string|null
     */
    private $title;


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
     * Set text.
     *
     * @param string|null $text
     *
     * @return TosVersions
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

    /**
     * Set hash.
     *
     * @param string|null $hash
     *
     * @return TosVersions
     */
    public function setHash($hash = null)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set ts.
     *
     * @param int $ts
     *
     * @return TosVersions
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts.
     *
     * @return int
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set docId.
     *
     * @param int $docId
     *
     * @return TosVersions
     */
    public function setDocId($docId)
    {
        $this->docId = $docId;

        return $this;
    }

    /**
     * Get docId.
     *
     * @return int
     */
    public function getDocId()
    {
        return $this->docId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return TosVersions
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
}
