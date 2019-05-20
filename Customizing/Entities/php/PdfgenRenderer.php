<?php



/**
 * PdfgenRenderer
 */
class PdfgenRenderer
{
    /**
     * @var int
     */
    private $rendererId = '0';

    /**
     * @var string
     */
    private $renderer = '';

    /**
     * @var string
     */
    private $path = '';


    /**
     * Get rendererId.
     *
     * @return int
     */
    public function getRendererId()
    {
        return $this->rendererId;
    }

    /**
     * Set renderer.
     *
     * @param string $renderer
     *
     * @return PdfgenRenderer
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Get renderer.
     *
     * @return string
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Set path.
     *
     * @param string $path
     *
     * @return PdfgenRenderer
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
