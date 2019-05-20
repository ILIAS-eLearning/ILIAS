<?php



/**
 * ExcAssFileOrder
 */
class ExcAssFileOrder
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $assignmentId = '0';

    /**
     * @var string
     */
    private $filename = '';

    /**
     * @var int
     */
    private $orderNr = '0';


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
     * Set assignmentId.
     *
     * @param int $assignmentId
     *
     * @return ExcAssFileOrder
     */
    public function setAssignmentId($assignmentId)
    {
        $this->assignmentId = $assignmentId;

        return $this;
    }

    /**
     * Get assignmentId.
     *
     * @return int
     */
    public function getAssignmentId()
    {
        return $this->assignmentId;
    }

    /**
     * Set filename.
     *
     * @param string $filename
     *
     * @return ExcAssFileOrder
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set orderNr.
     *
     * @param int $orderNr
     *
     * @return ExcAssFileOrder
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get orderNr.
     *
     * @return int
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }
}
