<?php



/**
 * SvyQstMetric
 */
class SvyQstMetric
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $subtype = '3';


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
     * Set subtype.
     *
     * @param string|null $subtype
     *
     * @return SvyQstMetric
     */
    public function setSubtype($subtype = null)
    {
        $this->subtype = $subtype;

        return $this;
    }

    /**
     * Get subtype.
     *
     * @return string|null
     */
    public function getSubtype()
    {
        return $this->subtype;
    }
}
