<?php



/**
 * DidacticTplSa
 */
class DidacticTplSa
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var string
     */
    private $objType = '';


    /**
     * Set id.
     *
     * @param int $id
     *
     * @return DidacticTplSa
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objType.
     *
     * @param string $objType
     *
     * @return DidacticTplSa
     */
    public function setObjType($objType)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string
     */
    public function getObjType()
    {
        return $this->objType;
    }
}
