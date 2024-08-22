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

namespace ILIAS\Test\ExportImport;

use ILIAS\UI\Factory as UIFactory;

class CertificateExport implements ExportAsAttachment
{
    public function __construct(
        private readonly \ilObjTest $object,
        private readonly \ilDBInterface $db,
        private readonly UIFactory $ui_factory
    ) {
    }

    public function deliver(): void
    {

    }

    private function exportCertificateArchive(): void
    {
        $global_certificate_prerequisites = new ilCertificateActiveValidator();
        if (!$global_certificate_prerequisites->validate()) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $pathFactory = new ilCertificatePathFactory();
        $objectId = $this->object->getId();
        $zipAction = new ilUserCertificateZip(
            $objectId,
            $pathFactory->create($this->object)
        );

        $archive_dir = $zipAction->createArchiveDirectory();

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $ilUserCertificateRepository = new ilUserCertificateRepository($this->db, $this->logger);
        $pdfGenerator = new ilPdfGenerator($ilUserCertificateRepository);

        $total_users = $this->object->evalTotalPersonsArray();
        if (count($total_users) === 0) {
            $this->outEvaluation([
                $this->ui_factory->messageBox()->info(
                    $this->lng->txt('export_cert_no_users')
                )
            ]);
            return;
        }

        $certValidator = new ilCertificateDownloadValidator();

        $num_pdfs = 0;
        $ignored_usr_ids = [];
        $failed_pdf_generation_usr_ids = [];
        foreach ($total_users as $active_id => $name) {
            $user_id = ilObjTest::_getUserIdFromActiveId($active_id);

            if (!$certValidator->isCertificateDownloadable($user_id, $objectId)) {
                $this->logger->debug(
                    sprintf(
                        'No certificate available for user %s in test %s ' .
                        '(Check if: ilServer is enabled / Certificates are enabled globally / ' .
                        'A Certificate is issued for the user)',
                        $user_id,
                        $objectId
                    )
                );
                $ignored_usr_ids[] = $user_id;
                continue;
            }

            $pdfAction = new ilCertificatePdfAction(
                $pdfGenerator,
                new ilCertificateUtilHelper(),
                $this->lng->txt('error_creating_certificate_pdf')
            );

            $pdf = $pdfAction->createPDF($user_id, $objectId);
            if ($pdf !== '') {
                $zipAction->addPDFtoArchiveDirectory($pdf, $archive_dir, $user_id . "_" . str_replace(
                    " ",
                    "_",
                    ilFileUtils::getASCIIFilename($name)
                ) . ".pdf");
                ++$num_pdfs;
            } else {
                $this->logger->error(
                    sprintf(
                        'The certificate service could not create a PDF for user %s and test %s',
                        $user_id,
                        $objectId
                    )
                );
                $failed_pdf_generation_usr_ids[] = $user_id;
            }
        }

        $components = [];

        if ($num_pdfs > 0) {
            try {
                $zipAction->zipCertificatesInArchiveDirectory($archive_dir, true);
            } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
                $components[] = $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('error_creating_certificate_zip_empty')
                );
            }
        }

        if ($ignored_usr_ids !== []) {
            $user_logins = array_map(
                static fn($usr_id): string => ilObjUser::_lookupLogin((int) $usr_id),
                $ignored_usr_ids
            );
            if (count($ignored_usr_ids) === 1) {
                $components[] = $this->ui_factory->messageBox()->info(sprintf(
                    $this->lng->txt('export_cert_ignored_for_users_s'),
                    implode(', ', $user_logins)
                ));
            } else {
                $components[] = $this->ui_factory->messageBox()->info(sprintf(
                    $this->lng->txt('export_cert_ignored_for_users_p'),
                    count($ignored_usr_ids),
                    implode(', ', $user_logins)
                ));
            }
        }

        if ($failed_pdf_generation_usr_ids !== []) {
            $user_logins = array_map(
                static fn($usr_id): string => ilObjUser::_lookupLogin((int) $usr_id),
                $failed_pdf_generation_usr_ids
            );
            if (count($failed_pdf_generation_usr_ids) === 1) {
                $components[] = $this->ui_factory->messageBox()->info(sprintf(
                    $this->lng->txt('export_cert_failed_for_users_s'),
                    implode(', ', $user_logins)
                ));
            } else {
                $components[] = $this->ui_factory->messageBox()->info(sprintf(
                    $this->lng->txt('export_cert_failed_for_users_p'),
                    count($ignored_usr_ids),
                    implode(', ', $user_logins)
                ));
            }
        }
    }
}
