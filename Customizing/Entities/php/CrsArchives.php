<?php



/**
 * CrsArchives
 */
class CrsArchives
{
    /**
     * @var int
     */
    private $archiveId = '0';

    /**
     * @var int
     */
    private $courseId = '0';

    /**
     * @var string|null
     */
    private $archiveName;

    /**
     * @var bool
     */
    private $archiveType = '0';

    /**
     * @var int|null
     */
    private $archiveDate;

    /**
     * @var int|null
     */
    private $archiveSize;

    /**
     * @var string|null
     */
    private $archiveLang;


    /**
     * Get archiveId.
     *
     * @return int
     */
    public function getArchiveId()
    {
        return $this->archiveId;
    }

    /**
     * Set courseId.
     *
     * @param int $courseId
     *
     * @return CrsArchives
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;

        return $this;
    }

    /**
     * Get courseId.
     *
     * @return int
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set archiveName.
     *
     * @param string|null $archiveName
     *
     * @return CrsArchives
     */
    public function setArchiveName($archiveName = null)
    {
        $this->archiveName = $archiveName;

        return $this;
    }

    /**
     * Get archiveName.
     *
     * @return string|null
     */
    public function getArchiveName()
    {
        return $this->archiveName;
    }

    /**
     * Set archiveType.
     *
     * @param bool $archiveType
     *
     * @return CrsArchives
     */
    public function setArchiveType($archiveType)
    {
        $this->archiveType = $archiveType;

        return $this;
    }

    /**
     * Get archiveType.
     *
     * @return bool
     */
    public function getArchiveType()
    {
        return $this->archiveType;
    }

    /**
     * Set archiveDate.
     *
     * @param int|null $archiveDate
     *
     * @return CrsArchives
     */
    public function setArchiveDate($archiveDate = null)
    {
        $this->archiveDate = $archiveDate;

        return $this;
    }

    /**
     * Get archiveDate.
     *
     * @return int|null
     */
    public function getArchiveDate()
    {
        return $this->archiveDate;
    }

    /**
     * Set archiveSize.
     *
     * @param int|null $archiveSize
     *
     * @return CrsArchives
     */
    public function setArchiveSize($archiveSize = null)
    {
        $this->archiveSize = $archiveSize;

        return $this;
    }

    /**
     * Get archiveSize.
     *
     * @return int|null
     */
    public function getArchiveSize()
    {
        return $this->archiveSize;
    }

    /**
     * Set archiveLang.
     *
     * @param string|null $archiveLang
     *
     * @return CrsArchives
     */
    public function setArchiveLang($archiveLang = null)
    {
        $this->archiveLang = $archiveLang;

        return $this;
    }

    /**
     * Get archiveLang.
     *
     * @return string|null
     */
    public function getArchiveLang()
    {
        return $this->archiveLang;
    }
}
