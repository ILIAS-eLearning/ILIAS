<?php



/**
 * ContentPageData
 */
class ContentPageData
{
    /**
     * @var int
     */
    private $contentPageId = '0';

    /**
     * @var int
     */
    private $stylesheet = '0';


    /**
     * Get contentPageId.
     *
     * @return int
     */
    public function getContentPageId()
    {
        return $this->contentPageId;
    }

    /**
     * Set stylesheet.
     *
     * @param int $stylesheet
     *
     * @return ContentPageData
     */
    public function setStylesheet($stylesheet)
    {
        $this->stylesheet = $stylesheet;

        return $this;
    }

    /**
     * Get stylesheet.
     *
     * @return int
     */
    public function getStylesheet()
    {
        return $this->stylesheet;
    }
}
