<?php



/**
 * UsrStartingPoint
 */
class UsrStartingPoint
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $position = '0';

    /**
     * @var int|null
     */
    private $startingPoint = '0';

    /**
     * @var int|null
     */
    private $startingObject = '0';

    /**
     * @var int|null
     */
    private $ruleType = '0';

    /**
     * @var string|null
     */
    private $ruleOptions;


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
     * Set position.
     *
     * @param int|null $position
     *
     * @return UsrStartingPoint
     */
    public function setPosition($position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set startingPoint.
     *
     * @param int|null $startingPoint
     *
     * @return UsrStartingPoint
     */
    public function setStartingPoint($startingPoint = null)
    {
        $this->startingPoint = $startingPoint;

        return $this;
    }

    /**
     * Get startingPoint.
     *
     * @return int|null
     */
    public function getStartingPoint()
    {
        return $this->startingPoint;
    }

    /**
     * Set startingObject.
     *
     * @param int|null $startingObject
     *
     * @return UsrStartingPoint
     */
    public function setStartingObject($startingObject = null)
    {
        $this->startingObject = $startingObject;

        return $this;
    }

    /**
     * Get startingObject.
     *
     * @return int|null
     */
    public function getStartingObject()
    {
        return $this->startingObject;
    }

    /**
     * Set ruleType.
     *
     * @param int|null $ruleType
     *
     * @return UsrStartingPoint
     */
    public function setRuleType($ruleType = null)
    {
        $this->ruleType = $ruleType;

        return $this;
    }

    /**
     * Get ruleType.
     *
     * @return int|null
     */
    public function getRuleType()
    {
        return $this->ruleType;
    }

    /**
     * Set ruleOptions.
     *
     * @param string|null $ruleOptions
     *
     * @return UsrStartingPoint
     */
    public function setRuleOptions($ruleOptions = null)
    {
        $this->ruleOptions = $ruleOptions;

        return $this;
    }

    /**
     * Get ruleOptions.
     *
     * @return string|null
     */
    public function getRuleOptions()
    {
        return $this->ruleOptions;
    }
}
