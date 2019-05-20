<?php



/**
 * ScResourceDependen
 */
class ScResourceDependen
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $resId;

    /**
     * @var string|null
     */
    private $identifierref;

    /**
     * @var int|null
     */
    private $nr;


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
     * Set resId.
     *
     * @param int|null $resId
     *
     * @return ScResourceDependen
     */
    public function setResId($resId = null)
    {
        $this->resId = $resId;

        return $this;
    }

    /**
     * Get resId.
     *
     * @return int|null
     */
    public function getResId()
    {
        return $this->resId;
    }

    /**
     * Set identifierref.
     *
     * @param string|null $identifierref
     *
     * @return ScResourceDependen
     */
    public function setIdentifierref($identifierref = null)
    {
        $this->identifierref = $identifierref;

        return $this;
    }

    /**
     * Get identifierref.
     *
     * @return string|null
     */
    public function getIdentifierref()
    {
        return $this->identifierref;
    }

    /**
     * Set nr.
     *
     * @param int|null $nr
     *
     * @return ScResourceDependen
     */
    public function setNr($nr = null)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int|null
     */
    public function getNr()
    {
        return $this->nr;
    }
}
