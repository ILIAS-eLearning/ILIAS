<?php



/**
 * TableTemplates
 */
class TableTemplates
{
    /**
     * @var string
     */
    private $name = '';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $context = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set name.
     *
     * @param string $name
     *
     * @return TableTemplates
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return TableTemplates
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set context.
     *
     * @param string $context
     *
     * @return TableTemplates
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return TableTemplates
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
