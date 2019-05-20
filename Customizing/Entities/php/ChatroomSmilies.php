<?php



/**
 * ChatroomSmilies
 */
class ChatroomSmilies
{
    /**
     * @var int
     */
    private $smileyId = '0';

    /**
     * @var string|null
     */
    private $smileyKeywords;

    /**
     * @var string|null
     */
    private $smileyPath;


    /**
     * Get smileyId.
     *
     * @return int
     */
    public function getSmileyId()
    {
        return $this->smileyId;
    }

    /**
     * Set smileyKeywords.
     *
     * @param string|null $smileyKeywords
     *
     * @return ChatroomSmilies
     */
    public function setSmileyKeywords($smileyKeywords = null)
    {
        $this->smileyKeywords = $smileyKeywords;

        return $this;
    }

    /**
     * Get smileyKeywords.
     *
     * @return string|null
     */
    public function getSmileyKeywords()
    {
        return $this->smileyKeywords;
    }

    /**
     * Set smileyPath.
     *
     * @param string|null $smileyPath
     *
     * @return ChatroomSmilies
     */
    public function setSmileyPath($smileyPath = null)
    {
        $this->smileyPath = $smileyPath;

        return $this;
    }

    /**
     * Get smileyPath.
     *
     * @return string|null
     */
    public function getSmileyPath()
    {
        return $this->smileyPath;
    }
}
