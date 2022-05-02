<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPdfGenerator
{
    private ilUserCertificateRepository $certificateRepository;
    private ilLogger $logger;
    private ilCertificateRpcClientFactoryHelper $rpcHelper;
    private ilCertificatePdfFileNameFactory $pdfFilenameFactory;

    public function __construct(
        ilUserCertificateRepository $userCertificateRepository,
        ilLogger $logger,
        ?ilCertificateRpcClientFactoryHelper $rpcHelper = null,
        ?ilCertificatePdfFileNameFactory $pdfFileNameFactory = null,
        ?ilLanguage $lng = null
    ) {
        global $DIC;

        $this->certificateRepository = $userCertificateRepository;
        $this->logger = $logger;

        if (null === $rpcHelper) {
            $rpcHelper = new ilCertificateRpcClientFactoryHelper();
        }
        $this->rpcHelper = $rpcHelper;

        if (null === $lng) {
            $lng = $DIC->language();
        }

        if (null === $pdfFileNameFactory) {
            $pdfFileNameFactory = new ilCertificatePdfFileNameFactory($lng);
        }
        $this->pdfFilenameFactory = $pdfFileNameFactory;
    }

    /**
     * @param int $userCertificateId
     * @return string
     * @throws ilException
     */
    public function generate(int $userCertificateId) : string
    {
        $certificate = $this->certificateRepository->fetchCertificate($userCertificateId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @param int $userId
     * @param int $objId
     * @return string
     * @throws ilException
     */
    public function generateCurrentActiveCertificate(int $userId, int $objId) : string
    {
        $certificate = $this->certificateRepository->fetchActiveCertificate($userId, $objId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @param int $userId
     * @param int $objId
     * @return string
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function generateFileName(int $userId, int $objId) : string
    {
        $certificate = $this->certificateRepository->fetchActiveCertificateForPresentation($userId, $objId);

        $user = ilObjectFactory::getInstanceByObjId($userId);
        if (!$user instanceof ilObjUser) {
            throw new ilException(sprintf('The user_id "%s" does NOT reference a user', $userId));
        }

        return $this->pdfFilenameFactory->create($certificate);
    }

    private function createPDFScalar(ilUserCertificate $certificate) : string
    {
        $certificateContent = $certificate->getCertificateContent();

        $certificateContent = str_replace(
            ['[BACKGROUND_IMAGE]', '[CLIENT_WEB_DIR]'],
            ['[CLIENT_WEB_DIR]' . $certificate->getBackgroundImagePath(), CLIENT_WEB_DIR],
            $certificateContent
        );

        $pdf_base64 = $this->rpcHelper->ilFO2PDF('RPCTransformationHandler', $certificateContent);

        return $pdf_base64->scalar;
    }
}
