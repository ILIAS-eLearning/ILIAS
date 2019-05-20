<?php



/**
 * QplQstNumeric
 */
class QplQstNumeric
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $maxnumofchars = '0';


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
     * Set maxnumofchars.
     *
     * @param int $maxnumofchars
     *
     * @return QplQstNumeric
     */
    public function setMaxnumofchars($maxnumofchars)
    {
        $this->maxnumofchars = $maxnumofchars;

        return $this;
    }

    /**
     * Get maxnumofchars.
     *
     * @return int
     */
    public function getMaxnumofchars()
    {
        return $this->maxnumofchars;
    }
}
