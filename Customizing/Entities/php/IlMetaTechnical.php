<?php



/**
 * IlMetaTechnical
 */
class IlMetaTechnical
{
    /**
     * @var int
     */
    private $metaTechnicalId = '0';

    /**
     * @var int|null
     */
    private $rbacId;

    /**
     * @var int|null
     */
    private $objId;

    /**
     * @var string|null
     */
    private $objType;

    /**
     * @var string|null
     */
    private $tSize;

    /**
     * @var string|null
     */
    private $ir;

    /**
     * @var string|null
     */
    private $irLanguage;

    /**
     * @var string|null
     */
    private $opr;

    /**
     * @var string|null
     */
    private $oprLanguage;

    /**
     * @var string|null
     */
    private $duration;


    /**
     * Get metaTechnicalId.
     *
     * @return int
     */
    public function getMetaTechnicalId()
    {
        return $this->metaTechnicalId;
    }

    /**
     * Set rbacId.
     *
     * @param int|null $rbacId
     *
     * @return IlMetaTechnical
     */
    public function setRbacId($rbacId = null)
    {
        $this->rbacId = $rbacId;

        return $this;
    }

    /**
     * Get rbacId.
     *
     * @return int|null
     */
    public function getRbacId()
    {
        return $this->rbacId;
    }

    /**
     * Set objId.
     *
     * @param int|null $objId
     *
     * @return IlMetaTechnical
     */
    public function setObjId($objId = null)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int|null
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return IlMetaTechnical
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set tSize.
     *
     * @param string|null $tSize
     *
     * @return IlMetaTechnical
     */
    public function setTSize($tSize = null)
    {
        $this->tSize = $tSize;

        return $this;
    }

    /**
     * Get tSize.
     *
     * @return string|null
     */
    public function getTSize()
    {
        return $this->tSize;
    }

    /**
     * Set ir.
     *
     * @param string|null $ir
     *
     * @return IlMetaTechnical
     */
    public function setIr($ir = null)
    {
        $this->ir = $ir;

        return $this;
    }

    /**
     * Get ir.
     *
     * @return string|null
     */
    public function getIr()
    {
        return $this->ir;
    }

    /**
     * Set irLanguage.
     *
     * @param string|null $irLanguage
     *
     * @return IlMetaTechnical
     */
    public function setIrLanguage($irLanguage = null)
    {
        $this->irLanguage = $irLanguage;

        return $this;
    }

    /**
     * Get irLanguage.
     *
     * @return string|null
     */
    public function getIrLanguage()
    {
        return $this->irLanguage;
    }

    /**
     * Set opr.
     *
     * @param string|null $opr
     *
     * @return IlMetaTechnical
     */
    public function setOpr($opr = null)
    {
        $this->opr = $opr;

        return $this;
    }

    /**
     * Get opr.
     *
     * @return string|null
     */
    public function getOpr()
    {
        return $this->opr;
    }

    /**
     * Set oprLanguage.
     *
     * @param string|null $oprLanguage
     *
     * @return IlMetaTechnical
     */
    public function setOprLanguage($oprLanguage = null)
    {
        $this->oprLanguage = $oprLanguage;

        return $this;
    }

    /**
     * Get oprLanguage.
     *
     * @return string|null
     */
    public function getOprLanguage()
    {
        return $this->oprLanguage;
    }

    /**
     * Set duration.
     *
     * @param string|null $duration
     *
     * @return IlMetaTechnical
     */
    public function setDuration($duration = null)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration.
     *
     * @return string|null
     */
    public function getDuration()
    {
        return $this->duration;
    }
}
