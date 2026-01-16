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

class ilPdfGenerator
{
    private ?ilLogger $logger = null;

    public function __construct(
        private readonly ilUserCertificateRepository $repository,
        private ?IRSS $irss = null,
        private ?ilCertificateRpcClientFactoryHelper $rpc = null,
        private ?ilCertificatePdfFileNameFactory $filename_factory = null,
        ?ilLanguage $lng = null
    ) {
        global $DIC;

        $this->irss = $irss ?? $DIC->resourceStorage();
        $this->rpc = $rpc ?? new ilCertificateRpcClientFactoryHelper();
        $this->filename_factory = $filename_factory ?? new ilCertificatePdfFileNameFactory(
            $lng ?? $DIC->language()
        );
    }

    public function withLogger(ilLogger $logger): self
    {
        $clone = clone $this;
        $clone->logger = $logger;

        return $clone;
    }

    /**
     * @throws ilException
     */
    public function generate(int $userCertificateId): string
    {
        $certificate = $this->repository->fetchCertificate($userCertificateId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @throws ilException
     */
    public function generateCurrentActiveCertificate(int $userId, int $objId): string
    {
        $certificate = $this->repository->fetchActiveCertificate($userId, $objId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilObjectNotFoundException
     */
    public function generateFileName(int $userId, int $objId): string
    {
        $certificate = $this->repository->fetchActiveCertificateForPresentation($userId, $objId);

        $user = ilObjectFactory::getInstanceByObjId($userId);
        if (!$user instanceof ilObjUser) {
            throw new ilException(sprintf('The usr_id "%s" does NOT reference a user', $userId));
        }

        return $this->filename_factory->create($certificate);
    }

    private function createPDFScalar(ilUserCertificate $certificate): string
    {
        $content = $certificate->getCertificateContent();

        $this->logger?->debug(
            'Delegating certificate PDF generation for certificate id {certificate_id} (user {user_id} and object {object_id}) to ilServer',
            [
                'certificate_id' => $certificate->getCertificateId()->asString(),
                'user_id' => $certificate->getUserId(),
                'object_id' => $certificate->getObjId()
            ]
        );

        $background_rid = $this->irss->manage()->find($certificate->getBackgroundImageIdentification());
        if ($background_rid instanceof ResourceIdentification) {
            $background_src = $this->irss->consume()->src($background_rid)->getSrc(true);

            $content = str_replace(
                ['[BACKGROUND_IMAGE]'],
                [$background_src],
                $content
            );
        }

        $pdf_base64 = $this->rpc->ilFO2PDF('RPCTransformationHandler', $content);
        if (!is_string($pdf_base64->scalar)) {
            $this->logger?->error(
                'ilServer returned invalid PDF content for certificate id {certificate_id} ' .
                '(user {user_id} and object {object_id})',
                [
                    'certificate_id' => $certificate->getCertificateId()->asString(),
                    'user_id' => $certificate->getUserId(),
                    'object_id' => $certificate->getObjId()
                ]
            );

            throw new ilException('ilServer returned invalid PDF content');
        }

        $this->logger?->debug(
            'Received generated PDF with size {size} bytes for certificate id {certificate_id} (user {user_id} ' .
            'and object {object_id}) from ilServer',
            [
                'size' => strlen($pdf_base64->scalar),
                'certificate_id' => $certificate->getCertificateId()->asString(),
                'user_id' => $certificate->getUserId(),
                'object_id' => $certificate->getObjId()
            ]
        );

        return $pdf_base64->scalar;
    }
}
