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

namespace ILIAS\Certificate\API;

use ILIAS\Certificate\API\Data\UserCertificateDto;
use ILIAS\Certificate\API\Filter\UserDataFilter;
use ILIAS\Certificate\API\Repository\UserDataRepository;
use ilCertificateQueueEntry;
use ilCertificateCron;
use ilCertificateTypeClassMap;
use ilCertificateTemplateRepository;
use ilLogger;
use ilCertificateTemplateDatabaseRepository;
use ilCertificateQueueRepository;
use ilCronConstants;
use ilSetting;
use ilObjectDataCache;
use ilCertificateTemplate;
use ilCertificateConsumerNotSupported;
use ilCouldNotFindCertificateTemplate;
use ilInvalidCertificateException;
use ilCertificateIssuingObjectNotFound;
use ilCertificateOwnerNotFound;

class UserCertificateAPI implements UserCertificateApiInterface
{
    private readonly UserDataRepository $user_data_repository;
    private readonly ilCertificateTemplateRepository $template_repository;
    private readonly ilCertificateQueueRepository $queue_repository;
    private readonly ilLogger $logger;
    private readonly ilObjectDataCache $object_data_cache;

    public function __construct(
        ?UserDataRepository $user_data_repository = null,
        ?ilCertificateTemplateRepository $template_repository = null,
        ?ilCertificateQueueRepository $queue_repository = null,
        private readonly ilCertificateTypeClassMap $type_class_map = new ilCertificateTypeClassMap(),
        ?ilLogger $logger = null,
        ?ilObjectDataCache $object_data_cache = null,
    ) {
        global $DIC;

        $this->logger = $logger ?? $DIC->logger()->cert();
        $this->object_data_cache = $object_data_cache ?? $DIC['ilObjDataCache'];
        $this->user_data_repository = $user_data_repository ?? new UserDataRepository(
            $DIC->database(),
            $DIC->ctrl()
        );
        $this->template_repository = $template_repository ?? new ilCertificateTemplateDatabaseRepository(
            $DIC->database(),
            $this->logger
        );
        $this->queue_repository = $queue_repository ?? new ilCertificateQueueRepository(
            $DIC->database(),
            $this->logger
        );
    }

    public function getUserCertificateData(UserDataFilter $filter, array $ilCtrlStack = []): array
    {
        return $this->user_data_repository->getUserData($filter, $ilCtrlStack);
    }

    public function getUserCertificateDataMaxCount(UserDataFilter $filter): int
    {
        return $this->user_data_repository->getUserCertificateDataMaxCount($filter);
    }

    public function certificateCriteriaMetForGivenTemplate(int $usr_id, ilCertificateTemplate $template): void
    {
        if (!$template->isCurrentlyActive()) {
            $this->logger->debug(sprintf(
                'Did not trigger certificate achievement for inactive template: usr_id: %s/obj_id: %s/type: %s/template_id: %s',
                $usr_id,
                $template->getObjId(),
                $template->getObjType(),
                $template->getId()
            ));
            return;
        }

        $this->processEntry(
            $usr_id,
            $template
        );
    }

    public function certificateCriteriaMet(int $usr_id, int $obj_id): void
    {
        $type = $this->object_data_cache->lookupType($obj_id);
        if (!$this->type_class_map->typeExistsInMap($type)) {
            throw new ilCertificateConsumerNotSupported(sprintf(
                "Oject type '%s' is not supported by the certificate component!",
                $type
            ));
        }

        $template = $this->template_repository->fetchCurrentlyActiveCertificate($obj_id);

        $this->certificateCriteriaMetForGivenTemplate($usr_id, $template);
    }

    public function isActiveCertificateTemplateAvailableFor(int $obj_id): bool
    {
        try {
            return $this->template_repository->fetchCurrentlyActiveCertificate($obj_id)->isCurrentlyActive();
        } catch (ilCouldNotFindCertificateTemplate) {
            return false;
        }
    }

    /**
     * @throws ilCertificateIssuingObjectNotFound
     * @throws ilCertificateOwnerNotFound
     * @throws ilCouldNotFindCertificateTemplate
     * @throws ilInvalidCertificateException
     */
    private function processEntry(
        int $userId,
        ilCertificateTemplate $template
    ): void {
        $this->logger->debug(sprintf(
            'Trigger persisting certificate achievement for: usr_id: %s/obj_id: %s/type: %s/template_id: %s',
            $userId,
            $template->getObjId(),
            $template->getObjType(),
            $template->getId()
        ));

        $entry = new ilCertificateQueueEntry(
            $template->getObjId(),
            $userId,
            $this->type_class_map->getPlaceHolderClassNameByType($template->getObjType()),
            ilCronConstants::IN_PROGRESS,
            $template->getId(),
            time()
        );

        $mode = (new ilSetting('certificate'))->get(
            'persistent_certificate_mode',
            'persistent_certificate_mode_cron'
        );
        if ($mode === 'persistent_certificate_mode_instant') {
            $cronjob = new ilCertificateCron();
            $cronjob->init();
            $cronjob->processEntry(0, $entry, []);
            return;
        }

        $this->queue_repository->addToQueue($entry);
    }
}
