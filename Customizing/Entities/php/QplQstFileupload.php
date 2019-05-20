<?php



/**
 * QplQstFileupload
 */
class QplQstFileupload
{
    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var string|null
     */
    private $allowedextensions;

    /**
     * @var float|null
     */
    private $maxsize;

    /**
     * @var bool
     */
    private $complBySubmission = '0';


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
     * Set allowedextensions.
     *
     * @param string|null $allowedextensions
     *
     * @return QplQstFileupload
     */
    public function setAllowedextensions($allowedextensions = null)
    {
        $this->allowedextensions = $allowedextensions;

        return $this;
    }

    /**
     * Get allowedextensions.
     *
     * @return string|null
     */
    public function getAllowedextensions()
    {
        return $this->allowedextensions;
    }

    /**
     * Set maxsize.
     *
     * @param float|null $maxsize
     *
     * @return QplQstFileupload
     */
    public function setMaxsize($maxsize = null)
    {
        $this->maxsize = $maxsize;

        return $this;
    }

    /**
     * Get maxsize.
     *
     * @return float|null
     */
    public function getMaxsize()
    {
        return $this->maxsize;
    }

    /**
     * Set complBySubmission.
     *
     * @param bool $complBySubmission
     *
     * @return QplQstFileupload
     */
    public function setComplBySubmission($complBySubmission)
    {
        $this->complBySubmission = $complBySubmission;

        return $this;
    }

    /**
     * Get complBySubmission.
     *
     * @return bool
     */
    public function getComplBySubmission()
    {
        return $this->complBySubmission;
    }
}
