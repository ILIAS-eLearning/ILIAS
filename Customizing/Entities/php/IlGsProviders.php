<?php



/**
 * IlGsProviders
 */
class IlGsProviders
{
    /**
     * @var string
     */
    private $providerClass = '';

    /**
     * @var string|null
     */
    private $purpose;

    /**
     * @var bool|null
     */
    private $dynamic;


    /**
     * Get providerClass.
     *
     * @return string
     */
    public function getProviderClass()
    {
        return $this->providerClass;
    }

    /**
     * Set purpose.
     *
     * @param string|null $purpose
     *
     * @return IlGsProviders
     */
    public function setPurpose($purpose = null)
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * Get purpose.
     *
     * @return string|null
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Set dynamic.
     *
     * @param bool|null $dynamic
     *
     * @return IlGsProviders
     */
    public function setDynamic($dynamic = null)
    {
        $this->dynamic = $dynamic;

        return $this;
    }

    /**
     * Get dynamic.
     *
     * @return bool|null
     */
    public function getDynamic()
    {
        return $this->dynamic;
    }
}
