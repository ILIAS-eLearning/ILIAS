<?php



/**
 * IlMetaEducational
 */
class IlMetaEducational
{
    /**
     * @var int
     */
    private $metaEducationalId = '0';

    /**
     * @var int|null
     */
    private $rbacId;

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var string|null
     */
    private $interactivityType;

    /**
     * @var string|null
     */
    private $learningResourceType;

    /**
     * @var string|null
     */
    private $interactivityLevel;

    /**
     * @var string|null
     */
    private $semanticDensity;

    /**
     * @var string|null
     */
    private $intendedEndUserRole;

    /**
     * @var string|null
     */
    private $context;

    /**
     * @var string|null
     */
    private $difficulty;

    /**
     * @var string|null
     */
    private $typicalLearningTime;


    /**
     * Get metaEducationalId.
     *
     * @return int
     */
    public function getMetaEducationalId()
    {
        return $this->metaEducationalId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaEducational
     */
    public function setRbacId($rbacId = null)
    {
        $this->rbacId = $rbacId;

        return $this;
    }

    /**
     * Get rbacId.
     *
     * @return int|null
     */
    public function getRbacId()
    {
        return $this->rbacId;
    }

    /**
     * Set objId.
     *
     * @param int|null $objId
     *
     * @return IlMetaEducational
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return IlMetaEducational
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set interactivityType.
     *
     * @param string|null $interactivityType
     *
     * @return IlMetaEducational
     */
    public function setInteractivityType($interactivityType = null)
    {
        $this->interactivityType = $interactivityType;

        return $this;
    }

    /**
     * Get interactivityType.
     *
     * @return string|null
     */
    public function getInteractivityType()
    {
        return $this->interactivityType;
    }

    /**
     * Set learningResourceType.
     *
     * @param string|null $learningResourceType
     *
     * @return IlMetaEducational
     */
    public function setLearningResourceType($learningResourceType = null)
    {
        $this->learningResourceType = $learningResourceType;

        return $this;
    }

    /**
     * Get learningResourceType.
     *
     * @return string|null
     */
    public function getLearningResourceType()
    {
        return $this->learningResourceType;
    }

    /**
     * Set interactivityLevel.
     *
     * @param string|null $interactivityLevel
     *
     * @return IlMetaEducational
     */
    public function setInteractivityLevel($interactivityLevel = null)
    {
        $this->interactivityLevel = $interactivityLevel;

        return $this;
    }

    /**
     * Get interactivityLevel.
     *
     * @return string|null
     */
    public function getInteractivityLevel()
    {
        return $this->interactivityLevel;
    }

    /**
     * Set semanticDensity.
     *
     * @param string|null $semanticDensity
     *
     * @return IlMetaEducational
     */
    public function setSemanticDensity($semanticDensity = null)
    {
        $this->semanticDensity = $semanticDensity;

        return $this;
    }

    /**
     * Get semanticDensity.
     *
     * @return string|null
     */
    public function getSemanticDensity()
    {
        return $this->semanticDensity;
    }

    /**
     * Set intendedEndUserRole.
     *
     * @param string|null $intendedEndUserRole
     *
     * @return IlMetaEducational
     */
    public function setIntendedEndUserRole($intendedEndUserRole = null)
    {
        $this->intendedEndUserRole = $intendedEndUserRole;

        return $this;
    }

    /**
     * Get intendedEndUserRole.
     *
     * @return string|null
     */
    public function getIntendedEndUserRole()
    {
        return $this->intendedEndUserRole;
    }

    /**
     * Set context.
     *
     * @param string|null $context
     *
     * @return IlMetaEducational
     */
    public function setContext($context = null)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     *
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set difficulty.
     *
     * @param string|null $difficulty
     *
     * @return IlMetaEducational
     */
    public function setDifficulty($difficulty = null)
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    /**
     * Get difficulty.
     *
     * @return string|null
     */
    public function getDifficulty()
    {
        return $this->difficulty;
    }

    /**
     * Set typicalLearningTime.
     *
     * @param string|null $typicalLearningTime
     *
     * @return IlMetaEducational
     */
    public function setTypicalLearningTime($typicalLearningTime = null)
    {
        $this->typicalLearningTime = $typicalLearningTime;

        return $this;
    }

    /**
     * Get typicalLearningTime.
     *
     * @return string|null
     */
    public function getTypicalLearningTime()
    {
        return $this->typicalLearningTime;
    }
}
