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

use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\FileDelivery\Services as FileDeliveryServices;

class CertificateExport implements Exporter
{
    public function __construct(
        private readonly Language $lng,
        private readonly \ilDBInterface $db,
        private \ilGlobalTemplateInterface $tpl,
        private FileDeliveryServices $file_delivery,
        private readonly \ilObjTest $object
    ) {
    }

    public function deliver(): void
    {
        if (($path = $this->write()) === null) {
            return;
        }
        $this->file_delivery->legacyDelivery()->attached(
            $path,
            null,
            null,
            true
        );
        $this->file_delivery->deliver();
    }

    public function write(): ?string
    {
        $global_certificate_prerequisites = new \ilCertificateActiveValidator();
        if (!$global_certificate_prerequisites->validate()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'));
            return null;
        }

        $path_factory = new \ilCertificatePathFactory();
        $obj_id = $this->object->getId();
        $zip_action = new \ilUserCertificateZip(
            $obj_id,
            $path_factory->create($this->object)
        );

        $archive_dir = $zip_action->createArchiveDirectory();

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $certificate_repo = new \ilUserCertificateRepository($this->db, $this->logger);
        $pdf_generator = new \ilPdfGenerator($certificate_repo);

        $total_users = $this->object->evalTotalPersonsArray();
        if (count($total_users) === 0) {
            $this->tpl->setOnScreenMessage(
                'info',
                $this->lng->txt('export_cert_no_users'),
                true
            );
            return null;
        }

        $cert_validator = new \ilCertificateDownloadValidator();

        $num_pdfs = 0;
        $ignored_usr_ids = [];
        $failed_pdf_generation_usr_ids = [];
        foreach ($total_users as $active_id => $name) {
            $user_id = \ilObjTest::_getUserIdFromActiveId($active_id);

            if (!$cert_validator->isCertificateDownloadable($user_id, $obj_id)) {
                $this->logger->debug(
                    sprintf(
                        'No certificate available for user %s in test %s ' .
                        '(Check if: \ilServer is enabled / Certificates are enabled globally / ' .
                        'A Certificate is issued for the user)',
                        $user_id,
                        $obj_id
                    )
                );
                $ignored_usr_ids[] = $user_id;
                continue;
            }

            $pdf_action = new \ilCertificatePdfAction(
                $pdf_generator,
                new \ilCertificateUtilHelper(),
                $this->lng->txt('error_creating_certificate_pdf')
            );

            $pdf = $pdf_action->createPDF($user_id, $obj_id);
            if ($pdf !== '') {
                $zip_action->addPDFtoArchiveDirectory(
                    $pdf,
                    $archive_dir,
                    $user_id . '_' . str_replace(' ', '_', \ilFileUtils::getASCIIFilename($name)) . '.pdf'
                );
                ++$num_pdfs;
            } else {
                $this->logger->error(
                    sprintf(
                        'The certificate service could not create a PDF for user %s and test %s',
                        $user_id,
                        $obj_id
                    )
                );
                $failed_pdf_generation_usr_ids[] = $user_id;
            }
        }

        $components = [];

        $zip_file_dir = null;
        if ($num_pdfs > 0) {
            try {
                $zip_file_dir = $zip_action->zipCertificatesInArchiveDirectory($archive_dir, false);
            } catch (\ILIAS\Filesystem\Exception\IOException $e) {
                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('error_creating_certificate_zip_empty'),
                    true
                );
            }
        }

        if ($ignored_usr_ids !== []) {
            $user_logins = array_map(
                static fn($usr_id): string => \ilObjUser::_lookupLogin((int) $usr_id),
                $ignored_usr_ids
            );
            if (count($ignored_usr_ids) === 1) {
                $components[] = sprintf(
                    $this->lng->txt('export_cert_ignored_for_users_s'),
                    implode(', ', $user_logins)
                );
            } else {
                $components[] = sprintf(
                    $this->lng->txt('export_cert_ignored_for_users_p'),
                    count($ignored_usr_ids),
                    implode(', ', $user_logins)
                );
            }
        }

        if ($failed_pdf_generation_usr_ids !== []) {
            $user_logins = array_map(
                static fn($usr_id): string => \ilObjUser::_lookupLogin((int) $usr_id),
                $failed_pdf_generation_usr_ids
            );
            if (count($failed_pdf_generation_usr_ids) === 1) {
                $components[] = sprintf(
                    $this->lng->txt('export_cert_failed_for_users_s'),
                    implode(', ', $user_logins)
                );
            } else {
                $components[] = sprintf(
                    $this->lng->txt('export_cert_failed_for_users_p'),
                    count($ignored_usr_ids),
                    implode(', ', $user_logins)
                );
            }
        }

        if ($components !== []) {
            $this->tpl->setOnScreenMessage('info', implode('<br>', $components), true);
        }

        return $zip_file_dir;
    }
}
