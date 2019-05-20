<?php



/**
 * SvyQstText
 */
class SvyQstText
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int|null
     */
    private $maxchars;

    /**
     * @var int
     */
    private $width = '50';

    /**
     * @var int
     */
    private $height = '5';


    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set maxchars.
     *
     * @param int|null $maxchars
     *
     * @return SvyQstText
     */
    public function setMaxchars($maxchars = null)
    {
        $this->maxchars = $maxchars;

        return $this;
    }

    /**
     * Get maxchars.
     *
     * @return int|null
     */
    public function getMaxchars()
    {
        return $this->maxchars;
    }

    /**
     * Set width.
     *
     * @param int $width
     *
     * @return SvyQstText
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height.
     *
     * @param int $height
     *
     * @return SvyQstText
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }
}
