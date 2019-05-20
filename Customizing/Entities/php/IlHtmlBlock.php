<?php



/**
 * IlHtmlBlock
 */
class IlHtmlBlock
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
     * @return IlHtmlBlock
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
}
