<?php



/**
 * QplQstFlash
 */
class QplQstFlash
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $params;

    /**
     * @var string|null
     */
    private $applet;

    /**
     * @var int
     */
    private $width = '550';

    /**
     * @var int
     */
    private $height = '400';


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
     * Set params.
     *
     * @param string|null $params
     *
     * @return QplQstFlash
     */
    public function setParams($params = null)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params.
     *
     * @return string|null
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set applet.
     *
     * @param string|null $applet
     *
     * @return QplQstFlash
     */
    public function setApplet($applet = null)
    {
        $this->applet = $applet;

        return $this;
    }

    /**
     * Get applet.
     *
     * @return string|null
     */
    public function getApplet()
    {
        return $this->applet;
    }

    /**
     * Set width.
     *
     * @param int $width
     *
     * @return QplQstFlash
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
     * @return QplQstFlash
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
