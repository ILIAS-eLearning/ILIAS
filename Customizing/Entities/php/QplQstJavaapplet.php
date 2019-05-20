<?php



/**
 * QplQstJavaapplet
 */
class QplQstJavaapplet
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $imageFile;

    /**
     * @var string|null
     */
    private $params;


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
     * Set imageFile.
     *
     * @param string|null $imageFile
     *
     * @return QplQstJavaapplet
     */
    public function setImageFile($imageFile = null)
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    /**
     * Get imageFile.
     *
     * @return string|null
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set params.
     *
     * @param string|null $params
     *
     * @return QplQstJavaapplet
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
}
