<?php



/**
 * ScormObject
 */
class ScormObject
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $cType;

    /**
     * @var int
     */
    private $slmId = '0';


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
     * Set title.
     *
     * @param string|null $title
     *
     * @return ScormObject
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return ScormObject
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
     * Set slmId.
     *
     * @param int $slmId
     *
     * @return ScormObject
     */
    public function setSlmId($slmId)
    {
        $this->slmId = $slmId;

        return $this;
    }

    /**
     * Get slmId.
     *
     * @return int
     */
    public function getSlmId()
    {
        return $this->slmId;
    }
}
