<?php



/**
 * ScOrganization
 */
class ScOrganization
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var string|null
     */
    private $structure;


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
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return ScOrganization
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set structure.
     *
     * @param string|null $structure
     *
     * @return ScOrganization
     */
    public function setStructure($structure = null)
    {
        $this->structure = $structure;

        return $this;
    }

    /**
     * Get structure.
     *
     * @return string|null
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
