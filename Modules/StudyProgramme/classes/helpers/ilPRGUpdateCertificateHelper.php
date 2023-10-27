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

trait ilPRGUpdateCertificateHelper
{
    protected \ilLogger $log;
    protected \ilCertificateTemplateRepository $certificate_template_repository;
    protected \ilCertificateTypeClassMap $certificate_type_class_map;
    protected \ilCertificateQueueRepository $certificate_queue_repository;
    protected \ilSetting $certificate_settings;
    protected \ilCertificateCron $certificate_cron;

    public function updateCertificateForPrg(
        int $assignment_id,
        int $acting_usr_id,
        \ilPRGMessageCollection $err_collection = null
    ): void {
        $class_name = $this->certificate_type_class_map->getPlaceHolderClassNameByType('prg');
        try {
            $template = $this->certificate_template_repository->fetchCurrentlyActiveCertificate(
                $assignment_id
            );

            $this->processEntry(
                $class_name,
                $assignment_id,
                $acting_usr_id,
                $template,
                $err_collection
            );
        } catch (\ilException $exception) {
            $this->log->warning($exception->getMessage());
        }
    }

    private function processEntry(
        string $class_name,
        int $assignment_id,
        int $usr_id,
        \ilCertificateTemplate $template,
        ilPRGMessageCollection $err_collection
    ): void {
        $entry = new \ilCertificateQueueEntry(
            $assignment_id,
            $usr_id,
            $class_name,
            \ilCronConstants::IN_PROGRESS,
            (int) $template->getId(),
            time()
        );

        $mode = $this->certificate_settings->get(
            'persistent_certificate_mode',
            'persistent_certificate_mode_cron'
        );

        if ($mode === 'persistent_certificate_mode_instant') {
            $this->certificate_cron->init();
            $this->certificate_cron->processEntry(0, $entry, []);
            return;
        }

        $this->certificate_queue_repository->addToQueue($entry);
    }
}
