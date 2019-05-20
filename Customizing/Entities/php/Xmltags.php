<?php



/**
 * Xmltags
 */
class Xmltags
{
    /**
     * @var int
     */
    private $tagPk = '0';

    /**
     * @var int
     */
    private $tagDepth = '0';

    /**
     * @var string|null
     */
    private $tagName;


    /**
     * Get tagPk.
     *
     * @return int
     */
    public function getTagPk()
    {
        return $this->tagPk;
    }

    /**
     * Set tagDepth.
     *
     * @param int $tagDepth
     *
     * @return Xmltags
     */
    public function setTagDepth($tagDepth)
    {
        $this->tagDepth = $tagDepth;

        return $this;
    }

    /**
     * Get tagDepth.
     *
     * @return int
     */
    public function getTagDepth()
    {
        return $this->tagDepth;
    }

    /**
     * Set tagName.
     *
     * @param string|null $tagName
     *
     * @return Xmltags
     */
    public function setTagName($tagName = null)
    {
        $this->tagName = $tagName;

        return $this;
    }

    /**
     * Get tagName.
     *
     * @return string|null
     */
    public function getTagName()
    {
        return $this->tagName;
    }
}
