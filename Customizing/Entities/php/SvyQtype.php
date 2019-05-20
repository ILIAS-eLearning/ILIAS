<?php



/**
 * SvyQtype
 */
class SvyQtype
{
    /**
     * @var int
     */
    private $questiontypeId = '0';

    /**
     * @var string|null
     */
    private $typeTag;

    /**
     * @var bool
     */
    private $plugin = '0';


    /**
     * Get questiontypeId.
     *
     * @return int
     */
    public function getQuestiontypeId()
    {
        return $this->questiontypeId;
    }

    /**
     * Set typeTag.
     *
     * @param string|null $typeTag
     *
     * @return SvyQtype
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
     * @return SvyQtype
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
}
