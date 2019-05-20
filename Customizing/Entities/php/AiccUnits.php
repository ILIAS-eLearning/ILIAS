<?php



/**
 * AiccUnits
 */
class AiccUnits
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $cType;

    /**
     * @var string|null
     */
    private $commandLine;

    /**
     * @var \DateTime|null
     */
    private $maxTimeAllowed;

    /**
     * @var string|null
     */
    private $timeLimitAction;

    /**
     * @var float|null
     */
    private $maxScore;

    /**
     * @var string|null
     */
    private $coreVendor;

    /**
     * @var string|null
     */
    private $systemVendor;

    /**
     * @var string|null
     */
    private $fileName;

    /**
     * @var int
     */
    private $masteryScore = '0';

    /**
     * @var string|null
     */
    private $webLaunch;

    /**
     * @var string|null
     */
    private $auPassword;


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
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return AiccUnits
     */
    public function setCType($cType = null)
    {
        $this->cType = $cType;

        return $this;
    }

    /**
     * Get cType.
     *
     * @return string|null
     */
    public function getCType()
    {
        return $this->cType;
    }

    /**
     * Set commandLine.
     *
     * @param string|null $commandLine
     *
     * @return AiccUnits
     */
    public function setCommandLine($commandLine = null)
    {
        $this->commandLine = $commandLine;

        return $this;
    }

    /**
     * Get commandLine.
     *
     * @return string|null
     */
    public function getCommandLine()
    {
        return $this->commandLine;
    }

    /**
     * Set maxTimeAllowed.
     *
     * @param \DateTime|null $maxTimeAllowed
     *
     * @return AiccUnits
     */
    public function setMaxTimeAllowed($maxTimeAllowed = null)
    {
        $this->maxTimeAllowed = $maxTimeAllowed;

        return $this;
    }

    /**
     * Get maxTimeAllowed.
     *
     * @return \DateTime|null
     */
    public function getMaxTimeAllowed()
    {
        return $this->maxTimeAllowed;
    }

    /**
     * Set timeLimitAction.
     *
     * @param string|null $timeLimitAction
     *
     * @return AiccUnits
     */
    public function setTimeLimitAction($timeLimitAction = null)
    {
        $this->timeLimitAction = $timeLimitAction;

        return $this;
    }

    /**
     * Get timeLimitAction.
     *
     * @return string|null
     */
    public function getTimeLimitAction()
    {
        return $this->timeLimitAction;
    }

    /**
     * Set maxScore.
     *
     * @param float|null $maxScore
     *
     * @return AiccUnits
     */
    public function setMaxScore($maxScore = null)
    {
        $this->maxScore = $maxScore;

        return $this;
    }

    /**
     * Get maxScore.
     *
     * @return float|null
     */
    public function getMaxScore()
    {
        return $this->maxScore;
    }

    /**
     * Set coreVendor.
     *
     * @param string|null $coreVendor
     *
     * @return AiccUnits
     */
    public function setCoreVendor($coreVendor = null)
    {
        $this->coreVendor = $coreVendor;

        return $this;
    }

    /**
     * Get coreVendor.
     *
     * @return string|null
     */
    public function getCoreVendor()
    {
        return $this->coreVendor;
    }

    /**
     * Set systemVendor.
     *
     * @param string|null $systemVendor
     *
     * @return AiccUnits
     */
    public function setSystemVendor($systemVendor = null)
    {
        $this->systemVendor = $systemVendor;

        return $this;
    }

    /**
     * Get systemVendor.
     *
     * @return string|null
     */
    public function getSystemVendor()
    {
        return $this->systemVendor;
    }

    /**
     * Set fileName.
     *
     * @param string|null $fileName
     *
     * @return AiccUnits
     */
    public function setFileName($fileName = null)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName.
     *
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set masteryScore.
     *
     * @param int $masteryScore
     *
     * @return AiccUnits
     */
    public function setMasteryScore($masteryScore)
    {
        $this->masteryScore = $masteryScore;

        return $this;
    }

    /**
     * Get masteryScore.
     *
     * @return int
     */
    public function getMasteryScore()
    {
        return $this->masteryScore;
    }

    /**
     * Set webLaunch.
     *
     * @param string|null $webLaunch
     *
     * @return AiccUnits
     */
    public function setWebLaunch($webLaunch = null)
    {
        $this->webLaunch = $webLaunch;

        return $this;
    }

    /**
     * Get webLaunch.
     *
     * @return string|null
     */
    public function getWebLaunch()
    {
        return $this->webLaunch;
    }

    /**
     * Set auPassword.
     *
     * @param string|null $auPassword
     *
     * @return AiccUnits
     */
    public function setAuPassword($auPassword = null)
    {
        $this->auPassword = $auPassword;

        return $this;
    }

    /**
     * Get auPassword.
     *
     * @return string|null
     */
    public function getAuPassword()
    {
        return $this->auPassword;
    }
}
