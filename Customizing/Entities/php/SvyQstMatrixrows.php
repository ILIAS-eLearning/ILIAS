<?php



/**
 * SvyQstMatrixrows
 */
class SvyQstMatrixrows
{
    /**
     * @var int
     */
    private $idSvyQstMatrixrows = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var int
     */
    private $sequence = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var bool
     */
    private $other = '0';

    /**
     * @var string|null
     */
    private $label;


    /**
     * Get idSvyQstMatrixrows.
     *
     * @return int
     */
    public function getIdSvyQstMatrixrows()
    {
        return $this->idSvyQstMatrixrows;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return SvyQstMatrixrows
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set sequence.
     *
     * @param int $sequence
     *
     * @return SvyQstMatrixrows
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence.
     *
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyQstMatrixrows
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

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
     * Set other.
     *
     * @param bool $other
     *
     * @return SvyQstMatrixrows
     */
    public function setOther($other)
    {
        $this->other = $other;

        return $this;
    }

    /**
     * Get other.
     *
     * @return bool
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * Set label.
     *
     * @param string|null $label
     *
     * @return SvyQstMatrixrows
     */
    public function setLabel($label = null)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }
}
