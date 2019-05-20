<?php



/**
 * DataCache
 */
class DataCache
{
    /**
     * @var string
     */
    private $module = 'common';

    /**
     * @var string
     */
    private $keyword = ' ';

    /**
     * @var string|null
     */
    private $value;


    /**
     * Set module.
     *
     * @param string $module
     *
     * @return DataCache
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module.
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set keyword.
     *
     * @param string $keyword
     *
     * @return DataCache
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
     * @return DataCache
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
