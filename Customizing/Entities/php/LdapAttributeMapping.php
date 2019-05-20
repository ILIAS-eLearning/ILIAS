<?php



/**
 * LdapAttributeMapping
 */
class LdapAttributeMapping
{
    /**
     * @var int
     */
    private $serverId = '0';

    /**
     * @var string
     */
    private $keyword = ' ';

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var bool
     */
    private $performUpdate = '0';


    /**
     * Set serverId.
     *
     * @param int $serverId
     *
     * @return LdapAttributeMapping
     */
    public function setServerId($serverId)
    {
        $this->serverId = $serverId;

        return $this;
    }

    /**
     * Get serverId.
     *
     * @return int
     */
    public function getServerId()
    {
        return $this->serverId;
    }

    /**
     * Set keyword.
     *
     * @param string $keyword
     *
     * @return LdapAttributeMapping
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
     * @return LdapAttributeMapping
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

    /**
     * Set performUpdate.
     *
     * @param bool $performUpdate
     *
     * @return LdapAttributeMapping
     */
    public function setPerformUpdate($performUpdate)
    {
        $this->performUpdate = $performUpdate;

        return $this;
    }

    /**
     * Get performUpdate.
     *
     * @return bool
     */
    public function getPerformUpdate()
    {
        return $this->performUpdate;
    }
}
