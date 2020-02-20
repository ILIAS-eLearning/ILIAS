<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificatePresentation
{
    /**
     * @var int
     */
    private $objId;

    /**
     * @var string
     */
    private $objType;
    
    /**
     * @var ilUserCertificate|null
     */
    private $userCertificate;

    /**
     * @var string
     */
    private $objectTitle;

    /**
     * @var string
     */
    private $objectDescription;

    /**
     * @var string
     */
    private $userName;

    /**
     * ilUserCertificatePresentation constructor.
     * @param int $objId
     * @param string $objType
     * @param ilUserCertificate|null $userCertificate
     * @param string $objectTitle
     * @param string $objectDescription
     * @param string $userName
     */
    public function __construct(
        int $objId,
        string $objType,
        ?ilUserCertificate $userCertificate,
        string $objectTitle,
        string $objectDescription,
        string $userName = ''
    ) {
        $this->objId = $objId;
        $this->objType = $objType;
        $this->userCertificate = $userCertificate;
        $this->objectTitle = $objectTitle;
        $this->objectDescription = $objectDescription;
        $this->userName = $userName;
    }

    /**
     * @return int
     */
    public function getObjId() : int
    {
        return $this->objId;
    }

    /**
     * @return string
     */
    public function getObjType() : string
    {
        return $this->objType;
    }

    /**
     * @return ilUserCertificate|null
     */
    public function getUserCertificate() : ?ilUserCertificate
    {
        return $this->userCertificate;
    }

    /**
     * @return string
     */
    public function getObjectTitle() : string
    {
        return $this->objectTitle;
    }

    /**
     * @return string
     */
    public function getObjectDescription() : string
    {
        return $this->objectDescription;
    }

    /**
     * @return string
     */
    public function getUserName() : string
    {
        return $this->userName;
    }
}
