<?php



/**
 * ExcData
 */
class ExcData
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $instruction;

    /**
     * @var int|null
     */
    private $timeStamp;

    /**
     * @var string
     */
    private $passMode = 'all';

    /**
     * @var int|null
     */
    private $passNr;

    /**
     * @var bool
     */
    private $showSubmissions = '0';

    /**
     * @var bool
     */
    private $complBySubmission = '0';

    /**
     * @var bool
     */
    private $certificateVisibility = '0';

    /**
     * @var bool
     */
    private $tfeedback = '7';


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set instruction.
     *
     * @param string|null $instruction
     *
     * @return ExcData
     */
    public function setInstruction($instruction = null)
    {
        $this->instruction = $instruction;

        return $this;
    }

    /**
     * Get instruction.
     *
     * @return string|null
     */
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * Set timeStamp.
     *
     * @param int|null $timeStamp
     *
     * @return ExcData
     */
    public function setTimeStamp($timeStamp = null)
    {
        $this->timeStamp = $timeStamp;

        return $this;
    }

    /**
     * Get timeStamp.
     *
     * @return int|null
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * Set passMode.
     *
     * @param string $passMode
     *
     * @return ExcData
     */
    public function setPassMode($passMode)
    {
        $this->passMode = $passMode;

        return $this;
    }

    /**
     * Get passMode.
     *
     * @return string
     */
    public function getPassMode()
    {
        return $this->passMode;
    }

    /**
     * Set passNr.
     *
     * @param int|null $passNr
     *
     * @return ExcData
     */
    public function setPassNr($passNr = null)
    {
        $this->passNr = $passNr;

        return $this;
    }

    /**
     * Get passNr.
     *
     * @return int|null
     */
    public function getPassNr()
    {
        return $this->passNr;
    }

    /**
     * Set showSubmissions.
     *
     * @param bool $showSubmissions
     *
     * @return ExcData
     */
    public function setShowSubmissions($showSubmissions)
    {
        $this->showSubmissions = $showSubmissions;

        return $this;
    }

    /**
     * Get showSubmissions.
     *
     * @return bool
     */
    public function getShowSubmissions()
    {
        return $this->showSubmissions;
    }

    /**
     * Set complBySubmission.
     *
     * @param bool $complBySubmission
     *
     * @return ExcData
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

    /**
     * Set certificateVisibility.
     *
     * @param bool $certificateVisibility
     *
     * @return ExcData
     */
    public function setCertificateVisibility($certificateVisibility)
    {
        $this->certificateVisibility = $certificateVisibility;

        return $this;
    }

    /**
     * Get certificateVisibility.
     *
     * @return bool
     */
    public function getCertificateVisibility()
    {
        return $this->certificateVisibility;
    }

    /**
     * Set tfeedback.
     *
     * @param bool $tfeedback
     *
     * @return ExcData
     */
    public function setTfeedback($tfeedback)
    {
        $this->tfeedback = $tfeedback;

        return $this;
    }

    /**
     * Get tfeedback.
     *
     * @return bool
     */
    public function getTfeedback()
    {
        return $this->tfeedback;
    }
}
