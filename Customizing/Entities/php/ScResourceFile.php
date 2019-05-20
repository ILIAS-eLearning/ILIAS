<?php



/**
 * ScResourceFile
 */
class ScResourceFile
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
    private $href;

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
     * @return ScResourceFile
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
     * Set href.
     *
     * @param string|null $href
     *
     * @return ScResourceFile
     */
    public function setHref($href = null)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Get href.
     *
     * @return string|null
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Set nr.
     *
     * @param int|null $nr
     *
     * @return ScResourceFile
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
