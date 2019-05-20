<?php



/**
 * StyleTemplateClass
 */
class StyleTemplateClass
{
    /**
     * @var int
     */
    private $templateId = '0';

    /**
     * @var string
     */
    private $classType = '';

    /**
     * @var string
     */
    private $class = '';


    /**
     * Set templateId.
     *
     * @param int $templateId
     *
     * @return StyleTemplateClass
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId.
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * Set classType.
     *
     * @param string $classType
     *
     * @return StyleTemplateClass
     */
    public function setClassType($classType)
    {
        $this->classType = $classType;

        return $this;
    }

    /**
     * Get classType.
     *
     * @return string
     */
    public function getClassType()
    {
        return $this->classType;
    }

    /**
     * Set class.
     *
     * @param string $class
     *
     * @return StyleTemplateClass
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
