<?php



/**
 * SahsLm
 */
class SahsLm
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $cOnline = 'n';

    /**
     * @var string|null
     */
    private $apiAdapter = 'API';

    /**
     * @var string|null
     */
    private $apiFuncPrefix = 'LMS';

    /**
     * @var string|null
     */
    private $credit = 'credit';

    /**
     * @var string|null
     */
    private $defaultLessonMode = 'normal';

    /**
     * @var string|null
     */
    private $autoReview = 'n';

    /**
     * @var string|null
     */
    private $cType;

    /**
     * @var int|null
     */
    private $maxAttempt = '0';

    /**
     * @var int|null
     */
    private $moduleVersion = '1';

    /**
     * @var int
     */
    private $editable = '0';

    /**
     * @var int
     */
    private $stylesheet = '0';

    /**
     * @var int
     */
    private $glossary = '0';

    /**
     * @var int|null
     */
    private $questionTries = '3';

    /**
     * @var string
     */
    private $unlimitedSession = 'n';

    /**
     * @var string
     */
    private $noMenu = 'n';

    /**
     * @var string
     */
    private $hideNavig = 'n';

    /**
     * @var string
     */
    private $debug = 'n';

    /**
     * @var string|null
     */
    private $debugpw = 'n';

    /**
     * @var int
     */
    private $entryPage = '0';

    /**
     * @var bool|null
     */
    private $seqExpMode = '0';

    /**
     * @var string|null
     */
    private $localization;

    /**
     * @var bool
     */
    private $openMode = '0';

    /**
     * @var int
     */
    private $width = '950';

    /**
     * @var int
     */
    private $height = '650';

    /**
     * @var string
     */
    private $autoContinue = 'n';

    /**
     * @var string
     */
    private $sequencing = 'y';

    /**
     * @var string
     */
    private $interactions = 'y';

    /**
     * @var string
     */
    private $objectives = 'y';

    /**
     * @var string
     */
    private $timeFromLms = 'n';

    /**
     * @var string
     */
    private $comments = 'y';

    /**
     * @var string
     */
    private $autoLastVisited = 'y';

    /**
     * @var string
     */
    private $checkValues = 'y';

    /**
     * @var string
     */
    private $offlineMode = 'n';

    /**
     * @var \DateTime|null
     */
    private $offlineZipCreated;

    /**
     * @var string
     */
    private $autoSuspend = 'n';

    /**
     * @var string
     */
    private $fourthEdition = 'n';

    /**
     * @var string|null
     */
    private $ieCompatibility;

    /**
     * @var string
     */
    private $ieForceRender = 'n';

    /**
     * @var bool|null
     */
    private $masteryScore;

    /**
     * @var bool
     */
    private $idSetting = '0';

    /**
     * @var bool
     */
    private $nameSetting = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cOnline.
     *
     * @param string|null $cOnline
     *
     * @return SahsLm
     */
    public function setCOnline($cOnline = null)
    {
        $this->cOnline = $cOnline;

        return $this;
    }

    /**
     * Get cOnline.
     *
     * @return string|null
     */
    public function getCOnline()
    {
        return $this->cOnline;
    }

    /**
     * Set apiAdapter.
     *
     * @param string|null $apiAdapter
     *
     * @return SahsLm
     */
    public function setApiAdapter($apiAdapter = null)
    {
        $this->apiAdapter = $apiAdapter;

        return $this;
    }

    /**
     * Get apiAdapter.
     *
     * @return string|null
     */
    public function getApiAdapter()
    {
        return $this->apiAdapter;
    }

    /**
     * Set apiFuncPrefix.
     *
     * @param string|null $apiFuncPrefix
     *
     * @return SahsLm
     */
    public function setApiFuncPrefix($apiFuncPrefix = null)
    {
        $this->apiFuncPrefix = $apiFuncPrefix;

        return $this;
    }

    /**
     * Get apiFuncPrefix.
     *
     * @return string|null
     */
    public function getApiFuncPrefix()
    {
        return $this->apiFuncPrefix;
    }

    /**
     * Set credit.
     *
     * @param string|null $credit
     *
     * @return SahsLm
     */
    public function setCredit($credit = null)
    {
        $this->credit = $credit;

        return $this;
    }

    /**
     * Get credit.
     *
     * @return string|null
     */
    public function getCredit()
    {
        return $this->credit;
    }

    /**
     * Set defaultLessonMode.
     *
     * @param string|null $defaultLessonMode
     *
     * @return SahsLm
     */
    public function setDefaultLessonMode($defaultLessonMode = null)
    {
        $this->defaultLessonMode = $defaultLessonMode;

        return $this;
    }

    /**
     * Get defaultLessonMode.
     *
     * @return string|null
     */
    public function getDefaultLessonMode()
    {
        return $this->defaultLessonMode;
    }

    /**
     * Set autoReview.
     *
     * @param string|null $autoReview
     *
     * @return SahsLm
     */
    public function setAutoReview($autoReview = null)
    {
        $this->autoReview = $autoReview;

        return $this;
    }

    /**
     * Get autoReview.
     *
     * @return string|null
     */
    public function getAutoReview()
    {
        return $this->autoReview;
    }

    /**
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return SahsLm
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
     * Set maxAttempt.
     *
     * @param int|null $maxAttempt
     *
     * @return SahsLm
     */
    public function setMaxAttempt($maxAttempt = null)
    {
        $this->maxAttempt = $maxAttempt;

        return $this;
    }

    /**
     * Get maxAttempt.
     *
     * @return int|null
     */
    public function getMaxAttempt()
    {
        return $this->maxAttempt;
    }

    /**
     * Set moduleVersion.
     *
     * @param int|null $moduleVersion
     *
     * @return SahsLm
     */
    public function setModuleVersion($moduleVersion = null)
    {
        $this->moduleVersion = $moduleVersion;

        return $this;
    }

    /**
     * Get moduleVersion.
     *
     * @return int|null
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * Set editable.
     *
     * @param int $editable
     *
     * @return SahsLm
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;

        return $this;
    }

    /**
     * Get editable.
     *
     * @return int
     */
    public function getEditable()
    {
        return $this->editable;
    }

    /**
     * Set stylesheet.
     *
     * @param int $stylesheet
     *
     * @return SahsLm
     */
    public function setStylesheet($stylesheet)
    {
        $this->stylesheet = $stylesheet;

        return $this;
    }

    /**
     * Get stylesheet.
     *
     * @return int
     */
    public function getStylesheet()
    {
        return $this->stylesheet;
    }

    /**
     * Set glossary.
     *
     * @param int $glossary
     *
     * @return SahsLm
     */
    public function setGlossary($glossary)
    {
        $this->glossary = $glossary;

        return $this;
    }

    /**
     * Get glossary.
     *
     * @return int
     */
    public function getGlossary()
    {
        return $this->glossary;
    }

    /**
     * Set questionTries.
     *
     * @param int|null $questionTries
     *
     * @return SahsLm
     */
    public function setQuestionTries($questionTries = null)
    {
        $this->questionTries = $questionTries;

        return $this;
    }

    /**
     * Get questionTries.
     *
     * @return int|null
     */
    public function getQuestionTries()
    {
        return $this->questionTries;
    }

    /**
     * Set unlimitedSession.
     *
     * @param string $unlimitedSession
     *
     * @return SahsLm
     */
    public function setUnlimitedSession($unlimitedSession)
    {
        $this->unlimitedSession = $unlimitedSession;

        return $this;
    }

    /**
     * Get unlimitedSession.
     *
     * @return string
     */
    public function getUnlimitedSession()
    {
        return $this->unlimitedSession;
    }

    /**
     * Set noMenu.
     *
     * @param string $noMenu
     *
     * @return SahsLm
     */
    public function setNoMenu($noMenu)
    {
        $this->noMenu = $noMenu;

        return $this;
    }

    /**
     * Get noMenu.
     *
     * @return string
     */
    public function getNoMenu()
    {
        return $this->noMenu;
    }

    /**
     * Set hideNavig.
     *
     * @param string $hideNavig
     *
     * @return SahsLm
     */
    public function setHideNavig($hideNavig)
    {
        $this->hideNavig = $hideNavig;

        return $this;
    }

    /**
     * Get hideNavig.
     *
     * @return string
     */
    public function getHideNavig()
    {
        return $this->hideNavig;
    }

    /**
     * Set debug.
     *
     * @param string $debug
     *
     * @return SahsLm
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Get debug.
     *
     * @return string
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set debugpw.
     *
     * @param string|null $debugpw
     *
     * @return SahsLm
     */
    public function setDebugpw($debugpw = null)
    {
        $this->debugpw = $debugpw;

        return $this;
    }

    /**
     * Get debugpw.
     *
     * @return string|null
     */
    public function getDebugpw()
    {
        return $this->debugpw;
    }

    /**
     * Set entryPage.
     *
     * @param int $entryPage
     *
     * @return SahsLm
     */
    public function setEntryPage($entryPage)
    {
        $this->entryPage = $entryPage;

        return $this;
    }

    /**
     * Get entryPage.
     *
     * @return int
     */
    public function getEntryPage()
    {
        return $this->entryPage;
    }

    /**
     * Set seqExpMode.
     *
     * @param bool|null $seqExpMode
     *
     * @return SahsLm
     */
    public function setSeqExpMode($seqExpMode = null)
    {
        $this->seqExpMode = $seqExpMode;

        return $this;
    }

    /**
     * Get seqExpMode.
     *
     * @return bool|null
     */
    public function getSeqExpMode()
    {
        return $this->seqExpMode;
    }

    /**
     * Set localization.
     *
     * @param string|null $localization
     *
     * @return SahsLm
     */
    public function setLocalization($localization = null)
    {
        $this->localization = $localization;

        return $this;
    }

    /**
     * Get localization.
     *
     * @return string|null
     */
    public function getLocalization()
    {
        return $this->localization;
    }

    /**
     * Set openMode.
     *
     * @param bool $openMode
     *
     * @return SahsLm
     */
    public function setOpenMode($openMode)
    {
        $this->openMode = $openMode;

        return $this;
    }

    /**
     * Get openMode.
     *
     * @return bool
     */
    public function getOpenMode()
    {
        return $this->openMode;
    }

    /**
     * Set width.
     *
     * @param int $width
     *
     * @return SahsLm
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height.
     *
     * @param int $height
     *
     * @return SahsLm
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set autoContinue.
     *
     * @param string $autoContinue
     *
     * @return SahsLm
     */
    public function setAutoContinue($autoContinue)
    {
        $this->autoContinue = $autoContinue;

        return $this;
    }

    /**
     * Get autoContinue.
     *
     * @return string
     */
    public function getAutoContinue()
    {
        return $this->autoContinue;
    }

    /**
     * Set sequencing.
     *
     * @param string $sequencing
     *
     * @return SahsLm
     */
    public function setSequencing($sequencing)
    {
        $this->sequencing = $sequencing;

        return $this;
    }

    /**
     * Get sequencing.
     *
     * @return string
     */
    public function getSequencing()
    {
        return $this->sequencing;
    }

    /**
     * Set interactions.
     *
     * @param string $interactions
     *
     * @return SahsLm
     */
    public function setInteractions($interactions)
    {
        $this->interactions = $interactions;

        return $this;
    }

    /**
     * Get interactions.
     *
     * @return string
     */
    public function getInteractions()
    {
        return $this->interactions;
    }

    /**
     * Set objectives.
     *
     * @param string $objectives
     *
     * @return SahsLm
     */
    public function setObjectives($objectives)
    {
        $this->objectives = $objectives;

        return $this;
    }

    /**
     * Get objectives.
     *
     * @return string
     */
    public function getObjectives()
    {
        return $this->objectives;
    }

    /**
     * Set timeFromLms.
     *
     * @param string $timeFromLms
     *
     * @return SahsLm
     */
    public function setTimeFromLms($timeFromLms)
    {
        $this->timeFromLms = $timeFromLms;

        return $this;
    }

    /**
     * Get timeFromLms.
     *
     * @return string
     */
    public function getTimeFromLms()
    {
        return $this->timeFromLms;
    }

    /**
     * Set comments.
     *
     * @param string $comments
     *
     * @return SahsLm
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments.
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set autoLastVisited.
     *
     * @param string $autoLastVisited
     *
     * @return SahsLm
     */
    public function setAutoLastVisited($autoLastVisited)
    {
        $this->autoLastVisited = $autoLastVisited;

        return $this;
    }

    /**
     * Get autoLastVisited.
     *
     * @return string
     */
    public function getAutoLastVisited()
    {
        return $this->autoLastVisited;
    }

    /**
     * Set checkValues.
     *
     * @param string $checkValues
     *
     * @return SahsLm
     */
    public function setCheckValues($checkValues)
    {
        $this->checkValues = $checkValues;

        return $this;
    }

    /**
     * Get checkValues.
     *
     * @return string
     */
    public function getCheckValues()
    {
        return $this->checkValues;
    }

    /**
     * Set offlineMode.
     *
     * @param string $offlineMode
     *
     * @return SahsLm
     */
    public function setOfflineMode($offlineMode)
    {
        $this->offlineMode = $offlineMode;

        return $this;
    }

    /**
     * Get offlineMode.
     *
     * @return string
     */
    public function getOfflineMode()
    {
        return $this->offlineMode;
    }

    /**
     * Set offlineZipCreated.
     *
     * @param \DateTime|null $offlineZipCreated
     *
     * @return SahsLm
     */
    public function setOfflineZipCreated($offlineZipCreated = null)
    {
        $this->offlineZipCreated = $offlineZipCreated;

        return $this;
    }

    /**
     * Get offlineZipCreated.
     *
     * @return \DateTime|null
     */
    public function getOfflineZipCreated()
    {
        return $this->offlineZipCreated;
    }

    /**
     * Set autoSuspend.
     *
     * @param string $autoSuspend
     *
     * @return SahsLm
     */
    public function setAutoSuspend($autoSuspend)
    {
        $this->autoSuspend = $autoSuspend;

        return $this;
    }

    /**
     * Get autoSuspend.
     *
     * @return string
     */
    public function getAutoSuspend()
    {
        return $this->autoSuspend;
    }

    /**
     * Set fourthEdition.
     *
     * @param string $fourthEdition
     *
     * @return SahsLm
     */
    public function setFourthEdition($fourthEdition)
    {
        $this->fourthEdition = $fourthEdition;

        return $this;
    }

    /**
     * Get fourthEdition.
     *
     * @return string
     */
    public function getFourthEdition()
    {
        return $this->fourthEdition;
    }

    /**
     * Set ieCompatibility.
     *
     * @param string|null $ieCompatibility
     *
     * @return SahsLm
     */
    public function setIeCompatibility($ieCompatibility = null)
    {
        $this->ieCompatibility = $ieCompatibility;

        return $this;
    }

    /**
     * Get ieCompatibility.
     *
     * @return string|null
     */
    public function getIeCompatibility()
    {
        return $this->ieCompatibility;
    }

    /**
     * Set ieForceRender.
     *
     * @param string $ieForceRender
     *
     * @return SahsLm
     */
    public function setIeForceRender($ieForceRender)
    {
        $this->ieForceRender = $ieForceRender;

        return $this;
    }

    /**
     * Get ieForceRender.
     *
     * @return string
     */
    public function getIeForceRender()
    {
        return $this->ieForceRender;
    }

    /**
     * Set masteryScore.
     *
     * @param bool|null $masteryScore
     *
     * @return SahsLm
     */
    public function setMasteryScore($masteryScore = null)
    {
        $this->masteryScore = $masteryScore;

        return $this;
    }

    /**
     * Get masteryScore.
     *
     * @return bool|null
     */
    public function getMasteryScore()
    {
        return $this->masteryScore;
    }

    /**
     * Set idSetting.
     *
     * @param bool $idSetting
     *
     * @return SahsLm
     */
    public function setIdSetting($idSetting)
    {
        $this->idSetting = $idSetting;

        return $this;
    }

    /**
     * Get idSetting.
     *
     * @return bool
     */
    public function getIdSetting()
    {
        return $this->idSetting;
    }

    /**
     * Set nameSetting.
     *
     * @param bool $nameSetting
     *
     * @return SahsLm
     */
    public function setNameSetting($nameSetting)
    {
        $this->nameSetting = $nameSetting;

        return $this;
    }

    /**
     * Get nameSetting.
     *
     * @return bool
     */
    public function getNameSetting()
    {
        return $this->nameSetting;
    }
}
