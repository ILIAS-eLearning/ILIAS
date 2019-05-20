<?php



/**
 * ScOrganizations
 */
class ScOrganizations
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $defaultOrganization;


    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set defaultOrganization.
     *
     * @param string|null $defaultOrganization
     *
     * @return ScOrganizations
     */
    public function setDefaultOrganization($defaultOrganization = null)
    {
        $this->defaultOrganization = $defaultOrganization;

        return $this;
    }

    /**
     * Get defaultOrganization.
     *
     * @return string|null
     */
    public function getDefaultOrganization()
    {
        return $this->defaultOrganization;
    }
}
