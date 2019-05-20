<?php



/**
 * IlCertBgtaskMigr
 */
class IlCertBgtaskMigr
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $lock = '0';

    /**
     * @var int
     */
    private $foundItems = '0';

    /**
     * @var int
     */
    private $processedItems = '0';

    /**
     * @var int
     */
    private $migratedItems = '0';

    /**
     * @var int
     */
    private $progress = '0';

    /**
     * @var string
     */
    private $state;

    /**
     * @var int|null
     */
    private $startedTs = '0';

    /**
     * @var int|null
     */
    private $finishedTs;


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
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return IlCertBgtaskMigr
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set lock.
     *
     * @param int $lock
     *
     * @return IlCertBgtaskMigr
     */
    public function setLock($lock)
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * Get lock.
     *
     * @return int
     */
    public function getLock()
    {
        return $this->lock;
    }

    /**
     * Set foundItems.
     *
     * @param int $foundItems
     *
     * @return IlCertBgtaskMigr
     */
    public function setFoundItems($foundItems)
    {
        $this->foundItems = $foundItems;

        return $this;
    }

    /**
     * Get foundItems.
     *
     * @return int
     */
    public function getFoundItems()
    {
        return $this->foundItems;
    }

    /**
     * Set processedItems.
     *
     * @param int $processedItems
     *
     * @return IlCertBgtaskMigr
     */
    public function setProcessedItems($processedItems)
    {
        $this->processedItems = $processedItems;

        return $this;
    }

    /**
     * Get processedItems.
     *
     * @return int
     */
    public function getProcessedItems()
    {
        return $this->processedItems;
    }

    /**
     * Set migratedItems.
     *
     * @param int $migratedItems
     *
     * @return IlCertBgtaskMigr
     */
    public function setMigratedItems($migratedItems)
    {
        $this->migratedItems = $migratedItems;

        return $this;
    }

    /**
     * Get migratedItems.
     *
     * @return int
     */
    public function getMigratedItems()
    {
        return $this->migratedItems;
    }

    /**
     * Set progress.
     *
     * @param int $progress
     *
     * @return IlCertBgtaskMigr
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress.
     *
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Set state.
     *
     * @param string $state
     *
     * @return IlCertBgtaskMigr
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set startedTs.
     *
     * @param int|null $startedTs
     *
     * @return IlCertBgtaskMigr
     */
    public function setStartedTs($startedTs = null)
    {
        $this->startedTs = $startedTs;

        return $this;
    }

    /**
     * Get startedTs.
     *
     * @return int|null
     */
    public function getStartedTs()
    {
        return $this->startedTs;
    }

    /**
     * Set finishedTs.
     *
     * @param int|null $finishedTs
     *
     * @return IlCertBgtaskMigr
     */
    public function setFinishedTs($finishedTs = null)
    {
        $this->finishedTs = $finishedTs;

        return $this;
    }

    /**
     * Get finishedTs.
     *
     * @return int|null
     */
    public function getFinishedTs()
    {
        return $this->finishedTs;
    }
}
