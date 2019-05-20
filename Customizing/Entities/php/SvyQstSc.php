<?php



/**
 * SvyQstSc
 */
class SvyQstSc
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $orientation = '0';


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
     * Set orientation.
     *
     * @param string|null $orientation
     *
     * @return SvyQstSc
     */
    public function setOrientation($orientation = null)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get orientation.
     *
     * @return string|null
     */
    public function getOrientation()
    {
        return $this->orientation;
    }
}
