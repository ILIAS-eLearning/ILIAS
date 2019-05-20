<?php



/**
 * CpSequencing
 */
class CpSequencing
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

    /**
     * @var string|null
     */
    private $activityabsdurlimit;

    /**
     * @var string|null
     */
    private $activityexpdurlimit;

    /**
     * @var string|null
     */
    private $attemptabsdurlimit;

    /**
     * @var string|null
     */
    private $attemptexpdurlimit;

    /**
     * @var int|null
     */
    private $attemptlimit;

    /**
     * @var string|null
     */
    private $begintimelimit;

    /**
     * @var bool|null
     */
    private $choice;

    /**
     * @var bool|null
     */
    private $choiceexit;

    /**
     * @var bool|null
     */
    private $completionbycontent;

    /**
     * @var bool|null
     */
    private $constrainchoice;

    /**
     * @var string|null
     */
    private $endtimelimit;

    /**
     * @var bool|null
     */
    private $flow;

    /**
     * @var bool|null
     */
    private $forwardonly;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var bool|null
     */
    private $measuresatisfactive;

    /**
     * @var float|null
     */
    private $objectivemeasweight;

    /**
     * @var bool|null
     */
    private $objectivebycontent;

    /**
     * @var bool|null
     */
    private $preventactivation;

    /**
     * @var string|null
     */
    private $randomizationtiming;

    /**
     * @var bool|null
     */
    private $reorderchildren;

    /**
     * @var string|null
     */
    private $requiredcompleted;

    /**
     * @var string|null
     */
    private $requiredincomplete;

    /**
     * @var string|null
     */
    private $requirednotsatisfied;

    /**
     * @var string|null
     */
    private $requiredforsatisfied;

    /**
     * @var bool|null
     */
    private $rollupobjectivesatis;

    /**
     * @var bool|null
     */
    private $rollupprogcompletion;

    /**
     * @var int|null
     */
    private $selectcount;

    /**
     * @var string|null
     */
    private $selectiontiming;

    /**
     * @var string|null
     */
    private $sequencingid;

    /**
     * @var bool|null
     */
    private $tracked;

    /**
     * @var bool|null
     */
    private $usecurattemptobjinfo;

    /**
     * @var bool|null
     */
    private $usecurattemptproginfo;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set activityabsdurlimit.
     *
     * @param string|null $activityabsdurlimit
     *
     * @return CpSequencing
     */
    public function setActivityabsdurlimit($activityabsdurlimit = null)
    {
        $this->activityabsdurlimit = $activityabsdurlimit;

        return $this;
    }

    /**
     * Get activityabsdurlimit.
     *
     * @return string|null
     */
    public function getActivityabsdurlimit()
    {
        return $this->activityabsdurlimit;
    }

    /**
     * Set activityexpdurlimit.
     *
     * @param string|null $activityexpdurlimit
     *
     * @return CpSequencing
     */
    public function setActivityexpdurlimit($activityexpdurlimit = null)
    {
        $this->activityexpdurlimit = $activityexpdurlimit;

        return $this;
    }

    /**
     * Get activityexpdurlimit.
     *
     * @return string|null
     */
    public function getActivityexpdurlimit()
    {
        return $this->activityexpdurlimit;
    }

    /**
     * Set attemptabsdurlimit.
     *
     * @param string|null $attemptabsdurlimit
     *
     * @return CpSequencing
     */
    public function setAttemptabsdurlimit($attemptabsdurlimit = null)
    {
        $this->attemptabsdurlimit = $attemptabsdurlimit;

        return $this;
    }

    /**
     * Get attemptabsdurlimit.
     *
     * @return string|null
     */
    public function getAttemptabsdurlimit()
    {
        return $this->attemptabsdurlimit;
    }

    /**
     * Set attemptexpdurlimit.
     *
     * @param string|null $attemptexpdurlimit
     *
     * @return CpSequencing
     */
    public function setAttemptexpdurlimit($attemptexpdurlimit = null)
    {
        $this->attemptexpdurlimit = $attemptexpdurlimit;

        return $this;
    }

    /**
     * Get attemptexpdurlimit.
     *
     * @return string|null
     */
    public function getAttemptexpdurlimit()
    {
        return $this->attemptexpdurlimit;
    }

    /**
     * Set attemptlimit.
     *
     * @param int|null $attemptlimit
     *
     * @return CpSequencing
     */
    public function setAttemptlimit($attemptlimit = null)
    {
        $this->attemptlimit = $attemptlimit;

        return $this;
    }

    /**
     * Get attemptlimit.
     *
     * @return int|null
     */
    public function getAttemptlimit()
    {
        return $this->attemptlimit;
    }

    /**
     * Set begintimelimit.
     *
     * @param string|null $begintimelimit
     *
     * @return CpSequencing
     */
    public function setBegintimelimit($begintimelimit = null)
    {
        $this->begintimelimit = $begintimelimit;

        return $this;
    }

    /**
     * Get begintimelimit.
     *
     * @return string|null
     */
    public function getBegintimelimit()
    {
        return $this->begintimelimit;
    }

    /**
     * Set choice.
     *
     * @param bool|null $choice
     *
     * @return CpSequencing
     */
    public function setChoice($choice = null)
    {
        $this->choice = $choice;

        return $this;
    }

    /**
     * Get choice.
     *
     * @return bool|null
     */
    public function getChoice()
    {
        return $this->choice;
    }

    /**
     * Set choiceexit.
     *
     * @param bool|null $choiceexit
     *
     * @return CpSequencing
     */
    public function setChoiceexit($choiceexit = null)
    {
        $this->choiceexit = $choiceexit;

        return $this;
    }

    /**
     * Get choiceexit.
     *
     * @return bool|null
     */
    public function getChoiceexit()
    {
        return $this->choiceexit;
    }

    /**
     * Set completionbycontent.
     *
     * @param bool|null $completionbycontent
     *
     * @return CpSequencing
     */
    public function setCompletionbycontent($completionbycontent = null)
    {
        $this->completionbycontent = $completionbycontent;

        return $this;
    }

    /**
     * Get completionbycontent.
     *
     * @return bool|null
     */
    public function getCompletionbycontent()
    {
        return $this->completionbycontent;
    }

    /**
     * Set constrainchoice.
     *
     * @param bool|null $constrainchoice
     *
     * @return CpSequencing
     */
    public function setConstrainchoice($constrainchoice = null)
    {
        $this->constrainchoice = $constrainchoice;

        return $this;
    }

    /**
     * Get constrainchoice.
     *
     * @return bool|null
     */
    public function getConstrainchoice()
    {
        return $this->constrainchoice;
    }

    /**
     * Set endtimelimit.
     *
     * @param string|null $endtimelimit
     *
     * @return CpSequencing
     */
    public function setEndtimelimit($endtimelimit = null)
    {
        $this->endtimelimit = $endtimelimit;

        return $this;
    }

    /**
     * Get endtimelimit.
     *
     * @return string|null
     */
    public function getEndtimelimit()
    {
        return $this->endtimelimit;
    }

    /**
     * Set flow.
     *
     * @param bool|null $flow
     *
     * @return CpSequencing
     */
    public function setFlow($flow = null)
    {
        $this->flow = $flow;

        return $this;
    }

    /**
     * Get flow.
     *
     * @return bool|null
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * Set forwardonly.
     *
     * @param bool|null $forwardonly
     *
     * @return CpSequencing
     */
    public function setForwardonly($forwardonly = null)
    {
        $this->forwardonly = $forwardonly;

        return $this;
    }

    /**
     * Get forwardonly.
     *
     * @return bool|null
     */
    public function getForwardonly()
    {
        return $this->forwardonly;
    }

    /**
     * Set id.
     *
     * @param string|null $id
     *
     * @return CpSequencing
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set measuresatisfactive.
     *
     * @param bool|null $measuresatisfactive
     *
     * @return CpSequencing
     */
    public function setMeasuresatisfactive($measuresatisfactive = null)
    {
        $this->measuresatisfactive = $measuresatisfactive;

        return $this;
    }

    /**
     * Get measuresatisfactive.
     *
     * @return bool|null
     */
    public function getMeasuresatisfactive()
    {
        return $this->measuresatisfactive;
    }

    /**
     * Set objectivemeasweight.
     *
     * @param float|null $objectivemeasweight
     *
     * @return CpSequencing
     */
    public function setObjectivemeasweight($objectivemeasweight = null)
    {
        $this->objectivemeasweight = $objectivemeasweight;

        return $this;
    }

    /**
     * Get objectivemeasweight.
     *
     * @return float|null
     */
    public function getObjectivemeasweight()
    {
        return $this->objectivemeasweight;
    }

    /**
     * Set objectivebycontent.
     *
     * @param bool|null $objectivebycontent
     *
     * @return CpSequencing
     */
    public function setObjectivebycontent($objectivebycontent = null)
    {
        $this->objectivebycontent = $objectivebycontent;

        return $this;
    }

    /**
     * Get objectivebycontent.
     *
     * @return bool|null
     */
    public function getObjectivebycontent()
    {
        return $this->objectivebycontent;
    }

    /**
     * Set preventactivation.
     *
     * @param bool|null $preventactivation
     *
     * @return CpSequencing
     */
    public function setPreventactivation($preventactivation = null)
    {
        $this->preventactivation = $preventactivation;

        return $this;
    }

    /**
     * Get preventactivation.
     *
     * @return bool|null
     */
    public function getPreventactivation()
    {
        return $this->preventactivation;
    }

    /**
     * Set randomizationtiming.
     *
     * @param string|null $randomizationtiming
     *
     * @return CpSequencing
     */
    public function setRandomizationtiming($randomizationtiming = null)
    {
        $this->randomizationtiming = $randomizationtiming;

        return $this;
    }

    /**
     * Get randomizationtiming.
     *
     * @return string|null
     */
    public function getRandomizationtiming()
    {
        return $this->randomizationtiming;
    }

    /**
     * Set reorderchildren.
     *
     * @param bool|null $reorderchildren
     *
     * @return CpSequencing
     */
    public function setReorderchildren($reorderchildren = null)
    {
        $this->reorderchildren = $reorderchildren;

        return $this;
    }

    /**
     * Get reorderchildren.
     *
     * @return bool|null
     */
    public function getReorderchildren()
    {
        return $this->reorderchildren;
    }

    /**
     * Set requiredcompleted.
     *
     * @param string|null $requiredcompleted
     *
     * @return CpSequencing
     */
    public function setRequiredcompleted($requiredcompleted = null)
    {
        $this->requiredcompleted = $requiredcompleted;

        return $this;
    }

    /**
     * Get requiredcompleted.
     *
     * @return string|null
     */
    public function getRequiredcompleted()
    {
        return $this->requiredcompleted;
    }

    /**
     * Set requiredincomplete.
     *
     * @param string|null $requiredincomplete
     *
     * @return CpSequencing
     */
    public function setRequiredincomplete($requiredincomplete = null)
    {
        $this->requiredincomplete = $requiredincomplete;

        return $this;
    }

    /**
     * Get requiredincomplete.
     *
     * @return string|null
     */
    public function getRequiredincomplete()
    {
        return $this->requiredincomplete;
    }

    /**
     * Set requirednotsatisfied.
     *
     * @param string|null $requirednotsatisfied
     *
     * @return CpSequencing
     */
    public function setRequirednotsatisfied($requirednotsatisfied = null)
    {
        $this->requirednotsatisfied = $requirednotsatisfied;

        return $this;
    }

    /**
     * Get requirednotsatisfied.
     *
     * @return string|null
     */
    public function getRequirednotsatisfied()
    {
        return $this->requirednotsatisfied;
    }

    /**
     * Set requiredforsatisfied.
     *
     * @param string|null $requiredforsatisfied
     *
     * @return CpSequencing
     */
    public function setRequiredforsatisfied($requiredforsatisfied = null)
    {
        $this->requiredforsatisfied = $requiredforsatisfied;

        return $this;
    }

    /**
     * Get requiredforsatisfied.
     *
     * @return string|null
     */
    public function getRequiredforsatisfied()
    {
        return $this->requiredforsatisfied;
    }

    /**
     * Set rollupobjectivesatis.
     *
     * @param bool|null $rollupobjectivesatis
     *
     * @return CpSequencing
     */
    public function setRollupobjectivesatis($rollupobjectivesatis = null)
    {
        $this->rollupobjectivesatis = $rollupobjectivesatis;

        return $this;
    }

    /**
     * Get rollupobjectivesatis.
     *
     * @return bool|null
     */
    public function getRollupobjectivesatis()
    {
        return $this->rollupobjectivesatis;
    }

    /**
     * Set rollupprogcompletion.
     *
     * @param bool|null $rollupprogcompletion
     *
     * @return CpSequencing
     */
    public function setRollupprogcompletion($rollupprogcompletion = null)
    {
        $this->rollupprogcompletion = $rollupprogcompletion;

        return $this;
    }

    /**
     * Get rollupprogcompletion.
     *
     * @return bool|null
     */
    public function getRollupprogcompletion()
    {
        return $this->rollupprogcompletion;
    }

    /**
     * Set selectcount.
     *
     * @param int|null $selectcount
     *
     * @return CpSequencing
     */
    public function setSelectcount($selectcount = null)
    {
        $this->selectcount = $selectcount;

        return $this;
    }

    /**
     * Get selectcount.
     *
     * @return int|null
     */
    public function getSelectcount()
    {
        return $this->selectcount;
    }

    /**
     * Set selectiontiming.
     *
     * @param string|null $selectiontiming
     *
     * @return CpSequencing
     */
    public function setSelectiontiming($selectiontiming = null)
    {
        $this->selectiontiming = $selectiontiming;

        return $this;
    }

    /**
     * Get selectiontiming.
     *
     * @return string|null
     */
    public function getSelectiontiming()
    {
        return $this->selectiontiming;
    }

    /**
     * Set sequencingid.
     *
     * @param string|null $sequencingid
     *
     * @return CpSequencing
     */
    public function setSequencingid($sequencingid = null)
    {
        $this->sequencingid = $sequencingid;

        return $this;
    }

    /**
     * Get sequencingid.
     *
     * @return string|null
     */
    public function getSequencingid()
    {
        return $this->sequencingid;
    }

    /**
     * Set tracked.
     *
     * @param bool|null $tracked
     *
     * @return CpSequencing
     */
    public function setTracked($tracked = null)
    {
        $this->tracked = $tracked;

        return $this;
    }

    /**
     * Get tracked.
     *
     * @return bool|null
     */
    public function getTracked()
    {
        return $this->tracked;
    }

    /**
     * Set usecurattemptobjinfo.
     *
     * @param bool|null $usecurattemptobjinfo
     *
     * @return CpSequencing
     */
    public function setUsecurattemptobjinfo($usecurattemptobjinfo = null)
    {
        $this->usecurattemptobjinfo = $usecurattemptobjinfo;

        return $this;
    }

    /**
     * Get usecurattemptobjinfo.
     *
     * @return bool|null
     */
    public function getUsecurattemptobjinfo()
    {
        return $this->usecurattemptobjinfo;
    }

    /**
     * Set usecurattemptproginfo.
     *
     * @param bool|null $usecurattemptproginfo
     *
     * @return CpSequencing
     */
    public function setUsecurattemptproginfo($usecurattemptproginfo = null)
    {
        $this->usecurattemptproginfo = $usecurattemptproginfo;

        return $this;
    }

    /**
     * Get usecurattemptproginfo.
     *
     * @return bool|null
     */
    public function getUsecurattemptproginfo()
    {
        return $this->usecurattemptproginfo;
    }
}
