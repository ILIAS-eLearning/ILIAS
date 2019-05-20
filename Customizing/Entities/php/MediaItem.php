<?php



/**
 * MediaItem
 */
class MediaItem
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string|null
     */
    private $width;

    /**
     * @var string|null
     */
    private $height;

    /**
     * @var string|null
     */
    private $halign = 'Left';

    /**
     * @var string|null
     */
    private $caption;

    /**
     * @var int
     */
    private $nr = '0';

    /**
     * @var string|null
     */
    private $purpose = 'Standard';

    /**
     * @var int
     */
    private $mobId = '0';

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var string|null
     */
    private $locationType = 'LocalFile';

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var string|null
     */
    private $param;

    /**
     * @var string|null
     */
    private $triedThumb = 'n';

    /**
     * @var string|null
     */
    private $textRepresentation;

    /**
     * @var string|null
     */
    private $uploadHash;


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
     * Set width.
     *
     * @param string|null $width
     *
     * @return MediaItem
     */
    public function setWidth($width = null)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width.
     *
     * @return string|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height.
     *
     * @param string|null $height
     *
     * @return MediaItem
     */
    public function setHeight($height = null)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height.
     *
     * @return string|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set halign.
     *
     * @param string|null $halign
     *
     * @return MediaItem
     */
    public function setHalign($halign = null)
    {
        $this->halign = $halign;

        return $this;
    }

    /**
     * Get halign.
     *
     * @return string|null
     */
    public function getHalign()
    {
        return $this->halign;
    }

    /**
     * Set caption.
     *
     * @param string|null $caption
     *
     * @return MediaItem
     */
    public function setCaption($caption = null)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption.
     *
     * @return string|null
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set nr.
     *
     * @param int $nr
     *
     * @return MediaItem
     */
    public function setNr($nr)
    {
        $this->nr = $nr;

        return $this;
    }

    /**
     * Get nr.
     *
     * @return int
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Set purpose.
     *
     * @param string|null $purpose
     *
     * @return MediaItem
     */
    public function setPurpose($purpose = null)
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * Get purpose.
     *
     * @return string|null
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Set mobId.
     *
     * @param int $mobId
     *
     * @return MediaItem
     */
    public function setMobId($mobId)
    {
        $this->mobId = $mobId;

        return $this;
    }

    /**
     * Get mobId.
     *
     * @return int
     */
    public function getMobId()
    {
        return $this->mobId;
    }

    /**
     * Set location.
     *
     * @param string|null $location
     *
     * @return MediaItem
     */
    public function setLocation($location = null)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set locationType.
     *
     * @param string|null $locationType
     *
     * @return MediaItem
     */
    public function setLocationType($locationType = null)
    {
        $this->locationType = $locationType;

        return $this;
    }

    /**
     * Get locationType.
     *
     * @return string|null
     */
    public function getLocationType()
    {
        return $this->locationType;
    }

    /**
     * Set format.
     *
     * @param string|null $format
     *
     * @return MediaItem
     */
    public function setFormat($format = null)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format.
     *
     * @return string|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set param.
     *
     * @param string|null $param
     *
     * @return MediaItem
     */
    public function setParam($param = null)
    {
        $this->param = $param;

        return $this;
    }

    /**
     * Get param.
     *
     * @return string|null
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Set triedThumb.
     *
     * @param string|null $triedThumb
     *
     * @return MediaItem
     */
    public function setTriedThumb($triedThumb = null)
    {
        $this->triedThumb = $triedThumb;

        return $this;
    }

    /**
     * Get triedThumb.
     *
     * @return string|null
     */
    public function getTriedThumb()
    {
        return $this->triedThumb;
    }

    /**
     * Set textRepresentation.
     *
     * @param string|null $textRepresentation
     *
     * @return MediaItem
     */
    public function setTextRepresentation($textRepresentation = null)
    {
        $this->textRepresentation = $textRepresentation;

        return $this;
    }

    /**
     * Get textRepresentation.
     *
     * @return string|null
     */
    public function getTextRepresentation()
    {
        return $this->textRepresentation;
    }

    /**
     * Set uploadHash.
     *
     * @param string|null $uploadHash
     *
     * @return MediaItem
     */
    public function setUploadHash($uploadHash = null)
    {
        $this->uploadHash = $uploadHash;

        return $this;
    }

    /**
     * Get uploadHash.
     *
     * @return string|null
     */
    public function getUploadHash()
    {
        return $this->uploadHash;
    }
}
