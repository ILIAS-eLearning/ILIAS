<?php



/**
 * QplQstMatching
 */
class QplQstMatching
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $shuffle = '1';

    /**
     * @var string|null
     */
    private $matchingType = '1';

    /**
     * @var int
     */
    private $thumbGeometry = '100';

    /**
     * @var string|null
     */
    private $matchingMode;


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
     * Set shuffle.
     *
     * @param string|null $shuffle
     *
     * @return QplQstMatching
     */
    public function setShuffle($shuffle = null)
    {
        $this->shuffle = $shuffle;

        return $this;
    }

    /**
     * Get shuffle.
     *
     * @return string|null
     */
    public function getShuffle()
    {
        return $this->shuffle;
    }

    /**
     * Set matchingType.
     *
     * @param string|null $matchingType
     *
     * @return QplQstMatching
     */
    public function setMatchingType($matchingType = null)
    {
        $this->matchingType = $matchingType;

        return $this;
    }

    /**
     * Get matchingType.
     *
     * @return string|null
     */
    public function getMatchingType()
    {
        return $this->matchingType;
    }

    /**
     * Set thumbGeometry.
     *
     * @param int $thumbGeometry
     *
     * @return QplQstMatching
     */
    public function setThumbGeometry($thumbGeometry)
    {
        $this->thumbGeometry = $thumbGeometry;

        return $this;
    }

    /**
     * Get thumbGeometry.
     *
     * @return int
     */
    public function getThumbGeometry()
    {
        return $this->thumbGeometry;
    }

    /**
     * Set matchingMode.
     *
     * @param string|null $matchingMode
     *
     * @return QplQstMatching
     */
    public function setMatchingMode($matchingMode = null)
    {
        $this->matchingMode = $matchingMode;

        return $this;
    }

    /**
     * Get matchingMode.
     *
     * @return string|null
     */
    public function getMatchingMode()
    {
        return $this->matchingMode;
    }
}
