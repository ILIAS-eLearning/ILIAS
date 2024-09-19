<?php

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

declare(strict_types=1);

use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilPdfGenerator
{
    private readonly ilCertificateRpcClientFactoryHelper $rpcHelper;
    private readonly ilCertificateMathJaxHelper $mathJaxHelper;
    private readonly ilCertificatePdfFileNameFactory $pdfFilenameFactory;

    public function __construct(
        private readonly ilUserCertificateRepository $certificateRepository,
        private ?IRSS $irss = null,
        ?ilCertificateRpcClientFactoryHelper $rpcHelper = null,
        ?ilCertificatePdfFileNameFactory $pdfFileNameFactory = null,
        ?ilLanguage $lng = null,
        ?ilCertificateMathJaxHelper $mathJaxHelper = null
    ) {
        global $DIC;

        if (null === $irss) {
            $irss = $DIC->resourceStorage();
        }
        $this->irss = $irss;

        if (null === $rpcHelper) {
            $rpcHelper = new ilCertificateRpcClientFactoryHelper();
        }
        $this->rpcHelper = $rpcHelper;

        if (null === $mathJaxHelper) {
            $mathJaxHelper = new ilCertificateMathJaxHelper();
        }
        $this->mathJaxHelper = $mathJaxHelper;

        if (null === $lng) {
            $lng = $DIC->language();
        }

        if (null === $pdfFileNameFactory) {
            $pdfFileNameFactory = new ilCertificatePdfFileNameFactory($lng);
        }
        $this->pdfFilenameFactory = $pdfFileNameFactory;
    }

    /**
     * @throws ilException
     */
    public function generate(int $userCertificateId): string
    {
        $certificate = $this->certificateRepository->fetchCertificate($userCertificateId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @throws ilException
     */
    public function generateCurrentActiveCertificate(int $userId, int $objId): string
    {
        $certificate = $this->certificateRepository->fetchActiveCertificate($userId, $objId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function generateFileName(int $userId, int $objId): string
    {
        $certificate = $this->certificateRepository->fetchActiveCertificateForPresentation($userId, $objId);

        $user = ilObjectFactory::getInstanceByObjId($userId);
        if (!$user instanceof ilObjUser) {
            throw new ilException(sprintf('The usr_id "%s" does NOT reference a user', $userId));
        }

        return $this->pdfFilenameFactory->create($certificate);
    }

    private function createPDFScalar(ilUserCertificate $certificate): string
    {
        $certificateContent = $certificate->getCertificateContent();

        $background_rid = $this->irss->manage()->find($certificate->getBackgroundImageIdentification());
        $background_src = '';
        if ($background_rid instanceof ResourceIdentification) {
            $background_src = $this->irss->consume()->src($background_rid)->getSrc();
        }

        $certificateContent = str_replace(
            ['[BACKGROUND_IMAGE]'],
            [$background_src],
            $certificateContent
        );

        $certificateContent = $this->mathJaxHelper->fillXlsFoContent($certificateContent);

        $pdf_base64 = $this->rpcHelper->ilFO2PDF('RPCTransformationHandler', $certificateContent);

        return $pdf_base64->scalar;
    }
}
