<?php



/**
 * QplAMterm
 */
class QplAMterm
{
    /**
     * @var int
     */
    private $termId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $term;

    /**
     * @var string|null
     */
    private $picture;

    /**
     * @var int|null
     */
    private $ident;


    /**
     * Get termId.
     *
     * @return int
     */
    public function getTermId()
    {
        return $this->termId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return QplAMterm
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
     * Set term.
     *
     * @param string|null $term
     *
     * @return QplAMterm
     */
    public function setTerm($term = null)
    {
        $this->term = $term;

        return $this;
    }

    /**
     * Get term.
     *
     * @return string|null
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Set picture.
     *
     * @param string|null $picture
     *
     * @return QplAMterm
     */
    public function setPicture($picture = null)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture.
     *
     * @return string|null
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set ident.
     *
     * @param int|null $ident
     *
     * @return QplAMterm
     */
    public function setIdent($ident = null)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Get ident.
     *
     * @return int|null
     */
    public function getIdent()
    {
        return $this->ident;
    }
}
