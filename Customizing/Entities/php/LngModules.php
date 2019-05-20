<?php



/**
 * LngModules
 */
class LngModules
{
    /**
     * @var string
     */
    private $module = ' ';

    /**
     * @var string
     */
    private $langKey = '';

    /**
     * @var string|null
     */
    private $langArray;


    /**
     * Set module.
     *
     * @param string $module
     *
     * @return LngModules
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
     * Set langKey.
     *
     * @param string $langKey
     *
     * @return LngModules
     */
    public function setLangKey($langKey)
    {
        $this->langKey = $langKey;

        return $this;
    }

    /**
     * Get langKey.
     *
     * @return string
     */
    public function getLangKey()
    {
        return $this->langKey;
    }

    /**
     * Set langArray.
     *
     * @param string|null $langArray
     *
     * @return LngModules
     */
    public function setLangArray($langArray = null)
    {
        $this->langArray = $langArray;

        return $this;
    }

    /**
     * Get langArray.
     *
     * @return string|null
     */
    public function getLangArray()
    {
        return $this->langArray;
    }
}
