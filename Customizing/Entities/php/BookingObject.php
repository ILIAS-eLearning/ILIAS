<?php



/**
 * BookingObject
 */
class BookingObject
{
    /**
     * @var int
     */
    private $bookingObjectId = '0';

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var int|null
     */
    private $scheduleId;

    /**
     * @var int|null
     */
    private $poolId = '0';

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int
     */
    private $nrItems = '1';

    /**
     * @var string|null
     */
    private $infoFile;

    /**
     * @var string|null
     */
    private $postText;

    /**
     * @var string|null
     */
    private $postFile;


    /**
     * Get bookingObjectId.
     *
     * @return int
     */
    public function getBookingObjectId()
    {
        return $this->bookingObjectId;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return BookingObject
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set scheduleId.
     *
     * @param int|null $scheduleId
     *
     * @return BookingObject
     */
    public function setScheduleId($scheduleId = null)
    {
        $this->scheduleId = $scheduleId;

        return $this;
    }

    /**
     * Get scheduleId.
     *
     * @return int|null
     */
    public function getScheduleId()
    {
        return $this->scheduleId;
    }

    /**
     * Set poolId.
     *
     * @param int|null $poolId
     *
     * @return BookingObject
     */
    public function setPoolId($poolId = null)
    {
        $this->poolId = $poolId;

        return $this;
    }

    /**
     * Get poolId.
     *
     * @return int|null
     */
    public function getPoolId()
    {
        return $this->poolId;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return BookingObject
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set nrItems.
     *
     * @param int $nrItems
     *
     * @return BookingObject
     */
    public function setNrItems($nrItems)
    {
        $this->nrItems = $nrItems;

        return $this;
    }

    /**
     * Get nrItems.
     *
     * @return int
     */
    public function getNrItems()
    {
        return $this->nrItems;
    }

    /**
     * Set infoFile.
     *
     * @param string|null $infoFile
     *
     * @return BookingObject
     */
    public function setInfoFile($infoFile = null)
    {
        $this->infoFile = $infoFile;

        return $this;
    }

    /**
     * Get infoFile.
     *
     * @return string|null
     */
    public function getInfoFile()
    {
        return $this->infoFile;
    }

    /**
     * Set postText.
     *
     * @param string|null $postText
     *
     * @return BookingObject
     */
    public function setPostText($postText = null)
    {
        $this->postText = $postText;

        return $this;
    }

    /**
     * Get postText.
     *
     * @return string|null
     */
    public function getPostText()
    {
        return $this->postText;
    }

    /**
     * Set postFile.
     *
     * @param string|null $postFile
     *
     * @return BookingObject
     */
    public function setPostFile($postFile = null)
    {
        $this->postFile = $postFile;

        return $this;
    }

    /**
     * Get postFile.
     *
     * @return string|null
     */
    public function getPostFile()
    {
        return $this->postFile;
    }
}
