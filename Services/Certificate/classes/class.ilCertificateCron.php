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

use ILIAS\DI\Container;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateCron extends ilCronJob
{
    public const DEFAULT_SCHEDULE_HOURS = 1;

    protected ?ilLanguage $lng;
    private ?ilCertificateQueueRepository $queueRepository;
    private ?ilCertificateTemplateRepository $templateRepository;
    private ?ilUserCertificateRepository $userRepository;
    private ?ilLogger $logger;
    private ?ilCertificateValueReplacement $valueReplacement;
    private ?ilCertificateObjectHelper $objectHelper;
    private ?Container $dic = null;
    private ?ilSetting $settings;
    private ?ilCronManager $cronManager;

    public function __construct(
        ?ilCertificateQueueRepository $queueRepository = null,
        ?ilCertificateTemplateRepository $templateRepository = null,
        ?ilUserCertificateRepository $userRepository = null,
        ?ilCertificateValueReplacement $valueReplacement = null,
        ?ilLogger $logger = null,
        ?Container $dic = null,
        ?ilLanguage $language = null,
        ?ilCertificateObjectHelper $objectHelper = null,
        ?ilSetting $setting = null,
        ?ilCronManager $cronManager = null
    ) {
        if (null === $dic) {
            global $DIC;
            $dic = $DIC;
        }
        $this->dic = $dic;

        $this->queueRepository = $queueRepository;
        $this->templateRepository = $templateRepository;
        $this->userRepository = $userRepository;
        $this->valueReplacement = $valueReplacement;
        $this->logger = $logger;
        $this->objectHelper = $objectHelper;
        $this->settings = $setting;
        $this->cronManager = $cronManager;

        if ($dic && isset($dic['lng'])) {
            $language = $dic->language();
            $language->loadLanguageModule('certificate');
        }

        $this->lng = $language;
    }

    public function getTitle() : string
    {
        return $this->lng->txt('cert_cron_task_title');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('cert_cron_task_desc');
    }

    public function init() : void
    {
        if (null === $this->dic) {
            global $DIC;
            $this->dic = $DIC;
        }

        $database = $this->dic->database();

        if (null === $this->logger) {
            $this->logger = $this->dic->logger()->cert();
        }

        if (null === $this->cronManager) {
            $this->cronManager = $this->dic->cron()->manager();
        }

        if (null === $this->queueRepository) {
            $this->queueRepository = new ilCertificateQueueRepository($database, $this->logger);
        }

        if (null === $this->templateRepository) {
            $this->templateRepository = new ilCertificateTemplateDatabaseRepository($database, $this->logger);
        }

        if (null === $this->userRepository) {
            $this->userRepository = new ilUserCertificateRepository($database, $this->logger);
        }

        if (null === $this->valueReplacement) {
            $this->valueReplacement = new ilCertificateValueReplacement();
        }

        if (null === $this->objectHelper) {
            $this->objectHelper = new ilCertificateObjectHelper();
        }

        if (null === $this->settings) {
            $this->settings = new ilSetting('certificate');
        }
    }

    public function run() : ilCronJobResult
    {
        $this->init();

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);

        $currentMode = $this->settings->get('persistent_certificate_mode', 'persistent_certificate_mode_cron');
        if ($currentMode !== 'persistent_certificate_mode_cron') {
            $this->logger->warning(sprintf(
                'Will not start cron job, because the mode is not set as cron job. Current Mode in settings: "%s"',
                $currentMode
            ));
            return $result;
        }

        $this->logger->debug('START - Begin with cron job to create user certificates from templates');

        $entries = $this->queueRepository->getAllEntriesFromQueue();

        $status = ilCronJobResult::STATUS_OK;

        $entryCounter = 0;
        $succeededGenerations = [];
        foreach ($entries as $entry) {
            try {
                $succeededGenerations = $this->processEntry(
                    $entryCounter,
                    $entry,
                    $succeededGenerations
                );

                ++$entryCounter;
            } catch (ilInvalidCertificateException $exception) {
                $this->logger->warning($exception->getMessage());
                $this->logger->warning('The user MAY not be able to achieve the certificate based on the adapters settings');
                $this->logger->warning('Due the error, the entry will now be removed from the queue.');

                $this->queueRepository->removeFromQueue($entry->getId());

                continue;
            } catch (ilException $exception) {
                $this->logger->warning($exception->getMessage());
                $this->logger->warning('Due the error, the entry will now be removed from the queue.');

                $this->queueRepository->removeFromQueue($entry->getId());
                continue;
            }
        }

        $result->setStatus($status);
        if (count($succeededGenerations) > 0) {
            $result->setMessage(sprintf(
                'Generated %s certificate(s) in run. Result: %s',
                count($succeededGenerations),
                implode(' | ', $succeededGenerations)
            ));
        } else {
            $result->setMessage('0 certificates generated in current run.');
        }

        return $result;
    }

    public function getId() : string
    {
        return 'certificate';
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    /**
     * @param int                     $entryCounter
     * @param ilCertificateQueueEntry $entry
     * @param array                   $succeededGenerations
     * @return array
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilInvalidCertificateException
     * @throws ilObjectNotFoundException
     */
    public function processEntry(int $entryCounter, ilCertificateQueueEntry $entry, array $succeededGenerations) : array
    {
        if ($entryCounter > 0 && $entryCounter % 10 === 0) {
            $this->cronManager->ping($this->getId());
        }

        $this->logger->debug('Entry found will start of processing the entry');

        /** @var ilCertificateQueueEntry $entry */
        $class = $entry->getAdapterClass();
        $this->logger->debug('Adapter class to be executed "' . $class . '"');

        $placeholderValueObject = new $class();
        if (!$placeholderValueObject instanceof ilCertificatePlaceholderValues) {
            throw new ilException('The given class ' . $class . ' MUST be an instance of ilCertificateCronAdapter and MUST have an accessible namespace. The class map MAY be reloader.');
        }

        $objId = $entry->getObjId();
        $userId = $entry->getUserId();
        $templateId = $entry->getTemplateId();

        $this->logger->debug(sprintf(
            'Fetch certificate template for user id: "%s" and object id: "%s" and template id: "%s"',
            $userId,
            $objId,
            $templateId
        ));

        $template = $this->templateRepository->fetchTemplate($templateId);

        $object = $this->objectHelper->getInstanceByObjId($objId, false);
        if (!$object instanceof ilObject) {
            throw new ilException(sprintf(
                'The given object id: "%s"  could not be referred to an actual object',
                $objId
            ));
        }

        $type = $object->getType();

        $userObject = $this->objectHelper->getInstanceByObjId($userId, false);
        if (!($userObject instanceof ilObjUser)) {
            throw new ilException('The given user id"' . $userId . '" could not be referred to an actual user');
        }

        $this->logger->debug(sprintf(
            'Object type: "%s"',
            $type
        ));

        $certificateContent = $template->getCertificateContent();

        $placeholderValues = $placeholderValueObject->getPlaceholderValues($userId, $objId);

        $this->logger->debug(sprintf(
            'Values for placeholders: "%s"',
            json_encode($placeholderValues, JSON_THROW_ON_ERROR)
        ));

        $certificateContent = $this->valueReplacement->replace(
            $placeholderValues,
            $certificateContent
        );

        $thumbnailImagePath = $template->getThumbnailImagePath();
        $userCertificate = new ilUserCertificate(
            $template->getId(),
            $objId,
            $type,
            $userId,
            $userObject->getFullname(),
            $entry->getStartedTimestamp(),
            $certificateContent,
            json_encode($placeholderValues, JSON_THROW_ON_ERROR),
            null,
            $template->getVersion(),
            ILIAS_VERSION_NUMERIC,
            true,
            $template->getBackgroundImagePath(),
            $thumbnailImagePath
        );

        $persistedUserCertificate = $this->userRepository->save($userCertificate);

        $succeededGenerations[] = implode('/', [
            'obj_id: ' . $objId,
            'usr_id: ' . $userId
        ]);

        $this->queueRepository->removeFromQueue($entry->getId());

        $this->dic->event()->raise(
            'Services/Certificate',
            'certificateIssued',
            ['certificate' => $persistedUserCertificate]
        );

        return $succeededGenerations;
    }
}
