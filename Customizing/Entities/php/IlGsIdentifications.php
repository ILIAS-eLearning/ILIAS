<?php



/**
 * IlGsIdentifications
 */
class IlGsIdentifications
{
    /**
     * @var string
     */
    private $identification = '';

    /**
     * @var string|null
     */
    private $providerClass;

    /**
     * @var bool|null
     */
    private $active;


    /**
     * Get identification.
     *
     * @return string
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set providerClass.
     *
     * @param string|null $providerClass
     *
     * @return IlGsIdentifications
     */
    public function setProviderClass($providerClass = null)
    {
        $this->providerClass = $providerClass;

        return $this;
    }

    /**
     * Get providerClass.
     *
     * @return string|null
     */
    public function getProviderClass()
    {
        return $this->providerClass;
    }

    /**
     * Set active.
     *
     * @param bool|null $active
     *
     * @return IlGsIdentifications
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
}
