<?php



/**
 * TstTests
 */
class TstTests
{
    /**
     * @var int
     */
    private $testId = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var string|null
     */
    private $author;

    /**
     * @var string|null
     */
    private $introduction;

    /**
     * @var bool
     */
    private $sequenceSettings = '0';

    /**
     * @var bool
     */
    private $scoreReporting = '0';

    /**
     * @var string|null
     */
    private $instantVerification = '0';

    /**
     * @var string|null
     */
    private $answerFeedback = '0';

    /**
     * @var string|null
     */
    private $answerFeedbackPoints = '0';

    /**
     * @var string|null
     */
    private $fixedParticipants = '0';

    /**
     * @var string|null
     */
    private $showCancel = '1';

    /**
     * @var string|null
     */
    private $anonymity = '0';

    /**
     * @var int
     */
    private $nrOfTries = '0';

    /**
     * @var string|null
     */
    private $usePreviousAnswers = '1';

    /**
     * @var string|null
     */
    private $titleOutput = '0';

    /**
     * @var string|null
     */
    private $processingTime;

    /**
     * @var string|null
     */
    private $enableProcessingTime = '0';

    /**
     * @var bool
     */
    private $resetProcessingTime = '0';

    /**
     * @var string|null
     */
    private $reportingDate;

    /**
     * @var string|null
     */
    private $shuffleQuestions = '0';

    /**
     * @var string|null
     */
    private $ectsOutput = '0';

    /**
     * @var float|null
     */
    private $ectsFx;

    /**
     * @var string|null
     */
    private $complete = '1';

    /**
     * @var float
     */
    private $ectsA = '90';

    /**
     * @var float
     */
    private $ectsB = '65';

    /**
     * @var float
     */
    private $ectsC = '35';

    /**
     * @var float
     */
    private $ectsD = '10';

    /**
     * @var float
     */
    private $ectsE = '0';

    /**
     * @var bool
     */
    private $keepQuestions = '0';

    /**
     * @var string|null
     */
    private $countSystem = '0';

    /**
     * @var string|null
     */
    private $mcScoring = '0';

    /**
     * @var string|null
     */
    private $scoreCutting = '0';

    /**
     * @var string|null
     */
    private $passScoring = '0';

    /**
     * @var string|null
     */
    private $password;

    /**
     * @var int|null
     */
    private $allowedusers;

    /**
     * @var int|null
     */
    private $alloweduserstimegap;

    /**
     * @var int
     */
    private $resultsPresentation = '3';

    /**
     * @var int
     */
    private $showSummary = '0';

    /**
     * @var string|null
     */
    private $showQuestionTitles = '1';

    /**
     * @var string|null
     */
    private $certificateVisibility = '0';

    /**
     * @var bool
     */
    private $showMarker = '0';

    /**
     * @var int
     */
    private $kiosk = '0';

    /**
     * @var int
     */
    private $resultoutput = '0';

    /**
     * @var string|null
     */
    private $finalstatement;

    /**
     * @var int
     */
    private $showfinalstatement = '0';

    /**
     * @var int
     */
    private $showinfo = '1';

    /**
     * @var int
     */
    private $forcejs = '0';

    /**
     * @var string|null
     */
    private $customstyle;

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var int
     */
    private $created = '0';

    /**
     * @var bool|null
     */
    private $mailnotification = '0';

    /**
     * @var int
     */
    private $mailnottype = '0';

    /**
     * @var int
     */
    private $exportsettings = '0';

    /**
     * @var string|null
     */
    private $enabledViewMode = '0';

    /**
     * @var int|null
     */
    private $templateId;

    /**
     * @var bool|null
     */
    private $poolUsage;

    /**
     * @var bool
     */
    private $onlineStatus = '0';

    /**
     * @var bool
     */
    private $printBsWithRes = '1';

    /**
     * @var bool
     */
    private $offerQuestionHints = '0';

    /**
     * @var int|null
     */
    private $highscoreEnabled = '0';

    /**
     * @var int|null
     */
    private $highscoreAnon = '0';

    /**
     * @var int|null
     */
    private $highscoreAchievedTs = '0';

    /**
     * @var int|null
     */
    private $highscoreScore = '0';

    /**
     * @var int|null
     */
    private $highscorePercentage = '0';

    /**
     * @var int|null
     */
    private $highscoreHints = '0';

    /**
     * @var int|null
     */
    private $highscoreWtime = '0';

    /**
     * @var int|null
     */
    private $highscoreOwnTable = '0';

    /**
     * @var int|null
     */
    private $highscoreTopTable = '0';

    /**
     * @var int|null
     */
    private $highscoreTopNum = '0';

    /**
     * @var int|null
     */
    private $specificFeedback = '0';

    /**
     * @var bool
     */
    private $obligationsEnabled = '0';

    /**
     * @var bool
     */
    private $autosave = '0';

    /**
     * @var int
     */
    private $autosaveIval = '0';

    /**
     * @var int
     */
    private $passDeletionAllowed = '0';

    /**
     * @var int
     */
    private $redirectionMode = '0';

    /**
     * @var string|null
     */
    private $redirectionUrl;

    /**
     * @var int
     */
    private $examidInTestPass = '0';

    /**
     * @var int
     */
    private $examidInTestRes = '0';

    /**
     * @var bool|null
     */
    private $enableExamview;

    /**
     * @var bool|null
     */
    private $showExamviewHtml;

    /**
     * @var bool|null
     */
    private $showExamviewPdf;

    /**
     * @var bool|null
     */
    private $enableArchiving;

    /**
     * @var string
     */
    private $questionSetType = 'FIXED_QUEST_SET';

    /**
     * @var int
     */
    private $signSubmission = '0';

    /**
     * @var int
     */
    private $charSelectorAvailability = '0';

    /**
     * @var string|null
     */
    private $charSelectorDefinition;

