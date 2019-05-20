<?php



/**
 * OrguObjTypeSettings
 */
class OrguObjTypeSettings
{
    /**
     * @var string
     */
    private $objType = '';

    /**
     * @var bool|null
     */
    private $active = '0';

    /**
     * @var bool|null
     */
    private $activationDefault = '0';

    /**
     * @var bool|null
     */
    private $changeable = '0';


    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return OrguObjTypeSettings
     */
    public function setActive($active = null)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool|null
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set activationDefault.
     *
     * @param bool|null $activationDefault
     *
     * @return OrguObjTypeSettings
     */
    public function setActivationDefault($activationDefault = null)
    {
        $this->activationDefault = $activationDefault;

        return $this;
    }

    /**
     * Get activationDefault.
     *
     * @return bool|null
     */
    public function getActivationDefault()
    {
        return $this->activationDefault;
    }

    /**
     * Set changeable.
     *
     * @param bool|null $changeable
     *
     * @return OrguObjTypeSettings
     */
    public function setChangeable($changeable = null)
    {
        $this->changeable = $changeable;

        return $this;
    }

    /**
     * Get changeable.
     *
     * @return bool|null
     */
    public function getChangeable()
    {
        return $this->changeable;
    }
}
