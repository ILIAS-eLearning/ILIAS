<?php



/**
 * XhtmlPage
 */
class XhtmlPage
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $saveContent;


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
     * Set content.
     *
     * @param string|null $content
     *
     * @return XhtmlPage
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set saveContent.
     *
     * @param string|null $saveContent
     *
     * @return XhtmlPage
     */
    public function setSaveContent($saveContent = null)
    {
        $this->saveContent = $saveContent;

        return $this;
    }

    /**
     * Get saveContent.
     *
     * @return string|null
     */
    public function getSaveContent()
    {
        return $this->saveContent;
    }
}
