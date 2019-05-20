<?php



/**
 * ContainerSettings
 */
class ContainerSettings
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $keyword = '';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return ContainerSettings
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set keyword.
     *
     * @param string $keyword
     *
     * @return ContainerSettings
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    /**
     * Get keyword.
     *
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return ContainerSettings
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
