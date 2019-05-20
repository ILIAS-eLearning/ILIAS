<?php



/**
 * IlBiblAttribute
 */
class IlBiblAttribute
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $entryId;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $value;


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
     * Set entryId.
     *
     * @param int|null $entryId
     *
     * @return IlBiblAttribute
     */
    public function setEntryId($entryId = null)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * Get entryId.
     *
     * @return int|null
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Set name.
     *
     * @param string|null $name
     *
     * @return IlBiblAttribute
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return IlBiblAttribute
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
