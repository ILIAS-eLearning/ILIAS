<?php



/**
 * SvyPhrase
 */
class SvyPhrase
{
    /**
     * @var int
     */
    private $phraseId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $defaultvalue = '0';

    /**
     * @var int
     */
    private $ownerFi = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get phraseId.
     *
     * @return int
     */
    public function getPhraseId()
    {
        return $this->phraseId;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SvyPhrase
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
     * Set defaultvalue.
     *
     * @param string|null $defaultvalue
     *
     * @return SvyPhrase
     */
    public function setDefaultvalue($defaultvalue = null)
    {
        $this->defaultvalue = $defaultvalue;

        return $this;
    }

    /**
     * Get defaultvalue.
     *
     * @return string|null
     */
    public function getDefaultvalue()
    {
        return $this->defaultvalue;
    }

    /**
     * Set ownerFi.
     *
     * @param int $ownerFi
     *
     * @return SvyPhrase
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
     * @return SvyPhrase
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
}
