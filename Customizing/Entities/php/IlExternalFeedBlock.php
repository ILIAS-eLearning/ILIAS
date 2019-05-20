<?php



/**
 * IlExternalFeedBlock
 */
class IlExternalFeedBlock
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $feedUrl;


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
     * Set feedUrl.
     *
     * @param string|null $feedUrl
     *
     * @return IlExternalFeedBlock
     */
    public function setFeedUrl($feedUrl = null)
    {
        $this->feedUrl = $feedUrl;

        return $this;
    }

    /**
     * Get feedUrl.
     *
     * @return string|null
     */
    public function getFeedUrl()
    {
        return $this->feedUrl;
    }
}
