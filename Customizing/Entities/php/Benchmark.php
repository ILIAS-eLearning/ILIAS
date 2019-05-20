<?php



/**
 * Benchmark
 */
class Benchmark
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var \DateTime|null
     */
    private $cdate;

    /**
     * @var string|null
     */
    private $module;

    /**
     * @var string|null
     */
    private $benchmark;

    /**
     * @var float|null
     */
    private $duration;

    /**
     * @var string|null
     */
    private $sqlStmt;


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
     * Set cdate.
     *
     * @param \DateTime|null $cdate
     *
     * @return Benchmark
     */
    public function setCdate($cdate = null)
    {
        $this->cdate = $cdate;

        return $this;
    }

    /**
     * Get cdate.
     *
     * @return \DateTime|null
     */
    public function getCdate()
    {
        return $this->cdate;
    }

    /**
     * Set module.
     *
     * @param string|null $module
     *
     * @return Benchmark
     */
    public function setModule($module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module.
     *
     * @return string|null
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set benchmark.
     *
     * @param string|null $benchmark
     *
     * @return Benchmark
     */
    public function setBenchmark($benchmark = null)
    {
        $this->benchmark = $benchmark;

        return $this;
    }

    /**
     * Get benchmark.
     *
     * @return string|null
     */
    public function getBenchmark()
    {
        return $this->benchmark;
    }

    /**
     * Set duration.
     *
     * @param float|null $duration
     *
     * @return Benchmark
     */
    public function setDuration($duration = null)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration.
     *
     * @return float|null
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set sqlStmt.
     *
     * @param string|null $sqlStmt
     *
     * @return Benchmark
     */
    public function setSqlStmt($sqlStmt = null)
    {
        $this->sqlStmt = $sqlStmt;

        return $this;
    }

    /**
     * Get sqlStmt.
     *
     * @return string|null
     */
    public function getSqlStmt()
    {
        return $this->sqlStmt;
    }
}
