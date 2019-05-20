<?php



/**
 * IlWikiMissingPage
 */
class IlWikiMissingPage
{
    /**
     * @var int
     */
    private $wikiId = '0';

    /**
     * @var int
     */
    private $sourceId = '0';

    /**
     * @var string
     */
    private $targetName = '';


    /**
     * Set wikiId.
     *
     * @param int $wikiId
     *
     * @return IlWikiMissingPage
     */
    public function setWikiId($wikiId)
    {
        $this->wikiId = $wikiId;

        return $this;
    }

    /**
     * Get wikiId.
     *
     * @return int
     */
    public function getWikiId()
    {
        return $this->wikiId;
    }

    /**
     * Set sourceId.
     *
     * @param int $sourceId
     *
     * @return IlWikiMissingPage
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * Get sourceId.
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * Set targetName.
     *
     * @param string $targetName
     *
     * @return IlWikiMissingPage
     */
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;

        return $this;
    }

    /**
     * Get targetName.
     *
     * @return string
     */
    public function getTargetName()
    {
        return $this->targetName;
    }
}