    /**
     * @var bool|null
     */
    private $skillService;

    /**
     * @var string|null
     */
    private $resultTaxFilters;

    /**
     * @var bool|null
     */
    private $showGradingStatus = '0';

    /**
     * @var bool|null
     */
    private $showGradingMark = '0';

    /**
     * @var bool|null
     */
    private $instFbAnswerFixation;

    /**
     * @var bool|null
     */
    private $introEnabled;

    /**
     * @var bool|null
     */
    private $startingTimeEnabled;

    /**
     * @var bool|null
     */
    private $endingTimeEnabled;

    /**
     * @var bool|null
     */
    private $passwordEnabled;

    /**
     * @var bool|null
     */
    private $limitUsersEnabled;

    /**
     * @var bool|null
     */
    private $broken;

    /**
     * @var bool|null
     */
    private $forceInstFb = '0';

    /**
     * @var int
     */
    private $startingTime = '0';

    /**
     * @var int
     */
    private $endingTime = '0';

    /**
     * @var string|null
     */
    private $passWaiting;

    /**
     * @var bool|null
     */
    private $followQstAnswerFixation = '0';

    /**
     * @var bool|null
     */
    private $blockAfterPassed = '0';


    /**
     * Get testId.
     *
     * @return int
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return TstTests
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set author.
     *
     * @param string|null $author
     *
     * @return TstTests
     */
    public function setAuthor($author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set introduction.
     *
     * @param string|null $introduction
     *
     * @return TstTests
     */
    public function setIntroduction($introduction = null)
    {
        $this->introduction = $introduction;

        return $this;
    }

    /**
     * Get introduction.
     *
     * @return string|null
     */
    public function getIntroduction()
    {
        return $this->introduction;
    }

    /**
     * Set sequenceSettings.
     *
     * @param bool $sequenceSettings
     *
     * @return TstTests
     */
    public function setSequenceSettings($sequenceSettings)
    {
        $this->sequenceSettings = $sequenceSettings;

        return $this;
    }

    /**
     * Get sequenceSettings.
     *
     * @return bool
     */
    public function getSequenceSettings()
    {
        return $this->sequenceSettings;
    }

    /**
     * Set scoreReporting.
     *
     * @param bool $scoreReporting
     *
     * @return TstTests
     */
    public function setScoreReporting($scoreReporting)
    {
        $this->scoreReporting = $scoreReporting;

        return $this;
    }

    /**
     * Get scoreReporting.
     *
     * @return bool
     */
    public function getScoreReporting()
    {
        return $this->scoreReporting;
    }

    /**
     * Set instantVerification.
     *
     * @param string|null $instantVerification
     *
     * @return TstTests
     */
    public function setInstantVerification($instantVerification = null)
    {
        $this->instantVerification = $instantVerification;

        return $this;
    }

    /**
     * Get instantVerification.
     *
     * @return string|null
     */
    public function getInstantVerification()
    {
        return $this->instantVerification;
    }

    /**
     * Set answerFeedback.
     *
     * @param string|null $answerFeedback
     *
     * @return TstTests
     */
    public function setAnswerFeedback($answerFeedback = null)
    {
        $this->answerFeedback = $answerFeedback;

        return $this;
    }

    /**
     * Get answerFeedback.
     *
     * @return string|null
     */
    public function getAnswerFeedback()
    {
        return $this->answerFeedback;
    }

    /**
     * Set answerFeedbackPoints.
     *
     * @param string|null $answerFeedbackPoints
     *
     * @return TstTests
     */
    public function setAnswerFeedbackPoints($answerFeedbackPoints = null)
    {
        $this->answerFeedbackPoints = $answerFeedbackPoints;

        return $this;
    }

    /**
     * Get answerFeedbackPoints.
     *
     * @return string|null
     */
    public function getAnswerFeedbackPoints()
    {
        return $this->answerFeedbackPoints;
    }

    /**
     * Set fixedParticipants.
     *
     * @param string|null $fixedParticipants
     *
     * @return TstTests
     */
    public function setFixedParticipants($fixedParticipants = null)
    {
        $this->fixedParticipants = $fixedParticipants;

        return $this;
    }

    /**
     * Get fixedParticipants.
     *
     * @return string|null
     */
    public function getFixedParticipants()
    {
        return $this->fixedParticipants;
    }

    /**
     * Set showCancel.
     *
     * @param string|null $showCancel
     *
     * @return TstTests
     */
    public function setShowCancel($showCancel = null)
    {
        $this->showCancel = $showCancel;

        return $this;
    }

    /**
     * Get showCancel.
     *
     * @return string|null
     */
    public function getShowCancel()
    {
        return $this->showCancel;
    }

    /**
     * Set anonymity.
     *
     * @param string|null $anonymity
     *
     * @return TstTests
     */
    public function setAnonymity($anonymity = null)
    {
        $this->anonymity = $anonymity;

        return $this;
    }

    /**
     * Get anonymity.
     *
     * @return string|null
     */
    public function getAnonymity()
    {
        return $this->anonymity;
    }

    /**
     * Set nrOfTries.
     *
     * @param int $nrOfTries
     *
     * @return TstTests
     */
    public function setNrOfTries($nrOfTries)
    {
        $this->nrOfTries = $nrOfTries;

        return $this;
    }

    /**
     * Get nrOfTries.
     *
     * @return int
     */
    public function getNrOfTries()
    {
        return $this->nrOfTries;
    }

    /**
     * Set usePreviousAnswers.
     *
     * @param string|null $usePreviousAnswers
     *
     * @return TstTests
     */
    public function setUsePreviousAnswers($usePreviousAnswers = null)
    {
        $this->usePreviousAnswers = $usePreviousAnswers;

        return $this;
    }

    /**
     * Get usePreviousAnswers.
     *
     * @return string|null
     */
    public function getUsePreviousAnswers()
    {
        return $this->usePreviousAnswers;
    }

    /**
     * Set titleOutput.
     *
     * @param string|null $titleOutput
     *
     * @return TstTests
     */
    public function setTitleOutput($titleOutput = null)
    {
        $this->titleOutput = $titleOutput;

        return $this;
    }

    /**
     * Get titleOutput.
     *
     * @return string|null
     */
    public function getTitleOutput()
    {
        return $this->titleOutput;
    }

    /**
     * Set processingTime.
     *
     * @param string|null $processingTime
     *
     * @return TstTests
     */
    public function setProcessingTime($processingTime = null)
    {
        $this->processingTime = $processingTime;

        return $this;
    }

    /**
     * Get processingTime.
     *
     * @return string|null
     */
    public function getProcessingTime()
    {
        return $this->processingTime;
    }

    /**
     * Set enableProcessingTime.
     *
     * @param string|null $enableProcessingTime
     *
     * @return TstTests
     */
    public function setEnableProcessingTime($enableProcessingTime = null)
    {
        $this->enableProcessingTime = $enableProcessingTime;

        return $this;
    }

    /**
     * Get enableProcessingTime.
     *
     * @return string|null
     */
    public function getEnableProcessingTime()
    {
        return $this->enableProcessingTime;
    }

    /**
     * Set resetProcessingTime.
     *
     * @param bool $resetProcessingTime
     *
     * @return TstTests
     */
    public function setResetProcessingTime($resetProcessingTime)
    {
        $this->resetProcessingTime = $resetProcessingTime;

        return $this;
    }

    /**
     * Get resetProcessingTime.
     *
     * @return bool
     */
    public function getResetProcessingTime()
    {
        return $this->resetProcessingTime;
    }

    /**
     * Set reportingDate.
     *
     * @param string|null $reportingDate
     *
     * @return TstTests
     */
    public function setReportingDate($reportingDate = null)
    {
        $this->reportingDate = $reportingDate;

        return $this;
    }

    /**
     * Get reportingDate.
     *
     * @return string|null
     */
    public function getReportingDate()
    {
        return $this->reportingDate;
    }

    /**
     * Set shuffleQuestions.
     *
     * @param string|null $shuffleQuestions
     *
     * @return TstTests
     */
    public function setShuffleQuestions($shuffleQuestions = null)
    {
        $this->shuffleQuestions = $shuffleQuestions;

        return $this;
    }

    /**
     * Get shuffleQuestions.
     *
     * @return string|null
     */
    public function getShuffleQuestions()
    {
        return $this->shuffleQuestions;
    }

    /**
     * Set ectsOutput.
     *
     * @param string|null $ectsOutput
     *
     * @return TstTests
     */
    public function setEctsOutput($ectsOutput = null)
    {
        $this->ectsOutput = $ectsOutput;

        return $this;
    }

    /**
     * Get ectsOutput.
     *
     * @return string|null
     */
    public function getEctsOutput()
    {
        return $this->ectsOutput;
    }

    /**
     * Set ectsFx.
     *
     * @param float|null $ectsFx
     *
     * @return TstTests
     */
    public function setEctsFx($ectsFx = null)
    {
        $this->ectsFx = $ectsFx;

        return $this;
    }

    /**
     * Get ectsFx.
     *
     * @return float|null
     */
    public function getEctsFx()
    {
        return $this->ectsFx;
    }

    /**
     * Set complete.
     *
     * @param string|null $complete
     *
     * @return TstTests
     */
    public function setComplete($complete = null)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete.
     *
     * @return string|null
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set ectsA.
     *
     * @param float $ectsA
     *
     * @return TstTests
     */
    public function setEctsA($ectsA)
    {
        $this->ectsA = $ectsA;

        return $this;
    }

    /**
     * Get ectsA.
     *
     * @return float
     */
    public function getEctsA()
    {
        return $this->ectsA;
    }

    /**
     * Set ectsB.
     *
     * @param float $ectsB
     *
     * @return TstTests
     */
    public function setEctsB($ectsB)
    {
        $this->ectsB = $ectsB;

        return $this;
    }

    /**
     * Get ectsB.
     *
     * @return float
     */
    public function getEctsB()
    {
        return $this->ectsB;
    }

    /**
     * Set ectsC.
     *
     * @param float $ectsC
     *
     * @return TstTests
     */
    public function setEctsC($ectsC)
    {
        $this->ectsC = $ectsC;

        return $this;
    }

    /**
     * Get ectsC.
     *
     * @return float
     */
    public function getEctsC()
    {
        return $this->ectsC;
    }

    /**
     * Set ectsD.
     *
     * @param float $ectsD
     *
     * @return TstTests
     */
    public function setEctsD($ectsD)
    {
        $this->ectsD = $ectsD;

        return $this;
    }

    /**
     * Get ectsD.
     *
     * @return float
     */
    public function getEctsD()
    {
        return $this->ectsD;
    }

    /**
     * Set ectsE.
     *
     * @param float $ectsE
     *
     * @return TstTests
     */
    public function setEctsE($ectsE)
    {
        $this->ectsE = $ectsE;

        return $this;
    }

    /**
     * Get ectsE.
     *
     * @return float
     */
    public function getEctsE()
    {
        return $this->ectsE;
    }

    /**
     * Set keepQuestions.
     *
     * @param bool $keepQuestions
     *
     * @return TstTests
     */
    public function setKeepQuestions($keepQuestions)
    {
        $this->keepQuestions = $keepQuestions;

        return $this;
    }

    /**
     * Get keepQuestions.
     *
     * @return bool
     */
    public function getKeepQuestions()
    {
        return $this->keepQuestions;
    }

    /**
     * Set countSystem.
     *
     * @param string|null $countSystem
     *
     * @return TstTests
     */
    public function setCountSystem($countSystem = null)
    {
        $this->countSystem = $countSystem;

        return $this;
    }

    /**
     * Get countSystem.
     *
     * @return string|null
     */
    public function getCountSystem()
    {
        return $this->countSystem;
    }

    /**
     * Set mcScoring.
     *
     * @param string|null $mcScoring
     *
     * @return TstTests
     */
    public function setMcScoring($mcScoring = null)
    {
        $this->mcScoring = $mcScoring;

        return $this;
    }

    /**
     * Get mcScoring.
     *
     * @return string|null
     */
    public function getMcScoring()
    {
        return $this->mcScoring;
    }

    /**
     * Set scoreCutting.
     *
     * @param string|null $scoreCutting
     *
     * @return TstTests
     */
    public function setScoreCutting($scoreCutting = null)
    {
        $this->scoreCutting = $scoreCutting;

        return $this;
    }

    /**
     * Get scoreCutting.
     *
     * @return string|null
     */
    public function getScoreCutting()
    {
        return $this->scoreCutting;
    }

    /**
     * Set passScoring.
     *
     * @param string|null $passScoring
     *
     * @return TstTests
     */
    public function setPassScoring($passScoring = null)
    {
        $this->passScoring = $passScoring;

        return $this;
    }

    /**
     * Get passScoring.
     *
     * @return string|null
     */
    public function getPassScoring()
    {
        return $this->passScoring;
    }

    /**
     * Set password.
     *
     * @param string|null $password
     *
     * @return TstTests
     */
    public function setPassword($password = null)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set allowedusers.
     *
     * @param int|null $allowedusers
     *
     * @return TstTests
     */
    public function setAllowedusers($allowedusers = null)
    {
        $this->allowedusers = $allowedusers;

        return $this;
    }

    /**
     * Get allowedusers.
     *
     * @return int|null
     */
    public function getAllowedusers()
    {
        return $this->allowedusers;
    }

    /**
     * Set alloweduserstimegap.
     *
     * @param int|null $alloweduserstimegap
     *
     * @return TstTests
     */
    public function setAlloweduserstimegap($alloweduserstimegap = null)
    {
        $this->alloweduserstimegap = $alloweduserstimegap;

        return $this;
    }

    /**
     * Get alloweduserstimegap.
     *
     * @return int|null
     */
    public function getAlloweduserstimegap()
    {
        return $this->alloweduserstimegap;
    }

    /**
     * Set resultsPresentation.
     *
     * @param int $resultsPresentation
     *
     * @return TstTests
     */
    public function setResultsPresentation($resultsPresentation)
    {
        $this->resultsPresentation = $resultsPresentation;

        return $this;
    }

    /**
     * Get resultsPresentation.
     *
     * @return int
     */
    public function getResultsPresentation()
    {
        return $this->resultsPresentation;
    }

    /**
     * Set showSummary.
     *
     * @param int $showSummary
     *
     * @return TstTests
     */
    public function setShowSummary($showSummary)
    {
        $this->showSummary = $showSummary;

        return $this;
    }

    /**
     * Get showSummary.
     *
     * @return int
     */
    public function getShowSummary()
    {
        return $this->showSummary;
    }

    /**
     * Set showQuestionTitles.
     *
     * @param string|null $showQuestionTitles
     *
     * @return TstTests
     */
    public function setShowQuestionTitles($showQuestionTitles = null)
    {
        $this->showQuestionTitles = $showQuestionTitles;

        return $this;
    }

    /**
     * Get showQuestionTitles.
     *
     * @return string|null
     */
    public function getShowQuestionTitles()
    {
        return $this->showQuestionTitles;
    }

    /**
     * Set certificateVisibility.
     *
     * @param string|null $certificateVisibility
     *
     * @return TstTests
     */
    public function setCertificateVisibility($certificateVisibility = null)
    {
        $this->certificateVisibility = $certificateVisibility;

        return $this;
    }

    /**
     * Get certificateVisibility.
     *
     * @return string|null
     */
    public function getCertificateVisibility()
    {
        return $this->certificateVisibility;
    }

    /**
     * Set showMarker.
     *
     * @param bool $showMarker
     *
     * @return TstTests
     */
    public function setShowMarker($showMarker)
    {
        $this->showMarker = $showMarker;

        return $this;
    }

    /**
     * Get showMarker.
     *
     * @return bool
     */
    public function getShowMarker()
    {
        return $this->showMarker;
    }

    /**
     * Set kiosk.
     *
     * @param int $kiosk
     *
     * @return TstTests
     */
    public function setKiosk($kiosk)
    {
        $this->kiosk = $kiosk;

        return $this;
    }

    /**
     * Get kiosk.
     *
     * @return int
     */
    public function getKiosk()
    {
        return $this->kiosk;
    }

    /**
     * Set resultoutput.
     *
     * @param int $resultoutput
     *
     * @return TstTests
     */
    public function setResultoutput($resultoutput)
    {
        $this->resultoutput = $resultoutput;

        return $this;
    }

    /**
     * Get resultoutput.
     *
     * @return int
     */
    public function getResultoutput()
    {
        return $this->resultoutput;
    }

    /**
     * Set finalstatement.
     *
     * @param string|null $finalstatement
     *
     * @return TstTests
     */
    public function setFinalstatement($finalstatement = null)
    {
        $this->finalstatement = $finalstatement;

        return $this;
    }

    /**
     * Get finalstatement.
     *
     * @return string|null
     */
    public function getFinalstatement()
    {
        return $this->finalstatement;
    }

    /**
     * Set showfinalstatement.
     *
     * @param int $showfinalstatement
     *
     * @return TstTests
     */
    public function setShowfinalstatement($showfinalstatement)
    {
        $this->showfinalstatement = $showfinalstatement;

        return $this;
    }

    /**
     * Get showfinalstatement.
     *
     * @return int
     */
    public function getShowfinalstatement()
    {
        return $this->showfinalstatement;
    }

    /**
     * Set showinfo.
     *
     * @param int $showinfo
     *
     * @return TstTests
     */
    public function setShowinfo($showinfo)
    {
        $this->showinfo = $showinfo;

        return $this;
    }

    /**
     * Get showinfo.
     *
     * @return int
     */
    public function getShowinfo()
    {
        return $this->showinfo;
    }

    /**
     * Set forcejs.
     *
     * @param int $forcejs
     *
     * @return TstTests
     */
    public function setForcejs($forcejs)
    {
        $this->forcejs = $forcejs;

        return $this;
    }

    /**
     * Get forcejs.
     *
     * @return int
     */
    public function getForcejs()
    {
        return $this->forcejs;
    }

    /**
     * Set customstyle.
     *
     * @param string|null $customstyle
     *
     * @return TstTests
     */
    public function setCustomstyle($customstyle = null)
    {
        $this->customstyle = $customstyle;

        return $this;
    }

    /**
     * Get customstyle.
     *
     * @return string|null
     */
    public function getCustomstyle()
    {
        return $this->customstyle;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return TstTests
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set created.
     *
     * @param int $created
     *
     * @return TstTests
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set mailnotification.
     *
     * @param bool|null $mailnotification
     *
     * @return TstTests
     */
    public function setMailnotification($mailnotification = null)
    {
        $this->mailnotification = $mailnotification;

        return $this;
    }

    /**
     * Get mailnotification.
     *
     * @return bool|null
     */
    public function getMailnotification()
    {
        return $this->mailnotification;
    }

    /**
     * Set mailnottype.
     *
     * @param int $mailnottype
     *
     * @return TstTests
     */
    public function setMailnottype($mailnottype)
    {
        $this->mailnottype = $mailnottype;

        return $this;
    }

    /**
     * Get mailnottype.
     *
     * @return int
     */
    public function getMailnottype()
    {
        return $this->mailnottype;
    }

    /**
     * Set exportsettings.
     *
     * @param int $exportsettings
     *
     * @return TstTests
     */
    public function setExportsettings($exportsettings)
    {
        $this->exportsettings = $exportsettings;

        return $this;
    }

    /**
     * Get exportsettings.
     *
     * @return int
     */
    public function getExportsettings()
    {
        return $this->exportsettings;
    }

    /**
     * Set enabledViewMode.
     *
     * @param string|null $enabledViewMode
     *
     * @return TstTests
     */
    public function setEnabledViewMode($enabledViewMode = null)
    {
        $this->enabledViewMode = $enabledViewMode;

        return $this;
    }

    /**
     * Get enabledViewMode.
     *
     * @return string|null
     */
    public function getEnabledViewMode()
    {
        return $this->enabledViewMode;
    }

    /**
     * Set templateId.
     *
     * @param int|null $templateId
     *
     * @return TstTests
     */
    public function setTemplateId($templateId = null)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId.
     *
     * @return int|null
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Set poolUsage.
     *
     * @param bool|null $poolUsage
     *
     * @return TstTests
     */
    public function setPoolUsage($poolUsage = null)
    {
        $this->poolUsage = $poolUsage;

        return $this;
    }

    /**
     * Get poolUsage.
     *
     * @return bool|null
     */
    public function getPoolUsage()
    {
        return $this->poolUsage;
    }

    /**
     * Set onlineStatus.
     *
     * @param bool $onlineStatus
     *
     * @return TstTests
     */
    public function setOnlineStatus($onlineStatus)
    {
        $this->onlineStatus = $onlineStatus;

        return $this;
    }

    /**
     * Get onlineStatus.
     *
     * @return bool
     */
    public function getOnlineStatus()
    {
        return $this->onlineStatus;
    }

    /**
     * Set printBsWithRes.
     *
     * @param bool $printBsWithRes
     *
     * @return TstTests
     */
    public function setPrintBsWithRes($printBsWithRes)
    {
        $this->printBsWithRes = $printBsWithRes;

        return $this;
    }

    /**
     * Get printBsWithRes.
     *
     * @return bool
     */
    public function getPrintBsWithRes()
    {
        return $this->printBsWithRes;
    }

    /**
     * Set offerQuestionHints.
     *
     * @param bool $offerQuestionHints
     *
     * @return TstTests
     */
    public function setOfferQuestionHints($offerQuestionHints)
    {
        $this->offerQuestionHints = $offerQuestionHints;

        return $this;
    }

    /**
     * Get offerQuestionHints.
     *
     * @return bool
     */
    public function getOfferQuestionHints()
    {
        return $this->offerQuestionHints;
    }

    /**
     * Set highscoreEnabled.
     *
     * @param int|null $highscoreEnabled
     *
     * @return TstTests
     */
    public function setHighscoreEnabled($highscoreEnabled = null)
    {
        $this->highscoreEnabled = $highscoreEnabled;

        return $this;
    }

    /**
     * Get highscoreEnabled.
     *
     * @return int|null
     */
    public function getHighscoreEnabled()
    {
        return $this->highscoreEnabled;
    }

    /**
     * Set highscoreAnon.
     *
     * @param int|null $highscoreAnon
     *
     * @return TstTests
     */
    public function setHighscoreAnon($highscoreAnon = null)
    {
        $this->highscoreAnon = $highscoreAnon;

        return $this;
    }

    /**
     * Get highscoreAnon.
     *
     * @return int|null
     */
    public function getHighscoreAnon()
    {
        return $this->highscoreAnon;
    }

    /**
     * Set highscoreAchievedTs.
     *
     * @param int|null $highscoreAchievedTs
     *
     * @return TstTests
     */
    public function setHighscoreAchievedTs($highscoreAchievedTs = null)
    {
        $this->highscoreAchievedTs = $highscoreAchievedTs;

        return $this;
    }

    /**
     * Get highscoreAchievedTs.
     *
     * @return int|null
     */
    public function getHighscoreAchievedTs()
    {
        return $this->highscoreAchievedTs;
    }

    /**
     * Set highscoreScore.
     *
     * @param int|null $highscoreScore
     *
     * @return TstTests
     */
    public function setHighscoreScore($highscoreScore = null)
    {
        $this->highscoreScore = $highscoreScore;

        return $this;
    }

    /**
     * Get highscoreScore.
     *
     * @return int|null
     */
    public function getHighscoreScore()
    {
        return $this->highscoreScore;
    }

    /**
     * Set highscorePercentage.
     *
     * @param int|null $highscorePercentage
     *
     * @return TstTests
     */
    public function setHighscorePercentage($highscorePercentage = null)
    {
        $this->highscorePercentage = $highscorePercentage;

        return $this;
    }

    /**
     * Get highscorePercentage.
     *
     * @return int|null
     */
    public function getHighscorePercentage()
    {
        return $this->highscorePercentage;
    }

    /**
     * Set highscoreHints.
     *
     * @param int|null $highscoreHints
     *
     * @return TstTests
     */
    public function setHighscoreHints($highscoreHints = null)
    {
        $this->highscoreHints = $highscoreHints;

        return $this;
    }

    /**
     * Get highscoreHints.
     *
     * @return int|null
     */
    public function getHighscoreHints()
    {
        return $this->highscoreHints;
    }

    /**
     * Set highscoreWtime.
     *
     * @param int|null $highscoreWtime
     *
     * @return TstTests
     */
    public function setHighscoreWtime($highscoreWtime = null)
    {
        $this->highscoreWtime = $highscoreWtime;

        return $this;
    }

    /**
     * Get highscoreWtime.
     *
     * @return int|null
     */
    public function getHighscoreWtime()
    {
        return $this->highscoreWtime;
    }

    /**
     * Set highscoreOwnTable.
     *
     * @param int|null $highscoreOwnTable
     *
     * @return TstTests
     */
    public function setHighscoreOwnTable($highscoreOwnTable = null)
    {
        $this->highscoreOwnTable = $highscoreOwnTable;

        return $this;
    }

    /**
     * Get highscoreOwnTable.
     *
     * @return int|null
     */
    public function getHighscoreOwnTable()
    {
        return $this->highscoreOwnTable;
    }

    /**
     * Set highscoreTopTable.
     *
     * @param int|null $highscoreTopTable
     *
     * @return TstTests
     */
    public function setHighscoreTopTable($highscoreTopTable = null)
    {
        $this->highscoreTopTable = $highscoreTopTable;

        return $this;
    }

    /**
     * Get highscoreTopTable.
     *
     * @return int|null
     */
    public function getHighscoreTopTable()
    {
        return $this->highscoreTopTable;
    }

    /**
     * Set highscoreTopNum.
     *
     * @param int|null $highscoreTopNum
     *
     * @return TstTests
     */
    public function setHighscoreTopNum($highscoreTopNum = null)
    {
        $this->highscoreTopNum = $highscoreTopNum;

        return $this;
    }

    /**
     * Get highscoreTopNum.
     *
     * @return int|null
     */
    public function getHighscoreTopNum()
    {
        return $this->highscoreTopNum;
    }

    /**
     * Set specificFeedback.
     *
     * @param int|null $specificFeedback
     *
     * @return TstTests
     */
    public function setSpecificFeedback($specificFeedback = null)
    {
        $this->specificFeedback = $specificFeedback;

        return $this;
    }

    /**
     * Get specificFeedback.
     *
     * @return int|null
     */
    public function getSpecificFeedback()
    {
        return $this->specificFeedback;
    }

    /**
     * Set obligationsEnabled.
     *
     * @param bool $obligationsEnabled
     *
     * @return TstTests
     */
    public function setObligationsEnabled($obligationsEnabled)
    {
        $this->obligationsEnabled = $obligationsEnabled;

        return $this;
    }

    /**
     * Get obligationsEnabled.
     *
     * @return bool
     */
    public function getObligationsEnabled()
    {
        return $this->obligationsEnabled;
    }

    /**
     * Set autosave.
     *
     * @param bool $autosave
     *
     * @return TstTests
     */
    public function setAutosave($autosave)
    {
        $this->autosave = $autosave;

        return $this;
    }

    /**
     * Get autosave.
     *
     * @return bool
     */
    public function getAutosave()
    {
        return $this->autosave;
    }

    /**
     * Set autosaveIval.
     *
     * @param int $autosaveIval
     *
     * @return TstTests
     */
    public function setAutosaveIval($autosaveIval)
    {
        $this->autosaveIval = $autosaveIval;

        return $this;
    }

    /**
     * Get autosaveIval.
     *
     * @return int
     */
    public function getAutosaveIval()
    {
        return $this->autosaveIval;
    }

    /**
     * Set passDeletionAllowed.
     *
     * @param int $passDeletionAllowed
     *
     * @return TstTests
     */
    public function setPassDeletionAllowed($passDeletionAllowed)
    {
        $this->passDeletionAllowed = $passDeletionAllowed;

        return $this;
    }

    /**
     * Get passDeletionAllowed.
     *
     * @return int
     */
    public function getPassDeletionAllowed()
    {
        return $this->passDeletionAllowed;
    }

    /**
     * Set redirectionMode.
     *
     * @param int $redirectionMode
     *
     * @return TstTests
     */
    public function setRedirectionMode($redirectionMode)
    {
        $this->redirectionMode = $redirectionMode;

        return $this;
    }

    /**
     * Get redirectionMode.
     *
     * @return int
     */
    public function getRedirectionMode()
    {
        return $this->redirectionMode;
    }

    /**
     * Set redirectionUrl.
     *
     * @param string|null $redirectionUrl
     *
     * @return TstTests
     */
    public function setRedirectionUrl($redirectionUrl = null)
    {
        $this->redirectionUrl = $redirectionUrl;

        return $this;
    }

    /**
     * Get redirectionUrl.
     *
     * @return string|null
     */
    public function getRedirectionUrl()
    {
        return $this->redirectionUrl;
    }

    /**
     * Set examidInTestPass.
     *
     * @param int $examidInTestPass
     *
     * @return TstTests
     */
    public function setExamidInTestPass($examidInTestPass)
    {
        $this->examidInTestPass = $examidInTestPass;

        return $this;
    }

    /**
     * Get examidInTestPass.
     *
     * @return int
     */
    public function getExamidInTestPass()
    {
        return $this->examidInTestPass;
    }

    /**
     * Set examidInTestRes.
     *
     * @param int $examidInTestRes
     *
     * @return TstTests
     */
    public function setExamidInTestRes($examidInTestRes)
    {
        $this->examidInTestRes = $examidInTestRes;

        return $this;
    }

    /**
     * Get examidInTestRes.
     *
     * @return int
     */
    public function getExamidInTestRes()
    {
        return $this->examidInTestRes;
    }

    /**
     * Set enableExamview.
     *
     * @param bool|null $enableExamview
     *
     * @return TstTests
     */
    public function setEnableExamview($enableExamview = null)
    {
        $this->enableExamview = $enableExamview;

        return $this;
    }

    /**
     * Get enableExamview.
     *
     * @return bool|null
     */
    public function getEnableExamview()
    {
        return $this->enableExamview;
    }

    /**
     * Set showExamviewHtml.
     *
     * @param bool|null $showExamviewHtml
     *
     * @return TstTests
     */
    public function setShowExamviewHtml($showExamviewHtml = null)
    {
        $this->showExamviewHtml = $showExamviewHtml;

        return $this;
    }

    /**
     * Get showExamviewHtml.
     *
     * @return bool|null
     */
    public function getShowExamviewHtml()
    {
        return $this->showExamviewHtml;
    }

    /**
     * Set showExamviewPdf.
     *
     * @param bool|null $showExamviewPdf
     *
     * @return TstTests
     */
    public function setShowExamviewPdf($showExamviewPdf = null)
    {
        $this->showExamviewPdf = $showExamviewPdf;

        return $this;
    }

    /**
     * Get showExamviewPdf.
     *
     * @return bool|null
     */
    public function getShowExamviewPdf()
    {
        return $this->showExamviewPdf;
    }

    /**
     * Set enableArchiving.
     *
     * @param bool|null $enableArchiving
     *
     * @return TstTests
     */
    public function setEnableArchiving($enableArchiving = null)
    {
        $this->enableArchiving = $enableArchiving;

        return $this;
    }

    /**
     * Get enableArchiving.
     *
     * @return bool|null
     */
    public function getEnableArchiving()
    {
        return $this->enableArchiving;
    }

    /**
     * Set questionSetType.
     *
     * @param string $questionSetType
     *
     * @return TstTests
     */
    public function setQuestionSetType($questionSetType)
    {
        $this->questionSetType = $questionSetType;

        return $this;
    }

    /**
     * Get questionSetType.
     *
     * @return string
     */
    public function getQuestionSetType()
    {
        return $this->questionSetType;
    }

    /**
     * Set signSubmission.
     *
     * @param int $signSubmission
     *
     * @return TstTests
     */
    public function setSignSubmission($signSubmission)
    {
        $this->signSubmission = $signSubmission;

        return $this;
    }

    /**
     * Get signSubmission.
     *
     * @return int
     */
    public function getSignSubmission()
    {
        return $this->signSubmission;
    }

    /**
     * Set charSelectorAvailability.
     *
     * @param int $charSelectorAvailability
     *
     * @return TstTests
     */
    public function setCharSelectorAvailability($charSelectorAvailability)
    {
        $this->charSelectorAvailability = $charSelectorAvailability;

        return $this;
    }

    /**
     * Get charSelectorAvailability.
     *
     * @return int
     */
    public function getCharSelectorAvailability()
    {
        return $this->charSelectorAvailability;
    }

    /**
     * Set charSelectorDefinition.
     *
     * @param string|null $charSelectorDefinition
     *
     * @return TstTests
     */
    public function setCharSelectorDefinition($charSelectorDefinition = null)
    {
        $this->charSelectorDefinition = $charSelectorDefinition;

        return $this;
    }

    /**
     * Get charSelectorDefinition.
     *
     * @return string|null
     */
    public function getCharSelectorDefinition()
    {
        return $this->charSelectorDefinition;
    }

    /**
     * Set skillService.
     *
     * @param bool|null $skillService
     *
     * @return TstTests
     */
    public function setSkillService($skillService = null)
    {
        $this->skillService = $skillService;

        return $this;
    }

    /**
     * Get skillService.
     *
     * @return bool|null
     */
    public function getSkillService()
    {
        return $this->skillService;
    }

    /**
     * Set resultTaxFilters.
     *
     * @param string|null $resultTaxFilters
     *
     * @return TstTests
     */
    public function setResultTaxFilters($resultTaxFilters = null)
    {
        $this->resultTaxFilters = $resultTaxFilters;

        return $this;
    }

    /**
     * Get resultTaxFilters.
     *
     * @return string|null
     */
    public function getResultTaxFilters()
    {
        return $this->resultTaxFilters;
    }

    /**
     * Set showGradingStatus.
     *
     * @param bool|null $showGradingStatus
     *
     * @return TstTests
     */
    public function setShowGradingStatus($showGradingStatus = null)
    {
        $this->showGradingStatus = $showGradingStatus;

        return $this;
    }

    /**
     * Get showGradingStatus.
     *
     * @return bool|null
     */
    public function getShowGradingStatus()
    {
        return $this->showGradingStatus;
    }

    /**
     * Set showGradingMark.
     *
     * @param bool|null $showGradingMark
     *
     * @return TstTests
     */
    public function setShowGradingMark($showGradingMark = null)
    {
        $this->showGradingMark = $showGradingMark;

        return $this;
    }

    /**
     * Get showGradingMark.
     *
     * @return bool|null
     */
    public function getShowGradingMark()
    {
        return $this->showGradingMark;
    }

    /**
     * Set instFbAnswerFixation.
     *
     * @param bool|null $instFbAnswerFixation
     *
     * @return TstTests
     */
    public function setInstFbAnswerFixation($instFbAnswerFixation = null)
    {
        $this->instFbAnswerFixation = $instFbAnswerFixation;

        return $this;
    }

    /**
     * Get instFbAnswerFixation.
     *
     * @return bool|null
     */
    public function getInstFbAnswerFixation()
    {
        return $this->instFbAnswerFixation;
    }

    /**
     * Set introEnabled.
     *
     * @param bool|null $introEnabled
     *
     * @return TstTests
     */
    public function setIntroEnabled($introEnabled = null)
    {
        $this->introEnabled = $introEnabled;

        return $this;
    }

    /**
     * Get introEnabled.
     *
     * @return bool|null
     */
    public function getIntroEnabled()
    {
        return $this->introEnabled;
    }

    /**
     * Set startingTimeEnabled.
     *
     * @param bool|null $startingTimeEnabled
     *
     * @return TstTests
     */
    public function setStartingTimeEnabled($startingTimeEnabled = null)
    {
        $this->startingTimeEnabled = $startingTimeEnabled;

        return $this;
    }

    /**
     * Get startingTimeEnabled.
     *
     * @return bool|null
     */
    public function getStartingTimeEnabled()
    {
        return $this->startingTimeEnabled;
    }

    /**
     * Set endingTimeEnabled.
     *
     * @param bool|null $endingTimeEnabled
     *
     * @return TstTests
     */
    public function setEndingTimeEnabled($endingTimeEnabled = null)
    {
        $this->endingTimeEnabled = $endingTimeEnabled;

        return $this;
    }

    /**
     * Get endingTimeEnabled.
     *
     * @return bool|null
     */
    public function getEndingTimeEnabled()
    {
        return $this->endingTimeEnabled;
    }

    /**
     * Set passwordEnabled.
     *
     * @param bool|null $passwordEnabled
     *
     * @return TstTests
     */
    public function setPasswordEnabled($passwordEnabled = null)
    {
        $this->passwordEnabled = $passwordEnabled;

        return $this;
    }

    /**
     * Get passwordEnabled.
     *
     * @return bool|null
     */
    public function getPasswordEnabled()
    {
        return $this->passwordEnabled;
    }

    /**
     * Set limitUsersEnabled.
     *
     * @param bool|null $limitUsersEnabled
     *
     * @return TstTests
     */
    public function setLimitUsersEnabled($limitUsersEnabled = null)
    {
        $this->limitUsersEnabled = $limitUsersEnabled;

        return $this;
    }

    /**
     * Get limitUsersEnabled.
     *
     * @return bool|null
     */
    public function getLimitUsersEnabled()
    {
        return $this->limitUsersEnabled;
    }

    /**
     * Set broken.
     *
     * @param bool|null $broken
     *
     * @return TstTests
     */
    public function setBroken($broken = null)
    {
        $this->broken = $broken;

        return $this;
    }

    /**
     * Get broken.
     *
     * @return bool|null
     */
    public function getBroken()
    {
        return $this->broken;
    }

    /**
     * Set forceInstFb.
     *
     * @param bool|null $forceInstFb
     *
     * @return TstTests
     */
    public function setForceInstFb($forceInstFb = null)
    {
        $this->forceInstFb = $forceInstFb;

        return $this;
    }

    /**
     * Get forceInstFb.
     *
     * @return bool|null
     */
    public function getForceInstFb()
    {
        return $this->forceInstFb;
    }

    /**
     * Set startingTime.
     *
     * @param int $startingTime
     *
     * @return TstTests
     */
    public function setStartingTime($startingTime)
    {
        $this->startingTime = $startingTime;

        return $this;
    }

    /**
     * Get startingTime.
     *
     * @return int
     */
    public function getStartingTime()
    {
        return $this->startingTime;
    }

    /**
     * Set endingTime.
     *
     * @param int $endingTime
     *
     * @return TstTests
     */
    public function setEndingTime($endingTime)
    {
        $this->endingTime = $endingTime;

        return $this;
    }

    /**
     * Get endingTime.
     *
     * @return int
     */
    public function getEndingTime()
    {
        return $this->endingTime;
    }

    /**
     * Set passWaiting.
     *
     * @param string|null $passWaiting
     *
     * @return TstTests
     */
    public function setPassWaiting($passWaiting = null)
    {
        $this->passWaiting = $passWaiting;

        return $this;
    }

    /**
     * Get passWaiting.
     *
     * @return string|null
     */
    public function getPassWaiting()
    {
        return $this->passWaiting;
    }

    /**
     * Set followQstAnswerFixation.
     *
     * @param bool|null $followQstAnswerFixation
     *
     * @return TstTests
     */
    public function setFollowQstAnswerFixation($followQstAnswerFixation = null)
    {
        $this->followQstAnswerFixation = $followQstAnswerFixation;

        return $this;
    }

    /**
     * Get followQstAnswerFixation.
     *
     * @return bool|null
     */
    public function getFollowQstAnswerFixation()
    {
        return $this->followQstAnswerFixation;
    }

    /**
     * Set blockAfterPassed.
     *
     * @param bool|null $blockAfterPassed
     *
     * @return TstTests
     */
    public function setBlockAfterPassed($blockAfterPassed = null)
    {
        $this->blockAfterPassed = $blockAfterPassed;

        return $this;
    }

    /**
     * Get blockAfterPassed.
     *
     * @return bool|null
     */
    public function getBlockAfterPassed()
    {
        return $this->blockAfterPassed;
    }
}
