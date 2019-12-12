<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserCertificatePresentation
{
    /**
     * @var ilUserCertificate
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
     * @param ilUserCertificate $userCertificate
     * @param string $objectTitle
     * @param string $objectDescription
     * @param string $userName
     */
    public function __construct(
        ilUserCertificate $userCertificate,
        string $objectTitle,
        string $objectDescription,
        string $userName = ''
    ) {
        $this->userCertificate = $userCertificate;
        $this->objectTitle = $objectTitle;
        $this->objectDescription = $objectDescription;
        $this->userName = $userName;
    }

    /**
     * @return ilUserCertificate
     */
    public function getUserCertificate() : ilUserCertificate
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
