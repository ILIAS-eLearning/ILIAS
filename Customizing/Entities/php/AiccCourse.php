<?php



/**
 * AiccCourse
 */
class AiccCourse
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $courseCreator;

    /**
     * @var string|null
     */
    private $courseId;

    /**
     * @var string|null
     */
    private $courseSystem;

    /**
     * @var string|null
     */
    private $courseTitle;

    /**
     * @var string|null
     */
    private $cLevel;

    /**
     * @var int|null
     */
    private $maxFieldsCst = '0';

    /**
     * @var int|null
     */
    private $maxFieldsOrt = '0';

    /**
     * @var int
     */
    private $totalAus = '0';

    /**
     * @var int
     */
    private $totalBlocks = '0';

    /**
     * @var int|null
     */
    private $totalComplexObj = '0';

    /**
     * @var int|null
     */
    private $totalObjectives = '0';

    /**
     * @var string|null
     */
    private $version;

    /**
     * @var bool|null
     */
    private $maxNormal;

    /**
     * @var string|null
     */
    private $description;


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
     * Set courseCreator.
     *
     * @param string|null $courseCreator
     *
     * @return AiccCourse
     */
    public function setCourseCreator($courseCreator = null)
    {
        $this->courseCreator = $courseCreator;

        return $this;
    }

    /**
     * Get courseCreator.
     *
     * @return string|null
     */
    public function getCourseCreator()
    {
        return $this->courseCreator;
    }

    /**
     * Set courseId.
     *
     * @param string|null $courseId
     *
     * @return AiccCourse
     */
    public function setCourseId($courseId = null)
    {
        $this->courseId = $courseId;

        return $this;
    }

    /**
     * Get courseId.
     *
     * @return string|null
     */
    public function getCourseId()
    {
        return $this->courseId;
    }

    /**
     * Set courseSystem.
     *
     * @param string|null $courseSystem
     *
     * @return AiccCourse
     */
    public function setCourseSystem($courseSystem = null)
    {
        $this->courseSystem = $courseSystem;

        return $this;
    }

    /**
     * Get courseSystem.
     *
     * @return string|null
     */
    public function getCourseSystem()
    {
        return $this->courseSystem;
    }

    /**
     * Set courseTitle.
     *
     * @param string|null $courseTitle
     *
     * @return AiccCourse
     */
    public function setCourseTitle($courseTitle = null)
    {
        $this->courseTitle = $courseTitle;

        return $this;
    }

    /**
     * Get courseTitle.
     *
     * @return string|null
     */
    public function getCourseTitle()
    {
        return $this->courseTitle;
    }

    /**
     * Set cLevel.
     *
     * @param string|null $cLevel
     *
     * @return AiccCourse
     */
    public function setCLevel($cLevel = null)
    {
        $this->cLevel = $cLevel;

        return $this;
    }

    /**
     * Get cLevel.
     *
     * @return string|null
     */
    public function getCLevel()
    {
        return $this->cLevel;
    }

    /**
     * Set maxFieldsCst.
     *
     * @param int|null $maxFieldsCst
     *
     * @return AiccCourse
     */
    public function setMaxFieldsCst($maxFieldsCst = null)
    {
        $this->maxFieldsCst = $maxFieldsCst;

        return $this;
    }

    /**
     * Get maxFieldsCst.
     *
     * @return int|null
     */
    public function getMaxFieldsCst()
    {
        return $this->maxFieldsCst;
    }

    /**
     * Set maxFieldsOrt.
     *
     * @param int|null $maxFieldsOrt
     *
     * @return AiccCourse
     */
    public function setMaxFieldsOrt($maxFieldsOrt = null)
    {
        $this->maxFieldsOrt = $maxFieldsOrt;

        return $this;
    }

    /**
     * Get maxFieldsOrt.
     *
     * @return int|null
     */
    public function getMaxFieldsOrt()
    {
        return $this->maxFieldsOrt;
    }

    /**
     * Set totalAus.
     *
     * @param int $totalAus
     *
     * @return AiccCourse
     */
    public function setTotalAus($totalAus)
    {
        $this->totalAus = $totalAus;

        return $this;
    }

    /**
     * Get totalAus.
     *
     * @return int
     */
    public function getTotalAus()
    {
        return $this->totalAus;
    }

    /**
     * Set totalBlocks.
     *
     * @param int $totalBlocks
     *
     * @return AiccCourse
     */
    public function setTotalBlocks($totalBlocks)
    {
        $this->totalBlocks = $totalBlocks;

        return $this;
    }

    /**
     * Get totalBlocks.
     *
     * @return int
     */
    public function getTotalBlocks()
    {
        return $this->totalBlocks;
    }

    /**
     * Set totalComplexObj.
     *
     * @param int|null $totalComplexObj
     *
     * @return AiccCourse
     */
    public function setTotalComplexObj($totalComplexObj = null)
    {
        $this->totalComplexObj = $totalComplexObj;

        return $this;
    }

    /**
     * Get totalComplexObj.
     *
     * @return int|null
     */
    public function getTotalComplexObj()
    {
        return $this->totalComplexObj;
    }

    /**
     * Set totalObjectives.
     *
     * @param int|null $totalObjectives
     *
     * @return AiccCourse
     */
    public function setTotalObjectives($totalObjectives = null)
    {
        $this->totalObjectives = $totalObjectives;

        return $this;
    }

    /**
     * Get totalObjectives.
     *
     * @return int|null
     */
    public function getTotalObjectives()
    {
        return $this->totalObjectives;
    }

    /**
     * Set version.
     *
     * @param string|null $version
     *
     * @return AiccCourse
     */
    public function setVersion($version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set maxNormal.
     *
     * @param bool|null $maxNormal
     *
     * @return AiccCourse
     */
    public function setMaxNormal($maxNormal = null)
    {
        $this->maxNormal = $maxNormal;

        return $this;
    }

    /**
     * Get maxNormal.
     *
     * @return bool|null
     */
    public function getMaxNormal()
    {
        return $this->maxNormal;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return AiccCourse
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
}
