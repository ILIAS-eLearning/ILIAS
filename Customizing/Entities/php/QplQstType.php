<?php



/**
 * QplQstType
 */
class QplQstType
{
    /**
     * @var int
     */
    private $questionTypeId = '0';

    /**
     * @var string|null
     */
    private $typeTag;

    /**
     * @var bool
     */
    private $plugin = '0';

    /**
     * @var string|null
     */
    private $pluginName;


    /**
     * Get questionTypeId.
     *
     * @return int
     */
    public function getQuestionTypeId()
    {
        return $this->questionTypeId;
    }

    /**
     * Set typeTag.
     *
     * @param string|null $typeTag
     *
     * @return QplQstType
     */
    public function setTypeTag($typeTag = null)
    {
        $this->typeTag = $typeTag;

        return $this;
    }

    /**
     * Get typeTag.
     *
     * @return string|null
     */
    public function getTypeTag()
    {
        return $this->typeTag;
    }

    /**
     * Set plugin.
     *
     * @param bool $plugin
     *
     * @return QplQstType
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    /**
     * Get plugin.
     *
     * @return bool
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set pluginName.
     *
     * @param string|null $pluginName
     *
     * @return QplQstType
     */
    public function setPluginName($pluginName = null)
    {
        $this->pluginName = $pluginName;

        return $this;
    }

    /**
     * Get pluginName.
     *
     * @return string|null
     */
    public function getPluginName()
    {
        return $this->pluginName;
    }
}
