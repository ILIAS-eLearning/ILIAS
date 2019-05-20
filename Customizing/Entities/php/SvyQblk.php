<?php



/**
 * SvyQblk
 */
class SvyQblk
{
    /**
     * @var int
     */
    private $questionblockId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $showQuestiontext = '1';

    /**
     * @var int
     */
    private $ownerFi = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $showBlocktitle;


    /**
     * Get questionblockId.
     *
     * @return int
     */
    public function getQuestionblockId()
    {
        return $this->questionblockId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SvyQblk
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
     * Set showQuestiontext.
     *
     * @param string|null $showQuestiontext
     *
     * @return SvyQblk
     */
    public function setShowQuestiontext($showQuestiontext = null)
    {
        $this->showQuestiontext = $showQuestiontext;

        return $this;
    }

    /**
     * Get showQuestiontext.
     *
     * @return string|null
     */
    public function getShowQuestiontext()
    {
        return $this->showQuestiontext;
    }

    /**
     * Set ownerFi.
     *
     * @param int $ownerFi
     *
     * @return SvyQblk
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return SvyQblk
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
     * Set showBlocktitle.
     *
     * @param string|null $showBlocktitle
     *
     * @return SvyQblk
     */
    public function setShowBlocktitle($showBlocktitle = null)
    {
        $this->showBlocktitle = $showBlocktitle;

        return $this;
    }

    /**
     * Get showBlocktitle.
     *
     * @return string|null
     */
    public function getShowBlocktitle()
    {
        return $this->showBlocktitle;
    }
}
